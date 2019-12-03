<?php
	header("Content-Type: text/html;charset=utf-8");

	date_default_timezone_set("Asia/Hong_Kong");
	
	include_once 'config.php';


	//
	class DummyObject {
		function __construct() {
		}
	}

	
	function mysql_fix_string($string) {
		if (get_magic_quotes_gpc()) 
			$string = stripslashes($string);
		
		//return mysql_real_escape_string($string);
		return $string;
	}
	
	function mysql_entities_fix_string($string) {
		return htmlentities(mysql_fix_string($string));
	}
	
	function s2hms($seconds) {
		if($seconds > 3600) {
			$hours = intval($seconds / 3600);
			$minutes = $seconds % 3600;
			$time = $hours.":".gmstrftime('%M:%S', $minutes);
		} else {
			$time = gmstrftime('%H:%M:%S', $seconds);
		}
		return $time;
	}
	
	function mylog($msg) {
	  error_log(date("[Y-m-d H:i:s]")." [".$_SERVER['REMOTE_ADDR']." - ".$_SERVER['SCRIPT_NAME']." ? ".$_SERVER['QUERY_STRING']."] :".$msg."\n", 3, "/tmp/php_err.log");
	}
	
	/**
	 * 获取文件扩展名
	 */
	function getExt($filename) {
		return strtolower(pathinfo($filename,PATHINFO_EXTENSION));
	}
 
	/**
	 * 获取唯一字符串
	 */
	function getUniName() {
		return md5(uniqid(microtime(true), true));
	}
	
	
	
?>