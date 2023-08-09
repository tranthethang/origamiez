<?php
namespace ReThumbAdvanced;
use \ReThumbAdvanced\ShortPixelLogger\ShortPixelLogger as Log;
use \ReThumbAdvanced\Controllers\AdminController as AdminController;

// For communication with the Javascripting.
class AjaxController
{
   protected static $instance;
   protected $status; // /for the status.s

   // Ok status
   const STATUS_OK  = 0;
   const STATUS_SUCCESS = 1;
   const STATUS_STOPPED = 10;
   const STATUS_FINISHED = 11;

   // Errors
   const ERROR_GENERAL = -1;
   const ERROR_NOFILE = -2;
   const ERROR_METADATA = -3;

   /** PERIOD OPTIONS */
   const PERIOD_ALL = 0;
   const PERIOD_DAY = 1;
   const PERIOD_WEEK = 2;
   const PERIOD_MONTH = 3;
   const PERIOD_3MONTH = 4;
   const PERIOD_6MONTH = 5;
   const PERIOD_YEAR = 6;

   public static function getInstance()
   {
       if (is_null(self::$instance))
       {
          self::$instance = new ajaxController();
       }

       return self::$instance;
   }

   // hooks
   public function init()
   {
     // Process JS
     add_action( 'wp_ajax_rta_do_process', array($this, 'ajax_do_process') );
     add_action( 'wp_ajax_rta_start_process', array($this, 'ajax_start_process') );
     add_action( 'wp_ajax_rta_stop_process', array($this, 'ajax_stop_process'));

     // For settings page
     add_action( 'wp_ajax_rta_save_image_sizes', array($this,'view_generate_thumbnails_save' ) );

   }

   public function add_status($name, $args = array() )
   {
     $status = array('error' => true, 'message' => __('Unknown Error occured', 'regenerate-thumbnails-advanced'), 'status' => 0);

     $defaults =  array(
         'name' => false,
         'thumb' => false,
         'count' => false,
     );

     $args = wp_parse_args($args, $defaults);

     switch($name)
     {
         case 'no_nonce':
             $status['message'] = __('Site error, Invalid Nonce', 'regenerate-thumbnails-advanced');
             $status['status']  = self::ERROR_GENERAL;
         break;
         case 'preparing':
             $status['message'] = __('Preparing Images and Thumbnails', 'regenerate-thumbnails-advanced');
             $status['status'] = self::STATUS_OK;
             $status['error'] = false;
         break;
         case 'prepared':
           $status['message'] = __('Prepared %s items', 'regenerate-thumbnails-advanced');
           $status['mask']  = array('count');
           $status['status'] = self::STATUS_OK;
           $status['error'] = false;
         break;
         case 'prepare_failed':
            $status['message'] = __('Preparing failed', 'regenerate-thumbnails-advanced');
            $status['status'] = self::ERROR_GENERAL;
         break;
         case 'no_images':
            $status['message'] = __('No images found for this period and/or settings or none uploaded', 'regenerate-thumbnails-advanced');
            $status['status'] = self::STATUS_OK;
            $status['error'] = false;
         break;
         case 'file_missing':
            $status['message'] =  __('<b>%s</b> is missing or not an image file', 'regenerate-thumbnails-advanced');
            $status['mask'] = array('name');
            $status['status'] = self::ERROR_NOFILE;
         break;
         case 'not_image':
            $status['message'] = __('<b>%s</b> skipped. MimeType is an image, but reports non-displayable', 'regenerate-thumbnails-advanced');
            $status['mask'] = array('name');
            $status['status']  = self::ERROR_NOFILE;
         break;
         case 'error_metadata':
           $status['message'] = __('<b>%s</b> failed on metadata. Possible issue with image', 'regenerate-thumbnails-advanced');
           $status['mask'] = array('name');
           $status['status'] = self::ERROR_METADATA;
         break;
         case 'request_stop':
            $status['message'] = __('Process stopped on request', 'regenerate-thumbnails-advanced');
            $status['status'] = self::STATUS_STOPPED;
         break;
         case 'regenerate_success':
            $status['message'] = '%s';
            $status['mask'] = array('thumb');
            $status['status'] = self::STATUS_SUCCESS;
            $status['error'] = false;
         break;

         default:
            $status['message']  = '[' . $name . ']';

         break;
     }

     if (isset($status['mask']))
     {
       $mask = $status['mask'];
       foreach($mask as $mname)
       {
          if ( isset($args[$mname]) )
          {
              $value = $args[$mname];
              $pos = strpos($status['message'], '%s');

              if ($pos !== false) {
                $status['message'] = substr_replace($status['message'], $value, $pos, strlen('%s'));
              }
          }
       }
     }

     if (isset($status['mask']))
      unset($status['mask']); // internal use.

     $this->status[] = $status;
   }

   public function get_status()
   {
     return $this->status;
   }

