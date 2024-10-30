<?php
/**
 * Class for reading book information from Amazon Web Services<br />
 * Coded and needed for WP plugin 'I am reading'
 * 
 * @author     Dominik Hanke <mail@dhdev.de>, Regina Struminski <support@i-am-reading.ginchen.de>
 * @copyright  Copyright 2009, Dominik Hanke, Regina Struminski
 * @link       http://i-am-reading.ginchen.de
 *
 * @package i-am-reading
 */
class awsBooks {
	
	/** @var  string  Amazon Access Key ID (needed for AWS queries) */
	protected $access_key_id = null;
	
	/** @var  string  Amazon Secret Key (needed for AWS queries) */
	protected $secret_key = null;
	
	/** @var  string  Amazon Associates ID (get a fee, if people buy the books) */
	protected $associates_id = null;
	
	/** @var  string  ISBN */
	protected $isbn = 0;
	
	/** @var  string  book title */
	protected $title = '';
	
	/** @var  string  book author */
	protected $author = '';
	
	/** @var  string  item page (for search requests) */
	protected $item_page = 1;
	
	/** @var  string  country code */
	protected $country_code = 'de';
	
	/** @var array  list of aws links for different countries */
	protected $aws_countries = array('de' => 'ecs.amazonaws.de',
	                                 'us' => 'ecs.amazonaws.com',
	                                 'uk' => 'ecs.amazonaws.co.uk',
	                                 'jp' => 'ecs.amazonaws.jp',
	                                 'fr' => 'ecs.amazonaws.fr',
	                                 'ca' => 'ecs.amazonaws.ca');
	
	/** @var  int  code of first occured error */
	protected $error_code = 0;
	
	// SearchIndex for ItemLookup and ItemSearch requests
	const AWS_SEARCH_INDEX = 'Books';
	
	// possible error codes
	const ERROR_WRONG_ACCESS_KEY_ID     = 1001;
	const ERROR_WRONG_SECRET_ACCESS_KEY = 1002;
	const ERROR_WRONG_ISBN              = 1003;
	const ERROR_NO_MATCHES              = 1004;
	
	/**
	 * Constructor, may set Access Key ID and Associates ID
	 *
	 * @param  string  $access_key_id  Amazon Access Key ID (needed for AWS queries)
	 * @param  string  $secret_key     Amazon Sectet Key    (needed for AWS queries)
	 * @param  string  $associates_id  Amazon Associates ID (get a fee, if people buy the books)
	 *
	 * @return void
	 */
	public function __construct( $access_key_id = null, $secret_key = null, $associates_id = null ) {
		
		$this->setAccessKeyID( $access_key_id );
		$this->setSecretKey( $secret_key );
		$this->setAssociatesID( $associates_id );
	}
	
	/**
	 * Generates URL for xml request
	 *
	 * @return  string  generated URL
	 */
	protected function generateRequestURL( $params = array() ) {
		
		// set default params and merge with args
		$defaults = array('AWSAccessKeyId' => $this->access_key_id,
		                  'AssociateTag'   => $this->associates_id,
		                  'Operation'      => 'ItemLookup',
		                  'IdType'         => 'ISBN',
		                  'ItemId'         => '',
		                  'Author'         => '',
		                  'Title'          => '',
		                  'ItemPage'       => 1,
		                  'ResponseGroup'  => 'Medium',
		                  'Service'        => 'AWSECommerceService',
		                  'Timestamp'      => gmdate('Y-m-d\TH:i:s\Z'),
		                  'Version'        => '2009-03-31');
		
		$params = array_merge($defaults, $params);
		
		// sort params on byte value
		ksort($params);
		
		foreach ( $params as $param_name => $param_value ) {
			
			$param_name  = str_replace('%7E', '~', rawurlencode($param_name));
			
			if ( $param_name != 'Power' ) {
				
				$param_value = str_replace('%7E', '~', rawurlencode($param_value));
				
			} else {
				
				$param_value = str_replace('%7E', '~', rawurlencode($param_value));
			}
			
			$canonical_params[] = $param_name . '=' . $param_value;
		}
		
		// tie them all together
		$canonical_string = implode('&', $canonical_params);
		$string_to_sign   = "GET\n" . $this->aws_countries[$this->country_code]."\n" . "/onca/xml\n" . $canonical_string;
		$signature        = urlencode(base64_encode(hash_hmac('sha256', $string_to_sign, $this->secret_key, true)));
		$request_url      = 'http://'.$this->aws_countries[$this->country_code].'/onca/xml?' . $canonical_string . '&Signature=' . $signature;
		
		// return generated URL
		return $request_url;
	}
	
