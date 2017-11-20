<?php
/*

	Update information of Firstname, Lastname, Birthday, Country, Gender, 
	Profile Picture, Cover Photo, Video File, Private status, Notifications

*/
	include("jp_library/jp_lib.php");

	require("PHPMailerAutoload.php");
	require("class.phpmailer.php");
	require("class.smtp.php");

	$_POST['update'] = "1";
	// $_POST['firstname'] = "Test";
	// $_POST['lastname'] = "User200";
	$_POST['user_id'] = "59";
	// $_POST['username'] = "Magic Johnson";
	// $_POST['email'] = "magic@yahoo.com";
	$_POST['bday'] = "1990-10-30";
	// $_POST['gender'] = "0";
	// $_POST['password'] = "123456qwerty";
	// $_POST['country'] = "Philippines";

	// if (isset($_POST['email'])) {
		
	// 	$reply = array();

	// 	$search['select'] = "firstname, lastname, bday, gender, username";
	// 	$search['table'] = "veeds_users";
	// 	$search['where'] = "email = '".$_POST['email']."'";

	// 	if(jp_count($search) > 0){

	// 		$result = jp_get($search);
	// 		$row = mysqli_fetch_assoc($result);

	// 		$code = $_POST;

	// 		// if($row['notifications'] == 0){
	// 		// 	$code['notifications'] = 0;
	// 		// }else{
	// 		// 	$code['notifications'] = 1;
	// 		// }

	// 		// if (!isset($_POST['bday'])) {
	// 		// 	$code['bday'] = date('Y-m-d');
	// 		// }

	// 		$data['data'] = $code;
	// 		$data['table'] = "veeds_users";
	// 		$data['where'] = "email = '".$_POST['email']."'";

	// 		// $reply = array('post' => $data);
	// 		if(jp_update($data)){
	// 			$reply = array('reply' => 'success');
	// 		}else{
	// 			$reply = array('reply' => 'failed');
	// 		}
	// 	}

	// 	echo json_encode($reply);
	// }

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
			// $_POST['personal_information'] = str_replace('"', "'", $_POST['personal_information']);
			// $_POST['gender'] = (int)$_POST['gender'];
			
			$code = $_POST;
			// $code['isProfileSet'] = 1;
			// $code['username'] = $_POST['username'];

			// $code = array(
			// 			'username' => $_POST['username'], 
			// 			'personal_information' => $_POST['personal_information'], 
			// 			'isProfileSet' => 1
			// 		);				
			
			$search['data'] = $code;
			$search['table'] = $data['table'];
			$search['where'] = "user_id = '".$_POST['user_id']."'";

			// $reply = array('post' => $search);
			if(jp_update($search)){
				$reply = array('reply' => '1', 'post' => $code);
			}else{
				$reply = array('reply' => '2');
			}
		}
			echo json_encode($reply);
	}
?>