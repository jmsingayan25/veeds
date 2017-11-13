<?php
	include("jp_library/jp_lib.php");
	
	
	require("PHPMailerAutoload.php");
	require("class.phpmailer.php");
	require("class.smtp.php");
	
	if(isset($_POST['user_id'])){
		
		
		$user['select'] = "firstname, lastname, username";
		$user['table'] = "veeds_users";
		$user['where'] = "user_id = '".$_POST['user_id']."'";
		$result = jp_get($user);
		$row = mysqli_fetch_assoc($result);
						
		$to = "contactus@loopbookinc.com";
		
		$subject = "[Veeds] User Reported";
		
		$mail = new PHPMailer;
		$mail->isSMTP();                                      
		$mail->Host = "email-smtp.us-east-1.amazonaws.com";
		$mail->SMTPAuth = true;                          
		$mail->Username = "AKIAI6XRKRWODRM4FEWQ";                 
		$mail->Password = "Agx3F2e2bNtQyO/rAArHSZl5oV5++XLBfiszbdYpnPlX";                           
		$mail->SMTPSecure = "tls";                           
		$mail->Port = 587;                                   
		$mail->From = "contactus@loopbookinc.com";
		$mail->FromName = "Quikflik";
		$mail->addAddress($to);
		$mail->isHTML(true);
	
		$message = "<html><body>Hi, <br />";
		$message .= "For your immediate action, please review ".$row['firstname']." ".$row['lastname']." (".$row['username'].")'s profile as the account has been reported for possible inappropriate live photo(s) content. <br />";
		$message .= "In the account that action is necessary, please click on the link below to disable the user's account: <br />";
		$message .= "<a href='http://ec2-52-40-31-134.us-west-2.compute.amazonaws.com/veeds/ban_user.php?uid=".$_POST['user_id']."'>http://ec2-52-40-31-134.us-west-2.compute.amazonaws.com/veeds/ban_user.php?uid=".$_POST['user_id']."</a> <br />";
		$message .= "Thank You!	<br>";
		$message .= "Quikflik Team</body></html>";

		$message2 = "Hi, \n";
		$message2 .= "For your immediate action, please review ".$row['firstname']." ".$row['lastname']."(".$row['username'].")'s profile as the account has been reported for possible inappropriate video(s) content. \n";
		$message2 .= "In the account that action is necessary, please visit the link below to disable the user's account: \n";
		$message2 .= "http://ec2-52-40-31-134.us-west-2.compute.amazonaws.com/veeds/ban_user.php?uid=".$_POST['user_id']." \n";
		$message2 .= "Thank You!	\n";
		$message2 .= "Quikflik Team";
	
		$mail->Subject = $subject;
		$mail->Body = $message;
		$mail->AltBody = $message2;

		if(!$mail->send()) {
			$reply = array('reply' => 'false', 'mail' => $mail);
		}else{
			$reply = array('reply' => 'true', 'mail' => $mail);
		}
			
		echo json_encode($reply);
	}
?>