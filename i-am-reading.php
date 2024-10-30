<?php
/*
Plugin Name: I am reading (continued)
Plugin URI:  http://i-am-reading.ginchen.de/
Description: Book display with search by ISBN, title and author for all Amazon market places with lots of individual display settings.
Version:     1.0.2
Author:      Ginchen
Author URI:  http://blog.ginchen.de/

--------------------------------------------------------------------------
Copyright 2009-2012  Dominik Hanke, Ginchen (e-mail: iamreading@ginchen.de)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, see http://www.gnu.org/licenses/.
*/
/**
 * I am reading :: Main file
 * 
 * @package i-am-reading
 */

// plugin database version (important for updates)
define('IAR_DB_VERSION', '1.0');

// min. required PHP version
define('IAR_PHP_REQUIRED', '5.1.2');

// text domain
define('IAR_TEXTDOMAIN', 'i_am_reading');

// set plugin directories
define('IAR_BASE_DIR',           dirname(__FILE__).'/');
define('IAR_LIB_DIR',            IAR_BASE_DIR.'includes/');
define('IAR_LANG_DIR',           basename(dirname(__FILE__)).'/lang');
define('IAR_ADMIN_DIR',          IAR_BASE_DIR.'admin/');
define('IAR_ADMIN_TEMPLATE_DIR', IAR_ADMIN_DIR.'templates/');
define('IAR_PLUGIN_URL',         WP_PLUGIN_URL.'/i-am-reading-continued/');

// support mail receiver
define('IAR_SUPPORT_MAIL',       'support@i-am-reading.info');

// plugin's pages
$iar_pages = array('iar_current', 'iar_search', 'iar_amazon', 'iar_display', 'iar_uninstall');

// Add plugin textdomain
add_action('init', 'iar_init');

// include management class
include IAR_LIB_DIR.'class.iAmReading.php';

// Add options in admin menu
add_action('admin_menu', 'iar_setup_menu');

// Add action for plugin activation
register_activation_hook( __FILE__, 'iar_activation' );

// Add uninstall action if WP > 1.7 / add extra menu item otherwise
$uninstall_hook = false;

if ( function_exists('register_uninstall_hook') ) {
	
	register_uninstall_hook(__FILE__, 'iar_uninstall');
	$uninstall_hook = true;
}

// Register sidebar widget and shortcode
add_action('widgets_init',   create_function('', 'return register_widget("iarSidebarWidget");'));
add_action('plugins_loaded', 'iar_add_shortcode');

#########################################################################################################
#  Functions needed by this plugin - don't modify the following contents if you're not sure what to do  #
#########################################################################################################

if ( check_php_version() === false ) {
	
	add_action('admin_notices', 'php_version_info');
}

function check_php_version() {
	
	if ( version_compare(PHP_VERSION, IAR_PHP_REQUIRED) < 1 ) {
		
		return false;
	}
	
	return true;
}

function php_version_info() {
	
	echo '<div id="message" class="error"><p><strong>I am reading</strong>: PHP Version '.IAR_PHP_REQUIRED.' or higher required to use this plugin.</p></div>';
}

/**
 * Initialises plugins settings.
 *
 * @return void
 */
function iar_init() {
	
	// set textdomain used by gettext()
	load_plugin_textdomain(IAR_TEXTDOMAIN, false, IAR_LANG_DIR);
	
//	echo IAR_TEXTDOMAIN . ' | ' . IAR_LANG_DIR;
}

/**
/**
 * Adds new top level menu called 'Books'
 * and registers action for loading JS.
 *
 * @return void
 */
function iar_setup_menu() {
	
	if ( (check_php_version() === true) && is_admin() ) {
		
		global $uninstall_hook, $iar_pages;
		
		add_menu_page('I am reading', 'I am reading', 1, 'iar_current', 'iar_load_menu', IAR_PLUGIN_URL.'/admin/images/menu-icon.png');
		
		$page_book    = add_submenu_page('iar_current', 'I am reading', __('Current Book', IAR_TEXTDOMAIN), 1, 'iar_current', 'iar_load_menu'); 
		$page_book    = add_submenu_page('iar_current', 'I am reading', __('Book Search', IAR_TEXTDOMAIN), 1, 'iar_search', 'iar_load_menu'); 
		$page_config  = add_submenu_page('iar_current', 'I am reading', __('Amazon API', IAR_TEXTDOMAIN), 1, 'iar_amazon', 'iar_load_menu'); 
		$page_display = add_submenu_page('iar_current', 'I am reading', __('Appearance', IAR_TEXTDOMAIN), 1, 'iar_display', 'iar_load_menu'); 
		
		if ( $uninstall_hook === false ) {
			
			$page_display = add_submenu_page('iar_current', 'I am reading', __('Uninstall', IAR_TEXTDOMAIN), 1, 'iar_uninstall', 'iar_load_menu'); 
		}
		
		if ( in_array($_GET['page'], $iar_pages) ) {
			
			add_action( 'admin_print_styles', 'iar_admin_styles' );
			add_action( 'admin_print_scripts', 'iar_admin_scripts' );
		}
	}
}

