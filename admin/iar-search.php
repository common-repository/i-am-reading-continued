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
include_once IAR_LIB_DIR.'class.pagination.php';

//+~~~~~~~~~~~+//
// global vars //
//+~~~~~~~~~~~+//

$form_send  = false;

// post / get params
$query_type  = null;
$item_page   = 1;
$book_author = '';
$book_title  = '';
$book_isbn   = '';
$book_asin   = '';

// books from search
$books = array();

// possible errors
$error_code = 0;

define('IAR_ERROR_MISSING_KEYS', 1001);
define('IAR_ERROR_NO_ISBN',      1002);
define('IAR_ERROR_WRONG_ISBN',   1003);
define('IAR_ERROR_NO_KEYWORDS',  1004);
define('IAR_ERROR_NO_MATCHES',   1005);

// instantiate plugin management class
$iAmReading = new iAmReading();

// load config
$config = $iAmReading->getConfig();


//+----------~~~~~~~~~~~~-+//
//                         //
// CHECK PAGE REQUIREMENTS //
//                         //
//+----------~~~~~~~~~~~~-+//

if ( ($config['aws_access_key_id'] == '') || ($config['aws_secret_key'] == '') ) {
	
	$error_code = IAR_ERROR_MISSING_KEYS;
}


//+-----------+//
//             //
// POST PARAMS //
//             //
//+-----------+//

if ( array_key_exists('form_send', $_POST) ) {
	
	$form_send = true;
	
	// query type
	if ( array_key_exists('query', $_POST) ) { $query_type = trim($_POST['query']); }
	
	if ( ($query_type == 'search_isbn') || ($query_type == 'change_book') ) {
		
		if ( array_key_exists('book_isbn', $_POST) && (trim($_POST['book_isbn']) != '') ) { $book_isbn = trim($_POST['book_isbn']); }
		
	} elseif ( $query_type == 'search_keywords' ) {
		
		// book title / author
		if ( array_key_exists('book_title', $_POST) && (trim($_POST['book_title']) != '') ) { $book_title = trim($_POST['book_title']); }
		if ( array_key_exists('book_author', $_POST) && (trim($_POST['book_author']) != '') ) { $book_author = trim($_POST['book_author']); }
	}
}


//+----------+//
//            //
// GET PARAMS //
//            //
//+----------+//

if ( array_key_exists('query', $_GET) ) {
	
	$query_type = trim($_GET['query']);
	$form_send  = true;
	
	if ( $query_type === 'search_keywords' ) {
		
		// book title / author
		if ( array_key_exists('title', $_GET) && (trim(urldecode($_GET['title'])) != '') ) { $book_title = trim(urldecode($_GET['title'])); }
		if ( array_key_exists('author', $_GET) && (trim(urldecode($_GET['author'])) != '') ) { $book_author = trim(urldecode($_GET['author'])); }
		
	} elseif ( $query_type === 'search_isbn' ) {
		
		if ( array_key_exists('isbn', $_GET) && (trim($_GET['isbn']) != '') ) { $book_isbn = trim($_GET['book_isbn']); }
	}
	
	// current item page
	if ( array_key_exists('ip', $_GET) && ((int)$_GET['ip'] > 0) ) { $item_page = (int)$_GET['ip']; }
}


//+----------------+//
//                  //
// PARAM VALIDATION //
//                  //
//+----------------+//