	/**
	 * Searches for books by using ISBN or ASIN
	 * 
	 * @return  array  book information / error code
	 */
	public function getBooksByID( $id_type = 'ISBN' ) {
		
		$id_type = strtoupper($id_type);
		
		//+ generate xml request url +//
		$params = array('AWSAccessKeyId' => $this->access_key_id,
		                'AssociateTag'   => $this->associates_id,
		                'AssociateTag'   => $this->associates_id,
		                'Operation'      => 'ItemLookup',
		                'IdType'         => $id_type);
		
		if ( $id_type === 'ISBN' ) {
			
			$params['SearchIndex'] = self::AWS_SEARCH_INDEX;
			$params['ItemId']      = $this->isbn;
			
		} elseif ( $id_type === 'ASIN' ) {
			
			$params['ItemId'] = $this->asin;
		}
		
		$xml_url = $this->generateRequestURL($params);
		
		//+ read out xml data +//
		$aws_xml = $this->sendHttpRequest($xml_url);
		
		//+ initialise return value +//
		$books = array();
		
		//+ instantiate SimpleXML +//
		$xml = new SimpleXMLElement($aws_xml);
		
		//+ error code and message, if error occured +//
		$aws_error_code    = '';
		$aws_error_message = '';
		
		//+ read out error information +//
		if( $xml->OperationRequest->Errors ) {
			
			$aws_error_code    = $xml->OperationRequest->Errors->Error->Code;
			$aws_error_message = $xml->OperationRequest->Errors->Error->Message;
			
		} elseif( $xml->Items->Request->Errors ) {
			
			$aws_error_code    = $xml->Items->Request->Errors->Error->Code;
			$aws_error_message = $xml->Items->Request->Errors->Error->Message;
		}
		
		//+ analyze first occured error +//
		if ( $aws_error_code != '' ) {
			
			// missing parameters
			if ( $aws_error_code == 'AWS.MinimumParameterRequirement' ) {
				
				// parameter AWSAccessKeyId
				if ( strpos($aws_error_message, 'AWSAccessKeyId') > -1 ) {
					
					$this->error_code = self::ERROR_WRONG_ACCESS_KEY_ID;
				}
				
			// wrong parameter value
			} elseif ( $aws_error_code == 'AWS.InvalidParameterValue' ) {
				
				// parameter AWSAccessKeyId
				if ( strpos($aws_error_message, 'AWSAccessKeyId') > -1 ) {
					
					$this->error_code = self::ERROR_WRONG_ACCESS_KEY_ID;
					
				// parameter ItemId (= ISBN)
				} elseif ( strpos($aws_error_message, 'ItemId') > -1 ) {
					
					$this->error_code = self::ERROR_WRONG_ISBN;
				}
			}
			
			// set error in return value
			$books = array('error' => $this->error_code);
			
		//+ generate return array +//
		} else {
			
			foreach ( $book_item = $xml->Items->Item as $book_item ) {
				
				// image :: small
				$aws_image_small = array('URL'    => (string) $book_item->SmallImage->URL,
				                         'Height' => (string) $book_item->SmallImage->Height,
				                         'Width'  => (string) $book_item->SmallImage->Width);
				
				// image :: medium
				$aws_image_medium = array('URL'    => (string) $book_item->MediumImage->URL,
				                          'Height' => (string) $book_item->MediumImage->Height,
				                          'Width'  => (string) $book_item->MediumImage->Width);
				
				// image :: large
				$aws_image_large = array('URL'    => (string) $book_item->LargeImage->URL,
				                         'Height' => (string) $book_item->LargeImage->Height,
				                         'Width'  => (string) $book_item->LargeImage->Width);
				
				$aws_images = array('small'  => $aws_image_small, 'medium' => $aws_image_medium, 'large'  => $aws_image_large);
				
				// authors
				$aws_authors = array();
				
				foreach ( $book_item->ItemAttributes->Author as $this_author ) {
					
					$aws_authors[] = (string) $this_author;
				}
				
				$books['item_pages'] = (string) $xml->Items->TotalPages;
				
				// create final array
				$book_info = array();
				
				$book_info['asin']        = (string) $book_item->ASIN;
				$book_info['isbn']        = (string) $book_item->ItemAttributes->ISBN;
				$book_info['title']       = (string) $book_item->ItemAttributes->Title;
				$book_info['authors']     = $aws_authors;
				$book_info['authors_str'] = implode(', ', $aws_authors);
				$book_info['binding']     = (string) $book_item->ItemAttributes->Binding;
				$book_info['format']      = (string) $book_item->ItemAttributes->Format;
				$book_info['publisher']   = (string) $book_item->ItemAttributes->Publisher;
				$book_info['public_date'] = (string) $book_item->ItemAttributes->PublicationDate;
				$book_info['book_link']   = (string) $book_item->DetailPageURL;
				$book_info['images']      = $aws_images;
				$book_info['pages_total'] = (int) $book_item->ItemAttributes->NumberOfPages;
				
				$books[] = $book_info;
			}
		}
		
		return $books;
	}
	
