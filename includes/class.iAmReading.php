<?php
/**
 * Management class of WP Plugin 'I am reading'.<br />
 * Handles setup of the database and everything else.
 * 
 * @author     Dominik Hanke <mail@dhdev.de>
 * @copyright  Copyright 2009, Dominik Hanke
 * @link       http://i-am-reading.ginchen.de
 *
 * @package i-am-reading
 */
class iAmReading {
	
	/** @var  array  name of plugin tables in db */
	public $tables = array('config' => 'iar_config');
	
	/** @var  string  plugin directory */
	public $plugin_dir = '';
	
	/** @var  string  text domain for gettext() translations */
	protected $text_domain = 0;
	
	/** @var  config  configuration */
	protected $config = null;
	
	/** @var  array  book data */
	protected $book_data = null;
	
	/** @var  error code first occured */
	protected $error_code = 0;
	
	// Possible errors
	const ERROR_UNKNOWN_ACCESS_KEY_ID = 1001;
	const ERROR_UNKNOWN_SECRET_KEY    = 1002;
	const ERROR_UNKNOWN_ISBN          = 1003;
	const ERROR_MISSING_CONFIG_TABLE  = 1004;
	
	/**
	 * Constructor determines the plugin's directory.
	 *
	 * @return void
	 */
	public function __construct() {
		
		// Pre-2.6 compatibility
		if ( ! defined( 'WP_PLUGIN_DIR' ) ) {
			
			define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );
		}
		
