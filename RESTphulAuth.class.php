<?php
class RESTphulAuth {
    private $realm = "Basic RESTfulPHP Web Auth";
    function __construct()
    {
		/**
		 * Process HTTP BASIC AUTH
		*
		*  - sets PHP_AUTH_USER and PHP_AUTH_PW from HTTP_AUTHORIZATION
		* @link http://ca2.php.net/manual/en/features.http-auth.php#106285
		*/
        $auth = $this->auth();
		if(isset($auth) && preg_match('/Basic\s+(.*)$/i', $auth, $matches)) {
			list($name, $password) = explode(':', base64_decode($matches[1]));
			$this->user = strip_tags($name);
			$this->password = strip_tags($password);
		}
    }

    function __destruct() { }
 
	/**
  	 * => _SERVER['HTTP_AUTHORIZATION'] <= 
  	 * or 
  	 * => _SERVER['REDIRECT_HTTP_AUTHORIZATION'] <=
     * @return string
     */
	function auth(){
        if (array_key_exists('HTTP_AUTHORIZATION', $_SERVER) &&
            isset($_SERVER['HTTP_AUTHORIZATION']))
            return $_SERVER['HTTP_AUTHORIZATION'];
        if (array_key_exists('REDIRECT_HTTP_AUTHORIZATION', $_SERVER) &&
            isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION']))
            return $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
        return FALSE;
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
            case 'username': 
               $_SERVER['PHP_AUTH_USER']='';
               if (strlen($value) > 3 && ctype_alnum($value)) { 
                   $_SERVER['PHP_AUTH_USER']=$value;
               }
               break;
             case 'password':
               $_SERVER['PHP_AUTH_PW']=''; 
               if (preg_match('/[a-zA-Z0-9-_!@#$%^%&*();:,.<>?|]{8,}/', $value)) { 
                   $_SERVER['PHP_AUTH_PW']=$value;
               } 
               break;
        }
       return TRUE;
   }

	/**
     * 'PHP_AUTH_USER'
     * When doing HTTP authentication this variable is set to the username provided by the user.
	 */
	function username(){
        if (array_key_exists('PHP_AUTH_USER', $_SERVER))
            return $_SERVER['PHP_AUTH_USER'];
   }

   /**
    * 'PHP_AUTH_PW'
    * When doing HTTP authentication this variable is set to the password provided by the user.
    */
   function password(){
        if (array_key_exists('PHP_AUTH_PW', $_SERVER))
            return $_SERVER['PHP_AUTH_PW'];
   }

	/**
     * 'PHP_AUTH_DIGEST'
     * When doing Digest HTTP authentication this variable is set to the 'Authorization' header sent by the client (which you should then use to make the appropriate validation). 
	 */
	function auth_digest(){
		if (array_key_exists('PHP_AUTH_DIGEST', $_SERVER))
 			return $_SERVER['PHP_AUTH_DIGEST'];
	}  
 
	/**
     * 'AUTH_TYPE'
     * When doing HTTP authenticated this variable is set to the authentication type. 
	 */
	function auth_type(){
 		if (array_key_exists('AUTH_TYPE', $_SERVER))
 			return $_SERVER['AUTH_TYPE'];
	}  

	/**
	  * Basic HTTP Auth
	  * 
	  * @return boolean
	  */
      function basic_auth() {
        if (! $this->username()) {
            header('WWW-Authenticate: Basic realm="'.$this->realm.'"');
            header('HTTP/1.0 401 Unauthorized');
           return FALSE; 	
        }
        return TRUE;
   }

    /**
     * Clears http auth and logs user out
     * 
     * only clear HTTP auth if _GET['clear'] is set, then redirect to script
     * 
     * @return void cancel HTTP Auth and exit
     */
   function cancel_auth(){
        if (array_key_exists('clear', $_GET) && isset($_GET['clear'])) {
             unset($_SERVER['PHP_AUTH_USER']); unset($_SERVER['PHP_AUTH_PW']); unset($_GET);
              header('WWW-Authenticate: Basic realm="Cancel to logout"');
              header('HTTP/1.0 401 Unauthorized');
              header("Location: ".$this->router()); 
              exit();
         }    
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
 		if (array_key_exists('PHP_SELF', $_SERVER))
 			return $_SERVER['PHP_SELF'];
 	}
}