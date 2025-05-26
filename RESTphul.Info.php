<?php

class RESTphulInfo {

    function __construct()
    {
        
    }

    function __destruct()
    {
        
    }

	 /**
	  * Set class value
	  * 
	  * @param string $name
	  * @param string $value set values as is except when name is the following:
	  * - username => name check min 4 characters
	  * - password => check min 8 characters Alpha Numaric and some specal chars
	  */
      function __set($name, $value) {
        switch ($name) {

             case 'http_ref': $_SERVER['HTTP_REFERER']=$value; break;
             case 'http_request_with': $_SERVER['HTTP_X_REQUESTED_WITH']=$value; break;
             case 'http_origin': $_SERVER['HTTP_ORIGIN']=$value; break;
             case 'content_type': $_SERVER['CONTENT_TYPE']=$value; break;

        }
       return TRUE;
    }

	/**
     * 'HTTP_REFERER'
     * The address of the page (if any) which referred the user agent to the current page. 
     * This is set by the user agent. 
     * Not all user agents will set this, and some provide the ability to modify HTTP_REFERER as a feature. 
     * In short, it cannot really be trusted. 
     */
	function http_ref(){
        if (array_key_exists('HTTP_REFERER', $_SERVER))
            return $_SERVER['HTTP_REFERER'];
    }

   /**
     * => _SERVER['HTTP_X_REQUESTED_WITH']
    * @return string
    */
   function http_request_with(){
        if (array_key_exists('HTTP_X_REQUESTED_WITH', $_SERVER))
            return $_SERVER['HTTP_X_REQUESTED_WITH'];
   }

   /**
    * => _SERVER['HTTP_ORIGIN']
    * @return string
    */
   function http_origin(){
        if (array_key_exists('HTTP_ORIGIN', $_SERVER))
            return $_SERVER['HTTP_ORIGIN'];
   }

	/**
	 * GATEWAY_INTERFACE
	 * What revision of the CGI specification the server is using;
	 * i.e. 'CGI/1.1'. 
	 */
	function gw_interface(){
        if (array_key_exists('GATEWAY_INTERFACE', $_SERVER))
            return $_SERVER['GATEWAY_INTERFACE'];
   }

	/**
	 *  'SERVER_SOFTWARE'
	 * Server identification string, given in the headers when responding to 
	 * requests. 
  	 */
      function srv_software(){
        if (array_key_exists('SERVER_SOFTWARE', $_SERVER))
            return $_SERVER['SERVER_SOFTWARE'];
   }

	/**
	 * 'REQUEST_TIME'
     * The timestamp of the start of the request. 
	 * Available since PHP 5.1.0. 
	 */
	function request_time(){
        if (array_key_exists('REQUEST_TIME', $_SERVER))
            return $_SERVER['REQUEST_TIME'];
   }


   /**
    * 'REQUEST_TIME_FLOAT'
    * The timestamp of the start of the request, with microsecond precision. 
    * Available since PHP 5.4.0. 
      */
   function request_timef(){
        if (array_key_exists('REQUEST_TIME_FLOAT', $_SERVER))
            return $_SERVER['REQUEST_TIME_FLOAT'];
   }

	/**
	 * 'HTTP_ACCEPT_LANGUAGE'
     * Contents of the Accept-Language: header from the current request, 
     * if there is one. Example: 'en'. 
  	 */
      function http_lang(){
        if (array_key_exists('HTTP_ACCEPT_LANGUAGE', $_SERVER))
            return $_SERVER['HTTP_ACCEPT_LANGUAGE'];
        return "en";
   }

   /**
    * 'HTTP_CONNECTION'
    * Contents of the Connection: header from the current request, 
    * if there is one. Example: 'Keep-Alive'. 
    */
   function http_conn(){
        if (array_key_exists('HTTP_CONNECTION', $_SERVER))
            return $_SERVER['HTTP_CONNECTION'];
   }

