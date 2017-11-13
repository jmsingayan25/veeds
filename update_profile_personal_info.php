<?php
/*

	Update information of Firstname, Lastname, Birthday, Country, Gender, 
	Profile Picture, Cover Photo, Video File, Private status, Notifications

*/
	include("jp_library/jp_lib.php");

	$_POST['update'] = 1;
	$_POST['user_id'] = "231";
	// $_POST['firstname'] = "Test";
	// $_POST['lastname'] = "User200";
	// $_POST['username'] = "testuser200";
	// $_POST['email'] = "testuser200@yahoo.com";
	$_POST['bday'] = "1994-10-25";
	$_POST['gender'] = "0";
	// $_POST['password'] = "123456qwerty";
	// $_POST['country'] = "Philippines";
	$_POST['personal_information'] = "";
	if(isset($_POST['update'])){

		$reply = array();

		if(isset($_POST['username'])){
			$search2['select'] = "user_id";
			$search2['table'] = "veeds_users";
			$search2['where'] = "username = '".$_POST['username']."'";

			if(jp_count($search2) > 0){
				$reply = array('reply' => 3, 'message' => 'username exist');
			}
		}

		if(!isset($reply) || empty($reply)){
			
			$_POST['personal_information'] = str_replace('"', "'", $_POST['personal_information']);
			$_POST['gender'] = (int)$_POST['gender'];
			
			$code = $_POST;
			$code['isProfileSet'] = 1;

			$search['data'] = $code;
			$search['table'] = "veeds_users";
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