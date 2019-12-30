<?php

	include_once 'config.php';
	include_once 'common.php';

	class ResultObject {
		public $ecode;
		public $errorMsg;
		public $timestamp;
		public $name;


		function __construct($ecode = 0, $errorMsg = "") {
			$this->ecode = $ecode;
			$this->errorMsg = $errorMsg;
			$this->timestamp = date('Y-m-d H:i:s',time());
			$this->name = "";
		}
	}

	function AnswerLogin($name, $password) {
		$name = mysql_entities_fix_string($name);
		$password = mysql_entities_fix_string($password);

		
		//connect to MySQL
		$conn_mysqli = new mysqli(Config::$db_hostname, Config::$db_username, Config::$db_password, Config::$db_database);
		if ($conn_mysqli->connect_error) {
			$returnObj = new ResultObject(-1, '连接数据库出错, ' . $conn_mysqli->connect_error);
			return $returnObj;
		} 

		$conn_mysqli->set_charset("utf8");

		//check record
		$query = "UPDATE answer SET login_time=NOW() WHERE name='$name' AND password='$password'";
		$result = $conn_mysqli->query($query);
		if(!$result || $conn_mysqli->affected_rows <= 0) {
			$returnObj = new ResultObject(-1, '用户名或密码错误');
			$conn_mysqli->close();
			return $returnObj;
		}
		
		$conn_mysqli->close();

		$_SESSION['answer_name'] = $name;

		//
		$returnObj = new ResultObject();
		$returnObj->name = $_SESSION['answer_name'];
		return $returnObj;
		
		
	}
	
	
	//
	if (isset($_POST['name'])) {
		$name = $_POST['name'];
	} else {
		$name = "";
	}
	if($name == "") {
		$returnObj = new ResultObject(-1, '用户名不能为空');
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
	
	session_start();
	if(isset($_SESSION['answer_name']) && $_SESSION['answer_name'] === $name){
        $returnObj = new ResultObject();
		$returnObj->name = $_SESSION['answer_name'];
		echo json_encode($returnObj, JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES);
		exit();
    }
	
	$returnObj = AnswerLogin($name, $password);
	echo json_encode($returnObj, JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES);
	 
	

?>


