<?php
namespace ReThumbAdvanced;
/**
 * Plugin Name: Regenerate Thumbnails Advanced
 * Description: Regenerate thumbnails fast and easy while removing unused thumbnails of existing images; very useful when changing a theme.
 * Version: 2.4.1
 * Author: ShortPixel
 * Author URI: https://shortpixel.com/
 * License: GPLv2 or later
 * GitHub Plugin URI: https://github.com/short-pixel-optimizer/regenerate-thumbnails-advanced-rewrite
 * Text Domain: regenerate-thumbnails-advanced
 * Domain Path: /languages
 */

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
  exit;
}

define( 'RTA_PLUGIN_VERSION', '2.4.1');
define( 'RTA_PLUGIN_PATH', plugin_dir_path(__FILE__) );
define( 'RTA_PLUGIN_URL', plugin_dir_url(__FILE__) );
define( 'RTA_SITE_BASE_URL',  rtrim(get_bloginfo('url'),"/")."/");
define( 'RTA_PLUGIN_FILE', __FILE__);
define( 'RTA_LANG_DIR', dirname( plugin_basename(__FILE__) ).'/languages' );

require_once(RTA_PLUGIN_PATH . 'build/shortpixel/autoload.php');
//require_once(RTA_PLUGIN_PATH . 'classes/rta_controller.php');
require_once(RTA_PLUGIN_PATH . 'classes/Plugin.php');

$loader = new Build\PackageLoader();
$loader->setComposerFile(RTA_PLUGIN_PATH . 'classes/plugin.json');
$loader->load(RTA_PLUGIN_PATH);

function RTA()
{
  return Plugin::getInstance();
}

RTA();


register_uninstall_hook(RTA_PLUGIN_FILE, array('ReThumbAdvanced\Install', 'uninstall'));
register_activation_hook(RTA_PLUGIN_FILE, array('ReThumbAdvanced\Install', 'activate'));
register_deactivation_hook(RTA_PLUGIN_FILE, array('ReThumbAdvanced\Install', 'deactivate'));