		$this->plugin_dir = WP_PLUGIN_DIR . '/i-am-reading-continued';
	}
	
	/**
	 * Creates database tables, if they don't exist and sets all
	 * config parameters to default values.
	 *
	 * @return void
	 */
	public function install() {
		
		// get wordpress database object
		global $wpdb;
		
		// set name for plugin config table
		$table_config = $wpdb->prefix . $this->tables['config'];
		
		// check if table exists and create otherwise with default config
		if( $wpdb->get_var("SHOW TABLES LIKE '".$table_config."'") != $table_config ) {
			
			// create table
			$sql = "CREATE TABLE ".$table_config." (
			          `option_id` INT NOT NULL AUTO_INCREMENT,
			          `option_name` VARCHAR(100) collate latin1_german1_ci default NULL,
			          `option_value` TEXT collate latin1_german1_ci default NULL,
			          PRIMARY KEY(`option_id`), UNIQUE INDEX(`option_name`)
			        )";
			
			$wpdb->query($sql);
			
			// insert default config
			$wpdb->query("INSERT INTO ".$table_config." (`option_name`, `option_value`) VALUES ('db_version', '".IAR_DB_VERSION."')");
			$wpdb->query("INSERT INTO ".$table_config." (`option_name`, `option_value`) VALUES ('widget_title', 'I am reading')");
			$wpdb->query("INSERT INTO ".$table_config." (`option_name`, `option_value`) VALUES ('data_source', 'aws')");
			$wpdb->query("INSERT INTO ".$table_config." (`option_name`, `option_value`) VALUES ('aws_country_code', 'de')");
			$wpdb->query("INSERT INTO ".$table_config." (`option_name`, `option_value`) VALUES ('aws_access_key_id', '')");
			$wpdb->query("INSERT INTO ".$table_config." (`option_name`, `option_value`) VALUES ('aws_secret_key', '')");
			$wpdb->query("INSERT INTO ".$table_config." (`option_name`, `option_value`) VALUES ('aws_associates_id', '')");
			$wpdb->query("INSERT INTO ".$table_config." (`option_name`, `option_value`) VALUES ('isbn', '')");
			$wpdb->query("INSERT INTO ".$table_config." (`option_name`, `option_value`) VALUES ('pages_read', 0)");
			$wpdb->query("INSERT INTO ".$table_config." (`option_name`, `option_value`) VALUES ('book_cache', '')");
			$wpdb->query("INSERT INTO ".$table_config." (`option_name`, `option_value`) VALUES ('display_theme', 'default')");
			$wpdb->query("INSERT INTO ".$table_config." (`option_name`, `option_value`) VALUES ('display_amazon_link', '0')");
			$wpdb->query("INSERT INTO ".$table_config." (`option_name`, `option_value`) VALUES ('display_alignment', 'left')");
			$wpdb->query("INSERT INTO ".$table_config." (`option_name`, `option_value`) VALUES ('display_cover_image', 1)");
			$wpdb->query("INSERT INTO ".$table_config." (`option_name`, `option_value`) VALUES ('display_cover_image_size', 'medium')");
			$wpdb->query("INSERT INTO ".$table_config." (`option_name`, `option_value`) VALUES ('display_book_title', 0)");
			$wpdb->query("INSERT INTO ".$table_config." (`option_name`, `option_value`) VALUES ('display_book_title_position', 'bottom')");
			$wpdb->query("INSERT INTO ".$table_config." (`option_name`, `option_value`) VALUES ('display_book_title_font_color', '#777777')");
			$wpdb->query("INSERT INTO ".$table_config." (`option_name`, `option_value`) VALUES ('display_book_title_font_family', 'Verdana')");
			$wpdb->query("INSERT INTO ".$table_config." (`option_name`, `option_value`) VALUES ('display_book_title_font_size', '10px')");
			$wpdb->query("INSERT INTO ".$table_config." (`option_name`, `option_value`) VALUES ('display_progressbar', 1)");
			$wpdb->query("INSERT INTO ".$table_config." (`option_name`, `option_value`) VALUES ('display_progressbar_color_back', '#CCCCCC')");
			$wpdb->query("INSERT INTO ".$table_config." (`option_name`, `option_value`) VALUES ('display_progressbar_color_front', '#008000')");
			$wpdb->query("INSERT INTO ".$table_config." (`option_name`, `option_value`) VALUES ('display_progressbar_color_border', '')");
			$wpdb->query("INSERT INTO ".$table_config." (`option_name`, `option_value`) VALUES ('display_progressbar_font_color', '#777777')");
			$wpdb->query("INSERT INTO ".$table_config." (`option_name`, `option_value`) VALUES ('display_progressbar_font_family', 'Verdana')");
			$wpdb->query("INSERT INTO ".$table_config." (`option_name`, `option_value`) VALUES ('display_progressbar_font_size', '10px')");
			
		// if table exists, check for database updates
		} else {
			
			$this->update();
		}
	}
	
	/**
	 * Checks if a database update is needed and executes queries,
	 * by using dbDelta function.
	 *
	 * @return void
	 */
	public function update() {
		
		// get wordpress database object
		global $wpdb;
		
		// set name for plugin config table
		$table_config = $wpdb->prefix . $this->tables['config'];
		
		// get currently installed version
		$version_installed = $wpdb->get_var("SELECT `option_value` FROM ".$table_config." WHERE `option_name` = 'db_version'");
		
		// update tables, if there is no version info or installed version is old
		if ( ($version_installed == null) || ($version_installed != IAR_DB_VERSION) ) {
			
			// include dbDelta function
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			
			// create table
			$sql = "CREATE TABLE ".$table_config." (
			          `option_id` INT NOT NULL AUTO_INCREMENT,
			          `option_name` VARCHAR(100) collate latin1_german1_ci default NULL,
			          `option_value` TEXT collate latin1_german1_ci default NULL,
			          PRIMARY KEY(`option_id`), UNIQUE INDEX(`option_name`)
			        )";
			
			dbDelta($sql);
			
			// insert / update table entries
			$wpdb->query("INSERT IGNORE INTO ".$table_config." (`option_name`, `option_value`) VALUES ('db_version', '".IAR_DB_VERSION."') ON DUPLICATE KEY UPDATE option_value = '".IAR_DB_VERSION."'");
			$wpdb->query("INSERT IGNORE INTO ".$table_config." (`option_name`, `option_value`) VALUES ('widget_title', 'I am reading')");
			$wpdb->query("INSERT IGNORE INTO ".$table_config." (`option_name`, `option_value`) VALUES ('data_source', 'aws')");
			$wpdb->query("INSERT IGNORE INTO ".$table_config." (`option_name`, `option_value`) VALUES ('aws_country_code', 'de')");
			$wpdb->query("INSERT IGNORE INTO ".$table_config." (`option_name`, `option_value`) VALUES ('aws_access_key_id', '')");
			$wpdb->query("INSERT IGNORE INTO ".$table_config." (`option_name`, `option_value`) VALUES ('aws_secret_key', '')");
			$wpdb->query("INSERT IGNORE INTO ".$table_config." (`option_name`, `option_value`) VALUES ('aws_associates_id', '')");
			$wpdb->query("INSERT IGNORE INTO ".$table_config." (`option_name`, `option_value`) VALUES ('isbn', '')");
			$wpdb->query("INSERT IGNORE INTO ".$table_config." (`option_name`, `option_value`) VALUES ('pages_read', 0)");
			$wpdb->query("INSERT IGNORE INTO ".$table_config." (`option_name`, `option_value`) VALUES ('book_cache', '')");
			$wpdb->query("INSERT IGNORE INTO ".$table_config." (`option_name`, `option_value`) VALUES ('display_theme', 'default')");
			$wpdb->query("INSERT IGNORE INTO ".$table_config." (`option_name`, `option_value`) VALUES ('display_amazon_link', '0')");
			$wpdb->query("INSERT IGNORE INTO ".$table_config." (`option_name`, `option_value`) VALUES ('display_alignment', 'left')");
			$wpdb->query("INSERT IGNORE INTO ".$table_config." (`option_name`, `option_value`) VALUES ('display_cover_image', 1)");
			$wpdb->query("INSERT IGNORE INTO ".$table_config." (`option_name`, `option_value`) VALUES ('display_cover_image_size', 'medium')");
			$wpdb->query("INSERT IGNORE INTO ".$table_config." (`option_name`, `option_value`) VALUES ('display_book_title', 0)");
			$wpdb->query("INSERT IGNORE INTO ".$table_config." (`option_name`, `option_value`) VALUES ('display_book_title_position', 'bottom')");
			$wpdb->query("INSERT IGNORE INTO ".$table_config." (`option_name`, `option_value`) VALUES ('display_book_title_font_color', '#777777')");
			$wpdb->query("INSERT IGNORE INTO ".$table_config." (`option_name`, `option_value`) VALUES ('display_book_title_font_family', 'Verdana')");
			$wpdb->query("INSERT IGNORE INTO ".$table_config." (`option_name`, `option_value`) VALUES ('display_book_title_font_size', '10px')");
			$wpdb->query("INSERT IGNORE INTO ".$table_config." (`option_name`, `option_value`) VALUES ('display_progressbar', 1)");
			$wpdb->query("INSERT IGNORE INTO ".$table_config." (`option_name`, `option_value`) VALUES ('display_progressbar_color_back', '#CCCCCC')");
			$wpdb->query("INSERT IGNORE INTO ".$table_config." (`option_name`, `option_value`) VALUES ('display_progressbar_color_front', '#008000')");
			$wpdb->query("INSERT IGNORE INTO ".$table_config." (`option_name`, `option_value`) VALUES ('display_progressbar_color_border', '')");
			$wpdb->query("INSERT IGNORE INTO ".$table_config." (`option_name`, `option_value`) VALUES ('display_progressbar_font_color', '#777777')");
			$wpdb->query("INSERT IGNORE INTO ".$table_config." (`option_name`, `option_value`) VALUES ('display_progressbar_font_family', 'Verdana')");
			$wpdb->query("INSERT IGNORE INTO ".$table_config." (`option_name`, `option_value`) VALUES ('display_progressbar_font_size', '10px')");
		}
	}
	
	/**
	 * Deletes database tables, if they exist.
	 *
	 * @return void
	 */
	public function uninstall() {
		
		// get wordpress database object
		global $wpdb;
		
		// set name for plugin config table
		$table_config = $wpdb->prefix . $this->tables['config'];
		
		// delete tables
		$wpdb->query("DROP TABLE ".$table_config);
	}
	
	/**
	 * Read out whole configuration or single option from database
	 *
	 * @param   string      single option to retrieve (optional)
	 * @return  mixed|bool  configuration option(s)|false
	 */
	public function getConfig( $option_name = null ) {
				
		// get wordpress database object
		global $wpdb;
		
		// set name for plugin config table
		$table_config = $wpdb->prefix . $this->tables['config'];
		
		// get whole configuration
		if ( $option_name === null ) {
			
			if ( $this->config != null ) {
				
				$config = $this->config;
				
				return $config;
				
			} else {
				
				// initialize return value
				$config = array();
				
				// get config from db
				$config_rows = $wpdb->get_results("SELECT `option_name`, `option_value` FROM `".$table_config."` WHERE `option_name` != 'book_cache' ORDER BY `option_id` ASC");
				
				if ( count($config_rows) > 0 ) {
					
					foreach ( $config_rows as $option ) {
						
						$config[$option->option_name] = $option->option_value;
					}
					
					$this->config = $config;
					
					return $config;
				}
			}
			
		// get single option
		} elseif ( trim($option_name) != '' )  {
			
			// get option from db
			$option_value = $wpdb->get_var("SELECT `option_value` FROM `".$table_config."` WHERE `option_name` = '".$option_name."'");
			
			if ( $option_value !== null ) {
				
				return $option_value;
			}
		}
		
		return false;
	}
	
	/**
	 * Update configuration in database
	 *
	 * @param  array  configuration options to set
	 * @return void
	 */
	public function updateConfig( $config_array = null ) {
		
		// get wordpress database object
		global $wpdb;
		
		if ( is_array($config_array) ) {
			
			// set name for plugin config table
			$table_config = $wpdb->prefix . $this->tables['config'];
			
			// update config values
			foreach ( $config_array as $name => $value ) {
				
				if ( $name == 'isbn' ) { $value_sql = trim(str_replace('-', '', $value)); }
				elseif ( is_string($value) ) { $value_sql = mysql_escape_string($value); }
				elseif ( is_array($value) ) { $value_sql = serialize($value); }
				elseif ( is_bool($value)  ) { $value_sql = int($value); }
				else { $value_sql = mysql_escape_string($value); }
				
				$wpdb->query("UPDATE ".$table_config." SET `option_value` = '".$value_sql."' WHERE `option_name` = '".$name."'");
			}
		}
	}
	
	/**
	 * Read book data from cache (database)
	 *
	 * @return  bool|array  false | book data
	 */
	public function getBook() {
		
		// initialize return value
		$book_data = array();
		
		// book data has been loaded before => return
		if ( $this->book_data != null ) {
			
			return $this->book_data;
			
		// read out from cache
		} else {
			
			// try to get data from cache
			$get_cache = $this->getBookCache();
			
			// return book data from cache
			if ( $get_cache !== false ) {
				
				$this->book_data = $get_cache;
				return $get_cache;
			}
		}
		
		return false;
	}
	
	/**
	 * Read book data from cache or generate it by using chosen
	 * service and return compiled output template.
	 *
	 * @param   string  $theme  theme file
	 * @return  string          compiled output template (/themes/default/default.phtml)
	 */
	public function getBookHtml( $theme = null ) {
		
		// get config and book data
		$this->getConfig();

		if ( $theme === null ) {

			$theme = $this->config['display_theme'];
		}

		if ( $this->getBook() !== false ) {
			
			// generate contents
			ob_start();
			include $this->plugin_dir.'/themes/'.$theme.'/'.$theme.'.php';
			$html_content = ob_get_contents();
			ob_end_clean();
			
			// return contents
			return $html_content;
			
		} else {
			
			return '<p>'.__('- not reading -', IAR_TEXTDOMAIN).'</p>';
		}
	}
	
	/**
	 * Update book data in database
	 *
	 * @param  array  book info to set
	 * @return void
	 */
	public function updateBook( $book_data = null ) {
		
		// get wordpress database object
		global $wpdb;
		
		if ( is_array($book_data) ) {
			
			// set name for plugin config table
			$table_config = $wpdb->prefix . $this->tables['config'];
			
			// read out book data
			$book_data_old = $this->getBook();
			
			// update book data
			foreach ( $book_data as $name => $value ) {
				
				if ( $name == 'isbn' ) { $value_save = trim(str_replace('-', '', $value)); }
				elseif ( is_string($value) ) { $value_save = mysql_escape_string($value); }
				elseif ( is_array($value) ) { $value_save = serialize($value); }
				elseif ( is_bool($value)  ) { $value_save = int($value); }
				
				$book_data_old[$name] = $value_save;
			}
			
			$book_data_new = serialize($book_data_old);
			
			$wpdb->query("UPDATE ".$table_config." SET `option_value` = '".$book_data_new."' WHERE `option_name` = 'book_cache'");
		}
	}
	