if ( $form_send === true )  {
	
	// search by ISBN
	if ( $query_type === 'search_isbn' ) {
		
		if ( $book_isbn === '' ) {
			
			$error_code = IAR_ERROR_NO_ISBN;
		}
		
	// search by author / title
	} elseif ( $query_type === 'search_keywords' ) {
		
		if ( ($book_title === '') && ($book_author === '') ) {
			
			$error_code = IAR_ERROR_NO_KEYWORDS;
		}
		
	// set book as current
	} elseif ( $query_type === 'set_book' ) {
		
		if ( ($book_asin === '') ) {
			
			$error_code = IAR_ERROR_NO_ASIN;
		}
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

$book_data  = $iAmReading->getBook();

if ( $book_data !== false ) {
	
	// get read pages
	$current_pages_read = $config['pages_read'];
}

// search for books
if ( ($form_send === true) && ($error_code === 0) ) {
	
	//+~~~~~~~~~~~~~~~~~~~~~~~~+//
	// search for books by ISBN //
	//+~~~~~~~~~~~~~~~~~~~~~~~~+//
	
	if ( $query_type === 'search_isbn' ) {
		
		$awsBooks = new awsBooks();
		$awsBooks->setISBN( $book_isbn );
		$awsBooks->setCountryCode( $config['aws_country_code'] );
		$awsBooks->setAccessKeyID( $config['aws_access_key_id'] );
		$awsBooks->setSecretKey( $config['aws_secret_key'] );
		$awsBooks->setAssociatesID( $config['aws_associates_id'] );
		
		$book_search = $awsBooks->getBooksByID('ISBN');
		
		if ( array_key_exists('error', $book_search) ) {
			
			if ( $book_search['error'] == awsBooks::ERROR_WRONG_ISBN) {
				
				$error_code = IAR_ERROR_WRONG_ISBN;
			}
		}
		
	//+~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~+//
	// search for books by title / author //
	//+~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~+//
	} elseif ( $query_type === 'search_keywords' ) {
		
		$awsBooks = new awsBooks();
		$awsBooks->setCountryCode( $config['aws_country_code'] );
		$awsBooks->setAccessKeyID( $config['aws_access_key_id'] );
		$awsBooks->setSecretKey( $config['aws_secret_key'] );
		$awsBooks->setAssociatesID( $config['aws_associates_id'] );
		$awsBooks->setTitle( $book_title );
		$awsBooks->setAuthor( $book_author );
		$awsBooks->setItemPage( $item_page );
		
		$book_search = $awsBooks->getBooksByKeywords();
		
		if ( array_key_exists('error', $book_search) ) {
			
			if ( $book_search['error'] == awsBooks::ERROR_NO_MATCHES) {
				
				$error_code = IAR_ERROR_NO_MATCHES;
			}
		}
		
		// generate pagination
		$pagination = new pagination();
		$pagination->items(($book_search['item_pages']*10));
		$pagination->limit(10);
		$pagination->target($_SERVER['PHP_SELF'].'?page=iar_search&query='.$query_type.'&title='.urlencode($book_title).'&author='.urlencode($book_author));
		$pagination->currentPage($item_page);
		$pagination->calculate();
		$pagination->parameterName('ip');
		$pagination->adjacents(1);
	}
}


//+--------+//
//          //
// TEMPLATE //
//          //
//+--------+//

$template = new phpTemplate( IAR_ADMIN_TEMPLATE_DIR );
$template->setFile('iar-search.php');

//+~~~~~~~~+//
// set vars //
//+~~~~~~~~+//

$template->setVar('form_send',          $form_send);
$template->setVar('query_type',         $query_type);
$template->setVar('error_code',         $error_code);
$template->setVar('current_book_title', $book_data['title']);
$template->setVar('pages_total',        $book_data['pages_total']);
$template->setVar('book_data',          $book_data);
$template->setVar('book_search',        $book_search);

if ( $query_type === 'search_keywords' ) {
	
	$template->setVar('pagination', $pagination);
}

// form prefill
if ( ($form_send === false) || ($query_type === 'change_book') ) {
	
	$template->setVar('book_title',   $book_title);
	$template->setVar('current_pages_read', $current_pages_read);
	
} else {
	
	$template->setVar('book_isbn',          $book_isbn);
	$template->setVar('book_title',         $book_title);
	$template->setVar('book_author',        $book_author);
	$template->setVar('current_book_title', $current_book_title);
	$template->setVar('current_pages_read', $current_pages_read);
}


//+~~~~~~~~~~~~~~+//
// parse template //
//+~~~~~~~~~~~~~~+//

$template->parse();
?>