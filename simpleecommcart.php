<?php
/*
Plugin Name: Simple eCommerce
Plugin URI: http://wordpress.org/
Description: Transform your wordpress site into an online shop and sell products or services with Simple eCommerce shopping cart plugin. Plugin includes Cart Widget, shortcodes and email tags to customise your shop.
Version: 2.2.2
Author: Niaz Showket

------------------------------------------------------------------------
SimpleEcommCart WordPress Ecommerce Plugin
Copyright 2012  Simple eCommerce

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

if(!class_exists('SimpleEcommCart')) {
  ob_start();
  define("SIMPLEECOMMCART_PATH", plugin_dir_path( __FILE__ ) ); // e.g. /var/www/example.com/wordpress/wp-content/plugins/simpleecommcart
  // define("SIMPLEECOMMCART_URL", rtrim(plugin_dir_url( __FILE__ ), '/') ); // e.g. http://example.com/wordpress/wp-content/plugins/simpleecommcart
  define('SIMPLEECOMMCART_URL', plugins_url() . '/' . basename(dirname(__FILE__)));

  require_once(SIMPLEECOMMCART_PATH. "/models/SimpleEcommCartCartWidget.php");
  require_once(SIMPLEECOMMCART_PATH. "/models/SimpleEcommCart.php");
  require_once(SIMPLEECOMMCART_PATH. "/models/SimpleEcommCartCommon.php");
  
  define("SIMPLEECOMMCART_ORDER_NUMBER", false);
  define("SIMPLEECOMMCART_PRO", true);
  define('SIMPLEECOMMCART_VERSION_NUMBER', '1.0.0');
  define("WPCURL", SimpleEcommCartCommon::getWpContentUrl());
  define("WPURL", SimpleEcommCartCommon::getWpUrl());
  define("INFO_ICON",SIMPLEECOMMCART_URL.'/images/info.png');
  define("SHOPPING_CART_IMAGE",SIMPLEECOMMCART_URL.'/images/Shoppingcart.png');
  
  define("BIZCART_BOX_1_IMAGE",SIMPLEECOMMCART_URL.'/images/more/bizcartbox1.png');
  define("PHOTOBIZCART_BOX_1_IMAGE",SIMPLEECOMMCART_URL.'/images/more/photobizcartbox1.png');
  
  define("BUG_IMAGE",SIMPLEECOMMCART_URL.'/images/more/bug.png');
  define("HELP_IMAGE",SIMPLEECOMMCART_URL.'/images/more/help.png');
  define("MONEY_IMAGE",SIMPLEECOMMCART_URL.'/images/more/money.png');
  define("TABLE_IMAGE",SIMPLEECOMMCART_URL.'/images/more/table.png');
  
  if(SIMPLEECOMMCART_PRO) {
    require_once(SIMPLEECOMMCART_PATH. "/advanced/models/SimpleEcommCartProCommon.php");
  }

  // IS_ADMIN is true when the dashboard or the administration panels are displayed
  if(!defined("IS_ADMIN")) {
    define("IS_ADMIN",  is_admin());
  }

  /* Uncomment this block of code for load time debugging
  $filename = SIMPLEECOMMCART_PATH . "/log.txt"; 
  if(file_exists($filename) && is_writable($filename)) {
    file_put_contents($filename, "\n\n\n================= Loading SimpleEcommCart Main File [" . date('m/d/Y g:i:s a') . "] " . 
      $_SERVER['REMOTE_ADDR'] . " " . $_SERVER['REQUEST_URI'] . " =================\n\n", FILE_APPEND);
  }
  */
  $simpleecommcart = new SimpleEcommCart();
  load_plugin_textdomain( 'simpleecommcart', false, '/' . basename(dirname(__FILE__)) . '/languages/' );
  
  // Register activation hook to install SimpleEcommCart database tables and system code
  register_activation_hook(__FILE__, array($simpleecommcart, 'install'));
  
  // Check for WordPress 3.1 auto-upgrades
  if(function_exists('register_update_hook')) {
    register_update_hook(__FILE__, array($simpleecommcart, 'install'));
  }

  add_action('init',  array($simpleecommcart, 'init'));
  add_action('widgets_init', array($simpleecommcart, 'registerCartWidget'));
}

/**
 * Prevent the link rel="next" content from showing up in the wordpress header 
 * because it can potentially prefetch a page with a [clearcart] shortcode
 */
remove_action('wp_head', 'adjacent_posts_rel_link_wp_head');