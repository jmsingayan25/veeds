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

	// $_POST['email'] = "testuser305@gmail.com";
	// $_POST['password'] = "123456";

	if(isset($_POST['email']) && isset($_POST['device_id'])){

		$reply = array();

		$search['select'] = "user_id, firstname, lastname, email, bday, gender, username, password, reset_code, reset_check, personal_information, fbid, profile_pic, cover_photo, video_file, country, private, disabled, notifications, signup_code, isProfileSet";
		$search['table'] = $data['table'];
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

				if($row['isProfileSet'] == 0){
					$row['isProfileSet'] = false;
				}else{
					$row['isProfileSet'] = true;
				}

				$reply = array(
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
							'device_id' => $_POST['device_id'],
							'isProfileSet' => $row['isProfileSet']
						);
			}else{
				// $reply = array('reply' => 0, 'login' => 'Login failed!', 'password' => crypt('st',$row['password']));
				$reply = array('login' => 'Login failed!');
			}
		}else{
			// $reply = array('reply' => 2, 'login' => 'user dont exist!', 'email' => $_POST['email']);
			$reply = array('login' => 'user dont exist!', 'email' => $_POST['email']);
		}

		echo json_encode($reply);
	}

?>