<?php

	include_once 'config.php';
	include_once 'common.php';

	class ResultObject {
		public $ecode;
		public $errorMsg;
		public $timestamp;
		public $records;
		public $start;
		public $count;
		public $totalcount;

		function __construct($ecode = 0, $errorMsg = "") {
			$this->ecode = $ecode;
			$this->errorMsg = $errorMsg;
			$this->timestamp = date('Y-m-d H:i:s',time());
			$this->records = array();
			$this->start = 0;
			$this->count = 0;
			$this->totalcount = 0;
		}
	}

	function GetData($keyword, $start, $count, $type, $option) {
		$keyword = mysql_entities_fix_string($keyword);
		$start = mysql_entities_fix_string($start);
		$count = mysql_entities_fix_string($count);
		$type = mysql_entities_fix_string($type);

		//connect to MySQL
		$conn_mysqli = new mysqli(Config::$db_hostname, Config::$db_username, Config::$db_password, Config::$db_database);
		if ($conn_mysqli->connect_error) {
			$returnObj = new ResultObject(-1, '连接数据库出错, ' . $conn_mysqli->connect_error);
			return json_encode($returnObj, JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES);
		} 

		$conn_mysqli->set_charset("utf8");

		if($keyword === "") {
			$keyword_where = "";
		} else {
			$keyword_where = "AND (id='$keyword' OR question_file_name LIKE '%$keyword%' OR question_desc LIKE '%$keyword%')";
		}
		
		if($option === "solved") {
			$option_where = "AND answer_file_name<>''";
		} elseif($option === "unsolved") {
			$option_where = "AND answer_file_name=''";
		} else {
			$option_where = "";
		}
		
		//get record count
		$query = "SELECT COUNT(*) AS count FROM question WHERE question_type='$type' $keyword_where $option_where";
		$result = $conn_mysqli->query($query);
		if(!$result) {
			$returnObj = new ResultObject(-1, '获得记录总数出错, ' . $conn_mysqli->error);
			$conn_mysqli->close();
			return json_encode($returnObj, JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES);
		}
		if($result->num_rows <= 0) {
			$returnObj = new ResultObject(-1, '获得记录总数出错, rows = 0');
			$conn_mysqli->close();
			return json_encode($returnObj, JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES);
		}
		$row = $result->fetch_assoc();
		$result->free();
		if(!$row) {
			$returnObj = new ResultObject(-1, "获得记录总数出错");
			$conn_mysqli->close();			
			return json_encode($returnObj, JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES);
		}
		$totalcount = (int)$row["count"];
		
		
		//get record info
		$query = "SELECT * FROM question WHERE question_type='$type' $keyword_where $option_where ORDER BY update_time DESC LIMIT $start, $count";
		//print_r($query);
		//exit();
		
		$result = $conn_mysqli->query($query);
		if(!$result) {
			$returnObj = new ResultObject(-1, '获得记录出错, ' . $conn_mysqli->error);
			$conn_mysqli->close();
			return json_encode($returnObj, JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES);
		}
		
		$records = array();
		$i = 0;
		while ($row = $result->fetch_assoc()) {
			$record = new DummyObject();
			$record->id = $row["id"];
			$record->question_desc = $row["question_desc"];
			$record->update_time = $row["update_time"];
			$record->question_file_name = $row["question_file_name"];
			$record->question_file_url = $row["question_file_url"];
			$record->answer_file_name = $row["answer_file_name"];
			$record->answer_file_url = $row["answer_file_url"];
			$record->answerer = $row["answerer"];
			$records[$i] = $record;
			$i++;
		}
		$result->free();
		
		$conn_mysqli->close();

		//
		$returnObj = new ResultObject();
		$returnObj->records = $records;
		$returnObj->start = $start;
		$returnObj->count = $count;
		$returnObj->totalcount = $totalcount;
		
		return json_encode($returnObj, JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES);
	}
	
	
	//
	//print_r($_GET);
	//exit();	
	
			
	if (isset($_GET['type'])) {
		$type = $_GET['type'];
	} else {
		$type = "";
	}
	if($type != "excel" && $type != "vba") {
		$returnObj = new ResultObject(-1, '问题类型错误');
		echo json_encode($returnObj, JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES);
		exit();
	}
	
	$keyword = "";
	if (isset($_GET['keyword'])) {
		$keyword = $_GET['keyword'];
	}
	
	$start = "0";
	if (isset($_GET['start'])) {
		if($_GET['start'] != "") {
			$start = $_GET['start'];
		}
	}
	
	$count = "10";
	if (isset($_GET['count'])) {
		if($_GET['count'] != "") {
			$count = $_GET['count'];
		}
	}
	
	$option = "all";
	if (isset($_GET['option'])) {
		$option = $_GET['option'];
	}
	
	echo GetData($keyword, $start, $count, $type, $option);
	
	

?>


