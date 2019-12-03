<?php

	include_once 'config.php';
	include_once 'common.php';

	class ResultObject {
		public $ecode;
		public $errorMsg;
		public $timestamp;

		function __construct($ecode = 0, $errorMsg = "") {
			$this->ecode = $ecode;
			$this->errorMsg = $errorMsg;
			$this->timestamp = date('Y-m-d H:i:s',time());
		}
	}

	function AddAnswer($name, $password) {
		$name = mysql_entities_fix_string($name);
		$password = mysql_entities_fix_string($password);

		
		//connect to MySQL
		$conn_mysqli = new mysqli(Config::$db_hostname, Config::$db_username, Config::$db_password, Config::$db_database);
		if ($conn_mysqli->connect_error) {
			$returnObj = new ResultObject(-1, '连接数据库出错, ' . $conn_mysqli->connect_error);
			return $returnObj;
		} 

		$conn_mysqli->set_charset("utf8");

		//insert record
		$query = "INSERT INTO answer (name, password) VALUES ('$name', '$password')";
		$result = $conn_mysqli->query($query);
		if(!$result) {
			$returnObj = new ResultObject(-1, '插入数据出错, ' . $conn_mysqli->error);
			$conn_mysqli->close();
			return $returnObj;
		}
		
		$conn_mysqli->close();

		//
		$returnObj = new ResultObject();
		return $returnObj;
		
	}
	
	
	//
	if (isset($_POST['code'])) {
		$code = $_POST['code'];
	} else {
		$code = "";
	}
	if(!in_array($code, Config::$gm_code_list)) {
		$returnObj = new ResultObject(-1, '错误的GM码');
		echo json_encode($returnObj, JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES);
		exit();
	}
	
	if (isset($_POST['name'])) {
		$name = $_POST['name'];
	} else {
		$name = "";
	}
	if($name == "") {
		$returnObj = new ResultObject(-1, '名字不能为空');
		echo json_encode($returnObj, JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES);
		exit();
	}

	if (isset($_POST['password'])) {
		$password = $_POST['password'];
	} else {
		$password = "";
	}
	if($password == "") {
		$returnObj = new ResultObject(-1, '密码不能为空');
		echo json_encode($returnObj, JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES);
		exit();
	}
	
	
	$returnObj = AddAnswer($name, $password);
	echo json_encode($returnObj, JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES);
	 
	

?>


