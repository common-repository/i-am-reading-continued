<?php
/**
 * Book search & settings
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

// post params
$book_author = '';
$pages_read  = '';

// get params
$asin = '';

// book data stored in db
$book = array();

// possible errors
$error_code = 0;

define('IAR_ERROR_NO_BOOK',                1001);
define('IAR_ERROR_NO_BOOK_TITLE',          1002);
define('IAR_ERROR_WRONG_PAGES_READ_VALUE', 1003);
define('IAR_ERROR_HIGH_PAGES_READ_VALUE',  1004);
define('IAR_ERROR_NO_ASIN',                1005);

// instantiate plugin management class
$iAmReading = new iAmReading();


//+-----------+//
//             //
// POST PARAMS //
//             //
//+-----------+//

if ( array_key_exists('form_send', $_POST) ) {
	
	$form_send = true;
	
	// query type
	if ( array_key_exists('query', $_POST) && (trim($_POST['query']) != '') ) { $query_type = trim($_POST['query']); }
	
	// book title / current pages read
	if ( array_key_exists('book_title', $_POST) && (trim($_POST['book_title']) != '') ) { $book_title = trim(stripslashes($_POST['book_title'])); }
	if ( array_key_exists('pages_read', $_POST) && ((int)$_POST['pages_read'] > 0) ) { $pages_read = (int)$_POST['pages_read']; }
	
	// ASIN
	if ( array_key_exists('book_asin', $_POST) && (trim($_POST['book_asin']) != '') ) { $book_asin = trim($_POST['book_asin']); }
}

//+----------+//
//            //
// GET PARAMS //
//            //
//+----------+//

if ( array_key_exists('query', $_GET) ) {
	
	// query type
	if ( array_key_exists('query', $_GET) && (trim($_GET['query']) != '') ) { $query_type = trim($_GET['query']); }
	
	// set new book as current
	if ( $query_type == 'set_book' ) {
		
		// ASIN
		if ( array_key_exists('asin', $_GET) && (trim($_GET['asin']) != '') ) { $book_asin = trim($_GET['asin']); }
	}
}


//+--------------+//
//                //
// DATA RETRIEVAL //
//                //
//+--------------+//

//+~~~~~~~~~~~~~~~~~~~~~~~~+//
// get current book from db //
//+~~~~~~~~~~~~~~~~~~~~~~~~+//

$book = $iAmReading->getBook();

if ( $book !== false ) {
	
	// get read pages
	$book['pages_read'] = $iAmReading->getConfig('pages_read');
	
} elseif ( $form_send === false ) {
	
	$error_code = IAR_ERROR_NO_BOOK;
}


//+----------------+//
//                  //
// PARAM VALIDATION //
//                  //
//+----------------+//

if ( $form_send === true )  {
	
	if ( $query_type === 'set_book' ) {
		
		if ( $book_asin == '' ) {
			
			$error_code = IAR_ERROR_NO_ASIN;
		}
		
	} elseif ( $query_type === 'update_current' ) {
		
		if ( $book_title == '' ) {
			
			$error_code = IAR_ERROR_NO_BOOK_TITLE;
			
		} elseif ( (strlen($pages_read === 0)) || (preg_match('/[^0-9]/', $pages_read)) ) {
			
			$error_code = IAR_ERROR_WRONG_PAGES_READ_VALUE;
		}
	}
}


//+-------+//
//         //
// ACTIONS //
//         //
//+-------+//

if ( ($form_send === true) && ($error_code === 0) ) {
	
	//+~~~~~~~~~~~~~~~~~~~~~~+//
	// save current book info //
	//+~~~~~~~~~~~~~~~~~~~~~~+//
	
	if ( $query_type === 'update_current' ) {
		
		// update config in db
		$config_new = array('pages_read' => $pages_read);
		
		$iAmReading->updateConfig($config_new);
		
		// update book data
		$book['title']      = $book_title;
		$book['pages_read'] = $pages_read;
		$book_data_new = array('title' => $book_title);
		
		$iAmReading->updateBook($book_data_new);
		
	//+~~~~~~~~~~~~~~~~~~~~~~~+//
	// set new book as current //
	//+~~~~~~~~~~~~~~~~~~~~~~~+//
	} elseif ( $query_type === 'set_book' ) {
		
		$config = $iAmReading->getConfig();
		
		$awsBooks = new awsBooks();
		$awsBooks->setASIN( $book_asin );
		$awsBooks->setCountryCode( $config['aws_country_code'] );
		$awsBooks->setAccessKeyID( $config['aws_access_key_id'] );
		$awsBooks->setSecretKey( $config['aws_secret_key'] );
		$awsBooks->setAssociatesID( $config['aws_associates_id'] );
		
		$get_book = $awsBooks->getBooksByID('ASIN');
		$book     = $get_book[0];
		
		$book['pages_read'] = 0;
		
		$iAmReading->setBookCache($book);
		$iAmReading->updateConfig(array('pages_read' => 0));
	}
}


//+--------+//
//          //
// TEMPLATE //
//          //
//+--------+//

$template = new phpTemplate( IAR_ADMIN_TEMPLATE_DIR );
$template->setFile('iar-current.php');

//+~~~~~~~~+//
// set vars //
//+~~~~~~~~+//

$template->setVar('query_type',         $query_type);
$template->setVar('form_send',          $form_send);
$template->setVar('error_code',         $error_code);
$template->setVar('book',               $book);
$template->setVar('pages_read',         $book_data['pages_read']);
$template->setVar('pages_total',        $book_data['pages_total']);

//+~~~~~~~~~~~~~~+//
// parse template //
//+~~~~~~~~~~~~~~+//

$template->parse();
?>