<?php
ini_set('memory_limit', '512M');
date_default_timezone_set('EST');

// general functions file for webservices

include_once('RESTphulError.class.php');


//include_once('WebServiceTools.php');

//$error = new RESTphulError();


/**
 * RESTphulSrv - based httpd php class for requests and server info related to that
 * 
 * 
 * 
 * Copyright 2025 Abu Khadeejah Karl Holz
 * 
 * @author Abu Khadeejah Karl Holz
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2 Licence
 * @package RESTfulSrv
 * 
 */
class RESTphulSrv {
	/** ------------------------------------- */
	/** set via ini file */
	/**  @var array $ecode HTTP Error codes with title */
 	public $ecode=array();
	/** @var array $mime MIME types */
	public $mime=array();

	/** @var array $reserved  __get/__set key names, some values can't be clobered/reset */
	//public $reserved=array();

	/** @var array $sendFile */
	public $sendFile=array();

	/** ------------------------------------- 
	 * Load configuration of the server
  	 */
	function __construct() {
		
		// apply from config ini file
		$ini=parse_ini_file('.htconfig/httpd.ini', true);
		$this->mime=$ini['mime'];
		// $this->very_taboo=$ini['filesystem']['very_taboo'];
		// $this->taboo=$ini['filesystem']['taboo'];
		// $this->taboo_ext=$ini['filesystem']['taboo_ext'];
		//$this->reserved=$ini['settings']['reserved'];
		$this->sendFile=$ini['filesystem']['sendFile'];
		$this->ecode = $ini['error']; // only 100, 200 and 300. 400 and 500 in RESTphulError

		//SCRIPT_NAME
		$this->router=$this->router();
		//HTTP_HOST
		$this->host=$this->host();
		//DOCUMENT_ROOT
		$this->webroot=$this->webroot();
		
		/**
		 *  @todo figure out what is best with this, API will need to deal with access controll/Download
		 *  useful for excel and epub files, maybe media files under webroot
		 */ 
		$uri_path=$this->uri_path();
		if (is_file($uri_path)) {
			$this->file=$uri_path;
			$this->dir=false;
		} elseif (is_dir($uri_path)) {
			$this->file=false;
			$this->dir=$uri_path;

		} else {
			$this->file=false;
			$this->dir=false;
		}
		$this->full_uri=$this->full_uri();
		//PATH_INFO
		$this->rest=$this->rest();
		//PHP_SELF
		$this->routerREST=$this->routerREST();

		$this->root = $this->root();
		
		$this->query = $this->query();
	  	/**
	  	 * process HTTP Request methods
		 * 
	 	 *  - GET => none of these vales should be used to change any data, only query and toggle ui 
	 	 *  - POST => should be for creating new or updating items into the database
	 	 *  - PUT => data comes in on the php://input stream, should be used for replacing resources and creating new resources
	  	 *  - DELETE => parses qurey string for DELETE HTTP Method, no _GET is populated
	 	 *
	 	 * update this to use the ini configuration for HTTPD Method processing
	 	 */
		switch($this->method()) {
			case 'OPTIONS': $this->process_uri_query_str();  break;
			case 'TRACE': $this->process_uri_query_str();  break;
			case 'HEAD': $this->process_uri_query_str();  break;
			case 'GET':  
				if (is_array($_GET))
					$this->process_array($_GET);  
				break; 
			case 'POST':
				if (is_array($_POST)) 
					$this->process_array($_POST);  
				break; 
			case 'PUT':  
			  	$putdata = fopen("php://input", "r");
			  	if (! function_exists('stream_get_contents')) {
				  	RestError('500','stream_get_contents');
					//return FALSE;
			  	}

			  	$x=stream_get_contents($putdata);
			  	fclose($putdata);/* Close the streams */

			  	$this->put=$x;
		 		break;
		 	case 'DELETE': 
				$this->process_uri_query_str(); 
				break;
			default:
				RestError('405','method');
				//return FALSE;
	   	}


		//return TRUE;
	}


	/** @var array $data _GET/_POST processed variable */
	public $data=array();

