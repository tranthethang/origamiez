<?php
namespace ReThumbAdvanced;
use \ReThumbAdvanced\ShortPixelLogger\ShortPixelLogger as Log;
use \ReThumbAdvanced\Notices\NoticeController as Notice;
use \ReThumbAdvanced\Controllers\AdminController as AdminController;

// load runtime.
class Plugin
{
  protected static $instance;

  protected $paths = array('classes', 'classes/controllers');

  protected $front;
  //protected $admin;

  public function __construct()
  {
      $log = Log::getInstance();
      if (Log::debugIsActive()) // upload dir can be expensive, so only do this when log is actually active.
      {
        $uploaddir = wp_upload_dir(null, false, false);
        if (isset($uploaddir['basedir']))
          $log->setLogPath($uploaddir['basedir'] . "/rta_log");
      }
    //  $this->initRuntime();

      add_action( 'after_setup_theme', array( $this, 'add_custom_sizes' ) );
      add_action( 'admin_init', array( $this, 'init' ) );
    //  add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ), 10 );

      add_action( 'admin_menu', array( $this, 'admin_menus' ) );

      add_filter( 'plugin_action_links_' . plugin_basename(RTA_PLUGIN_FILE), array($this, 'generate_plugin_links'));//for plugin settings page
  }

  public static function getInstance()
  {
     if (is_null(self::$instance))
      self::$instance = new Plugin();

     return self::$instance;
  }

  public static function namespaceit($name)
  {
    return '\ReThumbAdvanced\\'  . $name;
  }


  /*public function initRuntime()
  {

    foreach($this->paths as $short_path)
    {
      $directory_path = realpath(RTA_PLUGIN_PATH . $short_path);

      if ($directory_path !== false)
      {
        $it = new \DirectoryIterator($directory_path);
        foreach($it as $file)
        {
          $file_path = $file->getRealPath();
          if ($file->isFile() && pathinfo($file_path, PATHINFO_EXTENSION) == 'php')
          {
            require_once($file_path);
          }
        }
      }
    }
  } */

  // load textdomain, init.
  public function init()
  {
    load_plugin_textdomain( 'regenerate-thumbnails-advanced', FALSE, RTA_LANG_DIR );

    $this->front = new Front();

    $ajax = AjaxController::getInstance(); //init
    $ajax->init();

    add_filter('media_row_actions', array($this,'add_media_action'), 10, 2);
    add_action( 'add_meta_boxes', function () { add_meta_box('rta-link', __('Regenerate Thumbnails', 'enable-media-replace'), array($this, 'regenerate_meta_box'), 'attachment', 'side', 'low'); }  );
    add_filter('attachment_fields_to_edit', array($this, 'attachment_editor'), 10, 2);

    $this->check_media_action();
    //add_action('upload.php', array($this, 'check_media_action'), 10);

    $notices = Notice::getInstance();

    // Enqueue notices
    add_action('admin_notices', array($notices, 'admin_notices')); // previous page / init time
    add_action('admin_footer', array($notices, 'admin_notices')); // fresh notices between init - end

  }

  public function ajax()
  {
     return AjaxController::getInstance();
  }

  public function process()
  {
    return Process::getInstance();
  }

  public function admin()
  {
    return Admin::getInstance();
  }

  public function check_media_action()
  {
        if (isset($_GET['regen_action']) && $_GET['regen_action'] == 'regenerate_image_thumbnail')
        {
          $attach_id = intval($_GET['attachment_id']);
          if (! wp_verify_nonce($_GET['_wpnonce'], 'regenerate_image_thumbnail'))
          {
            Notice::addError(__('Incorrect nonce','regenerate-thumbnails-advanced'));
          }
          elseif ($attach_id <= 0)
          {
            Notice::addError(__('No Attachment ID found, not regenerating','regenerate-thumbnails-advanced'));
          }
          else {
            $result = RTA()->admin()->regenerate_single_image($attach_id);
          }

          $sendback = remove_query_arg( array('attachment_id', 'regen_action', '_wpnonce') );
          wp_redirect($sendback);
          exit();
        }

  }

  // Registering styles and scripts.
  public function enqueue_scripts() {

      //wp_enqueue_script( 'jquery' );
      wp_register_script('rta_js', RTA_PLUGIN_URL.'js/rta.js', array( 'jquery' ), RTA_PLUGIN_VERSION );
      wp_register_style( 'rta_css', RTA_PLUGIN_URL.'css/rta.css', array(), RTA_PLUGIN_VERSION );
      wp_register_style( 'rta_css_admin', RTA_PLUGIN_URL.'css/rta-admin-view.css', array(), RTA_PLUGIN_VERSION );
      wp_register_style( 'rta_css_admin_progress', RTA_PLUGIN_URL.'css/rta-admin-progress.css', array('rta_css_admin'), RTA_PLUGIN_VERSION );

      $admin_url = admin_url( 'admin-ajax.php' );
      if (Log::isManualDebug() )
      {
        $logLevel = Log::getLogLevel();
        $admin_url = admin_url('admin-ajax.php?SHORTPIXEL_DEBUG=' . $logLevel);
      }

      wp_localize_script( 'rta_js', 'rta_data', array(
                          'ajaxurl' => $admin_url,
                          'nonce_savesizes' => wp_create_nonce('rta_save_image_sizes'),
                          'nonce_doprocess' => wp_create_nonce('rta_do_process'), // continue
                          'nonce_generate' => wp_create_nonce('rta_generate'), // start / stop
                          'strings' => array(
                          'confirm_delete' => __('Are you sure you want to delete this image size?', 'regenerate-thumbnails-advanced'),
                          'confirm_stop' => __("This will stop the regeneration process. You want to stop?", 'regenerate-thumbnails-advanced' ),
                          'status_resume' => __("Interrupted process resumed", 'regenerate-thumbnails-advanced'),
                          'status_start' => __('New Process started', 'regenerate-thumbnails-advanced'),
                          'status_finish' => __('Process finished','regenerate-thumbnails-advanced' ),
                          'status_fatal' => __('A fatal error occured!', 'regenerate-thumbnails-advanced'),
                          ),
                          'blog_id' => get_current_blog_id(),
                          'process' => $this->ajax()->get_json_process(),
                          'is_debug' => (Log::debugIsActive()) ? 1 : 0,
                          ));

      do_action('rta_enqueue_scripts');
  }

