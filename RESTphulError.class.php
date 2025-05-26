<?php
/**
 * 
 * when using set mes, then code so that you have the http error code you intend for error page
 * 
 */

function RestError($code='500', $msg='No message set') {
	$error=new RESTphulError();
	$error->code = $code;
	$error->msg = $msg;
	print($error);
}

class RESTphulError {
	public $msg = FALSE;
	public $code = FALSE;
	public $debug = FALSE;

	protected $php=array();
	protected $messages=array();
	protected $ecode=array();

	function __construct($debug=FALSE)
	{
		$this->debug=$debug;
		$ini=parse_ini_file('.htconfig/error.ini', true);
		$this->ecode=$ini['error'];
		$this->messages=$ini['msg'];
		// check for supported functions
		// two arrays, support_needed and support_opt
		$this->php = $ini['php'];
	}
	
	function __destruct()
	{
		return TRUE;
	}

	function get_ecode($code='500') {
		if (array_key_exists($code, $this->ecode)) 
			return $this->ecode[$code];
		return  $this->ecode['500'];
	}

	function get_message($msg) {
		if (array_key_exists($msg, $this->messages)){
			if ($msg == 'method') {
				$this->code='405';
			} else {
				$this->code='500';
			}
			return $this->messages[$msg];
		} 
		$this->code='500';
		return "_Error_ <hr />=> ". $msg . "<=<hr />";  
		
	}

	function check_support($name) {
		if (function_exists($name) || class_exists($name)) { return TRUE; } 
		else { return FALSE; } 
	}

	 /**
	  * Error Page with jQuery mobile
	  *  - much better than plain text
	  *
	  * @return void prints error page
	  */
	function __toString()
	{
		global $rest;
		$msg = $this->code . ' - ' . $this->get_ecode($this->code);
		$text = $this->get_message($this->msg);
		$rest->http_header($this->code, 'text/html');
		?><!DOCTYPE html>
		<html xmlns="http://www.w3.org/1999/xhtml">
		<head>
			<title><?php echo $msg; ?> - Error</title>
			<meta name="viewport" content="width=device-width, initial-scale=1" />
			<meta http-equiv="content-type" content="text/html; charset=utf-8" />
			<link rel="stylesheet" href="http://code.jquery.com/mobile/1.2.0/jquery.mobile-1.2.0.min.css" />
			<script src="http://code.jquery.com/jquery-1.8.2.min.js"></script>
			<script src="http://code.jquery.com/mobile/1.2.0/jquery.mobile-1.2.0.min.js"></script>
		</head>
		<body>
			<div data-role="page">
				<div data-role="header"><h1><?php echo $msg; ?> - Error</h1></div> 
				<div data-role="content" >
					<div data-role="collapsible" data-collapsed="false" data-theme="a" data-content-theme="b">
						<h3>Error:</h3>
						<pre><?php echo HtmlSpecialChars($text);?></pre>
						<pre><?php echo $msg?></pre>
					</div>
				<?php if ($this->debug) {   ?>
					<div data-role="collapsible" data-collapsed="true" data-theme="a" data-content-theme="b">
						<h3>RESTphulSrv:</h3>
						<pre><?php print_r($rest, TRUE); ?></pre>
					</div>
					<div data-role="collapsible" data-collapsed="true" data-theme="a" data-content-theme="b">
						<h3>_SERVER:</h3>
						<pre><?php print_r($_SERVER, TRUE); ?></pre>
					</div>
					<div data-role="collapsible" data-collapsed="true" data-theme="a" data-content-theme="b">
						<h3>_GET:</h3>
						<pre><?php print_r($_GET, TRUE); ?></pre>
					</div>
					<div data-role="collapsible" data-collapsed="true" data-theme="a" data-content-theme="b">
						<h3>_POST:</h3>
						<pre><?php print_r($_POST, TRUE); ?></pre>
					</div>

				<?php } ?>
				</div> 
				<div data-role="footer"><h4><?php echo $text; ?> - Error</h4></div>
			</div>
		</body>
		</html><?php 
			exit();		
	}

}