	/**
	  * process any key=>value pairs
	  * 
	  * useful for $_GET and $_POST arrays
	  * 
	  * @todo add code to sanatize the input, avoid SQL injection/etc
	  * @param array $arg
	  * @return boolean fails if not an array with at least 1 item
	  */ 
	function process_array($arg) {
 		if (! is_array($arg)) return FALSE; 
    	if (count($arg) < 1) return FALSE;
    	$this->data=array();
		foreach (array_keys($arg) as $k)
    		$this->data[$k]=$arg[$k]; 
    	return TRUE;
	}

	/**
 	 * force saving with HTTP header
 	 */
    public function sendFile() {
		if (in_array(cutExt($this->file), $this->sendFile)){
            header('Content-disposition: attachment; filename='.trim(basename($this->file)));
        }
    }


     /**
      * mediaFileType()
      * 
      * @param string $filename - media file name
      * @return string $type returns MIME type for file for the <link /> tag in rss
      */
    public function mediaFileType($filename) {
     	if (array_key_exists(cutExt($filename), $this->mime)) {
     		return $this->mime[cutExt($filename)];
     	}
     	return 'text/plain';
    }

	/**
 	 * http header, send http header info
 	 * 
 	 * @param string $code http status code
 	 * @param string $type mime type
 	 * @param bool $cache is cacheing disabled
 	 * @todo add ETag support for caching support, maybe an ETag uuid?
 	 *
 	 */
	function httpHeader($code="200", $type="application/json", $cache=0) {
 		if (!headers_sent($filename, $linenum)) {
			header($this->srv_proto()." ".$code." ".$this->error->get_ecode($code));
			//header("HTTP/1.0 ".$code." ".$this->ecode[$code]);
			if ($cache == 0) {
 				header('Cache-Control: no-cache, must-revalidate');
 				header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
			}
 			header('Content-type: '.$type);
 			// You would most likely trigger an error here.
			return TRUE;
 		} 
		if ($this->debug) {
 			echo "Headers already sent in $filename on line $linenum\n" .
 			"Cannot redirect, for now please click this <a href=\"" . $this->router() . "\">link</a> instead\n";
		}
		return FALSE;
	}
	

	/**
	 * process the full query string for all other methods
	 * - OPTIONS
	 * - TRACE
	 * - HEAD
	 * - DELETE
	 * - PUT?
	 * @todo proceess this and make it standard for HTTP protocol
	 * 
	 *  pathinfo() and parse_url() won't solve the problem
	 *  
	 */
	function process_uri_query_str() {
		// example: /dashboard/phpinfo.php/test/path_info?a=1&b=2&c=33
		$full = $this->full_uri();
		list($rest_path, $query) = explode('?', $full);
		$path=str_replace($this->router(), '', $rest_path);
		$this->fix4cmd('query',$query);
		$this->fix4cmd('rest',$path);
		$argc=explode('&',$query);
		$args=array();
		foreach ($argc as $x) {
			list($key, $val) = explode('=', $x);
			$args[$key] = $val;
		}
		$this->process_array($args);
		return TRUE;
	}

 	/** @var array $arg used for __get() and __set() functions, extra variable as needed */
	public $arg=array();	
	 /**
	  * Set class value
	  * 
	  * @param string $name
	  * @param string $value 
	  */
	function __set($name, $value) {
		$this->arg[$name]=$value;
		return TRUE;
	}
	 /**
	  * Get Class value
	  * 
	  * @param string $name  if $name is with in the switch stament case names:
	  * 
	  *  
	  * @return string|boolean if the value is not set return false 
	  */
	function __get($name) {
		if (array_key_exists($name, $this->arg))  
			return $this->arg[$name]; 
		return NULL;
    }
	
	
	function fix4cmd($name, $value){
		switch ($name) {
			case 'query': $_SERVER['QUERY_STRING']=urlencode($value); break;
			case 'rest':
				$_SERVER['ORIG_PATH_INFO']=$value;
				$_SERVER['PATH_INFO']=$value;
				break;
			 case 'router': 
				 $_SERVER['SCRIPT_NAME']=$value;
				 $_SERVER['PHP_SELF']=$value;
				 break;
			 case 'content_type': $_SERVER['CONTENT_TYPE']=$value; break;
			 default: return FALSE;
		}
	   return TRUE;
	}


 
	/** - full_url => Current URI, uses  uses this_host and this_uri */
 	function full_url() { return $this->host().$this->full_uri(); }
 
