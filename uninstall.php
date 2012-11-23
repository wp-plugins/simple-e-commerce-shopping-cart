<?php
global $wpdb;

if( !defined('ABSPATH') && !defined('WP_UNINSTALL_PLUGIN') )
  exit();

define("SIMPLEECOMMCART_PATH", plugin_dir_path( __FILE__ ) ); // e.g. /var/www/example.com/wordpress/wp-content/plugins/simpleecommcart
require_once(SIMPLEECOMMCART_PATH . "/models/SimpleEcommCartCommon.php");
require_once(SIMPLEECOMMCART_PATH . "/models/SimpleEcommCartSetting.php");

if(SimpleEcommCartSetting::getValue('uninstall_db')) {
  global $wpdb;
  $prefix = $wpdb->prefix . "simpleecommcart_";
  $sqlFile = dirname( __FILE__ ) . "/sql/uninstall.sql";
  $sql = str_replace('[prefix]', $prefix, file_get_contents($sqlFile));
  $queries = explode(";\n", $sql);
  foreach($queries as $sql) {
    if(strlen($sql) > 5) {
      $wpdb->query($sql);
    }
  }
}