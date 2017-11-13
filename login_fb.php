<?php

	include("jp_library/jp_lib.php");

	require("PHPMailerAutoload.php");
	require("class.phpmailer.php");
	require("class.smtp.php");

	function register_device_id($device_id, $user_id){
		$device['select'] = "user_id";
		$device['table'] = "veeds_user_devices";
		$device['where'] = "user_id = ".$user_id." AND google_device_id ='".$device_id."'";

		if(jp_count($device) == 0){
			$device['data'] = array('user_id' => $user_id, 'google_device_id' => $device_id);
			$device['table'] = "veeds_user_devices";
			jp_add($device);
		}
	}

	$_POST['fbid'] = "1745879742120451";
	$_POST['register_fb'] = 1;
	$_POST['gender'] = 1;
	$_POST['device_id'] = "";

	if(isset($_POST['fbid'])){

		$data['table'] = "veeds_users";
		$reply = array();

		$search['select'] = "user_id, firstname, lastname, email, bday, gender, username, reset_code, reset_check, personal_information, fbid, profile_pic, cover_photo, video_file, country, private, disabled, notifications, signup_code, isProfileSet";
		$search['table'] = $data['table'];
		$search['where'] = "fbid = '".$_POST['fbid']."'";

		$result = jp_get($search);
		$row1 = mysqli_fetch_array($result);

		if(jp_count($search) > 0){
			if($_POST['register_fb'] == 1){
				$_POST['user_id'] = $row1['user_id'];
				$_POST['gender'] = (int) $_POST['gender'];
				$data['data'] = $_POST;
				$data['table'] = $data['table'];
				$data['where'] = "user_id = '".$row1['user_id']."'";

				// if(jp_update($data)){
				// 	$result = jp_get($search);
				// 	$row = mysqli_fetch_assoc($result);

				// 	if(empty($row['profile_pic']))
				// 		$row['profile_pic'] = "";

				// 	if($row['disabled'] == 1){
				// 		$reply = array('reply' => '3');
				// 	}else{
				// 		if(isset($_POST['device_id']))
				// 			register_device_id($_POST['device_id'],  $row['user_id']);

				// 		if($row['private'] == 0){
				// 			$row['private'] = false;
				// 		}else{
				// 			$row['private'] = true;
				// 		}

				// 		if($row['notifications'] == 0){
				// 			$row['notifications'] = false;
				// 		}else{
				// 			$row['notifications'] = true;
				// 		}

				// 		if($row['isProfileSet'] == 0){
				// 			$row['isProfileSet'] = false;
				// 		}else{
				// 			$row['isProfileSet'] = true;
				// 		}
			
				// 		$reply = array(
				// 			'reply' => 1,
				// 			'user_id' => $row['user_id'],
				// 			'firstname' => $row['firstname'],
				// 			'lastname' => $row['lastname'],
				// 			'email' => $row['email'],
				// 			'bday' => $row['bday'],
				// 			'gender' => $row['gender'],
				// 			'username' => $row['username'],
				// 			'reset_code' => $row['reset_code'],
				// 			'reset_check' => $row['reset_check'],
				// 			'info' => $row['personal_information'],
				// 			'fbid' => $row['fbid'],
				// 			'profile_pic' => $row['profile_pic'],
				// 			'cover_photo' => $row['cover_photo'],
				// 			'video_file' => $row['video_file'],
				// 			'country' => $row['country'],
				// 			'private' => $row['private'],
				// 			'disabled' => $row['disabled'],
				// 			'notifications' => $row['notifications'],
				// 			'signup_code' => $row['signup_code'],
				// 			// 'device_id' => $_POST['device_id'],
				// 			'isProfileSet' => $row['isProfileSet']
				// 		);
				// 	}
				// }else{
				// 	$reply = array('reply' => '0');
				// }
			}else{
				$reply = array('reply' => 3, 'message' => 'You may now update your profile', 'user_id' => $row1['user_id']);
			}

			$result = jp_get($search);
			$row = mysqli_fetch_array($result);

			// if(empty($row['profile_pic']))
			// 	$row['profile_pic'] = "";

			// if($row['disabled'] == 1){
			// 	$reply = array('reply' => '3');
			// }else{
			// 	if(isset($_POST['device_id']))
			// 		register_device_id($_POST['device_id'],  $row['user_id']);

			if($row['private'] == 0){
				$row['private'] = false;
			}else{
				$row['private'] = true;
			}

			if($row['notifications'] == 0){
				$row['notifications'] = false;
			}else{
				$row['notifications'] = true;
			}

			if($row['isProfileSet'] == 0){
				$row['isProfileSet'] = false;
			}else{
				$row['isProfileSet'] = true;
			}

			$reply = array(
				// 'reply' => 1,
				// 'user_id' => $row['user_id'],
				// 'fistname' => $row['firstname'],
				// 'lastname' => $row['lastname'],
				// 'username' => $row['username'],
				// 'info' => $row['personal_information'],
				// 'profile_pic' => $row['profile_pic'],
				// 'private' => $row['private'],
				// 'notifications' => $row['notifications']
					'reply' => 1,
					'user_id' => $row['user_id'],
					'firstname' => $row['firstname'],
					'lastname' => $row['lastname'],
					'email' => $row['email'],
					'bday' => $row['bday'],
					'gender' => $row['gender'],
					'username' => $row['username'],
					'reset_code' => $row['reset_code'],
					'reset_check' => $row['reset_check'],
					'info' => $row['personal_information'],
					'fbid' => $row['fbid'],
					'profile_pic' => $row['profile_pic'],
					'cover_photo' => $row['cover_photo'],
					'video_file' => $row['video_file'],
					'country' => $row['country'],
					'private' => $row['private'],
					'disabled' => $row['disabled'],
					'notifications' => $row['notifications'],
					'signup_code' => $row['signup_code'],
					// 'device_id' => $_POST['device_id'],
					'isProfileSet' => $row['isProfileSet']
			);
			
		}else{


			if($_POST['fbid'] != 0){

				$_POST['firstname'] = " ";
				$_POST['lastname'] = " ";
				$_POST['bday'] = date("Y-m-d");
				$_POST['gender'] = 0;
				$_POST['username'] = " ";
				$_POST['password'] = " ";
				$_POST['reset_code'] = NULL;
				$_POST['reset_check'] = 1;
				$_POST['personal_information'] = " ";
				$_POST['profile_pic'] = NULL;
				$_POST['cover_photo'] = NULL;
				$_POST['video_file'] = NULL;
				$_POST['post'] = 0;
				$_POST['country'] = " ";
				$_POST['private'] = 0;
				$_POST['disabled'] = 0;
				$_POST['notifications'] = 1;
				$_POST['total_likes'] = 0;
				$_POST['signup_code'] = NULL;
				$_POST['notifications'] = 1;
				$add['data'] = $_POST;
				$add['table'] = $data['table'];

				// if(jp_add($add)){
				// 	$fbid = $_POST['fbid'];
				// 	$search['select'] = "user_id";
				// 	$search['table'] = $data['table'];
				// 	// $search['where'] = "user_id = '".$fbid."'";
				// 	$search['where'] = "fbid = '".$fbid."'";
				// 	$result = jp_get($search);
				// 	$row = mysqli_fetch_assoc($result);
				// 	$reply = array('reply' => 3, 'message' => 'Thank you. You may now registered using your Facebook account', 'post' => $row['user_id']);

				// 	// $fbid = $_POST['fbid'];
				// 	// $new_id = jp_last_added();
				// 	// $update['table'] = $data['table'];
				// 	// $update['where'] = "user_id = '".$fbid."'";
				// 	// $update['select'] = "user_id, firstname, lastname, personal_information, profile_pic, private, notifications";
				// 	// $result = jp_get($update);
				// 	// $row = mysqli_fetch_array($result);

				// 	// if(empty($row['profile_pic']))
				// 	// 	$row['profile_pic'] = "";

				// 	// if(isset($_POST['device_id']))
				// 	// 	register_device_id($_POST['device_id'],  $row['user_id']);

				// 	// $reply = array(
				// 	// 	'reply' => '2',
				// 	// 	'user_id' => $row['user_id'],
				// 	// 	'fistname' => $row['firstname'],
				// 	// 	'lastname' => $row['lastname'],
				// 	// 	'username' => '',
				// 	// 	'info' => $row['personal_information'],
				// 	// 	'first_fb' => 1,
				// 	// 	'profile_pic' => $row['profile_pic'],
				// 	// 	'private' => $row['private'],
				// 	// 	'notifications' => $row['notifications']
				// 	// );
				// }else{
				// 	$reply = array('reply' => '0', 'error' => 'failed to add', 'post' => $_POST);
				// }
			}else
				$reply = array('reply' => '0', 'error' => 'no fbid');
		}
		echo json_encode($reply);
	}

	

?>