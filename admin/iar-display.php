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
include_once IAR_LIB_DIR.'class.phpTemplate.php';

//+~~~~~~~~~~~+//
// global vars //
//+~~~~~~~~~~~+//

$form_send = false;

// websafe fonts
$web_fonts = array('Verdana', 'Arial', 'Tahoma', 'Comic Sans MS', 'Courier New', 'Georgia', 'Impact');

// book data from cache
$book_data = null;

// config from db
$config = null;

// possible errors
$error_code = 0;

define('IAR_ERROR_NO_WIDGET_TITLE', 1001);
define('IAR_ERROR_NO_WIDGET_ITEMS', 1002);


//+---------------+//
//                 //
// POST VALIDATION //
//                 //
//+---------------+//

if ( array_key_exists('form_send', $_POST) ) { $form_send = true; }

if ( $form_send === true ) {
	
	// missing widget title
	if ( trim($_POST['widget_title']) == '' ) {
		
		$error_code = IAR_ERROR_NO_WIDGET_TITLE;
		
	// no items selected
	} elseif ( !array_key_exists('display_cover_image', $_POST) && !array_key_exists('display_progressbar', $_POST) && !array_key_exists('display_book_title', $_POST) ) {
		
		$error_code = IAR_ERROR_NO_WIDGET_ITEMS;
	}
}

//+--------------+//
//                //
// DATA RETRIEVAL //
//                //
//+--------------+//

if ( ($form_send === false) && ($book_data !== false) ) {
	
	// get book data
	$iAmReading = new iAmReading();
	$book_data  = $iAmReading->getBook();
	
	// get configuration
	$config     = $iAmReading->getConfig();
}


//+-------+//
//         //
// ACTIONS //
//         //
//+-------+//

//+~~~~~~~~~~~~~~~~~~~~~+//
// save display settings //
//+~~~~~~~~~~~~~~~~~~~~~+//

if ( ($form_send === true) && ($error_code === 0) ) {
	
	// update config in db
	$config_new = array('widget_title'                     => $_POST['widget_title'],
	                    'display_theme'                    => $_POST['display_theme'],
	                    'display_alignment'                => $_POST['display_alignment'],
	                    'display_cover_image'              => (int)$_POST['display_cover_image'],
	                    'display_book_title'               => (int)$_POST['display_book_title'],
	                    'display_progressbar'              => (int)$_POST['display_progressbar'],
	                    'display_cover_image_size'         => $_POST['display_cover_image_size'],
	                    'display_book_title_font_family'   => $_POST['display_book_title_font_family'],
	                    'display_book_title_font_size'     => $_POST['display_book_title_font_size'],
	                    'display_book_title_font_color'    => $_POST['display_book_title_font_color'],
	                    'display_progressbar_color_back'   => $_POST['display_progressbar_color_back'],
	                    'display_progressbar_color_front'  => $_POST['display_progressbar_color_front'],
	                    'display_progressbar_color_border' => $_POST['display_progressbar_color_border'],
	                    'display_progressbar_font_family'  => $_POST['display_progressbar_font_family'],
	                    'display_progressbar_font_size'    => $_POST['display_progressbar_font_size'],
	                    'display_progressbar_font_color'   => $_POST['display_progressbar_font_color']);
	
	$iAmReading = new iAmReading();
	$iAmReading->updateConfig($config_new);
}


//+--------+//
//          //
// TEMPLATE //
//          //
//+--------+//

$template = new phpTemplate( IAR_ADMIN_TEMPLATE_DIR );
$template->setFile('iar-display.php');

//+~~~~~~~~+//
// set vars //
//+~~~~~~~~+//

$template->setVar('form_send',  $form_send);
$template->setVar('error_code', $error_code);

// websafe fonts
$template->setVar('web_fonts', $web_fonts);

// book data
$template->setVar('book_data', $book_data);

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