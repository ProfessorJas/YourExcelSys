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

	function AddQestion($desc, $type, $filename, $fileurl, $email, $phone, $ip) {
		$desc = mysql_entities_fix_string($desc);
		$type = mysql_entities_fix_string($type);
		$filename = mysql_entities_fix_string($filename);
		$fileurl = mysql_entities_fix_string($fileurl);
		$email = mysql_entities_fix_string($email);
		$phone = mysql_entities_fix_string($phone);

		
		//connect to MySQL
		$conn_mysqli = new mysqli(Config::$db_hostname, Config::$db_username, Config::$db_password, Config::$db_database);
		if ($conn_mysqli->connect_error) {
			$returnObj = new ResultObject(-1, '连接数据库出错, ' . $conn_mysqli->connect_error);
			return $returnObj;
		} 

		$conn_mysqli->set_charset("utf8");

		//insert record
		$query = "INSERT INTO question (question_desc, question_type, question_file_name, question_file_url, questioner_email, questioner_phone, update_time, answerer, answer_file_name, answer_file_url, create_time, create_ip) VALUES ('$desc', '$type', '$filename', '$fileurl', '$email', '$phone', NOW(), '', '', '', NOW(), '$ip')";
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
	
	function GetIP() {
		if (getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
			$ip = getenv('HTTP_CLIENT_IP');
		} else if (getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
			$ip = getenv('HTTP_X_FORWARDED_FOR');
		} else if (getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
			$ip = getenv('REMOTE_ADDR');
		} else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		$res =  preg_match ( '/[\d\.]{7,15}/', $ip, $matches ) ? $matches [0] : '';
		return $res;
	}


	//print_r($_POST);
	//print_r($_FILES);
	//exit();
	
	//
	if (isset($_POST['desc'])) {
		$desc = $_POST['desc'];
	} else {
		$desc = "";
	}
	if($desc == "") {
		$returnObj = new ResultObject(-1, '描述不能为空');
		echo json_encode($returnObj, JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES);
		exit();
	}

	if (isset($_POST['type'])) {
		$type = $_POST['type'];
	} else {
		$type = "";
	}
	if($type != "excel" && $type != "vba") {
		$returnObj = new ResultObject(-1, '问题类型错误');
		echo json_encode($returnObj, JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES);
		exit();
	}
	
	if (isset($_POST['email'])) {
		$email = $_POST['email'];
	} else {
		$email = "";
	}
	
	if (isset($_POST['phone'])) {
		$phone = $_POST['phone'];
	} else {
		$phone = "";
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
		$destination = Config::$question_file_dir . '/' . $uniName . '.' . $ext;
        if(!move_uploaded_file($file['tmp_name'], $destination)) {
            $returnObj = new ResultObject(-1, '文件保存失败');
			echo json_encode($returnObj, JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES);
			exit();
        }
		
		$filename = $file['name'];
		$fileurl = $destination;
		
			
	} else {
		if($file['error'] != UPLOAD_ERR_NO_FILE) {
			$returnObj = new ResultObject(-1, '上传文件失败, Error:' . $file['error']);
			echo json_encode($returnObj, JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES);
			exit();
		}
		
	}
	
	$ip = GetIP();

	$returnObj = AddQestion($desc, $type, $filename, $fileurl, $email, $phone, $ip);
	echo json_encode($returnObj, JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES);
	 
	if($returnObj->ecode != 0) {
		if(file_exists($fileurl)) {
			unlink($fileurl);
		}
	}
	

?>


