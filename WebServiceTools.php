<?php

## other functions


 /**
  *  open zip files as RESTful resources
  *   these days there are many file formats that are encasulated within a zip file
  *  Examples would enclude MS Office 2007, OpenOffice, ePub, iBooks, there are probally others too
  */
 function zip_resorces($zip){
    // global $rest;
 	// if (! class_exists('ZipArchive')) {
 	// 	$rest->error='ZipArchive not found';
 	// 	error('php_fail');
 	// }
 	$za = new ZipArchive();
 	$za->open($zip);
 	$list=array();
 	for ($i=0; $i<$za->numFiles;$i++) {
 		$z=$za->statIndex($i);
 		$list[]=$z['name'];
 	}
 	$za->close();
 	return $list;
 }

  /**
    * Genarates an UUID 
    * 
    *  - borrowed from Anis uddin Ahmad's Universal FeedGerator, modified by Karl Holz
    * 
    * @param	  string $key value to hash
    * @param      string $prefix an optional prefix to Hash, use Class name as default
    * @return     string  the formated uuid
    */
    function uuid($key = null, $prefix = "resource", ) {
      $key = ($key == null)? date('DATE_RSS') : $key;
      $chars = md5($key);
      $uuid  = substr($chars,0,8) . '-';
      $uuid .= substr($chars,8,4) . '-';
      $uuid .= substr($chars,12,4) . '-';
      $uuid .= substr($chars,16,4) . '-';
      $uuid .= substr($chars,20,12);
      return $prefix .'|'. $uuid;
    }

    /** Send file to the client after everything has checked out */
    function streamFile() {
        global $rest;
        if (!headers_sent()) {
        	header("Content-Type: ".trim($rest->media_file_type($rest->file)));
        	header("Content-Length: ".trim(filesize($rest->file)));
        	$rest->sendFile();
        	readfile($rest->file);
        } else {
            // $rest->error='Failed to send file for saving';
		    // error('stream_file');
            return FALSE;
        }
        exit();
    }

 /**
  * json_out()
  * 
  * @param string $data
  * @return string
  */

  function json_out($data) {
//	if (!is_array($data) || (count($data) == 0)) { $this->error='No Data' ; $this->error('no_data'); }   
	header('Cache-Control: no-cache, must-revalidate');
	header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
	header('Content-type: application/json');
	echo json_encode($data); exit();
   }

    /**
     * returns the file size in a nicer easyer to read format
     * @param int $size
     * @return string 
     */
    function nice_size($size) {
        switch ($size) {
            case round((($size/1024)/1024)/1024) > 1:
                $nice_size=round(((($size/1024)/1024)/1024), 2)." GB";
            break;
            case round(($size/1024)/1024) > 1:
                $nice_size=round((($size/1024)/1024), 2)." MB";
            break;
            case round($size/1024) > 1:
                $nice_size=round(($size/1024), 2)." KB";
            break;
            default:
                $nice_size=$size." Bytes";
            break;
        }
        return $nice_size;
    }


	/**
	 * XSLT out
	 * 
	 * Apply xslt template to xml data
	 *
	 * @param string $xsltmpl         	XSLT stylesheet to be applied to XML
	 * @param string $xml_load      	XML data
	 * @return string 
	 */
	function xsl_out($xsltmpl, $xml_load) {
		if (! class_exists ( 'DOMDocument' ))  die('DOMDocument class not found');
		if (! class_exists ( 'XSLTProcessor' )) die('XSLTProcessor class not found');
		// loads XML data string
		$xml = new DOMDocument ();
		if (! $xml->loadXML ( $xml_load )) die('XML data failed to load'); 
		// loads XSL template string
		$xsl = new DOMDocument ();
		if (! $xsl->loadXML ( $xsltmpl ))  die('XSLT failed to load'); 
		$xslproc = new XSLTProcessor();
		$xslproc->importStylesheet( $xsl );
		return $xslproc->transformToXml( $xml );
	}



 /**
  * Make ini text
  */
 
  function make_ini($api) {
    $ini='';
    foreach ($api as $k => $v ) {
        $ini.='['.$k.']'."\n";
        foreach($v as $kk => $vv) {
            if (!is_array($vv)) {
                $ini.=$kk.'="'.$vv.'"'."\n";
            } else {
                foreach ($vv as $n =>$vvv) $ini.=$kk.'[]="'.$vvv.'"'."\n";
            }
        }
    }
    return $ini;
}

/**
 * search for the first .htaccess.ini for resorse config
 * @var string $root base directory
 */

function find_api_configs($root) {
    $match=".htaccess.ini";
    $htaccess=glob($root.'/{ 
                  *, 
                  */*, 
                  */*/*, 
                  */*/*/*, 
                  */*/*/*/*, 
                  */*/*/*/*/* 
       }/'.$match, GLOB_BRACE);
   
   return $htaccess;
}

 /**
  * cut off the file extention and return the end in lower case
  * @param string $filename
  * @return $type 
  */
function cutExt($filename) {
    $file=explode('.', $filename);
    $type=array_pop($file);
    return strtolower($type);
}


/** --------------------------------------------------------------------------------------------------------------------
 * apache_request_headers if not
 * @link http://ca2.php.net/manual/en/function.getallheaders.php#99814 
 *  -------------------------------------------------------------------------------------------------------------------- */
if (!function_exists('apache_request_headers')) {
	function apache_request_headers() {
		$out=array();
		foreach($_SERVER as $key=>$value) {
			if (substr($key,0,5)=="HTTP_") {
				$key=str_replace(" ","-",ucwords(strtolower(str_replace("_"," ",substr($key,5)))));
				$out[$key]=$value;
			}else{
				$out[$key]=$value;
			}
		}
		return $out;
	}
}




 /**
  * Keep it restful
  *  - kill sessions and cookies
  *  - use HTTP AUTH , use HTTPS if you want more security
  *  - must be called manually; most frameworks and PHP Platforms depend on Sessions and Cookies, so lets not break them!
  *  @todo add more items to improve the restful ness of these services
  *  
  */
function keepItRestful() {
	unset($_COOKIE);
	session_destroy();
	unset($_SESSION);
}