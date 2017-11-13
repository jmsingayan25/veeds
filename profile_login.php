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

	// $_POST['email_address'] = "1";
	// $_POST['email'] = "testuser305@gmail.com";
	// $_POST['password'] = "123456";
	$_POST['fbid'] = "1247016138673483";
	if(isset($_POST)){

		$reply = array();

		if(isset($_POST['email']) && isset($_POST['device_id'])){
			$search['select'] = "user_id, firstname, lastname, email, bday, gender, username, password, reset_code, reset_check, personal_information, fbid, profile_pic, cover_photo, video_file, country, private, disabled, notifications, signup_code";
			$search['table'] = "veeds_users";
			$search['where'] = "email = '".$_POST['email']."'";

			if(jp_count($search) > 0){
				$result = jp_get($search);
				$row = mysqli_fetch_assoc($result);

				if(empty($row['profile_pic']))
					$row['profile_pic'] = "";

				if($row['disabled'] == 1){
					$reply = array('login' => 'disabled');
				}else if(crypt('st',$row['password']) == $row['password']){
				// }else if(crypt($_POST['password'],$row['password']) == $row['password']){
					if(isset($_POST['device_id']))
						register_device_id($_POST['device_id'],  $row['user_id']);

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

					$reply = array(
								'reply' => 1,
								'login' => 'Success',
								'user_id' => $row['user_id'],
								'firstname' => $row['firstname'],
								'lastname' => $row['lastname'],
								'email' => $row['email'],
								'bday'	=> $row['bday'],
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
								'device_id' => $_POST['device_id']
							);
				}else{
					$reply = array('reply' => 0, 'login' => 'Login failed!', 'password' => crypt('st',$row['password']));
				}
			}else{
				$reply = array('reply' => 2, 'login' => 'user dont exist!', 'email' => $_POST['email']);
			}

		}else if(isset($_POST['fbid']) && isset($_POST['device_id'])){

			$search['select'] = "user_id, firstname, lastname, email, bday, gender, username, password, reset_code, reset_check, personal_information, fbid, profile_pic, cover_photo, video_file, country, private, disabled, notifications, signup_code";
			$search['table'] = "veeds_users";
			$search['where'] = "fbid = '".$_POST['fbid']."'";

			$result = jp_get($search);
			$row = mysqli_fetch_assoc($result);

			if(jp_count($search) > 0){
				
				if(empty($row['profile_pic'])){
					$row['profile_pic'] = "";
				}

				if($row['disabled'] == 1){
					$reply = array('reply' => 3);
				}else{
					if(isset($_POST['device_id'])){
						register_device_id($_POST['device_id'],  $row['user_id']);
					}

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

					$reply = array(
								'reply' => 1,
								'login' => 'Success',
								'user_id' => $row['user_id'],
								'firstname' => $row['firstname'],
								'lastname' => $row['lastname'],
								'email' => $row['email'],
								'bday'	=> $row['bday'],
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
								'device_id' => $_POST['device_id']
							);
				}
			}else{
				$reply = array('reply' => 0);

				// if($_POST['fbid'] != 0){

				// 	$_POST['firstname'] = " ";
				// 	$_POST['lastname'] = " ";
				// 	$_POST['bday'] = date("Y-m-d");
				// 	$_POST['gender'] = 0;
				// 	$_POST['username'] = " ";
				// 	$_POST['password'] = " ";
				// 	$_POST['reset_code'] = NULL;
				// 	$_POST['reset_check'] = 1;
				// 	$_POST['personal_information'] = " ";
				// 	$_POST['profile_pic'] = NULL;
				// 	$_POST['cover_photo'] = NULL;
				// 	$_POST['video_file'] = NULL;
				// 	$_POST['post'] = 0;
				// 	$_POST['country'] = " ";
				// 	$_POST['private'] = 0;
				// 	$_POST['disabled'] = 0;
				// 	$_POST['notifications'] = 1;
				// 	$_POST['total_likes'] = 0;
				// 	$_POST['signup_code'] = NULL;
				// 	$_POST['notifications'] = 1;
				// 	$add['data'] = $_POST;
				// 	$add['table'] = $data['table'];

				// 	if(jp_add($add)){
				// 		$fbid = $_POST['fbid'];
				// 		$search['select'] = "user_id";
				// 		$search['table'] = "veeds_users";
				// 		$search['where'] = "fbid = '".$fbid."'";
				// 		$result = jp_get($search);
				// 		$row = mysqli_fetch_assoc($result);

				// 		$reply = array(
				// 					'reply' => 3, 
				// 					'message' => 'Thank you. You may now registered using your Facebook account', 
				// 					'post' => $row['user_id']
				// 				);
				// 	}else{
				// 		$reply = array('reply' => 0, 'message' => 'Failed to add', 'post' => $_POST);
				// 	}
				// }else{
				// 	$reply = array('reply' => 0, 'message' => 'No fbid');
				// }
			}
		}

		echo json_encode($reply);
	}
?>