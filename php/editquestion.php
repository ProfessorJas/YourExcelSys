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

	function EditQestion($id, $filename, $fileurl, $notify, $answerer) {
		$id = mysql_entities_fix_string($id);
		$filename = mysql_entities_fix_string($filename);
		$fileurl = mysql_entities_fix_string($fileurl);

		
		//connect to MySQL
		$conn_mysqli = new mysqli(Config::$db_hostname, Config::$db_username, Config::$db_password, Config::$db_database);
		if ($conn_mysqli->connect_error) {
			$returnObj = new ResultObject(-1, '连接数据库出错, ' . $conn_mysqli->connect_error);
			return $returnObj;
		} 

		$conn_mysqli->set_charset("utf8");

		//get record info
		$query = "SELECT answer_file_url FROM question WHERE id=$id";
		//print_r($query);
		//exit();
		
		$result = $conn_mysqli->query($query);
		//print_r($result);
		//exit();
		if(!$result) {
			$returnObj = new ResultObject(-1, '获得记录出错, ' . $conn_mysqli->error);
			$conn_mysqli->close();
			return $returnObj;
		}
		
		$row = $result->fetch_assoc();
		$result->free();
		if(!$row) {
			$returnObj = new ResultObject(-1, '没有找到记录, id=' . $id);
			$conn_mysqli->close();
			return $returnObj;
		}
		
		$old_answer_file_url = $row["answer_file_url"];
		
		//update record info
		$query = "UPDATE question SET answer_file_url='$fileurl', answerer='$answerer', update_time=NOW() WHERE id=$id";
		//print_r($query);
		//exit();
		$result = $conn_mysqli->query($query);
		//print_r($result);
		//exit();
		if(!$result) {
			$returnObj = new ResultObject(-1, '更新记录出错, ' . $conn_mysqli->error);
			$conn_mysqli->close();
			return $returnObj;
		}
		
		$conn_mysqli->close();

		//echo 'old url:' . $old_answer_file_url;
		if(file_exists($old_answer_file_url)) {
			unlink($old_answer_file_url);
		}
		

		//
		$returnObj = new ResultObject();
		return $returnObj;
		
	}
	
	
	//print_r($_POST);
	//print_r($_FILES);
	//exit();
	
	//
	session_start();
	if(!isset($_SESSION['answer_name'])){
        $returnObj = new ResultObject(-1, '没有登入');
		echo json_encode($returnObj, JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES);
		exit();
    }
	$answerer = $_SESSION['answer_name'];
	
	
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
	
	if (isset($_POST['notify'])) {
		$notify = $_POST['notify'];
	} else {
		$notify = "";
	}
	
	$filename = "";
	$fileurl = "";
	
	if (!isset($_FILES['file'])) {
		$returnObj = new ResultObject(-1, '上传文件参数设置错误');
		echo json_encode($returnObj, JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES);
		exit();
	}
	
	$file = $_FILES['file'];
	if($file['error'] == UPLOAD_ERR_OK) {
		if($file['size'] > Config::$file_max_size) {
			$returnObj = new ResultObject(-1, '上传文件过大');
			echo json_encode($returnObj, JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES);
			exit();
		}
		
		$ext = getExt($file['name']);
		if(!in_array($ext, Config::$file_allow_ext)) {
			$returnObj = new ResultObject(-1, '非法文件类型');
			echo json_encode($returnObj, JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES);
			exit();
		}
		
		$uniName = getUniName();
		$destination = Config::$answer_file_dir . '/' . $uniName . '.' . $ext;
        if(!move_uploaded_file($file['tmp_name'], $destination)) {
            $returnObj = new ResultObject(-1, '文件保存失败');
			echo json_encode($returnObj, JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES);
			exit();
        }
		
		$filename = $file['name'];
		$fileurl = $destination;
		
			
	} else {
		if($file['error'] == UPLOAD_ERR_NO_FILE) {
			$returnObj = new ResultObject(-1, '没有上传文件');
			echo json_encode($returnObj, JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES);
			exit();
		} else {
			$returnObj = new ResultObject(-1, '上传文件失败, Error:' . $file['error']);
			echo json_encode($returnObj, JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES);
			exit();
		}
		
	}
	
	$returnObj = EditQestion($id, $filename, $fileurl, $notify, $answerer);
	echo json_encode($returnObj, JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES);
	 
	if($returnObj->ecode != 0) {
		if(file_exists($fileurl)) {
			unlink($fileurl);
		}
	}
	

?>


