	
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

	
	//
	session_start();
	if(isset($_SESSION['answer_name'])){
        $returnObj = new ResultObject();
    } else {
		$returnObj = new ResultObject(-1, '');
	}
	
	echo json_encode($returnObj, JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES);

	 
	

?>
