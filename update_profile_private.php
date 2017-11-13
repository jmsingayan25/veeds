<?php

	
	include("jp_library/jp_lib.php");

	require("PHPMailerAutoload.php");
	require("class.phpmailer.php");
	require("class.smtp.php");

	if(isset($_POST['private']) && isset($_POST['user_id'])){

		$reply = array();
		$search['data'] = $_POST;
		$search['table'] = $data['table'];
		$search['where'] = "user_id = '".$_POST['user_id']."'";

		if(jp_update($search)){
			$reply = array('reply' => true);
		}else{
			$reply = array('reply' => false);
		}

		echo json_encode($reply);
	}
?>