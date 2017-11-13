<?php

	include("jp_library/jp_lib.php");

	if(isset($_POST['email'])){

		$reply = array();

		$search['select'] = "user_id, lastname";
		$search['table'] = "veeds_users";
		$search['where'] = "email = '".$_POST['email']."'";

		if(jp_count($search) > 0){
			$length = rand(3,5);
			$code = "";
			for($i = 1; $i <= $length; $i++)
				$code .= rand(0, 9);

			$_POST['signup_code'] = "SONDER".$code;
			$data['data'] = $_POST;
			$data['table'] = "veeds_users";
			$data['where'] = "email = '".$_POST['email']."'";

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

				// $message = "<html><body>Hi Mr./Ms. ".ucfirst($row['lastname']).", <br> You have requested for a signup code and your code is: ".$_POST['signup_code']."<br />";
				// $message .= "Please note that this signup code is only valid until you change your password. <br />";
				$message = "Hi Mr./Ms. ".ucfirst($row['lastname']).", <br> You have requested for a signup code. Please click this link to activate your account: <br> http://192.168.1.20/veeds/verify.php?email=$email&code=$code";
				$message .= "Thank You!	<br>";
				$message .= "Sonder Team</body></html>";

				// $message2 = "Hi Mr./Ms. ".ucfirst($row['lastname']).", \n You have requested for a signup code and your code is: ".$_POST['signup_code']." \n";
				// $message2 .= "Please note that this signup code is only valid until you change your password. \n";
				$message2 = "Hi Mr./Ms. ".ucfirst($row['lastname']).", \n You have requested for a signup code.Please click this link to activate your account: <br> http://192.168.1.20/veeds/verify.php?email=$email&code=$code";
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
			// 			   'search' => $search
			// 	);
			$reply = array('reply' => '2', 'message' => 'User not exist');
		}
		echo json_encode($reply);
	}




?>