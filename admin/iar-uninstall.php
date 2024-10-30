<?php
/**
 * Uninstallation
 *
 * Removes all database tables and options created by this plugin.
 *
 * @package i-am-reading
 */

//+------------------------+//
//                          //
// GLOBAL VARS AND INCLUDES //
//                          //
//+------------------------+//

include_once IAR_LIB_DIR.'class.iAmReading.php';
include_once IAR_LIB_DIR.'class.phpTemplate.php';

//+~~~~~~~~~~~+//
// global vars //
//+~~~~~~~~~~~+//

$action_confirmed = false;

// possible errors
$error_code = 0;

define('IAR_ERROR_NOT_INSTALLED', 1001);


//+-----------+//
//             //
// POST PARAMS //
//             //
//+-----------+//

if ( array_key_exists('confirmed', $_POST) ) { $action_confirmed = true; }


//+-------+//
//         //
// ACTIONS //
//         //
//+-------+//

//+~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~+//
// delete database tables and deactivate plugin //
//+~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~+//

global $wpdb;

// get current config
$iAmReading = new iAmReading();
$config     = $iAmReading->getConfig();

// check if config table exist, otherwise there's nothing to delete
if ( $action_confirmed === false ) {

	// set name for plugin config table
	$table_config = $wpdb->prefix . $iAmReading->tables['config'];

	// check if table exists and create otherwise with default config
	if( $wpdb->get_var("SHOW TABLES LIKE '".$table_config."'") != $table_config ) {

		$error_code = IAR_ERROR_NOT_INSTALLED;
	}

// delete tables
} else {

	$iAmReading->uninstall();
}


//+--------+//
//          //
// TEMPLATE //
//          //
//+--------+//

$template = new phpTemplate( IAR_ADMIN_TEMPLATE_DIR );
$template->setFile('iar-uninstall.php');

$template->setVar('error_code',       $error_code);
$template->setVar('action_confirmed', $action_confirmed);

//+~~~~~~~~~~~~~~+//
// parse template //
//+~~~~~~~~~~~~~~+//

$template->parse();
?>