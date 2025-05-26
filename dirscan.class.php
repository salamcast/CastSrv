<?php
ini_set ( 'memory_limit', '512M' );
require_once (dirname ( __FILE__ ) . '/includes/getid3/getid3/getid3.php');
require_once (dirname ( __FILE__ ) . '/includes/getid3/getid3/extension.cache.sqlite3.php');

require_once './REST.class.php';
require_once './WebServiceTools.php';
/**
 *
 * @package DirScan
 * @author Abu Khadeejah Karl Holz
 * @version 0.2
 * 
 */
//$rest = new RESTphulSrv();

class DirScan  {

	/**
	 *
	 * @var string $media_root
	 * @access private
	 */
	private $media_root;


	/**
	 * Search by filetype, this will fail, since *.* scanning is not permited
	 * 
	 * @var string $search
	 * @access private
	 */
	private $search = '%2A';
	/**
	 * Full path Directories list
	 * 
	 * @var array $dir
	 * @access private
	 */
	private $dirs = array ();
	/**
	 * Full Path Files list, seperated by directory
	 * 
	 * @var array $files
	 * @access private
	 */
	private $files = array ();
	/**
	 * File to send to the browser
	 * 
	 * @var string $file
	 * @access private
	 */
	private $file = null;
	/**
	 * Array of relative path files, organized by directory within the
	 * media_root each item will contain basic file info:
	 * [file],[type],[extension],[created],[updated],[filesize],[access],[writeable],[owner]
	 * each item above is the 'info key' in the example bellow, access the array
	 * like this:
	 * $files=$dir->getFilesList;
	 * echo $files[$dir][index]['info key'];
	 *
	 * @var array $relFiles
	 * @access private
	 */
	protected $relFiles = array ();
	/**
	 *
	 * @var array $relDirs
	 * @access private
	 */
	protected $relDirs = array ();
	
	/**
	 *
	 * @var $id3 object getid3 class
	 */
	private $id3 = FALSE;
	
	/**
	 * scan file meta data for retriveing file info
	 * 
	 * @var $meta bool
	 */
	private $meta = FALSE;
	
	
	/**
	 * WaldScan, match need files by type
	 *
	 * Wald is the german word for forest/woods,
	 * this class will look through directory trees and find matching file types
	 * 

	 */

	/** @var array $very_taboo media_root can't be in these directories */
	public $very_taboo=array();
	/** @var array $taboo These directories should not be web/media root */
	public $taboo=array();
	/** @var array $taboo_ext throw on file matching  these types */
	public $taboo_ext=array();
	/** @var array $mime MIME types */
	public $mime=array();


	function __construct($root = null) {
		if (is_null ( $root )) {
			RestError("500", "You will need to suply a valid root with instantiating the class. - " . $root );
		}
		$ini=parse_ini_file('.htconfig/dirscan.ini', true);
		$this->very_taboo=$ini['filesystem']['very_taboo'];
		$this->taboo=$ini['filesystem']['taboo'];
		$this->taboo_ext=$ini['filesystem']['taboo_ext'];
		$this->mime=$ini['podcast'];
		
		//set array search for podcast
		$this->search_match(array_keys($this->mime));

		// set media_root
		$this->root($root);
		$this->getDirs();
		

		return TRUE;
	}

	function __destruct() {
		//$this->close_cache ();
		return TRUE;
	}

