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

	function DelQuestion($id) {
		$id = mysql_entities_fix_string($id);

		//connect to MySQL
		$conn_mysqli = new mysqli(Config::$db_hostname, Config::$db_username, Config::$db_password, Config::$db_database);
		if ($conn_mysqli->connect_error) {
			$returnObj = new ResultObject(-1, '连接数据库出错, ' . $conn_mysqli->connect_error);
			return json_encode($returnObj, JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES);
		} 

		$conn_mysqli->set_charset("utf8");

		//get record info
		$query = "SELECT question_file_url, answer_file_url FROM question WHERE id=$id";
		$result = $conn_mysqli->query($query);
		if(!$result) {
			$returnObj = new ResultObject(-1, '获得记录出错, ' . $conn_mysqli->error);
			$conn_mysqli->close();
			return json_encode($returnObj, JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES);
		}
		
		$row = $result->fetch_assoc();
		if(!$row) {
			$result->free();
			$returnObj = new ResultObject(-1, '没有找到记录, id=' . $id);
			$conn_mysqli->close();
			return json_encode($returnObj, JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES);
		}
		$result->free();
		
		$question_file_url = $row["question_file_url"];
		$answer_file_url = $row["answer_file_url"];
		
		//delete record info
		$query = "DELETE FROM question WHERE id=$id";
		$result = $conn_mysqli->query($query);
		if(!$result) {
			$returnObj = new ResultObject(-1, '删除记录出错, ' . $conn_mysqli->error);
			$conn_mysqli->close();
			return json_encode($returnObj, JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES);
		}
		
		$conn_mysqli->close();

		if(file_exists($question_file_url)) {
			unlink($question_file_url);
		}
		
		if(file_exists($answer_file_url)) {
			unlink($answer_file_url);
		}


		//
		$returnObj = new ResultObject();
		return json_encode($returnObj, JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES);
		
	}
	
	
	//
	//print_r($_POST);
	
	session_start();
	if(!isset($_SESSION['answer_name'])){
        $returnObj = new ResultObject(-1, '没有登入');
		echo json_encode($returnObj, JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES);
		exit();
    }
	
	if (isset($_POST['id'])) {
		$id = $_POST['id'];
	} else {
		$id = "";
	}
	if($id == "") {
		$returnObj = new ResultObject(-1, 'id不能为空');
		echo json_encode($returnObj, JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES);
		exit();
	}

	echo DelQuestion($id);
	

?>


