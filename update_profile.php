<?php
/*

	Update information of Firstname, Lastname, Birthday, Country, Gender, 
	Profile Picture, Cover Photo, Video File, Private status, Notifications

*/
	include("jp_library/jp_lib.php");

	require("PHPMailerAutoload.php");
	require("class.phpmailer.php");
	require("class.smtp.php");

	$_POST['firstname'] = "Test";
	$_POST['lastname'] = "User200";
	$_POST['email'] = "testuser200@yahoo.com";
	$_POST['bday'] = "1994-10-25";
	$_POST['gender'] = "0";
	$_POST['password'] = "123456qwerty";
	$_POST['country'] = "Philippines";

	if (isset($_POST['email'])) {
		
		$reply = array();

		$search['select'] = "firstname, lastname, bday, gender, username";
		$search['table'] = "veeds_users";
		$search['where'] = "email = '".$_POST['email']."'";

		if(jp_count($search) > 0){

			$result = jp_get($search);
			$row = mysqli_fetch_assoc($result);

			$code = $_POST;

			// if($row['notifications'] == 0){
			// 	$code['notifications'] = 0;
			// }else{
			// 	$code['notifications'] = 1;
			// }

			if (!isset($_POST['bday'])) {
				$code['bday'] = date('Y-m-d');
			}

			$data['data'] = $code;
			$data['table'] = "veeds_users";
			$data['where'] = "email = '".$_POST['email']."'";

			$reply = array('post' => $code);
			// if(jp_update($data)){
			// 	$reply = array('reply' => 'success');
			// }else{
			// 	$reply = array('reply' => 'failed');
			// }
		}

		echo json_encode($reply);
	}


?>