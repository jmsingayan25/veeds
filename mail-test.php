<?php

//require_once "vendor/autoload.php";
require("PHPMailerAutoload.php");
		require("class.phpmailer.php");
		require("class.smtp.php");
		
$mail = new PHPMailer;

//Enable SMTP debugging. 
//$mail->SMTPDebug = 3;                               
//Set PHPMailer to use SMTP.
$mail->isSMTP();            
//Set SMTP host name                          
$mail->Host = "email-smtp.us-east-1.amazonaws.com";
//Set this to true if SMTP host requires authentication to send email
$mail->SMTPAuth = true;                          
//Provide username and password     
$mail->Username = "AKIAI6XRKRWODRM4FEWQ";                 
$mail->Password = "Agx3F2e2bNtQyO/rAArHSZl5oV5++XLBfiszbdYpnPlX";                           
//If SMTP requires TLS encryption then set it
$mail->SMTPSecure = "tls";                           
//Set TCP port to connect to 
$mail->Port = 587;                                   

$mail->From = "april@myoptimind.com";
$mail->FromName = "Quikflik";

$mail->addAddress("murakaminight813@gmail.com");

$mail->isHTML(true);

$message = "<html><body>Hi, you have requested for a reset code and your code is: QUIKFLIK-2731298313<br />";
$message .= "Please note that this reset code is only valid until you change your password. <br />";
$message .= "Thank You!	</body></html>";

$message2 = "Hi, you have requested for a reset code and your code is: QUIKFLIK-2731298313 \n";
$message2 .= "Please note that this reset code is only valid until you change your password. \n";
$message2 .= "Thank You!";

$mail->Subject = "Subject Text";
$mail->Body = $message;
$mail->AltBody = $message2;

if(!$mail->send()) 
{
    echo "Mailer Error: " . $mail->ErrorInfo;
} 
else 
{
    echo "Message has been sent successfully";
}
?>