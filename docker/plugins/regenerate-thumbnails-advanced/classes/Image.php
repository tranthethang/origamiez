<?php
namespace ReThumbAdvanced;
use \ReThumbAdvanced\ShortPixelLogger\ShortPixelLogger as Log;


class Image
{
  protected $id;

  protected $is_image = true;
  protected $does_exist = true;
  protected $do_cleanup =false;
  protected $do_metacheck = false;

  protected $filePath;
  protected $fileUri;
  protected $fileDir;
  protected $metadata = array();

  protected $persistentMeta = array();
  protected $regeneratedSizes = array();

  protected $customThumbSuffixes =  array('_c', '_tl', '_tr', '_br', '_bl');

  public function __construct($image_id)
  {
      $this->id = $image_id;

      if (function_exists('wp_get_original_image_path')) // WP 5.3+
      {
        $this->filePath = wp_get_original_image_path($image_id);

        /** When this function returns false it's possible the post_mime_type in wp_posts table got corrupted. If the file is displayable image,
        * attempt to fix this issue, then reget the item for further processing */
        if ($this->filePath === false)
        {
          $this->filePath = get_attached_file($image_id);

					if ($this->filePath === false)
					{
						RTA()->ajax()->add_status('file_missing', array('name' => basename($image_id)) );
						return false;
					}

          if (file_is_displayable_image($this->filePath))
          {
            $this->fixMimeType($image_id);
            $this->filePath = wp_get_original_image_path($image_id);
          }
        }
      }
      else
        $this->filePath = get_attached_file($image_id);
      $this->fileDir = trailingslashit(pathinfo($this->filePath,  PATHINFO_DIRNAME));

      if (function_exists('wp_get_original_image_url')) // WP 5.3+
        $this->fileUri = wp_get_original_image_url($image_id);
      else
        $this->fileUri = wp_get_attachment_url($image_id);

      if (!file_exists($this->filePath))
        $this->does_exist = false;

      if (! file_is_displayable_image($this->filePath)) // this is based on getimagesize
			{
          $this->is_image = false;
			}
      $this->metadata = wp_get_attachment_metadata($image_id);

  /*    $is_image_mime = wp_attachment_is('image', $image_id); // this is based on post mime.
      if (! $is_image_mime && $this->is_image )
      {
        $this->fixMimeType($image_id);
      }
*/
  }

  public function regenerate()
  {
    if (RTA()->process()->doRemoveThumbnails())
    {
      $this->setCleanUp(true);
      Log::addDebug('Image thumbnails will be cleaned');
    }

    if(RTA()->process()->doDeleteLeftMeta() && ! $this->exists() )  {
				$post = get_post($this->id);

				// Ugly exception for a plugin that doesn't play by the rules
				// @todo add a mechanism so we can cater for more bad plugins here.
				if ($post->post_mime_type == 'video/videopress')
				{
					return false;
				}
        Log::addDebug('Deleting post ' . $this->id);
        wp_delete_post($this->id, true);

    }

    if ($this->isImage() ) {

        @set_time_limit(900);
        do_action('shortpixel-thumbnails-before-regenerate', $this->id);

        //use the original main image if exists
        $backup = apply_filters('shortpixel_get_backup', $this->getPath() );
        if($backup && $backup !== $this->filePath) {
            Log::addDebug('Retrieving SPIO backups for process');
            copy($this->getPath(), $backup . "_optimized_" . $this->id);
            copy($backup, $this->getPath());
        }

        add_filter('intermediate_image_sizes_advanced', array($this, 'capture_generate_sizes'));
        // RTA should never touch source files. This happens when redoing scaling. This would also be problematic in combination with optimisers. Disable scaling when doing thumbs.
        add_filter('big_image_size_threshold', array($this, 'disable_scaling'));

        $new_metadata = wp_generate_attachment_metadata($this->id, $this->filePath);

        remove_filter('intermediate_image_sizes_advanced', array($this, 'capture_generate_sizes'));
        remove_filter('big_image_size_threshold', array($this, 'disable_scaling'));

        Log::addDebug('New Attachment metadata generated');
        //restore the optimized main image
        if($backup && $backup !== $this->filePath) {
            rename($backup . "_optimized_" . $this->id, $this->filePath);
        }

        //get the attachment name
        if (is_wp_error($new_metadata)) {

          RTA()->ajax()->add_status('error_metadata', array('name' => basename($this->filePath) ));
        }
        else if (empty($new_metadata)) {
            Log::addDebug('File missing - New metadata returned empty', array($new_metadata, $this->fileUri,$this->filePath ));
            RTA()->ajax()->add_status('file_missing', array('name' => basename($this->fileUri) ));
        } else {

            // going for the save.
            $original_meta = $this->getMetaData();
            $result = $this->saveNewMeta($new_metadata); // this here calls the regeneration.
            Log::addDebug('Result :', $result);

            $is_a_bulk = false; // not a bulk in the SPIO sense ( directly optimize )
            $regenSizes = isset($new_metadata['sizes']) ? $new_metadata['sizes'] : array();

            // Do not send if nothing was regenerated, otherwise SP thinks all needs to be redone
            if (count($regenSizes) > 0)
            {
              do_action('shortpixel-thumbnails-regenerated', $this->id, $original_meta, $regenSizes, $is_a_bulk);
            }
            $last_success_url = $this->fileUri;

        }
      //  $imageUrl = $this->fileUri;
        //$logstatus = 'Processed';
      //  $thumb = wp_get_attachment_thumb_url($this->id);
        RTA()->ajax()->add_status('regenerate_success', array('thumb' => $last_success_url));

    } else {

          $debug_filename = (strlen($this->fileUri) > 0) ? $this->fileUri : $this->filePath;
          if ($this->does_exist) // Existing files, not image, can be attachments, zipfiles, pdf etc. Fail silently.
          {
            $mime = get_post_mime_type($this->id);
            if (strpos($mime, 'image') !== false)
              RTA()->ajax()->add_status('not_image', array('name' => $debug_filename));
          }
          else
          {
            Log::addDebug('File missing - Current Image reported as not an image', array($this->filePath) );
            RTA()->ajax()->add_status('file_missing', array('name' => basename($debug_filename)) );
          }

          return false;
    }

    return true;
  }

