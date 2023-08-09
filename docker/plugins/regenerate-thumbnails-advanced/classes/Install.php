<?php
namespace ReThumbAdvanced;
use \ReThumbAdvanced\ShortPixelLogger\ShortPixelLogger as Log;
use \ReThumbAdvanced\Notices\NoticeController as Notice;

use \ReThumbAdvanced\ShortQ as ShortQ;

class Install
{

  /** The handler when user completely uninstalls the plugin */
    public static function uninstall()
    {

      $process = new Process();
      $q = $process->getQueue();
      $q->uninstall();
    }

  /** Handler on activation */
    public static function activate()
    {

    }

  /** Handler on deactivate */
    public static function deactivate()
    {

    }



}
