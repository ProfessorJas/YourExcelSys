<?php

	include_once 'config.php';
	include_once 'common.php';

	require_once("phpmailer/Phpmailer.php");
	require_once("phpmailer/class.smtp.php");

	
	function SendMail($address, $body) {
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
		$mail->addAddress($address);
		
		// 添加该邮件的主题
		$mail->Subject = '问题更新通知';
		
		// 添加邮件正文
		$mail->Body = $body;
		
		// 为该邮件添加附件
		//$mail->addAttachment('./example.pdf');
		
		// 发送邮件 返回状态
		$status = $mail->send();

		if(!$status) {
			return "[send to $address] error: " . $mail->ErrorInfo;
		}
		
		return "[send to $address] ok";
		
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

	function EditQestion($id, $filename, $fileurl, $notify, $answerer) {
		$id = mysql_entities_fix_string($id);
		$filename = mysql_entities_fix_string($filename);
		$fileurl = mysql_entities_fix_string($fileurl);
		$answerer = mysql_entities_fix_string($answerer);

		
		//connect to MySQL
		$conn_mysqli = new mysqli(Config::$db_hostname, Config::$db_username, Config::$db_password, Config::$db_database);
		if ($conn_mysqli->connect_error) {
			$returnObj = new ResultObject(-1, '连接数据库出错, ' . $conn_mysqli->connect_error);
			return $returnObj;
		} 

		$conn_mysqli->set_charset("utf8");

		//get record info
		$query = "SELECT * FROM question WHERE id=$id";
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
		
		$question_desc = $row["question_desc"];
		$question_file_name = $row["question_file_name"];
		$question_file_url = $row["question_file_url"];
		$questioner_email = $row["questioner_email"];	
		$questioner_phone = $row["questioner_phone"];
		
			
		//update record info
		$query = "UPDATE question SET answer_file_name='$filename', answer_file_url='$fileurl', answerer='$answerer', update_time=NOW() WHERE id=$id";
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
		$sendMailInfo = "";
		if($notify === "email" || $notify === "all") {
			$mailbody = '<h2>您在Your Excel System提交的问题已被更新</h2>';
			$mailbody .= '<b>问题ID:</b> ' . $id . '<br/>';
			$mailbody .= '<b>解决者:</b> ' . $answerer . '<br/><br/>';
			$mailbody .= '<b>问题描述:</b> <br/><pre><code>' . $question_desc . '</code></pre><br/><br/>';
			if($question_file_url === "") {
				$mailbody .= '<b>问题文件:</b> 无<br/><br/>';
			} else {
				$mailbody .= '<b>问题文件:</b> <a href="' . $_SERVER['HTTP_HOST'] . $question_file_url . '" download="' . $question_file_name . '">下载</a><br/><br/>';
			}
			$mailbody .= '<b>答案文件:</b> <a href="' . $_SERVER['HTTP_HOST'] . $fileurl . '" download="' . $filename . '">下载</a><br/><br/>';
			
			if($questioner_email != "") {
				$sendMailInfo = SendMail($questioner_email, $mailbody);
			}
			
			//print_r($mailbody);
		}

		if ($notify === "phone" || $notify === "all") {
			
		}



		//
		$returnObj = new ResultObject();
		$returnObj->sendmailinfo = $sendMailInfo;
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


