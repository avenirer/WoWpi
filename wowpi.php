<?php
/*
Plugin Name:    WoWpi - the World of Warcraft API Armory plugin
Plugin URI:     https://wordpress.org/plugins/wowpi/
Description:    WoWpi is an World of Warcraft API Armory plugin used to get data from Battle.net API
Version:        2.5.2
Author:         avenirer - Adrian Voicu
Text Domain:    wowpi
Domain Path:    /languages
Author URI:     http://avenir.ro
*/


/********************************************
* version
********************************************/

if (!defined('WOWPI_VERSION_KEY'))
    define('WOWPI_VERSION_KEY', 'wowpi_version');

if (!defined('WOWPI_VERSION_NUM'))
    define('WOWPI_VERSION_NUM', '2.5.2');

add_option(WOWPI_VERSION_KEY, WOWPI_VERSION_NUM);

function wowpi_load_plugin_textdomain() {
    load_plugin_textdomain( 'wowpi', FALSE, dirname( plugin_basename(__FILE__) ) . '/languages/' );
}
add_action( 'plugins_loaded', 'wowpi_load_plugin_textdomain' );


/********************************************
* global variables
********************************************/

$wowpi_plugin_name = 'WoWpi - World of Warcraft API Armory';
$wowpi_plugin_url = plugin_dir_url( __FILE__ );
$wowpi_plugin_dir = plugin_dir_path( __FILE__ );
$wowpi_options = get_option('wowpi_options');
$wowpi_stylings = array('wowpi_faction'=>'Faction styling','wowpi_light' => 'Light','wowpi_dark'=>'Dark','no_styling'=>'No styling');
$wowpi_character_showable = array(
  'achievement_feed' => __('Achievement Feed','wowpi'),
  'achievement_points' => __('Achievement Points','wowpi'),
  'ilvl' => __('Item Level','wowpi'),
  'gear' => __('Gear (Armor and Weapons)','wowpi'),
  'artifact_weapon' => __('Artifact Weapon','wowpi'),
  'kills' => __('Honorable Kills','wowpi'),
  'professions' => __('Professions','wowpi'),
  'talents' => __('Talents','wowpi'));

/********************************************
* includes
********************************************/

include('includes/scripts.php'); // This controls all JS / CSS
include('includes/wowpi_helpers.php');
include('includes/wowpi_data_processing.php'); // This controls all saving of data
include('includes/wowpi_item_data_processing.php');
include('includes/wowpi_realm_data_processing.php');
include('includes/wowpi_image_processing.php');
include('includes/wowpi_character_data_processing.php');
include('includes/wowpi_guild_data_processing.php');
include('includes/wowpi_admin.php');
include('includes/wowpi_views.php');
include('includes/wowpi_shortcodes_characters.php');
include('includes/wowpi_shortcodes_guilds.php');
include('includes/wowpi_shortcodes_realms.php');
include('includes/wowpi_shortcodes.php');
include('includes/wowpi_widget_character.php');
include('includes/wowpi_widget_realms.php');
include('includes/wowpi_widgets.php');
