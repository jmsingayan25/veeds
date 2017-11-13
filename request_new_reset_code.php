<?php
/*

	Request for a new Reset Code

*/
	include("jp_library/jp_lib.php");

	require("PHPMailerAutoload.php");
	require("class.phpmailer.php");
	require("class.smtp.php");

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

			$_POST['reset_code'] = "SONDER".$code;
			$data['data'] = $_POST;
			$data['table'] = "veeds_users";
			$data['where'] = "email = '".$_POST['email']."'";

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

				$message = "<html><body>Hi Mr./Ms. ".ucfirst($row['lastname']).", <br> you have requested for a new reset code and your code is: ".$_POST['reset_code']."<br />";
				$message .= "Please note that this reset code is only valid until you change your password. <br />";
				$message .= "Thank You!	<br>";
				$message .= "Sonder Team</body></html>";

				$message2 = "Hi Mr./Ms. ".ucfirst($row['lastname']).", \n you have requested for a new reset code and your code is: ".$_POST['reset_code']." \n";
				$message2 .= "Please note that this reset code is only valid until you change your password. \n";
				$message2 .= "Thank You! \n";
				$message2 .= "Sonder Team";

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
			// 				'search' => $search
			// 			);
			$reply = array('reply' => '2', 'message' => 'User not exist');
		}

		echo json_encode($reply);
	}
?>