	/**
     * 'SERVER_ADMIN'
     * The value given to the SERVER_ADMIN (for Apache) directive in the web server 
     * configuration file. If the script is running on a virtual host, this will be 
     * the value defined for that virtual host. 
	 */
	function srv_admin(){
        if (array_key_exists('SERVER_ADMIN', $_SERVER))
            return $_SERVER['SERVER_ADMIN'];
    }  

	/**
     * 'SERVER_SIGNATURE'
     * String containing the server version and virtual host name which are added to server-generated pages, if enabled. 
	 */
	function srv_signature(){
        if (array_key_exists('SERVER_SIGNATURE', $_SERVER))
            return $_SERVER['SERVER_SIGNATURE'];
   }  

	/**
	 * 'HTTP_ACCEPT_CHARSET'
	 * Contents of the Accept-Charset: header from the current request, 
     * if there is one. Example: 'iso-8859-1,*,utf-8'. 
	 */
	function http__charset(){
        if (array_key_exists('HTTP_ACCEPT_CHARSET', $_SERVER))
            return $_SERVER['HTTP_ACCEPT_CHARSET'];
        return 'iso-8859-1,*,utf-8';
    }

    /**
     * 'HTTP_ACCEPT_ENCODING'
     * Contents of the Accept-Encoding: header from the current request, 
     * if there is one. Example: 'gzip'. 
     */
    function http_encoding(){
       if (array_key_exists('HTTP_ACCEPT_ENCODING', $_SERVER))
            return $_SERVER['HTTP_ACCEPT_ENCODING'];
    }

	/**
     * 'HTTP_ACCEPT'
     * Contents of the Accept: header from the current request, if there is one. 
  	 */
    function accept(){
        if (array_key_exists('HTTP_ACCEPT', $_SERVER))
            return $_SERVER['HTTP_ACCEPT'];
    }
	/**
     * 'HTTP_USER_AGENT'
     * Contents of the User-Agent: header from the current request, if there is one. 
     * This is a string denoting the user agent being which is accessing the page. 
     * A typical example is: Mozilla/4.5 [en] (X11; U; Linux 2.2.9 i586). 
     * Among other things, you can use this value with get_browser() to tailor your page's output to the capabilities of the user agent. 
	 * @return string
  	 */
    function client_name(){
        /**  @todo add some parsing for matching clients like the iWork iPhone app */
        if (array_key_exists("HTTP_USER_AGENT", $_SERVER))
            return $_SERVER["HTTP_USER_AGENT"];
    }
}


class RESTphulRemote {

    function __construct()
    {
        
    }

	/**
	 * 'REMOTE_ADDR'
     * The IP address from which the user is viewing the current page. 
	 */
	function remote_addr(){
        if (array_key_exists('REMOTE_ADDR', $_SERVER))
            return $_SERVER['REMOTE_ADDR'];
   }  

   /**
    * 'REMOTE_HOST'
    * The Host name from which the user is viewing the current page. 
    * The reverse dns lookup is based off the REMOTE_ADDR of the user.
    * 
    * Note: Your web server must be configured to create this variable. 
    * For example in Apache you'll need HostnameLookups On inside httpd.conf for it to exist. 
    * See also gethostbyaddr(). 
    */
   function remote_host(){
        if (array_key_exists('REMOTE_HOST', $_SERVER))
            return $_SERVER['REMOTE_HOST'];
   }  

   /**
    * 'REMOTE_PORT'
    * The port being used on the user's machine to communicate with the web server. 
    */
   function remote_port(){
        if (array_key_exists('REMOTE_PORT', $_SERVER))
            return $_SERVER['REMOTE_PORT'];
   }  

   /**
    * 'REMOTE_USER'
    * The authenticated user. 
    */
   function remote_user(){
        if (array_key_exists('REMOTE_USER', $_SERVER))
            return $_SERVER['REMOTE_USER'];
   }  

   /**
    * 'REDIRECT_REMOTE_USER'
    * The authenticated user if the request is internally redirected.
    */
   function redirect_remote_user(){
        if (array_key_exists('REDIRECT_REMOTE_USER', $_SERVER))
            return $_SERVER['REDIRECT_REMOTE_USER'];
   }  
}