<?php
/**
 * PHP based template engine without preg_match() and all the other dirty things
 * 
 * @author     Dominik Hanke <mail@dhdev.de>
 * @copyright  Copyright 2010, Dominik Hanke
 * @link       http://i-am-reading.ginchen.de
 *
 * @package i-am-reading
 */
class phpTemplate {
	
	/** @var  bool  print errors ? */
	private $show_errors = true;
	
	/** @var  bool  stop script execution on errors ? */
	private $halt_on_errors = false;
	
	/** @var  string  template path */
	private $template_path = null;
	
	/** @var  string  template file */
	private $template_file = null;
	
	/** @var  array  variable container */
	private $vars = array();
	
	/** error codes */
	const ERROR_CODE_NOTICE  = 1;
	const ERROR_CODE_WARNING = 2;
	const ERROR_CODE_FATAL   = 3;
	
	/**
	 * Constructor
	 *
	 * @param  string  $template_path  template path
	 * @return void
	 */
	public function __construct( $template_path ) {
		
		try {
			
			// set path if given and guilty
			if ( trim($template_path) != '' && ( file_exists(trim($template_path)) === true) ) {
				
				$this->template_path = $template_path;
				
			// error: unknown path
			} else {
				
				throw new Exception('__construct(): Incorrect template path "'.$template_path.'"');
			}
			
		} catch (Exception $e) {
			
			$this->handleException($e);
		}
	}
	
	/**
	 * Sets the template path where template files are loaded from.
	 *
	 * @param  string  $path  template path
	 * @return void
	 */
	public function setPath( $path ) {
		
		try {
			
			// set path if given and guilty
			if ( file_exists($path) === true ) {
				
				$this->template_path = $path;
				
			// error: unknown path
			} elseif ( $path != '' ) {
				
				throw new Exception('setPath(): Can\'t set template path to "'.$path.'". Directory not found.', self::ERROR_CODE_FATAL);
			}
			
		} catch (Exception $e) {
			
			$this->handleException($e);
		}
	}
	
	/**
	 * Sets the template file to use and load when calling parse().
	 *
	 * @param  string  $file  template file
	 * @return void
	 */
	public function setFile( $file = null ) {
				
		try {
			
			// build file path
			$template = $this->template_path . $file;
			
			// check if template exists
			if ( file_exists($template) === true ) {
				
				$this->template_file = $template;
				
			// error: template not found
			} elseif ( $file != '' ) {
				
				throw new Exception('setFile(): Can\'t load template file "'.$template.'"', self::ERROR_CODE_WARNING);
			}
			
		} catch (Exception $e) {
			
			$this->handleException($e);
		}
	}
	
	/**
	 * Sets a variable to use in templates ($this->vars[]).
	 *
	 * @param  string  $name   name
	 * @param  mixed   $value  value
	 * @return void
	 */
	public function setVar($name, $value) {
		
		try {
			
			// set var, wenn name isn't empty
			if ( (trim($name) != '') && (!preg_match('/^[0-9]/', $name)) ) {
				
				$this->vars[$name] = $value;
				
			// error: no or wrong variable name
			} else {
				
				throw new Exception('setVar(): No or illegal name given for new template var', self::ERROR_CODE_NOTICE);
			}
			
		} catch (Exception $e) {
			
			$this->handleException($e);
		}
	}
	
	/**
	 * Returns the value of a template variable.
	 *
	 * @param   string  $name  variable name
	 * @return  mixed          variable value
	 */
	public function getVar($name) {
		
		try {
			
			// return value, if var is set
			if ( (trim($name) != '') && (array_key_exists($name, $this->vars)) ) {
				
				return $this->vars[$name];
				
			// error: var not set
			} else {
				
				throw new Exception('getVar(): Var "'.$name.'" not set.', self::ERROR_CODE_WARNING);
			}
			
		} catch (Exception $e) {
			
			$this->handleException($e);
		}
	}
	
	/**
	 * Clears the variable container ($this->vars[])
	 * 
	 * @return void
	 */
	public function clearVars() {
		
		$this->vars = array();
	}
	
	/**
	 * Loads a template file, parses it and returns the contents.
	 * 
	 * @return  mixed  bool / string
	 */
	public function includeFile($file) {
		
		$this->setFile($file);
		
		$file_contents = $this->parse(true);
		
		return $file_contents;
	}
	
	/**
	 * Loads a template file, parses it and returns the contents or
	 * prints it out directly.
	 *
	 * @param   string  $print_content  print out content directly ?
	 * @return  string                  template content
	 */
	function parse($print_content = true) {
		
		try {
			
			// error: no template set
			if ( $this->template_file == null ) {
				
				throw new Exception('parse: No template file set', self::ERROR_CODE_FATAL);
				
			} else {
				
				// extract template vars to local scope
				extract($this->vars);
				
				// start output buffering
				ob_start();
				
				// load template file
				include($this->template_file);
				
				// get buffered contents
				$content = ob_get_contents();
				
				// stop and empty output buffer
				ob_end_clean();
				
				// print out template content
				if ( $print_content === true ) {
					
					print $content;
				}
				
				// return template content
				return $content;
			}
			
		} catch (Exception $e) {
			
			$this->handleException($e);
		}
	}
	
	/**
	 * Exception handler
	 *
	 * @param  object  $e  exception
	 * @return void
	 */
	function handleException($e) {
		
		// print error
		if ( $this->show_errors === true ) {
			
			$this->printError($e);
		}
		
		// stop script execution
		if ( $this->halt_on_errors === true ) {
			
			exit;
		}
	}
	
	/**
	 * Prints HTML error messages
	 * 
	 * @param  object  exception
	 * @return void
	 */
	private function printError($e) {
		
		$trace = $e->getTrace();
		
		print '<b>phpTemplate Error:</b>: '.$e->getMessage().' in <b>'.$trace[0]['file'].'</b> on line <b>'.$trace[0]['line'].'</b><br />';
	}
}
?>