/**
	 * Set book data as database cache
	 *
	 * @param  array  book data
	 */
	public function setBookCache( $book_data ) {
		
		// get wordpress database object
		global $wpdb;
		
		// set name for plugin config table
		$table_config = $wpdb->prefix . $this->tables['config'];
		
		// write data in cache
		$wpdb->query("UPDATE ".$table_config." SET `option_value` = '".mysql_escape_string(serialize($book_data))."' WHERE `option_name` = 'book_cache'");
	}
	
	/**
	 * Read book data from cache
	 *
	 * @return  bool|array  false | book data
	 */
	public function getBookCache() {
		
		// get wordpress database object
		global $wpdb;
		
		// initialize return value
		$cache_content = '';
		
		// set name for plugin config table
		$table_config = $wpdb->prefix . $this->tables['config'];
		
		// get cache data
		$cache_db = $wpdb->get_var("SELECT `option_value` FROM ".$table_config." WHERE `option_name` = 'book_cache'");
		
		// set cache data if not empty
		if ( ($cache_db !== null) && (trim($cache_db) != '') ) {
			
			return unserialize($cache_db);
		}
		
		return false;
	}
	
	/**
	 * Get all themes out of the theme directory ('i-am-reading-continued/themes/')
	 * and return them as array.
	 * 
	 * @return array  available themes
	 */
	public function getThemes() {
		
		// set up vars
		$theme_dir   = $this->plugin_dir.'/themes/';
		$themes      = array();
		$theme_files = array();
			
		// read theme files in i-am-reading-continued/themes directory
		$themes_dir = @ opendir($theme_dir);
		
		if ( !$themes_dir ) { return false; }
		
		while ( ($theme_file = readdir($themes_dir)) !== false ) {
			
			if ( ($theme_file != '.') && ($theme_file != '..') && is_dir($theme_dir.$theme_file) ) {
				
				$this_theme_dir  = $theme_dir . $theme_file . '/';
				$this_theme_file = $this_theme_dir . $theme_file . '.php';
				$this_theme_id   = $theme_file;
				
				if ( file_exists($this_theme_file) ) {
					
					$theme_data = get_theme_data($this_theme_file);
					
					$themes[$this_theme_id] = $theme_data;
				}
			}
		}
		
		// return found themes
		return $themes;
	}
}
?>