<?php
namespace ReThumbAdvanced;
?>

<form method="POST" name="rta_settings_form" id="rta_settings_form" enctype="multipart/form-data">
<section class='image_sizes'>
  <div class='container'>
  <h4><?php _e('Image Sizes','regenerate-thumbnails-advanced'); ?>   <input type="button" name="btn_add_image_size" id="btn_add_image_size" class="btn_add_more" value="<?php _e('Add New Size','regenerate-thumbnails-advanced'); ?>"  /></h4>

      <div class='table imagesizes'>
        <?php
        $image_sizes = $view->custom_image_sizes;
        $headershow = (count($image_sizes) == 0) ? ' rta_hidden' : '';
        ?>

              <div class='header  <?php echo $headershow ?>'>

                          <span><b><?php _e('Public Name','regenerate-thumbnails-advanced'); ?></b></span>
                          <span><b><?php _e('Max. Width','regenerate-thumbnails-advanced'); ?></b></span>
                          <span><b><?php _e('Max. Height','regenerate-thumbnails-advanced'); ?></b></span>
                          <span><b><?php _e('Cropping','regenerate-thumbnails-advanced'); ?></b></span>
                          <span><b><?php _e('Image Size Name','regenerate-thumbnails-advanced'); ?></b></span>
                          <span>&nbsp;</span>
              </div>

                      <?php
                      if (isset($image_sizes['name'])){
                        for($i=0;$i<count($image_sizes['name']);$i++){ ?>

                      <?php
                        $rowid = uniqid();

                      ?>
                    <div id="<?php echo $rowid;?>" class='row'>

                            <span><input type="text" name="image_sizes[pname][]" class="image_sizes_pname" value="<?php echo $image_sizes['pname'][$i];?>" placeholder="<?php _e('Name','regenerate-thumbnails-advanced'); ?>" /></span>

                            <span><input type="number" min="0" name="image_sizes[width][]" class="image_sizes_width tiny" value="<?php echo $image_sizes['width'][$i];?>" placeholder="<?php _e('Width','regenerate-thumbnails-advanced'); ?>" /> px </span>

                            <span> <input type="number" min="0" name="image_sizes[height][]" class="image_sizes_height tiny" value="<?php echo $image_sizes['height'][$i];?>" placeholder="<?php _e('Height','regenerate-thumbnails-advanced'); ?>" /> px </span>

                            <span>  <select name="image_sizes[cropping][]" class="image_sizes_cropping">
                              <?php echo $this->cropOptions($image_sizes['cropping'][$i]); ?>
                              </select>
                            </span>

                            <span >
                              <input type="text" readonly name="image_sizes[name][]" class="image_sizes_name" value="<?php echo $image_sizes['name'][$i];?>" placeholder="<?php _e('Image Size name','regenerate-thumbnails-advanced'); ?>" />

                            </span>
                            <span>
                                  <button class="btn_remove_row" type="button" name="btn_remove_image_size_row"><span class='dashicons dashicons-no'>&nbsp;</span></button>
                            </span>
                    </div>  <!-- row -->
                      <?php }}?>
                      <div class='row proto'>
                            <span><input type="text" name="image_sizes[pname][]" class="image_sizes_pname" value="" placeholder="<?php _e('Name','regenerate-thumbnails-advanced'); ?>" /></span>
                            <span><input type="number" name="image_sizes[width][]" class="image_sizes_width tiny" value="" placeholder="<?php _e('Width','regenerate-thumbnails-advanced'); ?>" /> px </span>
                            <span> <input type="number" name="image_sizes[height][]" class="image_sizes_height tiny" value="" placeholder="<?php _e('Height','regenerate-thumbnails-advanced'); ?>" /> px </span>
                            <span><select name="image_sizes[cropping][]" class="image_sizes_cropping">
                              <?php echo $this->cropOptions(); ?>
                              </select>
                            </span>
                            <span>
                              <input type="text" readonly  name="image_sizes[name][]" class="image_sizes_name" value="" placeholder="<?php _e('Image Size name','regenerate-thumbnails-advanced'); ?>" />
                            </span>
                            <span>
                                <button class="btn_remove_row" type="button" name="btn_remove_image_size_row"><span class='dashicons dashicons-no'>&nbsp;</span></button>

                            </span>
                      </div> <!-- row -->
          </div> <!-- table -->

        </div>
</section>

<section class='thumbnail_select'>
  <div class='container'>

      <div class='option'>
        <label><?php _e('Regenerate these thumbnails:', 'regenerate-thumbnails-advanced') ?></label>
        <div class='checkbox-list'>
            <?php echo $this->generateImageSizeOptions($view->process_image_sizes); ?>
        </div>
        <div class='select-options'><span class='select' data-action='select' data-target='regenerate_sizes'><?php _e('Select All', 'regenerate-thumbnails-advanced'); ?></span>
          <span class='deselect' data-action='deselect' data-target='regenerate_sizes'><?php _e('Deselect All', 'regenerate-thumbnails-advanced'); ?></span>
          <div class='note'>
            <ul>
            <li><?php _e('Select the thumbnails you want to regenerate. ', 'regenerate-thumbnails-advanced') ?></li>
            <li><?php printf(__('%s Don\'t redo existing %s  - prevent regenerating images that already have these sizes as a thumbnail file. ', 'regenerate-thumbnails-advanced'), '<strong>','</strong>'); ?></li>
           </ul>
         </div>

        </div>

      </div> <!-- option -->

    <div class='option'>
      <label><?php _e('Default JPEG Quality','regenerate-thumbnails-advanced'); ?></label>
      <input type="number" name="jpeg_quality" id="jpeg_quality" value="<?php echo $view->jpeg_quality ?>" onchange="javascript: rta_save_image_sizes();" />
    </div>
</div> <!-- container -->
</section>

<section class='form_controls'>
  <div class='container'>
    <button type='submit' name='save_settings' disabled class='disabled'><?php _e('Save Settings', 'regenerate-thumbnails-advanced'); ?> </button>
    <span class="save_indicator"><?php _e('Saving', 'regenerate-thumbnails-advanced'); ?> </span>


  </div>
</section>
</form>
