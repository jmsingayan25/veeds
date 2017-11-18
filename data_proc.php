<?php

	include("jp_library/jp_lib.php");

	require("PHPMailerAutoload.php");
	require("class.phpmailer.php");
	require("class.smtp.php");


	function register_device_id($device_id, $user_id){
		$device['select'] = "user_id";
		$device['table'] = "veeds_user_devices";
		$device['where'] = "user_id = ".$user_id." AND google_device_id ='".$device_id."'";

		if($GLOBALS['con']->jp_count($device) == 0){
			$device['data'] = array('user_id' => $user_id, 'google_device_id' => $device_id);
			 $GLOBALS['con']->jp_add($device);
		}
	}

	if(isset($_POST)){
		$data['table'] = "veeds_users";

		if(isset($_POST['login'])) {
			$search['select'] = "user_id, firstname, lastname, email, bday, gender, username, password, reset_code, reset_check, personal_information, fbid, profile_pic, cover_photo, video_file, country, private, disabled, notifications, signup_code";
			$search['table'] = $data['table'];
			$search['where'] = "username = '".$_POST['username']."' AND fbid = 0";

			if(jp_count($search) > 0){
				$result = jp_get($search);
				$row = mysqli_fetch_array($result);

				if(empty($row['profile_pic']))
					$row['profile_pic'] = "";

				if($row['disabled'] == 1){
					$reply = array('login' => 'disabled');
				// }elseif(crypt('st',$_POST['password']) == $row['password']){
				}elseif(crypt($_POST['password'],$row['password']) == $row['password']){
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
						'login' => 'Success',
						'user_id' => $row['user_id'],
						'firstname' => $row['firstname'],
						'lastname' => $row['lastname'],
						'email' => $row['email'],
						'bday'	=> $row['bday'],
						'gender' => $row['gender'],
						'username' => $_POST['username'],
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
					$reply = array('login' => 'Login failed!');
				}
			}else{
				$reply = array('login' => 'user dont exist!');
			}


		}elseif(isset($_POST['email_address'])) {
			$search['select'] = "user_id, firstname, lastname, email, bday, gender, username, password, reset_code, reset_check, personal_information, fbid, profile_pic, cover_photo, video_file, country, private, disabled, notifications, signup_code, isProfileSet";
			$search['table'] = $data['table'];
			$search['where'] = "email = '".$_POST['email']."' AND fbid = 0";

			if(jp_count($search) > 0){
				$result = jp_get($search);
				$row = mysqli_fetch_array($result);

				if(empty($row['profile_pic']))
					$row['profile_pic'] = "";

				if($row['disabled'] == 1){
					$reply = array('login' => 'disabled');
				}elseif(crypt('st',$_POST['password']) == $row['password']){
				// }elseif(crypt($_POST['password'],$row['password']) == $row['password']){
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

					// $reply = array(
					// 	'login' => 'Success',
					// 	'user_id' => $row['user_id'],
					// 	'firstname' => $row['firstname'],
					// 	'lastname' => $row['lastname'],
					// 	'username' => $_POST['username'],
					// 	'info' => $row['personal_information'],
					// 	'profile_pic' => $row['profile_pic'],
					// 	'private' => $row['private'],
					// 	'notifications' => $row['notifications'],
					// 	'reset_code' => $row['reset_code'],
					// 	'reset_check' => $row['reset_check'],
					// 	'email' => $row['email'],
					// 	'fb_id' => $row['fbid'],
					// 	'signup_code' => $row['signup_code'],
					// 	'device_id' => $_POST['device_id'],
					// 	'bday'	=> $row['bday'],
					// 	'gender' => $row['gender'],
					// 	'country' => $row['country']
					// );
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
					$reply = array('login' => 'Login failed!');
				}
			}else{
				$reply = array('login' => 'user dont exist!', 'email' => $_POST['email']);
			}

		} elseif(isset($_POST['fb_login'])){

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

					if(jp_update($data)){
						$result = jp_get($search);
						$row = mysqli_fetch_assoc($result);

						if(empty($row['profile_pic']))
							$row['profile_pic'] = "";

						if($row['disabled'] == 1){
							$reply = array('reply' => '3');
						}else{
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
						}
					}else{
						$reply = array('reply' => '0');
					}
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
				// if(isset($_POST['device_id']))
				// 	register_device_id($_POST['device_id'],  $row['user_id']);

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

					if(jp_add($add)){
						$fbid = $_POST['fbid'];
						$search['select'] = "user_id";
						$search['table'] = $data['table'];
						// $search['where'] = "user_id = '".$fbid."'";
						$search['where'] = "fbid = '".$fbid."'";
						$result = jp_get($search);
						$row = mysqli_fetch_assoc($result);
						$reply = array('reply' => 3, 'message' => 'Thank you. You may now registered using your Facebook account', 'post' => $row['user_id']);

						// $fbid = $_POST['fbid'];
						// $new_id = jp_last_added();
						// $update['table'] = $data['table'];
						// $update['where'] = "user_id = '".$fbid."'";
						// $update['select'] = "user_id, firstname, lastname, personal_information, profile_pic, private, notifications";
						// $result = jp_get($update);
						// $row = mysqli_fetch_array($result);

						// if(empty($row['profile_pic']))
						// 	$row['profile_pic'] = "";

						// if(isset($_POST['device_id']))
						// 	register_device_id($_POST['device_id'],  $row['user_id']);

						// $reply = array(
						// 	'reply' => '2',
						// 	'user_id' => $row['user_id'],
						// 	'fistname' => $row['firstname'],
						// 	'lastname' => $row['lastname'],
						// 	'username' => '',
						// 	'info' => $row['personal_information'],
						// 	'first_fb' => 1,
						// 	'profile_pic' => $row['profile_pic'],
						// 	'private' => $row['private'],
						// 	'notifications' => $row['notifications']
						// );
					}else{
						$reply = array('reply' => '0', 'error' => 'failed to add', 'post' => $_POST);
					}
				}else
					$reply = array('reply' => '0', 'error' => 'no fbid');
			}


		}elseif(isset($_POST['forget'])){
			$search2['select'] = "user_id, profile_pic, reset_code";
			$search2['table'] = $data['table'];
			$search2['where'] = "email = '".$_POST['email']."'";

			if(jp_count($search2) > 0){ // check if email is existed

				$result10 = jp_get($search2);
				$row10 = mysqli_fetch_assoc($result10);

				if($row10['reset_code'] == NULL){
					$length = rand(3,5);
					$code = "";
					for($i = 1; $i <= $length; $i++)
						$code .= rand(0, 9);

					$code = array("reset_code" => "QUIKFLIK".$code, "reset_check" => 0);

					$data['data'] = $code;
					$data['table'] = $data['table'];
					$data['where'] = $search2['where'];

					if(jp_update($data)){
						$to = $_POST['email'];

						$subject = "[Quikflik] Your Reset Code";

						$mail = new PHPMailer;
						$mail->isSMTP();
						$mail->Host = "email-smtp.us-east-1.amazonaws.com";
						$mail->SMTPAuth = true;
						$mail->Username = "AKIAI6XRKRWODRM4FEWQ";
						$mail->Password = "Agx3F2e2bNtQyO/rAArHSZl5oV5++XLBfiszbdYpnPlX";
						$mail->SMTPSecure = "tls";
						$mail->Port = 587;
						$mail->From = "noreply@loopbookinc.com";
						$mail->FromName = "Quikflik";
						$mail->addAddress($to);
						$mail->isHTML(true);

						$message = "<html><body>Hi, <br> You have requested for a reset code and your code is: ".$code['reset_code']."<br />";
						$message .= "Please note that this reset code is only valid until you change your password. <br />";
						$message .= "Thank You!	<br>";
						$message .= "Quikflik Team</body></html>";

						$message2 = "Hi, \n you have requested for a reset code and your code is: ".$code['reset_code']." \n";
						$message2 .= "Please note that this reset code is only valid until you change your password. \n";
						$message2 .= "Thank You! \n";
						$message2 .= "Quikflik Team";

						$mail->Subject = $subject;
						$mail->Body = $message;
						$mail->AltBody = $message2;

						if(!$mail->send()) {
							$reply = array('reply' => '2', 'status' => !$mail->send(), 'mail' => $mail);
						}else{
							$reply = array('reply' => '1', 'email' => $_POST['email'], 'profile_pic' => $row10['profile_pic']);
						}
					}
				}else{
					$reply = array('reply' => '5', 'message' => 'reset code exist');
				}
			}else{
				// $reply = array('reply' => '2',
				// 			   'email' => $_POST['email'],
				// 			   'table' => $data['table'],
				// 			   'search' => $search2

				// 	);
				$reply = array('reply' => '2', 'message' => 'User not exist');
			}
		} elseif(isset($_POST['request_new_reset_code'])) {
			$search2['select'] = "user_id";
			$search2['table'] = $data['table'];
			$search2['where'] = "email = '".$_POST['email']."'";

			if(jp_count($search2) > 0){
				$length = rand(3,5);
				$code = "";
				for($i = 1; $i <= $length; $i++)
					$code .= rand(0, 9);

				$_POST['reset_code'] = "QUIKFLIK".$code;
				$data['data'] = $_POST;
				$data['table'] = $data['table'];
				$data['where'] = $search2['where'];

				if(jp_update($data)){
					$to = $_POST['email'];

					$subject = "[Quikflik] Your Reset Code";

					$mail = new PHPMailer;
					$mail->isSMTP();
					$mail->Host = "email-smtp.us-east-1.amazonaws.com";
					$mail->SMTPAuth = true;
					$mail->Username = "AKIAI6XRKRWODRM4FEWQ";
					$mail->Password = "Agx3F2e2bNtQyO/rAArHSZl5oV5++XLBfiszbdYpnPlX";
					$mail->SMTPSecure = "tls";
					$mail->Port = 587;
					$mail->From = "noreply@loopbookinc.com";
					$mail->FromName = "Quikflik";
					$mail->addAddress($to);
					$mail->isHTML(true);

					$message = "<html><body>Hi, <br> You have requested for a new reset code and your code is: ".$_POST['reset_code']."<br />";
					$message .= "Please note that this reset code is only valid until you change your password. <br />";
					$message .= "Thank You!	<br>";
					$message .= "Quikflik Team</body></html>";

					$message2 = "Hi, \n you have requested for a new reset code and your code is: ".$_POST['reset_code']." \n";
					$message2 .= "Please note that this reset code is only valid until you change your password. \n";
					$message2 .= "Thank You! \n";
					$message2 .= "Quikflik Team";

					$mail->Subject = $subject;
					$mail->Body = $message;
					$mail->AltBody = $message2;

					if(!$mail->send()) {
						$reply = array('reply' => '2');
					}else{
						$reply = array('reply' => '1', );
					}
				}
			}else{
				// $reply = array(
				// 				'reply' => '2',
				// 				'email' => $_POST['email'],
				// 				'table' => $data['table'],
				// 				'search' => $search2
				// 			);
				$reply = array('reply' => '2', 'message' => 'User not exist');
			}
		} elseif(isset($_POST['request_new_signup_code'])) {
			$search2['select'] = "user_id";
			$search2['table'] = $data['table'];
			$search2['where'] = "email = '".$_POST['email']."'";

			if(jp_count($search2) > 0){
				$length = rand(3,5);
				$code = "";
				for($i = 1; $i <= $length; $i++)
					$code .= rand(0, 9);

				$_POST['signup_code'] = "QUIKFLIK".$code;
				$data['data'] = $_POST;
				$data['table'] = $data['table'];
				$data['where'] = $search2['where'];

				if(jp_update($data)){
					$to = $_POST['email'];

					$subject = "[Quikflik] Your Signup Code";

					$mail = new PHPMailer;
					$mail->isSMTP();
					$mail->Host = "email-smtp.us-east-1.amazonaws.com";
					$mail->SMTPAuth = true;
					$mail->Username = "AKIAI6XRKRWODRM4FEWQ";
					$mail->Password = "Agx3F2e2bNtQyO/rAArHSZl5oV5++XLBfiszbdYpnPlX";
					$mail->SMTPSecure = "tls";
					$mail->Port = 587;
					$mail->From = "noreply@loopbookinc.com";
					$mail->FromName = "Quikflik";
					$mail->addAddress($to);
					$mail->isHTML(true);

					$message = "<html><body>Hi, <br> You have requested for a signup code and your code is: ".$_POST['signup_code']."<br />";
					$message .= "Please note that this signup code is only valid until you change your password. <br />";
					$message .= "Thank You!	<br>";
					$message .= "Quikflik Team</body></html>";

					$message2 = "Hi, \n you have requested for a signup code and your code is: ".$_POST['signup_code']." \n";
					$message2 .= "Please note that this signup code is only valid until you change your password. \n";
					$message2 .= "Thank You! \n";
					$message2 .= "Quikflik Team";

					$mail->Subject = $subject;
					$mail->Body = $message;
					$mail->AltBody = $message2;

					if(!$mail->send()) {
						$reply = array('reply' => '2');
					}else{
						$reply = array('reply' => '1');
					}
				}
			}else{
				// $reply = array('reply' => '2',
				// 			   'email' => $_POST['email'],
				// 			   'table' => $data['table'],
				// 			   'search' => $search2
				// 	);
				$reply = array('reply' => '2', 'message' => 'User not exist');
			}
		} elseif(isset($_POST['change_pass'])){
			$search2['select'] = "user_id";
			$search2['table'] = $data['table'];
			$search2['where'] = "email = '".$_POST['email']."' AND reset_code = '".$_POST['code']."' AND reset_check = 0";
			// $search2['where'] = "username = '".$_POST['username']."' AND reset_code = '".$_POST['code']."' AND reset_check = 0";

			if(jp_count($search2) > 0){

				$code = array("password" => crypt('st',$_POST['password']), "reset_check" => 1, "reset_code" => "");

				$data['data'] = $code;
				$data['table'] = $data['table'];
				$data['where'] = $search2['where'];

				if(jp_update($data))
					$reply = array('reply' => 'success');
			}else
				$reply = array('reply' => 'failed');
		} elseif (isset($_POST['confirm_signup'])) {
			$search2['select'] = "email, user_id";
			$search2['table'] = $data['table'];
			$search2['where'] = "email = '".$_POST['email']."' AND signup_code = '".$_POST['signup_code']."'";
			$count = jp_count($search2);
			$id = mysqli_fetch_assoc(jp_get($search2));
			if (jp_count($search2) > 0) {
				$code = array("signup_code" => "");
				$data['data'] = $code;
				$data['table'] = $data['table'];
				$data['where'] = $search2['where'];
				// $reply = array();
				if (jp_update($data)) {

					$reply = array('reply' => 'success', 'user_id' => $id['user_id']);
				} else {
					$reply = array('reply' => 'failed');
				}
			}else{
				$reply = array('reply' => 'failed');
			}
		} elseif(isset($_POST['update'])){

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

				if(jp_update($search)){
					$reply = array('reply' => '1', 'post' => $code);
				}else{
					$reply = array('reply' => '2');
				}
			}
		}elseif(isset($_POST['update_profile_pic'])){

			$search['select'] = "user_id";
			$search['table'] = $data['table'];
			$search['where'] = "user_id = '".$_POST['user_id']."'";

			if(jp_count($search) > 0){

				$code = array("profile_pic" => $_POST['update_profile_pic']);

				$data['data'] = $code;
				$data['table'] = $data['table'];
				$data['where'] = $search['where'];

				if(jp_update($data))
					$reply = array('reply' => 'success');
				}else
					$reply = array('reply' => 'failed');

			// $search['table'] = $data['table'];
			// $search['data'] = $_POST;
			// $search['where'] = "user_id = '".$_POST['user_id']."'";

			// if(jp_update($search))
			// 	$reply = array('reply' => '1', 'post' => $_POST);
			// else
			// 	$reply = array('reply' => '2');
		}elseif(isset($_POST['private'])){

			if($_POST['private'] == 0)
				$_POST['private'] = false;
			else
				$_POST['private'] = true;

			$search['data'] = $_POST;
			$search['table'] = $data['table'];
			$search['where'] = "user_id = '".$_POST['user_id']."'";

			if(jp_update($search))
				$reply = array('result' => true, 'private' => $_POST['private']);
			else
				$reply = array('reply' => false);

		}elseif(isset($_POST['notif_toggle'])){
			// $search['select'] = "notifications";
			// $search['table'] = $data['table'];
			// $search['where'] = "user_id = '".$_POST['user_id']."'";

			// $result = jp_get($search);
			// $row = mysqli_fetch_assoc($result);
			// if($row['notifications'] == 0)
			// 	$_POST['notifications'] = false;
			// 	// $_POST['notifications'] = 0;
			// else
			// 	// $_POST['notifications'] = 1;
			// 	$_POST['notifications'] = true;

			if($_POST['notif_toggle'] == 0)
				$_POST['notifications'] = false;
			else
				$_POST['notifications'] = true;

			$search['data'] = $_POST;
			$search['table'] = $data['table'];
			$search['where'] = "user_id = '".$_POST['user_id']."'";

			if(jp_update($search))
				$reply = array('result' => 'true', 'notification' => $_POST['notifications']);
			else
				$reply = array('result' => 'false', 'error' => 'Failed to toggle.');
		}elseif(isset($_POST['username_update'])){
			if (isset($_POST['email'])) {
				$search2['select'] = "username";
				$search2['table'] = "veeds_users";
				$search2['where'] = "username = '".$_POST['username']."'";

				if(jp_count($search2) > 0){
					$reply = array('reply' => '2');
				}else{
					$reply = array('reply' => '1');
				}
			} else {
				$search2['select'] = "user_id";
				$search2['table'] = $data['table'];
				$search2['where'] = "username = '".$_POST['username']."'";

				if(jp_count($search2) > 0){
					$reply = array('reply' => '2');
				}else{

					$search['data'] = $_POST;
					$search['table'] = $data['table'];
					$search['where'] = "email = '".$_POST['email']."'";

					if(jp_update($search))
						$reply = array('reply' => '1', 'username' => $_POST['username']);
					else
						$reply = array('reply' => '0');
				}
			}
		}elseif(isset($_POST['update_pass'])){

			$search['select'] = "password";
			$search['table'] = $data['table'];
			$search['where'] = "user_id = '".$_POST['user_id']."'";

			if(jp_count($search) > 0){
				$result = jp_get($search);
				$row = mysqli_fetch_array($result);

				// if(crypt($_POST['old_password'], $row['password']) == $row['password']){
				if(crypt('st',$_POST['old_password']) == $row['password']){
					$_POST['password'] = crypt('st',$_POST['password']);
					$search['data'] = $_POST;


					if(jp_update($search))
						$reply = array('reply' => 'Password updated');
					else
						$reply = array('reply' => 'Update failed');
				}else{
					$reply = array('reply' => 'Incorrect Password');
				}
			}
		}else{
			// $search['select'] = "user_id";
			// $search['table'] = $data['table'];
			// $search['where'] = "username = '".$_POST['username']."'";

			$search2['select'] = "user_id";
			$search2['table'] = $data['table'];
			$search2['where'] = "email = '".$_POST['email']."'";

			$_POST['password'] = crypt('st',$_POST['password']);

			$_POST['disabled'] = 0;
			$_POST['private'] = 0;
			$_POST['notifications'] = 1;
			$_POST['profile_pic'] = NULL;
			$_POST['cover_photo'] = NULL;
			$_POST['video_file'] = NULL;
			$_POST['post'] = 0;
			$_POST['reset_code'] = NULL;
			$_POST['reset_check'] = 1;
			$_POST['total_likes'] = 0;
			$_POST['fbid'] = " ";
			$_POST['gender'] = 0;
			$_POST['bday'] = date("Y-m-d");

			$search3['select'] = "signup_code";
			$search3['table'] = "veeds_users";
			$search3['where'] = "email = '".$_POST['email']."'";
			$signup_codeResult = mysqli_fetch_assoc(jp_get($search3));
			$length = rand(3,5);
			$code = "";
			for($i = 1; $i <= $length; $i++)
				$code .= rand(0, 9);

			//$code = array("signup_code" => "QUIKFLIK".$code);
			$_POST['signup_code'] = "QUIKFLIK".$code;
			$data['data'] = $_POST;

			// if(jp_count($search) > 0){
			// 	$reply = array('signup' => 'Username already taken', 'code' => 0, 'post' => $_POST);
			// } else if (jp_count($search2) > 0) {
			// 	if (!is_null($signup_codeResult)) {
			// 		$reply = array('signup' => 'Email has already been used to register and has a sign up code sent to this email address', 'code' => 4);
			// 	} else {
			// 		$reply = array('signup' => 'Email already taken', 'code' => 1, 'code' => $signup_codeResult);
			// 	}

			// }
			if (jp_count($search2) > 0) {
				if(!is_null($signup_codeResult)){
					$reply = array('signup' => 'Email has already been used to register and has a sign up code sent to this email address', 'code' => 4);
				}else{
					$reply = array('signup' => 'Email already taken', 'code' => 1, 'code' => $signup_codeResult);
				}
			}else if(jp_add($data)){
					$to = $_POST['email'];

					$subject = "[Quikflik] Your Signup Code";

					$mail = new PHPMailer;
					$mail->isSMTP();
					$mail->Host = "email-smtp.us-east-1.amazonaws.com";
					$mail->SMTPAuth = true;
					$mail->Username = "AKIAI6XRKRWODRM4FEWQ";
					$mail->Password = "Agx3F2e2bNtQyO/rAArHSZl5oV5++XLBfiszbdYpnPlX";
					$mail->SMTPSecure = "tls";
					$mail->Port = 587;
					$mail->From = "noreply@loopbookinc.com";
					$mail->FromName = "Quikflik";
					$mail->addAddress($to);
					$mail->isHTML(true);

					$message = "<html><body>Hi, <br> You have requested for a reset code and your code is:".$_POST['signup_code']."<br />";
					$message .= "Please note that this reset code is only valid until you change your password. <br />";
					$message .= "Thank You!	<br>";
					$message .= "Quikflik Team</body></html>";

					$message2 = "Hi, \n you have requested for a reset code and your code is: ".$_POST['signup_code']." \n";
					$message2 .= "Please note that this reset code is only valid until you change your password. \n";
					$message2 .= "Thank You! \n";
					$message2 .= "Quikflik Team";

					$mail->Subject = $subject;
					$mail->Body = $message;
					$mail->AltBody = $message2;
					if(!$mail->send()) {
						$reply = array('reply' => '2', 'message' => 'email not sent');
					}else{
						$reply = array('reply' => '1');
						$reply = array('signup' => 'Sign up successful', 'code' => 2, 'signup_code' => $_POST['signup_code']);
					}

			}else{
				$reply = array('signup' => 'Could not complete your signup, please try again!', 'code' => 3, 'post' => $_POST);
			}
		}
		echo json_encode($reply);
	}
?>
