<?php

	include("jp_library/jp_lib.php");

	require("PHPMailerAutoload.php");
	require("class.phpmailer.php");
	require("class.smtp.php");

	
	if(isset($_POST['email']) && isset($_POST['password'])){

		$reply = array();

		// $search['select'] = "user_id";
		// $search['table'] = $data['table'];
		// $search['where'] = "username = '".$_POST['username']."'";

		$search2['select'] = "user_id, lastname";
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
		$_POST['signup_code'] = "SONDER".$code;
		$data['data'] = $_POST;
		// if(jp_count($search) > 0){
		// 	$reply = array('signup' => 'Username already taken', 'code' => 0, 'post' => $_POST);
		// }else if(jp_count($search2) > 0){
		// 	if (!is_null($signup_codeResult)){
		// 		$reply = array('signup' => 'Email has already been used to register and has a sign up code sent to this email address', 'code' => 4);
		// 	}else{
		// 		$reply = array('signup' => 'Email already taken', 'code' => 1, 'code' => $signup_codeResult);
		// 	}

		// }

		if(jp_count($search2) > 0){
			if (!is_null($signup_codeResult)){
				$reply = array('signup' => 'Email has already been used to register and has a sign up code sent to this email address', 'code' => 4);
			}else{
				$reply = array('signup' => 'Email already taken', 'code' => 1, 'code' => $signup_codeResult);
			}
		}else if(jp_add($data)){
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

			$message = "<html><body>Hi Mr./Ms. ".ucfirst($row['lastname']).", <br> You have requested for a signup code and your code is:".$_POST['signup_code']."<br />";
			$message .= "Please note that this signup code is only valid until you change your password. <br />";
			$message .= "Thank You!	<br>";
			$message .= "Sonder Team</body></html>";

			$message2 = "Hi Mr./Ms. ".ucfirst($row['lastname']).", \n you have requested for a signup code and your code is: ".$_POST['signup_code']." \n";
			$message2 .= "Please note that this signup code is only valid until you change your password. \n";
			$message2 .= "Thank You! \n";
			$message2 .= "Sonder Team";

			$mail->Subject = $subject;
			$mail->Body = $message;
			$mail->AltBody = $message2;
			if(!$mail->send()) {
				$reply = array('reply' => '2', 'signup' => 'Error, email not sent');
			}else{
				$reply = array('reply' => '1', 'signup' => 'Sign up successful', 'code' => 2, 'signup_code' => $_POST['signup_code']);
			}
		}else{
			$reply = array('signup' => 'Could not complete your signup, please try again!', 'code' => 3, 'post' => $_POST);
		}
		echo json_encode($reply);
	}
	

?>