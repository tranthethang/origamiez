<?php
namespace ReThumbAdvanced;


?>

<form method="post" name="rtaform_process" id="rtaform_process" class="frm_rta" enctype="multipart/form-data">
<section class='period'>
  <div class='container'>
    <div class='option'>
      <label><?php _e('Regenerate period:','regenerate-thumbnails-advanced'); ?></label>
      <select name='period' class="timeDropdownSelect" id="">
          <option value="0"><?php _e('All','regenerate-thumbnails-advanced'); ?></option>
          <option value="1"><?php _e('Past Day','regenerate-thumbnails-advanced'); ?></option>
          <option value="2"><?php _e('Past Week','regenerate-thumbnails-advanced'); ?></option>
          <option value="3"><?php _e('Past Month','regenerate-thumbnails-advanced'); ?></option>
          <option value="4"><?php _e('Past 3 Months','regenerate-thumbnails-advanced'); ?></option>
          <option value="5"><?php _e('Past 6 Months','regenerate-thumbnails-advanced'); ?></option>
          <option value="6"><?php _e('Past Year','regenerate-thumbnails-advanced'); ?></option>
      </select>
    </div>
    <div class='option'>
      <label for='regenonly_featured'><?php _e(sprintf('Regenerate %sonly%s Featured Images', '<strong>','</strong>'),'regenerate-thumbnails-advanced');  ?></label>
      <input type='checkbox' id='regenonly_featured' name="regenonly_featured" value="1">
    </div>
  </div>

  <input type='hidden' name='posts_per_page' value='3' />
</section>

<section class='extra_options'>
  <div class='container'>
    <div class='toggle-window' data-window='advanced-window'>
        <h4><?php _e('Advanced options', 'regenerate-thumbnails-advanced') ?></h4>
        <span class='dashicons dashicons-arrow-down'>&nbsp;</span>
    </div>
    <div class='cleanup-wrapper window-up' id='advanced-window'>
      <div class='option'>

      </div>

      <div class='option'>
          <label>
            <input type='checkbox' name='process_clean_metadata' value='1'>
            <span><?php _e('Clean unknown metadata', 'regenerate-thumbnails-advanced'); ?></span>
         </label>

         <div class='note'><p><?php _e('Clean old metadata not defined in system sizes. Use after removing plugins / themes with old definitions. Will not remove thumbnails from disk', 'regenerate-thumbnails-advanced') ?></p></div>
      </div>


      <div class='option'>
          <label for="del_associated_thumbs">
            <input type="checkbox" name="del_associated_thumbs" id="del_associated_thumbs" value="1" />
            <span><?php _e('Delete Unselected Thumbnails','regenerate-thumbnails-advanced'); ?></span>
          </label>
          <div class='note'><p><?php _e('Delete thumbnails and metadata not selected in the settings. Will delete thumbnails from disk - be sure they are not in use.  ','regenerate-thumbnails-advanced'); ?></p></div>
      </div>
      <div class='warning inline rta-notice rta_hidden' id='warn-delete-items'>
      <div class='icon dashicons-info dashicons'></div>


      <p><?php _e('Not selected thumbnails will be removed from your site. Check your settings if this is intentional.'); ?></p>

      <p class='small'><?php _e('Regenerate Thumbnails Advanced will not prevent new media uploads from generating removed sizes', 'regenerate-thumbnails-advanced', 'regenerate-thumbnails-advanced'); ?></span></p>
      </div>

      <div class='option'>
          <label for="del_leftover_metadata">
            <input type="checkbox" name="del_leftover_metadata" id="del_leftover_metadata" value="1" />
            <span><?php _e('Remove non-existent images','regenerate-thumbnails-advanced'); ?></span>
          </label>
          <div class='note'>
            <p><?php _e('If the main image does not exist, removes this image, thumbnails and metadata','regenerate-thumbnails-advanced'); ?></p>
            <p><?php _e('For removing images that are gone on disk, but still in media library', 'regenerate-thumbnails-advanced'); ?></p>
        </div>
      </div>

        <a href="https://help.shortpixel.com/article/233-quick-guide-to-using-regenerate-thumbnails-advanced-settings" target="_blank">
            <span class="dashicons dashicons-editor-help"></span>Read more</a>
    </div>
  </div> <!-- container -->
</section>

<section class='form_controls'>
  <div class='container'>
    <button type='submit' disabled class='rta_regenerate disabled'><span class='dashicons dashicons-controls-play'>&nbsp;</span> <?php _e('Regenerate', 'regenerate-thumbnails-advanced'); ?></button>
    <p class='save_note rta_hidden'><?php _e('Save your settings first','regenerate-thumbnails-advanced'); ?></p>
  </div>
</section>
</form>