  // add admin pages
  public function admin_menus(){
      $title = __('Regenerate Thumbnails', 'regenerate-thumbnails-advanced');
      add_management_page($title, $title, 'manage_options', 'rta_generate_thumbnails', array($this, 'view_generate_thumbnails' ));
  }

  // filter for plugin page.
  public function generate_plugin_links($links) {
      $in = '<a href="tools.php?page=rta_generate_thumbnails">'  . __('Settings', 'regenerate-thumbnails-advanced') . '</a>';
      array_unshift($links, $in);
      return $links;
  }

  public function view_generate_thumbnails() {
      $this->enqueue_scripts();
      wp_enqueue_style('rta_css');
      wp_enqueue_script('rta_js');
      //$rta_image_sizes = get_option( 'rta_image_sizes' );
      $view = new AdminController();
      $view->show();
  }

  public function add_custom_sizes() {

      $rta_image_sizes = get_option( 'rta_image_sizes', false);
      if (! $rta_image_sizes)
        return $rta_image_sizes;

      $image_sizes = isset($rta_image_sizes['image_sizes']) && is_array($rta_image_sizes['image_sizes']) ? $rta_image_sizes['image_sizes'] : array();

      if(count($image_sizes) > 0 && count($image_sizes['name']) > 0){
          for($i=0;$i<sizeof($image_sizes['name']);$i++){
              $crop = false;
              if($image_sizes['cropping'][$i]=='no_cropped'){
                  $crop = false;
              }elseif($image_sizes['cropping'][$i]=='cropped') {
                  $crop = true;
              }else{
                  $crop = explode("_", $image_sizes['cropping'][$i]);
              }
              add_image_size( $image_sizes['name'][$i], $image_sizes['width'][$i], $image_sizes['height'][$i], $crop );
          }
      }
  }

  public function add_media_action( $actions, $post) {

    $editurl = $this->getRegenerateLink($post->ID);
    $link = "href=\"$editurl\"";
    $action = 'regenerate_image_thumbnail';


    $newaction[$action] = '<a ' . $link . ' aria-label="' . esc_attr(__("Regenerate Thumbnails", "regenerate-thumbnails-advanced")) . '" rel="permalink">' . esc_html(__("Regenerate Thumbnails", "regenerate-thumbnails-advanced")) . '</a>';

    return array_merge($actions,$newaction);
  }

  /** Generates a link to single regen images.
  * @param $post_id int Image Post Post ID
  * @param $url String URL to base link on, otherwise current uRL will be used
  */
  private function getRegenerateLink($post_id, $url = '')
  {
    $action = 'regenerate_image_thumbnail';

      $url = add_query_arg(array(
          'regen_action' => $action,
          'attachment_id' => $post_id,
      ), $url);

    $editurl = wp_nonce_url( $url, $action );
    return $editurl;

  }

  /** The metabox in edit attachment view */
  public function regenerate_meta_box($post)
  {
    $url = admin_url('post.php');
    if (isset($_GET['post']))
      $url = add_query_arg('post', intval($_GET['post']), $url);
    if (isset($_GET['action']))
      $url = add_query_arg('action', sanitize_text_field($_GET['action']), $url);

    $editurl = $this->getRegenerateLink($post->ID, $url);
    $link = "href=\"$editurl\"";

    echo "<p><a class='button-secondary' $link>" . esc_html__("Regenerate Thumbnails", "regenerate-thumbnails-advanced") . "</a></p>";
  }

  /** Adding a button to the attachements view popup */
  public function attachment_editor($form_fields, $post)
  {
      $screen = null;
      if (function_exists('get_current_screen'))
      {
        $screen = get_current_screen();

        if(! is_null($screen) && $screen->id == 'attachment') // hide on edit attachment screen.
          return $form_fields;
      }

      $url = admin_url('upload.php');
      $url = add_query_arg('item', $post->ID, $url);

      $editurl = $this->getRegenerateLink($post->ID, $url);

      $link = "href=\"$editurl\"";
      $form_fields["regenerate-thumbnails-advanced"] = array(
              "label" => esc_html__("Regenerate Thumbnails", "enable-media-replace"),
              "input" => "html",
              "html" => "<a class='button-secondary' $link>" . esc_html__("Regenerate Thumbnails", "enable-media-replace") . "</a>"
            );

      return $form_fields;
  }




}