	function root($value){
		if (is_dir ( $value )) {
			if (in_array ( $value, $this->taboo )) {
				RestError("403", "Media root is in the taboo directory list" );
			}
			foreach ( $this->very_taboo as $t ) {
				if (preg_match ( '/^' . $t . '/', $value )) {
					RestError("403", 'Media root is located in a very taboo location on your system' );
				}
			}
			$this->media_root = preg_replace ( '/\/$/', '', $value );
		} else {
			RestError("400", "Media root is not a valid directory" );
		}
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

	public function clearTaboo() {
		$this->very_taboo = array ();
		$this->taboo = array ();
		return TRUE;
	}

	function search_match($value=false) {
		if (is_array ( $value )) {
			$this->search = implode ( ',', $value );
		} elseif (is_string ( $value )) {
			$this->search = trim ( $value );
		} else {
			RestError('404', 'Search match value is invalid, use an array or a csv list of file types' );
		}
		return true;
	}

    /**
      * glob_type_brace()  * Might not work on Solaris and other non GNU systems *
      *
      * Configures a filetype list for use with glob searches, 
      * will match uppercase or lowercase extensions only
      * @param string $dir directory to add filetype in upper lower
      * @return string
      */
	  function globTypeBrace($dir) {
		// global $rest;
        $dir=str_replace(array('///', '//'), array('/', '/' ), $dir);
         if (!is_dir($dir)) {
			// $rest->error="Not Dir! in glob_type_brace";  
			// error('not_dir');
            return FALSE;
		}

        $ext=array();
        $epld=explode(',', $this->search);
        foreach ($epld as $new) {
            $new=trim($new);
            if ($new != '*' || $new != '%2A') {
                $ext[]=strtolower($new); // lower case file extention
                $ext[]=strtoupper($new); // upper case file extention
            }
        }
        $build=$dir.'/*.{'.implode(',', $ext).'}';
        return $build;    
    }

	/**
	 * Get all dirs under path, 7 levels deep
	 * 
	 * @param string $path
	 *        	path to scan under media root
	 * @return TRUE
	 */
	function getDirs() {
		if (! is_dir ( $this->media_root )) {
			RestError('404',"Can't get directories from a non directory" );
		}
		$dirs = array_merge ( 
			glob ( $this->media_root . '/*', GLOB_ONLYDIR ), 
			glob ( $this->media_root . '/*/*', GLOB_ONLYDIR ), 
			glob ( $this->media_root . '/*/*/*', GLOB_ONLYDIR ), 
			glob ( $this->media_root . '/*/*/*/*', GLOB_ONLYDIR ), 
			glob ( $this->media_root . '/*/*/*/*/*', GLOB_ONLYDIR ), 
			glob ( $this->media_root . '/*/*/*/*/*/*', GLOB_ONLYDIR ), 
			glob ( $this->media_root . '/*/*/*/*/*/*/*', GLOB_ONLYDIR ) 
		);
		if (count ( $dirs ) < 1) {
			$dirs = array ( $this->media_root );
		}
		$this->dirs = $dirs;
		return TRUE;
	}



	function file($value) {
		if (is_file ( $value )) {
			if (preg_match ( "/^" . str_replace ( '/', '\/', $this->media_root ) . "/", $value ) && in_array ( cutExt ( $value ), explode ( ',', $this->search ) )) {
				$this->file = $value;
				return TRUE;
			} else {
				RestError("404", $value . " is not in media root or not a real file" );
			}
		} elseif (is_file ( $this->media_root . '/' . $value )) {
			$this->file = str_replace ( array (
					'///',
					'//' 
			), array (
					'//',
					'/' 
			), $this->media_root . '/' . $value );
			return TRUE;
		}
		RestError("404", $value . " is not a real file in this media root" );
	}

	function getRealDirsList() {
		if (count ( $this->dirs ) == 0) {
			$this->getDirs ();
		}
		return $this->dirs;
	}

	function getRealFilesList() {
		if (count ( $this->dirs ) == 0) {
			$this->getDirs ();
		}
		if (count ( $this->files ) == 0) {
			$this->getFiles ();
		}
		return $this->files;
	}

	function getFilesList() {
		if (count ( $this->dirs ) == 0) {
			$this->getDirs ();
		// } elseif (count ( $this->dirs ) == 1) {
		// 	$this->get_cache_files ( $this->dirs [0] );
		}
		if (count ( $this->relFiles ) == 0 && $this->file === NULL) {
			$this->getFiles ();
		} elseif (count ( $this->relFiles ) > 0) {
			return $this->relFiles;
		} else {
			$this->file_info ( $this->file );
		}
		return $this->relFiles;
	}

	function getDirsList(){
		// $this->get_cache_dir();
		if (count ( $this->dirs ) == 0) {
			$this->getDirs ();
		}
		$w = array ();
		if (count ( glob ( globTypeBrace ( $this->media_root, GLOB_BRACE ) ) ) > 0)
		$w [] = '/';
		foreach ( $this->dirs as $d ) {
			if (count ( glob ( globTypeBrace ( $d ), GLOB_BRACE ) ) > 0)
				$w [] = str_replace ( $this->media_root, '', $d );
		}
		return $w;
	}

	function getJSON(){
		if (count ( $this->dirs ) == 0) {
			$this->getDirs ();
		} 
		// elseif (count ( $this->dirs ) == 1) {
		// 	$this->get_cache_files ( $this->dirs [0] );
		// }
		if (count ( $this->relFiles ) == 0) {
			$this->getFiles ();
		}
		return json_encode ( $this->relFiles );
	}

	function getJSONdirs(){
		if (count ( $this->dirs ) == 0) {
			$this->getDirs ();
		}
		$w = array ();
		foreach ( $this->dirs as $d ) {
			$w [] = str_replace ( $this->media_root, '', $d );
		}
		return json_encode ( $w );
	}

	/** @todo use a lookup from the RESTphulSrv class */
	function getMime(){
		if ($this->file !== NULL) {
			return $this->media_file_type ( $this->file );
		} else {
			return NULL;
		}
	}


	function getDirInfo($dir) {
		$d = $this->dir_info ( $dir );
		foreach ( $this->relFiles [$this->getWebPath ( $dir )] as $i => $v ) {
			$d ['size'] = $d ['size'] + $v ['filesize'];
			$d ['files'] ++;
		}
		$d ['nice_size'] = nice_size ( $d ['size'] );
		return $d;
	}


	/**
	 * Prints a JSON document of all matching files with relitive paths and
	 * basic file info
	 */
	public function printJSON() {
		if (! headers_sent ()) {
			header ( 'Content-Type: text/json' );
			header ( 'Cache-Control: max-age=28800' );
		}
		echo $this->getJSON;
		exit ();
	}
	/**
	 * Prints a JSON document of all directories and basic info
	 */
	public function printJSONdirs() {
		if (! headers_sent ()) {
			header ( 'Content-Type: text/json' );
			header ( 'Cache-Control: max-age=28800' );
		}
		echo $this->getJSONdirs;
		exit ();
	}
	/**
	 * Send file to the client after everything has checked out
	 * @todo fix for RESTphulSrv class
	 */
	public function streamFile() {
		if (! headers_sent ()) {
			header ( "Content-Type: " . trim ( $this->media_file_type ( $this->file ) ) );
			header ( "Content-Length: " . trim ( filesize ( $this->file ) ) );
//			$this->sendFile ();
			@readfile ( $this->file );
			exit ();
		} else {
			RestError( 'Failed to send file for saving' );
		}
	}
	
	

	/**
	 * get all media root files that match search
	 * 
	 * @return TRUE
	 */
	private function getFiles() {
		//$this->get_cache_files ( $this->media_root ) || 
		$this->file_list ( $this->media_root );
		foreach ( $this->dirs as $k => $value ) {
			//$this->get_cache_files ( $value ) || 
			$this->file_list ( $value );
		}
		return TRUE;
	}
	/**
	 * file_list()
	 * 
	 * @access private
	 * @param string $search        	
	 * @return mixed
	 */
	private function file_list($dir) {
		// Lookup file
		$search = globTypeBrace ( $dir );
		$s = glob ( $search, GLOB_BRACE );
		$web = $this->getWebPath ( $dir );
		if ($web == '') {
			$web = '/';
		}
		foreach ( $s as $i => $f ) {
			$f = str_replace ( array (
					'///',
					'//' 
			), array (
					'/',
					'/' 
			), $f );
			$this->files [$dir] [$i] = $f;
			if ($this->meta == FALSE) {
				$this->relFiles [$web] [$i] = $this->file_info ( $f );
			} 
			// else {
			// 	$this->relFiles [$web] [$i] = $this->getMETA ( $f );
			// }
			// Dirs
			if (! array_key_exists ( $web, $this->relDirs ) || ! is_array ( $this->relDirs [$web] )) {
				$this->dir_info ( $dir );
			}

		}
		if (array_key_exists ( $web, $this->relFiles ) && (count ( $this->relFiles [$web] ) > 0)) {
			//$this->cache_files ( $dir );
			return $this->relFiles [$web];
		}
		return false;
	}
	private function dir_info($dir) {
		$ctime = filectime ( $dir );
		$keywords = str_replace ( '/', ',', $dir ) . ',' . $this->search;
		$build = time ();
		return array (
				'dirname' => $dir,
				'webpath' => $this->getWebPath ( $dir ),
				'size' => 0,
				'nice_size' => nice_size ( 0 ),
				'files' => 0,
				'search' => $this->search,
				'keywords' => trim ( $keywords, ',' ),
				'created' => date ( DATE_RSS, $ctime ),
				'year' => date ( "Y", $ctime ),
				'month' => date ( "m", $ctime ),
				'day' => date ( "d", $ctime ),
				'hour' => date ( "H", $ctime ),
				'min' => date ( "i", $ctime ),
				'sec' => date ( "s", $ctime ),
				'build' => date ( DATE_RSS, $build ),
				'build_year' => date ( "Y", $build ),
				'build_month' => date ( "m", $build ),
				'build_day' => date ( "d", $build ),
				'build_hour' => date ( "H", $build ),
				'build_min' => date ( "i", $build ),
				'build_sec' => date ( "s", $build ) 
		);
	}
	
	/**
	 * generate file info
	 * 
	 * @param string $f        	
	 * @return TRUE
	 */
	private function file_info($f) {
		if (! is_file ( $f )) {
			return FALSE;
		}
		$dir = dirname ( $f );
		$f = str_replace ( array (
				'///',
				'//' 
		), array (
				'/',
				'/' 
		), $f );
		
		if (is_writeable ( $f )) {
			$write = 'TRUE';
		} else {
			$write = 'FALSE';
		}
		$posix = posix_getpwuid ( fileowner ( $f ) );
		$info = pathinfo ( $f );
		if (array_key_exists ( 'extension', $info )) {
			$ext = $info ['extension'];
		} else {
			$ext = "FALSE";
		}
		$size = filesize ( $f );
		$nice_size = nice_size ( $size );
		$ctime = filectime ( $f );
		$utime = filemtime ( $f );
		// Files
		$info = array (
				'file' => $this->getWebPath ( $f ),
				'dir' => $this->getWebPath ( $dir ),
				'filename' => $info ['filename'],
				'dirname' => $info ['dirname'],
				'basename' => $info ['basename'],
				'type' => $this->media_file_type ( $f ),
				'extension' => $ext,
				'created' => date ( DATE_RSS, $ctime ),
				'year' => date ( "Y", $ctime ),
				'month' => date ( "m", $ctime ),
				'day' => date ( "d", $ctime ),
				'hour' => date ( "H", $ctime ),
				'min' => date ( "i", $ctime ),
				'sec' => date ( "s", $ctime ),
				'updated' => date ( DATE_RSS, $utime ),
				'update_year' => date ( "Y", $utime ),
				'update_month' => date ( "m", $utime ),
				'update_day' => date ( "d", $utime ),
				'update_hour' => date ( "H", $utime ),
				'update_min' => date ( "i", $utime ),
				'update_sec' => date ( "s", $utime ),
				'filesize' => $size,
				'nice_size' => $nice_size,
				// 'access' => substr(sprintf('%o', fileperms($f)), -4),
				'writeable' => "$write",
				'owner' => $posix ['gecos'] 
		);
		return $info;
	}
	/**
	 * returns the file size in a nicer easyer to read format
	 * 
	 * @param int $size        	
	 * @return string
	 */
	// private function nice_size($size) {
	// 	switch ($size) {
	// 		case round ( (($size / 1024) / 1024) / 1024 ) > 1 :
	// 			$nice_size = round ( ((($size / 1024) / 1024) / 1024), 2 ) . " GB";
	// 			break;
	// 		case round ( ($size / 1024) / 1024 ) > 1 :
	// 			$nice_size = round ( (($size / 1024) / 1024), 2 ) . " MB";
	// 			break;
	// 		case round ( $size / 1024 ) > 1 :
	// 			$nice_size = round ( ($size / 1024), 2 ) . " KB";
	// 			break;
	// 		default :
	// 			$nice_size = $size . " Bytes";
	// 			break;
	// 	}
	// 	return $nice_size;
	// }

	/**
	 * getWebPath of given file
	 * 
	 * @todo fix for base class
	 * @param
	 *        	$file
	 * @return web class path
	 */
	private function getWebPath($file = __FILE__) {
		if (array_key_exists ( 'DOCUMENT_ROOT', $_SERVER ) && ($_SERVER ['DOCUMENT_ROOT'] == $this->media_root)) {
			return str_replace ( $_SERVER ['DOCUMENT_ROOT'], '', $file );
		}
		return str_replace ( $this->media_root, '', $file );
	}
	/**
	 * cut off the file extention and return the end in lower case
	 * 
	 * @param string $filename        	
	 * @return $type
	 */
	// private function cut_ext($filename) {
	// 	$fn = explode ( '.', $filename );
	// 	$type = array_pop ( $fn );
	// 	return strtolower ( $type );
	//}
	/**
	 * media_file_type()
	 * @todo fix for base class
	 * @param string $filename
	 *        	- media file name
	 * @return string $type returns MIME type for file for the <link /> tag in
	 *         rss
	 */
	private function media_file_type($filename) {
		$ext= cutExt ( $filename );
		if (array_key_exists($ext, $rest->mime)) {
			return $rest->mime[$ext];
		} 
		return $rest->mime['default'];
	}	
}
?>
