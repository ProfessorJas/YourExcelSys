<?php

	include_once 'config.php';
	include_once 'common.php';

	require_once("phpmailer/Phpmailer.php");
	require_once("phpmailer/class.smtp.php");

	
	function SendMail($address_list, $body) {
		// 实例化PHPMailer核心类
		$mail = new PHPMailer();
		
		// 是否启用smtp的debug进行调试 开发环境建议开启 生产环境注释掉即可 默认关闭debug调试模式
		$mail->SMTPDebug = 1;
		
		// 使用smtp鉴权方式发送邮件
		$mail->isSMTP();
		
		// smtp需要鉴权 这个必须是true
		$mail->SMTPAuth = true;
		
		// 链接qq域名邮箱的服务器地址
		$mail->Host = 'Shmail.hylinkad.com';
		
		// 设置使用ssl加密方式登录鉴权
		//$mail->SMTPSecure = 'ssl';
		
		// 设置ssl连接smtp服务器的远程服务器端口号
		$mail->Port = 25;
		
		// 设置发送的邮件的编码
		$mail->CharSet = 'UTF-8';
		
		// 设置发件人昵称 显示在收件人邮件的发件人邮箱地址前的发件人姓名
		$mail->FromName = 'Your Excel System';
		
		// smtp登录的账号 QQ邮箱即可
		$mail->Username = 'yes@pagechoice.com';
		
		// smtp登录的密码 使用生成的授权码
		$mail->Password = 'hy888888';
		
		// 设置发件人邮箱地址 同登录账号
		$mail->From = 'yes@pagechoice.com';
		
		// 邮件正文是否为html编码 注意此处是一个方法
		$mail->isHTML(true);
		
		// 设置收件人邮箱地址
		//$mail->addAddress($address);
		for($i = 0; $i < count($address_list); $i++) {
			$mail->addAddress($address_list[$i]);
		}
		
		// 添加该邮件的主题
		$mail->Subject = '新建问题通知';
		
		// 添加邮件正文
		$mail->Body = $body;
		
		// 为该邮件添加附件
		//$mail->addAttachment('./example.pdf');
		
		// 发送邮件 返回状态
		$status = $mail->send();

		if(!$status) {
			return "[send mail] error: " . $mail->ErrorInfo;
		}
		
		return "[send mail] ok";
		
	}
	
	
	class ResultObject {
		public $ecode;
		public $errorMsg;
		public $timestamp;
		public $sendmailinfo;

		function __construct($ecode = 0, $errorMsg = "") {
			$this->ecode = $ecode;
			$this->errorMsg = $errorMsg;
			$this->timestamp = date('Y-m-d H:i:s',time());
			$this->sendmailinfo = "";
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
		$sendMailInfo = "";
		if(count(Config::$answer_mail_list) > 0) {
			$mailbody = '<h2>Your Excel System有新问题提交</h2>';
			$mailbody .= '<b>问题描述:</b> <br/><pre><code>' . $desc . '</code></pre><br/><br/>';
			if($fileurl === "") {
				$mailbody .= '<b>问题文件:</b> 无<br/><br/>';
			} else {
				$mailbody .= '<b>问题文件:</b> <a href="' . $_SERVER['HTTP_HOST'] . $fileurl . '" download="' . $filename . '">下载</a><br/><br/>';
			}
			$mailbody .= '<b>网站链接:</b> <a href="' . $_SERVER['HTTP_HOST'] . '/excelqa.html">http://' . $_SERVER['HTTP_HOST'] . '/excelqa.html</a><br/><br/>';
						
			
			$sendMailInfo = SendMail(Config::$answer_mail_list, $mailbody);
			
			//print_r($mailbody);
		}


		//
		$returnObj = new ResultObject();
		$returnObj->sendmailinfo = $sendMailInfo;
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


