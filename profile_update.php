<?php

	include("jp_library/jp_lib.php");

	require("PHPMailerAutoload.php");
	require("class.phpmailer.php");
	require("class.smtp.php");

	$data['table'] = "veeds_users";

	if(isset($_POST['email'])){

		$reply = array();

		$search['select'] = "firstname, lastname, bday, gender, username, personal_information";
		$search['table'] = $data['table'];
		$search['where'] = "email = '".$_POST['email']."' AND fbid = 0";

		if(jp_count($search) > 0){
			// $result = jp_get($search);
			// $row = mysqli_fetch_assoc($result);

			// $code = array(
			// 			'firstname' => $_POST['firstname'], 
			// 			'lastname' => $_POST['lastname'], 
			// 			'bday' => $_POST['bday'], 
			// 			'username' => $_POST['username'], 
			// 			'personal_information' => $_POST['personal_information']
			// 		);

			// $data['data'] = $code;
			$data['data'] = $_POST;
			$data['table'] = $data['table'];
			$data['where'] = "email = '".$_POST['email']."'";

			if(jp_update($data)){
				$reply = array('reply' => 1, 'message' => 'Profile updated');
			}else{
				$reply = array('reply' => 0, 'message' => 'Update failed');
			}
		}
	}else if(isset($_POST['email']) && isset($_POST['code']) && isset($_POST['password'])){

		$search['select'] = "user_id";
		$search['table'] = $data['table'];
		$search['where'] = "email = '".$_POST['email']."' AND reset_code = '".$_POST['code']."' AND reset_check = 0";
		// $search2['where'] = "username = '".$_POST['username']."' AND reset_code = '".$_POST['code']."' AND reset_check = 0";

		if(jp_count($search) > 0){

			$code = array("password" => crypt('st',$_POST['password']), "reset_check" => 1, "reset_code" => "");

			$data['data'] = $code;
			$data['table'] = $data['table'];
			$data['where'] = $search2['where'];

			if(jp_update($data)){
				$reply = array('reply' => 1, 'message' => 'success');
			}else{
				$reply = array('reply' => 0,'message' => 'failed');
			}
		}
	}else if(isset($_POST['private']) && isset($_POST['user_id'])){
		$search['data'] = $_POST;
		$search['table'] = $data['table'];
		$search['where'] = "user_id = '".$_POST['user_id']."'";

		if(jp_update($search)){
			$reply = array('reply' => true);
		}else{
			$reply = array('reply' => false);
		}
	}else if(isset($_POST['notif_toggle']) && isset($_POST['user_id'])){
		$search['select'] = "notifications";
		$search['table'] = $data['table'];
		$search['where'] = "user_id = '".$_POST['user_id']."'";

		$result = jp_get($search);
		$row = mysqli_fetch_assoc($result);
		if($row['notifications'] == 0)
			$_POST['notifications'] = 1;
		else
			$_POST['notifications'] = 0;

		$search['data'] = $_POST;

		if(jp_update($search)){
			$reply = array('result' => true, 'notification' => $_POST['notifications']);
		}else{
			$reply = array('result' => false, 'error' => 'Failed to toggle.');
		}
	}else if(isset($_POST['update_profile_pic']) && isset($_POST['user_id'])){

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

	}else if(isset($_POST['forget']) && isset($_POST['email'])){

		$search2['select'] = "user_id, profile_pic";
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

				$code = array("reset_code" => "SONDER".$code, "reset_check" => 0);

				$data['data'] = $code;
				$data['table'] = $data['table'];
				$data['where'] = $search2['where'];

				if(jp_update($data)){
					$to = $_POST['email'];

					$subject = "[Sonder] Your Reset Code";

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

					$message = "<html><body>Hi, <br> You have requested for a reset code and your code is: ".$code['reset_code']."<br />";
					$message .= "Please note that this reset code is only valid until you change your password. <br />";
					$message .= "Thank You!	<br>";
					$message .= "Sonder Team</body></html>";

					$message2 = "Hi, \n you have requested for a reset code and your code is: ".$code['reset_code']." \n";
					$message2 .= "Please note that this reset code is only valid until you change your password. \n";
					$message2 .= "Thank You! \n";
					$message2 .= "Sonder Team";

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
			$reply = array('reply' => '2', 'message' => 'Account not exist');
		}
	}else if(isset($_POST['request_new_reset_code']) && isset($_POST['email'])) {
		$search2['select'] = "user_id";
		$search2['table'] = $data['table'];
		$search2['where'] = "email = '".$_POST['email']."'";

		if(jp_count($search2) > 0){
			$length = rand(3,5);
			$code = "";
			for($i = 1; $i <= $length; $i++)
				$code .= rand(0, 9);

			$_POST['reset_code'] = "SONDER".$code;
			$data['data'] = $_POST;
			$data['table'] = $data['table'];
			$data['where'] = $search2['where'];

			if(jp_update($data)){
				$to = $_POST['email'];

				$subject = "[Sonder] Your Reset Code";

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

				$message = "<html><body>Hi, <br> You have requested for a new reset code and your code is: ".$_POST['reset_code']."<br />";
				$message .= "Please note that this reset code is only valid until you change your password. <br />";
				$message .= "Thank You!	<br>";
				$message .= "Sonder Team</body></html>";

				$message2 = "Hi, \n you have requested for a new reset code and your code is: ".$_POST['reset_code']." \n";
				$message2 .= "Please note that this reset code is only valid until you change your password. \n";
				$message2 .= "Thank You! \n";
				$message2 .= "Sonder Team";

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
			$reply = array('reply' => '2', 'message' => 'Account not exist');
		}
	}else if(isset($_POST['request_new_signup_code']) && isset($_POST['email'])){
		$search2['select'] = "user_id";
		$search2['table'] = $data['table'];
		$search2['where'] = "email = '".$_POST['email']."'";

		if(jp_count($search2) > 0){
			$length = rand(3,5);
			$code = "";
			for($i = 1; $i <= $length; $i++)
				$code .= rand(0, 9);

			$_POST['signup_code'] = "SONDER".$code;
			$data['data'] = $_POST;
			$data['table'] = $data['table'];
			$data['where'] = $search2['where'];

			if(jp_update($data)){
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

				$message = "<html><body>Hi, <br> You have requested for a new signup code and your code is: ".$_POST['signup_code']."<br />";
				// $message .= "Please note that this reset code is only valid until you change your password. <br />";
				$message .= "Thank You!	<br>";
				$message .= "Sonder Team</body></html>";

				$message2 = "Hi, \n You have requested for a new signup code and your code is: ".$_POST['signup_code']." \n";
				// $message2 .= "Please note that this reset code is only valid until you change your password. \n";
				$message2 .= "Thank You! \n";
				$message2 .= "Sonder Team";

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
	}
	echo json_encode($reply);

?>