  // Todo before doing this, function to remove thumbnails need to run somehow, without killing all.
  public function saveNewMeta($updated_meta)
  {
      if (count($this->persistentMeta) > 0)
      {
        foreach($this->persistentMeta as $rsize => $add)
        {
          $updated_meta['sizes'][$rsize] = $add;
        }
      }

      /* Retain in metadata main categories, if they are not set in the new metadata.
      *  This is for custom data that may be set by others, but will be removed upon regen.
      *  Of the main categories (sizes, width, file etc ) they are fixed format, so should always be present, regardless of content.
      */
      foreach($this->metadata as $key => $data)
      {
        if (! isset($updated_meta[$key]))
        {
          $updated_meta[$key] = $data;
        }
      }

      $result = array();

      if ($this->do_metacheck && isset($updated_meta['sizes']))
      {
        Log::addDebug('Do metaCheck now for ' . $this->id);
        foreach($updated_meta['sizes'] as $size => $sizedata)
        {
           $thumbfile = $this->getDir() . $sizedata['file'];
           if (! file_exists($thumbfile))
           {
             Log::addDebug('Thumbfile not existing. Unsetting this size', array($size, $thumbfile, $this->id));
             unset($updated_meta['sizes'][$size]);
           }
        }
      }

      $result['update'] = wp_update_attachment_metadata($this->id, $updated_meta);
      $this->metadata = wp_get_attachment_metadata($this->id);

      if ($this->do_cleanup)
      {
        $result = $this->clean($result);
      }

      return $result;
  }

  public function disable_scaling()
  {
     return false;
  }

  public function capture_generate_sizes($full_sizes)
  {
      $do_regenerate_sizes = RTA()->admin()->getOption('process_image_sizes'); // $this->viewControl->process_image_sizes; // to images to be regenerated.
      $process_options = RTA()->admin()->getOption('process_image_options'); // $this->viewControl->process_image_options; // the setting options for each size.

      // imageMetaSizes is sizeName => Data based array of WP metadata.
      $imageMetaSizes = $this->getCurrentSizes();

      $prevent_regen = array();
      foreach($do_regenerate_sizes as $rsize)
      {
        // 1. Check if size exists, if not, needs generation anyhow.
        if (! isset($imageMetaSizes[$rsize]))
        {
          Log::addDebug("Image Meta size setting missing - $rsize ");
          continue;
        }

        // 2. Check meta info (file) from the current meta info we have.
        $metaSize = $imageMetaSizes[$rsize];
        $overwrite = isset($process_options[$rsize]['overwrite_files']) ? $process_options[$rsize]['overwrite_files'] : false; // 3. Check if we keep or overwrite.

         if (! $overwrite)
         {
          // thumbFile is RELATIVE. So find dir via main image.
           $thumbFile = $this->getDir() . $metaSize['file'];
           //Log::addDebug('Preventing overwrite of - ' . $thumbFile);
           if (file_exists($thumbFile)) // 4. Check if file is really there
           {
              $prevent_regen[] = $rsize;
              // Add to current Image the metaSize since it will be dropped by the metadata redoing.
              Log::addDebug('File exists on ' . $rsize . ' ' . $thumbFile . '  - skipping regen - prevent overwrite');
              $this->addPersistentMeta($rsize, $metaSize);
           }
         }
      }


      // 5. Drop the 'not to be' regen. images from the sizes so it will not process.
      $do_regenerate_sizes = array_diff($do_regenerate_sizes, $prevent_regen);
      Log::addDebug('Sizes going for regen - ' . count($do_regenerate_sizes) );

      /* 6. If metadata should be cleansed of undefined sizes, remove them from the imageMetaSizes
      *   This is for sizes that are -undefined- in total by system sizes.
      */
      if (RTA()->process()->doCleanMetadata())
      {
          $system_sizes = RTA()->admin()->getOption('system_image_sizes'); //$this->viewControl->system_image_sizes;

          $not_in_system = array_diff( array_keys($imageMetaSizes), array_keys($system_sizes) );
          if (count($not_in_system) > 0)
            Log::addDebug('Cleaning not in system', $not_in_system);

          foreach($not_in_system as $index => $unset)
          {
            unset($imageMetaSizes[$unset]);
          }
      }

      // 7. If unused thumbnails are not set for delete, keep the metadata intact.
      if (! RTA()->process()->doRemoveThumbnails() )
      {
        $other_meta = array_diff( array_keys($imageMetaSizes), $do_regenerate_sizes, $prevent_regen);
        if (count($other_meta) > 0)
          Log::addDebug('Image sizes not selected, but not up for deletion', $other_meta);

        foreach($other_meta as $size)
        {
           if (isset($imageMetaSizes[$size]))
             $this->addPersistentMeta($size, $imageMetaSizes[$size]);
        }
      }

      $returned_sizes = array();
      foreach($full_sizes as $key => $data)
      {
          if (in_array($key, $do_regenerate_sizes))
          {
            $returned_sizes[$key] = $data;
          }
      }

      $this->setRegeneratedSizes($do_regenerate_sizes);
      return $returned_sizes;
  }


