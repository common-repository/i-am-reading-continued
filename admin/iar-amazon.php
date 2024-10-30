<?php
/**
 * Main configuration
 * 
 * @package i-am-reading
 */

//+------------------------+//
//                          //
// GLOBAL VARS AND INCLUDES //
//                          //
//+------------------------+//

include_once IAR_LIB_DIR.'class.iAmReading.php';
include_once IAR_LIB_DIR.'class.awsBooks.php';
include_once IAR_LIB_DIR.'class.phpTemplate.php';

//+~~~~~~~~~~~+//
// global vars //
//+~~~~~~~~~~~+//

$form_send = false;

// possible errors
$error_code = 0;

define('IAR_ERROR_NO_ACCESS_KEY_ID',        1001);
define('IAR_ERROR_WRONG_ACCESS_KEY_ID',     1002);
define('IAR_ERROR_NO_SECRET_ACCESS_KEY',    1003);
define('IAR_ERROR_WRONG_SECRET_ACCESS_KEY', 1004);


//+---------------+//
//                 //
// POST VALIDATION //
//                 //
//+---------------+//

if ( array_key_exists('form_send', $_POST) ) { $form_send = true; }

if ( $form_send === true ) {
	
	// Error: Missing Access Key ID
	if ( trim($_POST['aws_access_key_id'] == '') ) {
		
		$error_code = IAR_ERROR_NO_ACCESS_KEY_ID;
		
	// Error: Wrong Access Key ID
	} elseif ( preg_match('/[^a-z0-9]/i', trim($_POST['aws_access_key_id'])) ) {
		
		$error_code = IAR_ERROR_WRONG_ACCESS_KEY_ID;
		
	// Error: Missing Secret Access Key
	} elseif ( trim($_POST['aws_secret_key']) == '' ) {
		
		$error_code = IAR_ERROR_NO_SECRET_ACCESS_KEY;
		
	// Error: Wrong Secret Access Key
	} 
	else {
		
		// Send request to Amazon API to check if Access Key ID and Secret Key are okay
		$awsBooks   = new awsBooks($_POST['aws_access_key_id'], $_POST['aws_secret_key']);
		$check_keys = $awsBooks->checkKeys();
		
		if ( $check_keys !== true ) {
			
			switch ($check_keys) {
				
				case awsBooks::ERROR_WRONG_ACCESS_KEY_ID:
					$error_code = IAR_ERROR_WRONG_ACCESS_KEY_ID;
					break;
				
				case awsBooks::ERROR_WRONG_SECRET_ACCESS_KEY:
					$error_code = IAR_ERROR_WRONG_SECRET_ACCESS_KEY;
					break;
			}
		}
	}
}


//+--------------+//
//                //
// DATA RETRIEVAL //
//                //
//+--------------+//

if ( $form_send === false ) {
	
	// get configuration
	$iAmReading = new iAmReading();
	$config     = $iAmReading->getConfig();
}


//+-------+//
//         //
// ACTIONS //
//         //
//+-------+//

//+~~~~~~~~~~~~~+//
// save settings //
//+~~~~~~~~~~~~~+//

if ( ($form_send === true) && ($error_code === 0) ) {
	
	// update config in db
	$config_new = array('data_source'         => $_POST['data_source'],
	                    'aws_country_code'    => $_POST['aws_country_code'],
	                    'aws_access_key_id'   => $_POST['aws_access_key_id'],
	                    'aws_secret_key'      => $_POST['aws_secret_key'],
	                    'aws_associates_id'   => $_POST['aws_associates_id'],
	                    'display_amazon_link' => $_POST['display_amazon_link']);
	
	$iAmReading = new iAmReading();
	$iAmReading->updateConfig($config_new);
}


//+--------+//
//          //
// TEMPLATE //
//          //
//+--------+//

$template = new phpTemplate( IAR_ADMIN_TEMPLATE_DIR );
$template->setFile('iar-amazon.php');

//+~~~~~~~~+//
// set vars //
//+~~~~~~~~+//

$template->setVar('form_send',  $form_send);
$template->setVar('error_code', $error_code);

// prefill form
if ( $form_send === false ) {
	
	$template->setVar('prefill', $config);
	
} else {
	
	$template->setVar('prefill', $_POST);
}

//+~~~~~~~~~~~~~~+//
// parse template //
//+~~~~~~~~~~~~~~+//

$template->parse();
?>