   public function ajax_start_process()
   {

     $this->checkNonce('rta_generate');

     if (isset($_POST['genform']))
     {
         $form = $this->getFormData();
         $process = RTA()->process();

         $process->setRemoveThumbnails($form['del_associated_thumbs']);
         $process->setDeleteLeftMeta($form['del_leftover_metadata']);
         $process->setCleanMetadata($form['process_clean_metadata']);
         $process->setOnlyFeatured($form['regenonly_featured']);

         $stamps = $this->getQueryDate($form['period']);
      //   if ($period['date'] !== false)
         //{
           $startstamp = $stamps['startstamp']; // $period['args']['startstamp'];
           $endstamp = $stamps['endstamp']; // period['args']['endstamp'];
           $process->setTime($startstamp, $endstamp);
         //}

         $this->add_status('preparing');
         $process->start();
         $result = $this->runprocess(); // This would mostly be preparing.
     }
     else {
       Log::addError('Ajax Start Process - Starting without form post');
       exit(0);
     }

     //wp_send_json($this->get_json_process());
   }

   /** Collect form data, make a storable process array out of it */
   protected function getFormData()
   {
       $defaults = array(
           //'period' => self::PERIOD_ALL,
           'regenonly_featured' => false,
           'del_associated_thumbs' => false,
           'del_leftover_metadata' => false,
           'process_clean_metadata' => false,
       );

       $data = array();
       $form = isset($_POST['genform']) ? $_POST['genform'] : '';
       parse_str($form, $data);

       return wp_parse_args($data, $defaults);
   }

   // retrieve JS friendly overview, if we are in process and if yes, what are we doing here.
   public function get_json_process()
   {
       //$json = array('running' => false);
       $process = RTA()->process();
       $json = array();
       $json['running'] = $process->get('running');
       $json['preparing'] = $process->get('preparing');
       $json['finished'] = $process->get('finished');
       $json['done'] = $process->get('done');
       $json['items'] = $process->get('items');
       $json['errors'] = $process->get('errors');
       $json['status'] = $this->status;
       return $json;
   }

   public function ajax_do_process()
   {
     $this->checkNonce('rta_do_process');

     $result = $this->runProcess();
     $this->jsonResponse($this->get_json_process());
     exit();
   }

   protected function runProcess()
   {
      // check if preparing, or running
      $process = RTA()->process();
      if ($process->get('preparing') == true)  // prepare loop
      {
         $count = $process->prepare();
         $this->add_status('prepared', array('count' => $count));
         $this->jsonResponse($this->get_json_process());
      }

      if ($process->get('running') == true)
      {
          $items = $process->getItems();

          if ($items)
          {
            foreach($items as $item)
            {
              $item_id = $item->item_id;
              $image = new Image($item_id);
              $status = $image->regenerate();
            }
          }
      }


      if ($process->get('finished') == true)
      {
         if ($process->get('done') == 0) // if Q is finished with 0 done, it was empty.
           $this->add_status('no_images');

         $this->jsonResponse($this->get_json_process());
      }
   }

   public function ajax_stop_process()
   {
       $this->checkNonce('rta_generate');

      // $this->process = $this->get_json_process();
       $process = RTA()->process();
       $process->end();
       $this->add_status('request_stop');

       $this->jsonResponse($this->get_json_process());
   }

   /* Saves and generates JSON response */
   public function view_generate_thumbnails_save()
   {
     $json = true;
     $view = new AdminController($this);
     $response = $view->save_image_sizes();

     if ($json)
     {
       $this->jsonResponse($response);
     }
     else
     {
       return $response;
     }
   }

   // period comes from form.
   protected function getQueryDate($period)
     {
       $now = time();
       $endstamp = current_time('timestamp');
       switch (intval($period)) {
           case self::PERIOD_ALL:
             $startstamp = 0;
           break;
           case self::PERIOD_DAY:
             $startstamp = $now - DAY_IN_SECONDS;
          break;
           case self::PERIOD_WEEK:
             $startstamp = $now - WEEK_IN_SECONDS;
             break;
           case self::PERIOD_MONTH:
             $startstamp = $now - MONTH_IN_SECONDS;
             break;
         case self::PERIOD_3MONTH:
             $startstamp = $now - (3* MONTH_IN_SECONDS);
         break;
         case self::PERIOD_6MONTH:
             $startstamp = $now - (6* MONTH_IN_SECONDS);
             break;
         case self::PERIOD_YEAR:
             $startstamp = $now - YEAR_IN_SECONDS;
         break;
       }
    //   $result = array('date' => $date, 'args' => $args);
       $result = array('startstamp' => $startstamp, 'endstamp' => $endstamp);
       return $result;
     }

   // No Noncense function.
   protected function checkNonce($action)
   {
      $nonce = isset($_POST['nonce']) ? $_POST['nonce'] : false;

      if (! wp_verify_nonce($nonce, $action))
      {
           $this->add_status('no_nonce');
           Log::addError('Ajax Start Process - Nonce failed ' . $nonce . 'on ' . $action);
           $this->jsonResponse($this->get_json_process());
           exit();
      }

      return true;
   }

   /** Central function for JSON responses. Can be extended whenever needed */
   protected function jsonResponse($response)
   {
     wp_send_json($response);
     exit();
   }

}