  /** This function tries to find related thumbnails to the current image. If there are not in metadata after our process, assume cleanup.
  * This removes thumbnail files.
  * See ShortPixel Image Optimiser's findThumbs method
  **
  **/
  public function clean()
  {
    $mainFile = $this->filePath;
    $exclude = array();

    if (isset($this->metadata['sizes']))
    {
      foreach($this->metadata['sizes'] as $size => $data)
      {
         $exclude[] = $data['file'];
      }
    }
    $result['excluding'] = $exclude;

    $ext = pathinfo($mainFile, PATHINFO_EXTENSION); // file extension
    $base = substr($mainFile, 0, strlen($mainFile) - strlen($ext) - 1);
    $pattern = '/' . preg_quote($base, '/') . '-\d+x\d+\.'. $ext .'/';
    $thumbsCandidates = @glob($base . "-*." . $ext);

    $thumbs = array();
    if(is_array($thumbsCandidates)) {
        foreach($thumbsCandidates as $th) {
            if(preg_match($pattern, $th)) {
                $thumbs[]= $th;
            }
        }
        if( count($this->customThumbSuffixes)
           && !(   is_plugin_active('envira-gallery/envira-gallery.php')
                || is_plugin_active('soliloquy/soliloquy.php')
                || is_plugin_active('soliloquy-lite/soliloquy-lite.php'))){
            foreach ($this->customThumbSuffixes as $suffix){
                $pattern = '/' . preg_quote($base, '/') . '-\d+x\d+'. $suffix . '\.'. $ext .'/';
                foreach($thumbsCandidates as $th) {
                    if(preg_match($pattern, $th)) {
                        $thumbs[]= $th;
                    }
                }
            }
        }
    }

    $result['removed'] = array();

    foreach($thumbs as $thumb) {
        if($thumb === $mainFile)
        {
          continue;
        }
        if (in_array(basename($thumb), $exclude))
        {
          continue;
        }

        if($thumb !== $mainFile) {
          $status = @unlink($thumb);
          $result['removed'][] = $thumb . "($status)";
        }
    }

    return $result;
  }

  public function exists()
  {
    return $this->does_exist;
  }

  public function isImage()
  {
      return $this->is_image;
  }

  public function getUri()
  {
    return $this->fileUri;
  }

  public function getPath()
  {
    return $this->filePath;
  }

  public function getDir()
  {
    return $this->fileDir;
  }

  public function getMetaData()
  {
    return $this->metadata;
  }



  public function getCurrentSizes()
  {
    return (isset($this->metadata['sizes'])) ? $this->metadata['sizes'] : array();
  }

  public function addPersistentMeta($size, $data)
  {
      $this->persistentMeta[$size] = $data;
  }

  public function setRegeneratedSizes($sizes)
  {
    $this->regeneratedSizes = $sizes;
  }

  public function setCleanUp($clean)
  {
    $this->do_cleanup = $clean;
  }

  public function setMetaCheck($bool)
  {
    $this->do_metacheck = $bool;
  }

  public function fixMimeType($image_id)
  {
      $post = get_post($image_id);

      if ($post->post_mime_type == '')
      {
        $mime = wp_get_image_mime($this->filePath);
        $post->post_mime_type = $mime;
        Log::addDebug('Fixing File Mime for ' . $this->filePath . ' new MIME - ' . $mime);
        wp_update_post($post);
      }
  }

}
