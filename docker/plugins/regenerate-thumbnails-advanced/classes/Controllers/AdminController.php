<?php
namespace ReThumbAdvanced\Controllers;
use function ReThumbAdvanced\RTA;

class AdminController extends Controller
{
  //protected $controller;

  /** Settings saved in the option table. Being set on construct. Refreshed on save */
/*  protected $custom_image_sizes = array();
  protected $process_image_sizes = false;
  protected $process_image_options = array();
  protected $system_image_sizes = array();
  protected $jpeg_quality = 90; */

  protected $cropOptions;

  public function __construct()
  {
        wp_enqueue_style( 'rta_css_admin');
        wp_enqueue_style( 'rta_css_admin_progress');

      //  $this->controller = $controller;

        $this->cropOptions = array(
            'no_cropped' => __('No','regenerate-thumbnails-advanced'),
            'cropped' => __('Yes','regenerate-thumbnails-advanced'),
            'left_top' => __('Left top','regenerate-thumbnails-advanced'),
            'left_center' => __('Left center','regenerate-thumbnails-advanced'),
            'left_bottom' => __('Left bottom','regenerate-thumbnails-advanced'),
            'center_top' => __('Center top','regenerate-thumbnails-advanced'),
            'center_center' => __('Center center','regenerate-thumbnails-advanced'),
            'center_bottom' => __('Center bottom','regenerate-thumbnails-advanced'),
            'right_top' => __('Right top','regenerate-thumbnails-advanced'),
            'right_center' => __('Right center','regenerate-thumbnails-advanced'),
            'right_bottom' => __('Right bottom','regenerate-thumbnails-advanced'),
        );

      //  $this->setOptionData();

  }



  public function show()
  {
    $view = new \stdClass;

    $html = $this->load_template( "rta_generate_thumbnails", "admin", array('view' => $view) );
    echo $html;
  }

  public function loadChildTemplate($name)
  {
    $view = new \stdClass;
    if ($name == 'view_rta_settings')
    {
      $view->custom_image_sizes = RTA()->admin()->getOption('custom_image_sizes');
      $view->process_image_sizes = RTA()->admin()->getOption('process_image_sizes');
      $view->process_image_options = RTA()->admin()->getOption('process_image_options');
      $view->jpeg_quality = RTA()->admin()->getOption('jpeg_quality');
    }

    $html = $this->load_template($name, 'admin', array('view' => $view ));
    echo $html;
  }

  /** Generate cropOptions
  *
  */
  public function cropOptions($current = '')
  {
    $output = '';
    foreach($this->cropOptions as $name => $label)
    {
      $selected =  ($name == $current) ? 'selected' : '';
      $output .= "<option value='$name' $selected>$label</option>";
    }

    return $output;
  }

/*
  public function __get($name)
  {
    if (isset($this->{$name}))
    {
      return $this->{$name};
    }
    return false;
  } */

  /** Save thumbnail settings.
  *
  * @return JSON  Returns json result data
  */
  public function save_image_sizes() {
      global $_wp_additional_image_sizes;

      $jsonReponse = array('message' => '', 'error' => '');
      $error = false;
      $rta_image_sizes = array();
      $option = array();
      $exclude = array();

      $nonce = isset($_POST['save_nonce']) ? $_POST['save_nonce'] : false;
      if (! wp_verify_nonce($nonce, 'rta_save_image_sizes'))
      {
        $jsonResponse['error'] = 'Invalid Nonce';
        return $jsonResponse;
      }

      if (isset($_POST['saveform']))
          parse_str($_POST['saveform'], $formpost);
       else
          $formpost = array();

      $image_sizes = isset($formpost['image_sizes']) ? $formpost['image_sizes'] : array();
      $jpeg_quality = isset( $formpost['jpeg_quality']) ? $formpost['jpeg_quality'] : 0;

      if (isset($image_sizes['name']))
      {
        for($i =0; $i < count($image_sizes['name']); $i++)
        {
            if (strlen($image_sizes['name'][$i]) <= 0)
            {
              continue;
            }
            // sanitize!
            $rta_image_sizes['name'][] = isset($image_sizes['name'][$i]) ? sanitize_text_field($image_sizes['name'][$i]) : '';
            $rta_image_sizes['pname'][] = isset($image_sizes['pname'][$i]) ? sanitize_text_field($image_sizes['pname'][$i]) : '';
            $rta_image_sizes['width'][] = isset($image_sizes['width'][$i]) ? intval($image_sizes['width'][$i]) : '';
            $rta_image_sizes['height'][] = isset($image_sizes['height'][$i]) ? intval($image_sizes['height'][$i]) : '';
            $rta_image_sizes['cropping'][] = isset($image_sizes['cropping'][$i]) ? sanitize_text_field($image_sizes['cropping'][$i]) : '';
        }

      }

      if ($jpeg_quality > 0)
        $option['jpeg_quality'] = $jpeg_quality;

      $option['image_sizes'] = $rta_image_sizes;

      // redo the thumbnail options, apply changes
      $sizes = isset($formpost['regenerate_sizes']) ? $formpost['regenerate_sizes'] : array();
      $size_options = array();
      foreach($sizes as $rsize)
      {
          if (isset($formpost['keep_' . $rsize]))
          {
            $size_options[$rsize] = array('overwrite_files' => false);
          }
          else {
            $size_options[$rsize] = array('overwrite_files' => true);
          }
      }
      $option['process_image_sizes'] = array_values($sizes);  // the once that are set to regen. Array values resets index
      $option['process_image_options'] = $size_options;

      update_option( 'rta_image_sizes', $option );
      //$this->setOptionData();
      RTA()->admin()->resetOptionData();

      $newsizes = $this->generateImageSizeOptions($sizes);
      $jsonResponse = array( 'error' => $error, 'message' => '', 'new_image_sizes' => $newsizes );

      return $jsonResponse;

  }


  public function generateImageSizeOptions($checked_ar = false)
  {
    $output = '';
    $i = 0;
    $check_all = ($checked_ar === false) ? true : false;

    //$process_options = $this->process_image_options;
    $process_options = RTA()->admin()->getOption('process_image_options');

    $system_image_sizes = RTA()->admin()->getOption('system_image_sizes');

    // size here is a name, value is how the name is found in the system (in interface, the technical name)
    foreach($system_image_sizes as $value => $size):

      //if ($check_all)
        //$checked = 'checked';
      $checked = ($check_all || in_array($value, $checked_ar)) ? 'checked' : '';
      $hidden = ($checked == 'checked') ? '' : 'hidden'; // hide add. option if not checked.

      $option_in_db = (isset($process_options[$value])) ? true : false;
      $checked_keep = (isset($process_options[$value]) && isset($process_options[$value]['overwrite_files']) && ! $process_options[$value]['overwrite_files'] )  ? 'checked' : '';

      if ($option_in_db)
        $checked .= ' data-setbyuser=true'; // if value was ever saved in DB, don't change it in the JS.

      $output .= "<div class='item'>";
      $output .= "<span>
        <label> <input type='checkbox' id='regenerate_sizes[$i]' name='regenerate_sizes[$i]' value='$value' $checked>
          " .  ucfirst($size) . "</label>
      </span>";
      $output .= "<span class='options $hidden'><label><input value='1' type='checkbox' $checked_keep name='keep_" . $value . "'> " . __('Don\'t redo existing', 'regenerate-thumbnails-advanced') . "</label></span>";
      $output .= "</div>";

      $i++;

    endforeach;
    return $output;
  }

} // class