 	/** - base_url => Base URI, uses this_host and script */
 	function base_url() { return $this->host().$this->router(); }

	/**
	 * 'SERVER_ADDR'
	 *  The IP address of the server under which the current script is executing. 
	 */
	function srv_addr(){
	 	if (array_key_exists('SERVER_ADDR', $_SERVER))
	 		return $_SERVER['SERVER_ADDR'];
		return '127.0.0.1';
 	}
 
 	/**
	 * 'SERVER_NAME'
     * The name of the server host under which the current script is executing. 
     * If the script is running on a virtual host, this will be the value defined 
     * for that virtual host. 
     */
	function srv_name(){
	 	if (array_key_exists('SERVER_NAME', $_SERVER))
	 		return $_SERVER['SERVER_NAME'];
		return 'localhost';
	}

	/**
	 * 'SERVER_PROTOCOL'
	 * Name and revision of the information protocol via which the page was requested;
	 *  i.e. 'HTTP/1.0'; 
  	 */
	function srv_proto(){
 		if (array_key_exists('SERVER_PROTOCOL', $_SERVER))
 			return $_SERVER['SERVER_PROTOCOL'];
		return FALSE;
	}
 
 
	/**
     * => parse the string and replace '&' with ',' in _SERVER['QUERY_STRING']
     * 'QUERY_STRING'
     * The query string, if any, via which the page was accessed.
	 */
	function query(){
 		if (array_key_exists('QUERY_STRING', $_SERVER))
 			return urldecode($_SERVER['QUERY_STRING']);
		return FALSE;
	}
 
	/**
     * 'PATH_TRANSLATED'
     * Filesystem- (not document root-) based path to the current script, after the server has done any virtual-to-real mapping.
     * 
     * Note: As of PHP 4.3.2, PATH_TRANSLATED is no longer set implicitly under the Apache 2 SAPI in contrast to the situation in Apache 1, 
     * where it's set to the same value as the SCRIPT_FILENAME server variable when it's not populated by Apache. 
     * This change was made to comply with the CGI specification that PATH_TRANSLATED should only exist if PATH_INFO is defined. 
     * Apache 2 users may use AcceptPathInfo = On inside httpd.conf to define PATH_INFO. 
	 ************************************** 
  	 * => $_SERVER["PATH_TRANSLATED"] <= 
  	 * checks for a file along the request uri
	 **************************************
	 * => $_SERVER["PATH_TRANSLATED"] <= 
  	 * checks for a directory along the request uri
	 */
	function uri_path(){
 		if (array_key_exists("PATH_TRANSLATED", $_SERVER) )
 			return $_SERVER["PATH_TRANSLATED"];
		return FALSE;
	}

	/**
     * WebService file
     * 'SCRIPT_NAME'
     * Contains the current script's path. 
     * This is useful for pages which need to point to themselves. 
     * The __FILE__ constant contains the full path and filename of the current (i.e. included) file.
	 * 
     * 'PHP_SELF'
     * The filename of the currently executing script, relative to the document root. 
     * For instance, $_SERVER['PHP_SELF'] in a script at the address http://example.com/foo/bar.php would be /foo/bar.php. 
     * The __FILE__ constant contains the full path and filename of the current (i.e. included) file. 
     * If PHP is running as a command-line processor this variable contains the script name since PHP 4.3.0. 
     * Previously it was not available. 
  	 */
	function router(){
		if (array_key_exists('SCRIPT_NAME', $_SERVER))
 			return $_SERVER['SCRIPT_NAME'];
		return FALSE;

	}

	/** will contain path_info after the script */
	function routerREST() { 
 		if (array_key_exists('PHP_SELF', $_SERVER))
 			return $_SERVER['PHP_SELF'];
		return FALSE;
 	}
 
	/**
     * 'SCRIPT_FILENAME'
     * 
     * The absolute pathname of the currently executing script.
     * 
     * Note:
     * If a script is executed with the CLI, as a relative path, such as 
     * file.php or ../file.php, $_SERVER['SCRIPT_FILENAME'] will contain the relative path specified by the user.   	 
	 */
	function app_file(){
 		if (array_key_exists('SCRIPT_FILENAME', $_SERVER))
 			return $_SERVER['SCRIPT_FILENAME'];
		return false;
 	}
 
