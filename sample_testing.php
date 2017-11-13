<?php

	include("jp_library/jp_lib.php");

	require("PHPMailerAutoload.php");
	require("class.phpmailer.php");
	require("class.smtp.php");

	$_POST['username'] = "testuser302";
	$_POST['personal_information'] = "";
	$_POST['isProfileSet'] = "";
	$_POST['gender'] = "1";
	$_POST['update'] = "1";
	$_POST['user_id'] = "229";

	if(isset($_POST['update'])){

		$data['table'] = "veeds_users";

		if(isset($_POST['username'])){
			$search2['select'] = "user_id";
			$search2['table'] = $data['table'];
			$search2['where'] = "username = '".$_POST['username']."'";

			if(jp_count($search2) > 0){
				$reply = array('reply' => '3', 'message' => 'username exist');
			}
		}

		if(!isset($reply)){
			$_POST['personal_information'] = str_replace('"', "'", $_POST['personal_information']);
			// $reply = array('post' => $_POST);
			$_POST['gender'] = (int)$_POST['gender'];
			$code = $_POST;
			$code['isProfileSet'] = 1;
			// $code['username'] = $_POST['username'];

			// $code = array(
			// 			'username' => $_POST['username'], 
			// 			'personal_information' => $_POST['personal_information'], 
			// 			'isProfileSet' => 1
			// 		);				
			
			$search['data'] = $code;
			$search['table'] = $data['table'];
			$search['where'] = "user_id = '".$_POST['user_id']."'";

			$reply = array('post' => $code);

			// if(jp_update($search)){
			// 	$reply = array('reply' => '1', 'post' => $code);
			// }else{
			// 	$reply = array('reply' => '2');
			// }
		}
		echo json_encode($reply);
	}

?>