	/**
	 * Searches for books by using title / author as keywords
	 * 
	 * @return  array  book information / error code
	 */
	public function getBooksByKeywords() {
		
		//+ generate xml request url +//
		$params = array('AWSAccessKeyId' => $this->access_key_id,
		                'AssociateTag'   => $this->associates_id,
		                'AssociateTag'   => $this->associates_id,
		                'Operation'      => 'ItemSearch',
		                'SearchIndex'    => self::AWS_SEARCH_INDEX,
		                'ItemPage'       => $this->item_page
		                );
		
		if ( ($this->author !== '') && ($this->title !== '') ) {
			
			$keywords = '(author: '.$this->author.' and title: '.$this->title.')';
			
		} elseif ( $this->author !== '' ) {
			
			$keywords = 'author: '.$this->author;
			
		} elseif ( $this->title !== '' ) {
			
			$keywords = 'title: '.$this->title;
		}
		
		$params['Power'] = $keywords . ' and not binding: Audio CD and not binding: Audio Cassette and not binding: Turtleback and not binding: CD-ROM';
		
		$xml_url = $this->generateRequestURL($params);
		
		//+ read out xml data +//
		$aws_xml = $this->sendHttpRequest($xml_url);
		
		//+ initialise return value +//
		$books = array();
		
		//+ instantiate SimpleXML +//
		$xml = new SimpleXMLElement($aws_xml);
		
		//+ error code and message, if error occured +//
		$aws_error_code    = '';
		$aws_error_message = '';
		
		//+ read out error information +//
		if( $xml->OperationRequest->Errors ) {
			
			$aws_error_code    = $xml->OperationRequest->Errors->Error->Code;
			$aws_error_message = $xml->OperationRequest->Errors->Error->Message;
			
		} elseif( $xml->Items->Request->Errors ) {
			
			$aws_error_code    = $xml->Items->Request->Errors->Error->Code;
			$aws_error_message = $xml->Items->Request->Errors->Error->Message;
		}
		
		//+ analyze first occured error +//
		if ( $aws_error_code != '' ) {
			
			// missing parameters
			if ( $aws_error_code == 'AWS.MinimumParameterRequirement' ) {
				
				// parameter AWSAccessKeyId
				if ( strpos($aws_error_message, 'AWSAccessKeyId') > -1 ) {
					
					$this->error_code = self::ERROR_WRONG_ACCESS_KEY_ID;
				}
				
			// wrong parameter value
			} elseif ( $aws_error_code == 'AWS.InvalidParameterValue' ) {
				
				// parameter AWSAccessKeyId
				if ( strpos($aws_error_message, 'AWSAccessKeyId') > -1 ) {
					
					$this->error_code = self::ERROR_WRONG_ACCESS_KEY_ID;
					
				}
			// no exact matches found
			} elseif ( $aws_error_code == 'AWS.ECommerceService.NoExactMatches' ) {
				
				$this->error_code = self::ERROR_NO_MATCHES;
			}
			
			// set error in return value
			$books = array('error' => $this->error_code);;
			
		//+ generate return array +//
		} else {
			
			foreach ( $book_item = $xml->Items->Item as $book_item ) {
				
				// image :: small
				$aws_image_small = array('URL'    => (string) $book_item->SmallImage->URL,
				                         'Height' => (string) $book_item->SmallImage->Height,
				                         'Width'  => (string) $book_item->SmallImage->Width);
				
				// image :: medium
				$aws_image_medium = array('URL'    => (string) $book_item->MediumImage->URL,
				                          'Height' => (string) $book_item->MediumImage->Height,
				                          'Width'  => (string) $book_item->MediumImage->Width);
				
				// image :: large
				$aws_image_large = array('URL'    => (string) $book_item->LargeImage->URL,
				                         'Height' => (string) $book_item->LargeImage->Height,
				                         'Width'  => (string) $book_item->LargeImage->Width);
				
				$aws_images = array('small'  => $aws_image_small, 'medium' => $aws_image_medium, 'large'  => $aws_image_large);
				
				// authors
				$aws_authors = array();
				
				foreach ( $book_item->ItemAttributes->Author as $this_author ) {
					
					$aws_authors[] = (string) $this_author;
				}
				
				$books['item_pages'] = (string) $xml->Items->TotalPages;
				
				// create final array
				$book_info = array();
				
				$book_info['asin']          = (string) $book_item->ASIN;
				$book_info['isbn']          = (string) $book_item->ItemAttributes->ISBN;
				$book_info['title']         = (string) $book_item->ItemAttributes->Title;
				$book_info['authors']       = $aws_authors;
				$book_info['authors_str']   = implode(', ', $aws_authors);
				$book_info['binding']       = (string) $book_item->ItemAttributes->Binding;
				$book_info['format']        = (string) $book_item->ItemAttributes->Format;
				$book_info['publisher']     = (string) $book_item->ItemAttributes->Publisher;
				$book_info['public_date']   = (string) $book_item->ItemAttributes->PublicationDate;
				$book_info['book_link']     = (string) $book_item->DetailPageURL;
				$book_info['images']        = $aws_images;
				$book_info['pages_total']   = (int) $book_item->ItemAttributes->NumberOfPages;
				
				$books[] = $book_info;
			}
		}
		
		return $books;
	}
	
