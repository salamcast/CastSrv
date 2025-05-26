<?php
require_once 'REST.class.php';
/**
 *  @todo fix this
 */  
class RESTphulAPI extends RESTphulSrv {
	/** @var array $api a list of found configuration files */
	public $api=array();

	private $srv;


	
	/** @var string $search Search by filetype, this will fail, since *.* scanning is not permited */
	public $search='%2A';

	/** @var array $json a json array to output to browser */
	public $json=array();

	/** @var array $keys keys used in reqest, check againt acl config */
	public $keys=array();   

	/** @var array $accept_list */
	public $accept_list=array();

    /** @var array $env  */
    protected $env=array();
	function __construct()
	{

		//$this->env['query']=$value;
		//$this->env['rest']=$value;

		//$this->env['router']=$value;
		//$this->env['content_type']=$value; 
		//$this->env['arg']=$rest->arg;
		//$this->env['method']=$this->method;

	}

    function httpAuth($auth) {

        //$this->env['username']=$value;
		//$this->env['password']=$value;
    }

	function loadApi($ini) {
		if (is_file($ini)) {
			$this->api=parse_ini_file($ini, TRUE);
		} elseif (count($this->api) < 1 && $ini != '') {
			$this->api=parse_ini_string($ini, TRUE);
		}

		if ($this->checkApi('ext')) if (is_dir($this->pwd)) globTypeBrace($this->pwd);

			// builds configured urls
			$x=0;
		foreach ($this->api as $d => $api) {
			if (! preg_match('/^_.*/', $d) ) {
				$rest=str_replace(array($this->root, $this->webroot, '///', '//'), array('/', '/', '/', '/'), $d);
				$x++;
			}
		}

        // accept_list defaults 
		if (count($this->accept_list) < 1) {
			if ($this->accept) {
				$this->accept_list=explode(',', $this->accept);
			}
		}
	}
	/**
	  * Get Class value
	  * 
	  * @param string $name  searches API for requested rest value 
	  * @return string|boolean if the value is not set return false 
	  */
	function __get($name) {
 		if (array_key_exists($this->srv->rest, $this->api) &&
 			 array_key_exists($name, $this->api[$this->srv->rest]))
 				return $this->api[$this->srv->rest][$name];
	}

	/**
	 *  Checks for value of $key if it's configured for this $method
	 */
  	function checkApi($key) {
		if (! array_key_exists($this->srv->rest, $this->api)) return FALSE;	
		if (! array_key_exists($key, $this->api[$this->srv->rest])) return FALSE;
	 	return $this->api[$this->srv->rest][$key];
	}

	/**
	 * HTTP PUT File
	 * handle http put automaticly if the api is configured for write under PUT
	 * [PUT]
	 * write=1
	 */ 

	function autoHttpPutFile() {
		if (! function_exists('file_put_contents')) {
			$this->error='file_put_contents not found';
			error('php_fail');
		}
		if ($this->srv->method != 'PUT') return FALSE;
		if ($this->checkApi('write')) {
			file_put_contents($this->srv->root.$this->srv->rest, $this->srv->put);
			return TRUE;
		} else {
			return FALSE;
		}
 }
	/**
	 * HTTP DELETE File
	 * handle http delete automaticly if the api is configured for write under DELETE
	 * [DELETE]
	 * write=1
	 */ 
 	function autoHttpDeleteFile() {
		if ($this->srv->method != 'DELETE') return FALSE;
		if ($this->checkApi('write')) {
			if (is_file($this->srv->file)) {
	  			unlink($this->srv->file);
	  			return TRUE;
 	 		}
		} 
		return FALSE;
	}	
}