/**
 * Load JavaScripts needed on different admin pages.
 * 
 * @return void
 */
function iar_admin_scripts() {
	
	wp_enqueue_script('jquery');
	wp_enqueue_script('iar_boxover', plugins_url($path = 'i-am-reading-continued/admin/js/boxover.js'));
	wp_enqueue_script('iar_settings', plugins_url($path = 'i-am-reading-continued/admin/js/config.js'));
	
	switch ( $_GET['page'] ) {
		
		case 'iar_display':
			wp_enqueue_script('DHTMLColor', IAR_PLUGIN_URL . 'admin/js/DHTMLColors/301a.js');
			break;
	}
}

/**
 * Load CSS styles needed on different admin pages.
 * 
 * @return void
 */
function iar_admin_styles() {
	
	wp_enqueue_style('iar_admin_css', plugins_url($path = 'i-am-reading-continued/admin/admin.css'));
	
	switch ( $_GET['page'] ) {
		
		case 'iar_display':
			wp_enqueue_style('colorpicker');
			break;
	}
}

/**
 * Loads an admin page.
 * 
 * @return void;
 */
function iar_load_menu() {
	
	switch ( $_GET['page'] ) {
		
		case 'iar_current':
			include IAR_ADMIN_DIR.'iar-current.php';
			break;
			
		case 'iar_search':
			include IAR_ADMIN_DIR.'iar-search.php';
			break;
		
		case 'iar_amazon':
			include IAR_ADMIN_DIR.'iar-amazon.php';
			break;
			
		case 'iar_display':
			include IAR_ADMIN_DIR.'iar-display.php';
			break;
			
		case 'iar_uninstall':
			include IAR_ADMIN_DIR.'iar-uninstall.php';
			break;
	}
}

/**
 * Generates all needed database tables and information when the plugin
 * gets activated in admin panel. Database will be upgraded if a current
 * plugin version gets detected.
 * 
 * @return void
 */
function iar_activation() {
	
	if ( check_php_version() === true ) {
		
		$iAmReading = new iAmReading();
		$iAmReading->install();
		
	} else {
		
		return false;
	}
}

/**
 * Removes all database tables and information stored in the database
 * by this plugin.
 * 
 * @return void
 */
function iar_uninstall() {
	
	$iAmReading = new iAmReading();
	$iAmReading->uninstall();
}

/**
 * Instantiates management object (class iAmReading) and
 * prints out book data as HTML.
 *
 * @return void
 * @see iAmReading::getBookHtml
 */
function iar_print_html() {
	
	$iAmReading = new iAmReading();
	print $iAmReading->getBookHtml();
}

/**
 * Registers the shortcode [i-am-reading]
 * 
 * @return void
 */
function iar_add_shortcode() {
	
	add_shortcode('i-am-reading', 'iar_print_html');
}

/**
 * Widget for generating html output with widget title and content.
 */
class iarSidebarWidget extends WP_Widget {
	
	function iarSidebarWidget() {
		parent::WP_Widget('iar_widget', 'I am reading');
	}
	
	function widget($args, $instance) {
		
		$iAmReading = new iAmReading();
		
		$widget_display = $iAmReading->getBookHtml();
		$config         = $iAmReading->getConfig();
		
		echo $args['before_widget'];
	  echo $args['before_title'].$config['widget_title'].$args['after_title'];
		echo $widget_display;
		echo $args['after_widget'];
	}
	
	function update($new_instance, $old_instance) {
		
		$instance = $old_instance;
		return $instance;
	}
	
	function form($instance) {
	}
}

/**
 * Registers the sidebar widget
 *
 * @return void
 */
function iar_register_sidebar_widget() {
	
//	register_widget('iarSidebarWidget');
}

/**
 * Register AJAX function for retrieving theme
 *
 * @return void
 */
function iar_get_theme() {

	$theme = trim($_POST['theme']);

	$iAmReading = new iAmReading();

	echo $iAmReading->getBookHtml($theme);

	die();
}

add_action('wp_ajax_iar_get_theme', 'iar_get_theme');
?>