	/**
	 * Checks if current set Access Key ID and Secret Access Key
	 * are okay. Sends a request to AWS and examines the
	 * response.
	 * 
	 * @return bool/int  true / error code
	 */
	public function checkKeys() {
		
		// send request to AWS
		$aws_url = $this->generateRequestURL();
		$aws_xml = $this->sendHttpRequest($aws_url);
		
		// get xml object from response
		$xml = new SimpleXMLElement($aws_xml);
		
		// error: Wrong Access Key ID
		if ( $xml->Error->Code == 'InvalidClientTokenId' ) {
			
			return self::ERROR_WRONG_ACCESS_KEY_ID;
			
		// error: Wrong Secret Key
		} elseif ( $xml->Error->Code == 'SignatureDoesNotMatch' ) {
			
			return self::ERROR_WRONG_SECRET_ACCESS_KEY;
		}
		
		return true;
	}
	
	/**
	 * Set country code to use for AWS requests
	 *
	 * @param  string  $country_code  country code to get the aws url
	 * @return void
	 */
	public function setCountryCode( $country_code = null ) {
		 
		if ( !is_null($country_code) && (array_key_exists($country_code, $this->aws_countries)) ) {
			
			$this->country_code = $country_code;
		}
	 }
	
	/**
	 * Set Access Key ID to use for AWS requests
	 *
	 * @param  string  $access_key_id  Amazon Access Key ID
	 * @return void
	 */
	public function setAccessKeyID( $access_key_id = null ) {
		 
		if ( !is_null($access_key_id) ) {
			
			$this->access_key_id = $access_key_id;
		}
	 }
	
