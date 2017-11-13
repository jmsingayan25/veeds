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

			$device1['data'] = array('user_id' => $user_id, 'google_device_id' => $device_id);
			$device1['table'] = "veeds_user_devices";
			jp_add($device1);
		}
	}

	// $_POST['firstname'] = "John Michael";
	// $_POST['lastname'] = "Singayan";
	// $_POST['email'] = "jsingayan@loopbook.com";
	// $_POST['bday'] = date("Y-m-d");
	// $_POST['gender'] = "1";
	// $_POST['username'] = "anticrisis";
	// $_POST['password'] = "123456qwerty";
	// $_POST['country'] = "Philippines";
	// $_POST['register'] = "1";

	$reply = array();

	$data['table'] = "veeds_users";

	if(isset($_POST['username']) && isset($_POST['password']) && isset($_POST['email'])){

		$search['select'] = "user_id";
		$search['table'] = $data['table'];
		$search['where'] = "username = '".$_POST['username']."'";

		$search2['select'] = "user_id";
		$search2['table'] = $data['table'];
		$search2['where'] = "email = '".$_POST['email']."'";

		$search3['select'] = "signup_code";
		$search3['table'] = $data['table'];
		$search3['where'] = "email = '".$_POST['email']."'";

		$signup_codeResult = mysqli_fetch_assoc(jp_get($search3));
		$length = rand(3,5);
		$code = "";
		for($i = 1; $i <= $length; $i++)
			$code .= rand(0, 9);

		//$code = array("signup_code" => "QUIKFLIK".$code);
		$_POST['signup_code'] = "SONDER".$code;
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

		$data['data'] = $_POST;
		$data['table'] = $data['table'];

		if(jp_count($search) > 0){ // check if username already used
			$reply = array('signup' => 'Username already taken', 'code' => 0, 'post' => $_POST);
		}else if(jp_count($search2) > 0) { // check if email exist
			if (!is_null($signup_codeResult)) { // check if code already sent to the email
				$reply = array('signup' => 'Email has already been used to register and has a sign up code sent to this email address', 'code' => 4);
			}else{
				$reply = array('signup' => 'Email already taken', 'code' => 1, 'code' => $signup_codeResult);
			}
		}else if(jp_add($data)){ // if username and email don't exist
			$to = $_POST['email'];

			$subject = "[Sonder] Your Signup Code";

			$mail = new PHPMailer;
			$mail->isSMTP();
			$mail->Host = "email-smtp.us-east-1.amazonaws.com";
			$mail->SMTPAuth = true;
			$mail->Username = "AKIAI6XRKRWODRM4FEWQ";
			$mail->Password = "Agx3F2e2bNtQyO/rAArHSZl5oV5++XLBfiszbdYpnPlX";
			$mail->SMTPSecure = "tls";
			$mail->Port = 587;
			$mail->From = "noreply@loopbookinc.com";
			$mail->FromName = "Sonder";
			$mail->addAddress($to);
			$mail->isHTML(true);

			$message = "<html><body>Hi, <br> you have requested for a signup code and your code is:".$_POST['signup_code']."<br />";
			// $message .= "Please note that this signup code is only valid until you change your password. <br />";
			$message .= "Please use this signup code on your registration. <br />";
			$message .= "Thank You!	<br>";
			$message .= "Sonder Team</body></html>";

			$message2 = "Hi, \n you have requested for a signup code and your code is: ".$_POST['signup_code']." \n";
			// $message2 .= "Please note that this signup code is only valid until you change your password. \n";
			$message2 .= "Please use this signup code on your registration. <br />";
			$message2 .= "Thank You! \n";
			$message2 .= "Sonder Team";

			$mail->Subject = $subject;
			$mail->Body = $message;
			$mail->AltBody = $message2;
			if(!$mail->send()) {
				$reply = array('reply' => '2');
			}else{
				$reply = array('reply' => '1');
				$reply = array('signup' => 'Sign up successful', 'code' => 2, 'signup_code' => $_POST['signup_code']);
			}
		}else{
			$reply = array('signup' => 'Could not complete your signup, please try again!', 'code' => 3, 'post' => $_POST);
		}
		
	}else if(isset($_POST['confirm_signup']) && isset($_POST['email']) && isset($_POST['signup_code'])){

		$search['select'] = "email, user_id";
		$search['table'] = $data['table'];
		$search['where'] = "email = '".$_POST['email']."' AND signup_code = '".$_POST['signup_code']."'";

		if (jp_count($search) > 0) {

			$row = mysqli_fetch_assoc(jp_get($search));

			$code = array("signup_code" => "");
			$data['data'] = $code;
			$data['table'] = $data['table'];
			$data['where'] = $search['where'];

			if (jp_update($data)) {
				$reply = array('reply' => 'success', 'user_id' => $row['user_id']);
			} else {
				$reply = array('reply' => 'failed');
			}
		}else{
			$reply = array('reply' => 'failed');
		}
	}else if(isset($_POST['register']) && isset($_POST['username'])){
		$search['select'] = "firstname, lastname, bday, gender, username, personal_information";
		$search['table'] = "veeds_users";
		$search['where'] = "username = '".$_POST['username']."'";

		$result = jp_get($search);
		$row = mysqli_fetch_assoc($result);

		if($_POST['username'] != $row['username']){
		// if(jp_count($search) == 0){

			$code = array(
				'firstname' => $_POST['firstname'], 
				'lastname' => $_POST['lastname'], 
				'email' => $_POST['email'], 
				'bday' => $_POST['bday'], 
				'gender' => $_POST['gender'], 
				'username' => $_POST['username'], 
				'password' => crypt('st',$_POST['password']),
				'reset_code' => NULL,
				'reset_check' => 1,
				'personal_information' => " ",
				'fbid' => NULL,
				'profile_pic' => NULL,
				'cover_photo' => NULL,
				'video_file' => NULL,
				'post' => 0,
				'country' => $_POST['country'],
				'private' => 0,
				'disabled' => 0,
				'notifications' => 0,
				'signup_code' => NULL,
				'total_Likes' => 0
				);

			$data['data'] = $code;
			$data['table'] = "veeds_users";

			$reply = array('post' => $code);
			// if(jp_add($data)){
			// 	$reply = array('reply' => 'Signup success');
			// }else{
			// 	$reply = array('reply' => 'Signup failed')
			// }
		}else{
			$reply = array('reply' => 'Username already exist');
		}
	}else if(isset($_POST['fbid']) && isset($_POST['fb_register'])){

		$search['select'] = "firstname, lastname, bday, gender, username, personal_information";
		$search['table'] = "veeds_users";
		$search['where'] = "username = '".$_POST['fbid']."'";

		if(jp_count($search) == 0){
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
					$search['table'] = "veeds_users";
					$search['where'] = "fbid = '".$fbid."'";
					$result = jp_get($search);
					$row = mysqli_fetch_assoc($result);

					$reply = array(
								'reply' => 3, 
								'message' => 'Thank you. You may now registered using your Facebook account', 
								'post' => $row['user_id']
							);
				}else{
					$reply = array('reply' => 0, 'message' => 'Failed to add', 'post' => $_POST);
				}
			}else{
				$reply = array('reply' => 0, 'message' => 'No fbid');
			}
		}else{
			if($_POST['register_fb'] == 1){

				$result = jp_get($search);
				$row = mysqli_fetch_assoc($result);

				$_POST['user_id'] = $row['user_id'];
				$_POST['gender'] = (int)$_POST['gender'];
				$data['data'] = $_POST;
				$data['table'] = $data['table'];
				$data['where'] = "user_id = '".$row['user_id']."'";

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
							'signup_code' => $row['signup_code']
						);
					}
				}else{
					$reply = array('reply' => '0');
				}
			}else{
				$reply = array('reply' => 3, 'message' => 'You may now update your profile', 'user_id' => $row1['user_id']);
			}
		}
	}

	echo json_encode($reply);
?>