	/**
	 * uses the dirname of this server value
	 */
	function root(){
 		return dirname($this->app_file()); 
	}
 
	/**
     * 'PATH_INFO'
     * Contains any client-provided pathname information trailing the 
     * actual script filename but preceding the query string, if available. 
     * For instance, if the current script was accessed via the 
     * URL http://www.example.com/php/path_info.php/some/stuff?foo=bar, 
     * then $_SERVER['PATH_INFO'] would contain /some/stuff. 
     * 
     * 'ORIG_PATH_INFO'
     * Original version of 'PATH_INFO' before processed by PHP. 
     * 
     * also 'rest' in $_GET and $_POST
	 * @return string
  	 */
	function rest(){
 		if (array_key_exists('PATH_INFO', $_SERVER))
 			return rawurldecode($_SERVER['PATH_INFO']);
		return FALSE;
	}
 
	function orig_path_info() {
		if (array_key_exists('ORIG_PATH_INFO', $_SERVER))
			return rawurldecode($_SERVER['ORIG_PATH_INFO']);
		return FALSE;
	}
 
	/**
     * 'REQUEST_METHOD'
     * Which request method was used to access the page; 
     * i.e. 'GET', 'HEAD', 'POST', 'PUT'.
     * 
     * Note:
     * 
     * PHP script is terminated after sending headers 
     * (it means after producing any output without output buffering) 
     * if the request method was HEAD.
	 */
	function method(){
 		if (array_key_exists('REQUEST_METHOD', $_SERVER))
 			return $_SERVER['REQUEST_METHOD'];
 		return 'GET';	
	}
 
	/**
  	 * => _SERVER["HTTP_HOST"] <= if _SERVER['HTTPS'] is set than use https://
     * 'HTTP_HOST'
     * Contents of the Host: header from the current request, if there is one. 
     *     
     * 'HTTPS'
     * Set to a non-empty value if the script was queried through the HTTPS protocol.
     * 
     * Note: Note that when using ISAPI with IIS, the value will be off if the request was not made through the HTTPS protocol. 
     * @return string
	 */
	function host(){

 		if (! array_key_exists('HTTP_HOST', $_SERVER))
 			return "http://127.0.0.1";
 		if (array_key_exists('HTTPS', $_SERVER) && isset($_SERVER['HTTPS'])){
			return "https://".$_SERVER["HTTP_HOST"];
		}
 			
 		return "http://".$_SERVER["HTTP_HOST"];
	}

	/**
     * 'SERVER_PORT'
     * The port on the server machine being used by the web server for communication. 
     * For default setups, this will be '80'; using SSL, for instance, will change this to whatever your defined secure HTTP port is.
     * 
     * Note: Under the Apache 2, you must set UseCanonicalName = On, as well as UseCanonicalPhysicalPort = On in order to get the physical (real) port, otherwise, this value can be spoofed and it may or may not return the physical port value. 
     * It is not safe to rely on this value in security-dependent contexts. 
	 */
	function srv_port(){
		if (array_key_exists('SERVER_PORT', $_SERVER))
			return $_SERVER['SERVER_PORT'];
		return 80;
    }
 
	/**
  	 * Content-Type
  	 * 
  	 * => _SERVER['CONTENT_TYPE']
  	 * @return string
     */
	function content_type(){
 		if (array_key_exists('CONTENT_TYPE', $_SERVER))
 			return $_SERVER['CONTENT_TYPE'];
		return 'plain/text';
	}
 
	/**
     * 'DOCUMENT_ROOT'
     * The document root directory under which the current script is executing, 
     * as defined in the server's configuration file. 
	 * @return string
  	 */
 	function webroot(){
		if (array_key_exists("DOCUMENT_ROOT", $_SERVER))
 			return $_SERVER["DOCUMENT_ROOT"];
		return FALSE;
	}
 
	/**
     * 'REQUEST_URI'
     * The URI which was given in order to access this page; 
     * for instance, '/index.html'. 
	 * @return string
	 */
	function full_uri(){
 		if (array_key_exists("REQUEST_URI", $_SERVER))
 			return $_SERVER["REQUEST_URI"];
		return FALSE;
	}
 
}



?>