	/**
	 * Set Secret Access Key to use for AWS requests
	 *
	 * @param  string  $secret_key  Amazon Secret Key
	 * @return void
	 */
	public function setSecretKey( $secret_key = null ) {
		 
		if ( !is_null($secret_key) ) {
			
			$this->secret_key = $secret_key;
		}
	 }
	
	/**
	 * Set Associates Key ID to use for AWS links
	 *
	 * @param  string  $associates_id  Amazon Associates ID (get a fee, if people buy the books)
	 * @return void
	 */
	public function setAssociatesID( $associates_id = null ) {
		 
		if ( !is_null($associates_id) ) {
			
			$this->associates_id = $associates_id;
		}
	}
	
	/**
	 * Set ISBN to use for AWS requests
	 *
	 * @param  string  $isbn  ISBN-10 / ISBN-13
	 * @return void
	 */
	public function setISBN( $isbn = null ) {
		
		if ( !is_null($isbn) ) {
			
			$isbn = str_replace('-', '', $isbn);
			
			switch ( strlen($isbn) ) {
				
				case 10:
				case 12:
					$this->isbn = $this->isbn10_to_isbn13($isbn);
					break;
				
				case 13:
					$this->isbn = $isbn;
					break;
			}
		}
	}
	
	
	/**
	 * Set ASIN to use for AWS requests
	 *
	 * @param  string  $asin  ASIN
	 * @return void
	 */
	public function setASIN( $asin = null ) {
		
		if ( !is_null($asin) ) {
			
			$this->asin = $asin;
		}
	}
	
	/**
	 * Set book title to use for AWS requests
	 *
	 * @param  string  $title  book title
	 * @return void
	 */
	public function setTitle( $title = '' ) {
		
		if ( trim($title) != '' ) {
			
			$this->title = trim($title);
		}
	}
	
	/**
	 * Set book author to use for AWS requests
	 *
	 * @param  string  $author  book author
	 * @return void
	 */
	public function setAuthor( $author = '' ) {
		
		if ( trim($author) != '' ) {
			
			$this->author = trim($author);
		}
	}
	
	/**
	 * Set item page AWS requests
	 *
	 * @param  int  $item_page  item page
	 * @return void
	 */
	public function setItemPage( $item_page = '' ) {
		
		if ( (int)$item_page > 0 ) {
			
			$this->item_page = (int)$item_page;
		}
	}
	
	/**
	 * Converts ISBN-10 to ISBN-13
	 *
	 * @param  string  $isbn  ISBN-10
	 * @return string         ISBN-13
	 */
	protected function isbn10_to_isbn13( $isbn = null ) {
		
		$isbn13 = $isbn;
		$isbn   = trim( preg_replace( '![^0-9X]!', '', $isbn ) );
		
		$isbn13 = '978' . substr( $isbn, 0, 9 );
		$check  = 0;
		
		for ($i = 0; $i < 11; $i += 2) {
			
			$check += substr( $isbn13, $i, 1 );
			$check += substr( $isbn13, $i+1, 1 ) * 3;
		}
		
		$check = ( 10 - ( $check % 10 ) );
		
		if ( $check == 10 ) {
			
			$check = 0;
		}
		
		$isbn13 .= $check;
		
		return $isbn13;
	}
	
	/**
	 * Sends an HTTP request and parses the response by
	 * using different PHP functions, depending on the
	 * server configuration.
	 * 
	 * @param  string  URL to retrieve
	 * @return string  response text
	 */
	public function sendHttpRequest( $url ) {
		
		// initialize return value
		$response_text = '';
		
		// use curl, if enabled
		if ( function_exists('curl_init') === true ) {
			
			// initialize new curl resource
			$ch = curl_init();
			
			// set options and use firefox user agent to mimic a browser
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.6pre) Gecko/2009011606 Firefox/3.1'); 
			
			// get the content
			$response_text = curl_exec($ch);
			
		// use file_get_contents() instead, if possible
		} elseif ( ini_get('allow_url_fopen') == '1' ) {
			
			$response_text = file_get_contents($url);
		}
		
		return $response_text;
	}
}
?>