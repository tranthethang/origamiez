<?php
namespace ReThumbAdvanced;
?>

<div class='wrap'> <!-- this wrap to keep notices and other scum out of the interface -->
  <h1><?php _e('Regenerate Thumbnails Advanced','regenerate-thumbnails-advanced'); ?></h1>

<div class="rta-admin-wrap rta-admin wrap">

    <div class='two-panel-wrap settings-panels'>
      <div class='rta-regenerate-wrap'>
        <h2><?php _e('Regenerate Options', 'regenerate-thumbnails-advanced') ?></h2>
        <?php $this->loadChildTemplate('view_rta_regenerate'); ?>
      </div>
      <div class='rta-settings-wrap'>
        <h2><?php _e('Settings', 'regenerate-thumbnails-advanced'); ?></h2>
        <?php $this->loadChildTemplate('view_rta_settings'); ?>
      </div>
    </div>
    <?php $this->loadChildTemplate('view_ad');  ?>
    <?php $this->loadChildTemplate('view_regenerate_process'); ?>
</div> <!-- rta admin wrap. -->

</div>
