<?php

	include("jp_library/jp_lib.php");

	$_POST['username'] = "allenpogi";
	$_POST['password'] = "123456";
	if(isset($_POST)){

		$reply = array();
		$data['table'] = "veeds_users";

		// if(isset($_POST['login'])) {
			$search['select'] = "user_id, firstname, lastname, password, personal_information, profile_pic, country, bday, gender, private, disabled, notifications, reset_code, reset_check, email, fbid, signup_code";
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

					$reply = array(
						'login' => 'Success',
						'user_id' => $row['user_id'],
						'fistname' => $row['firstname'],
						'lastname' => $row['lastname'],
						'username' => $_POST['username'],
						'info' => $row['personal_information'],
						'profile_pic' => $row['profile_pic'],
						'private' => $row['private'],
						'notifications' => $row['notifications'],
						'reset_code' => $row['reset_code'],
						'reset_check' => $row['reset_check'],
						'email' => $row['email'],
						'fb_id' => $row['fbid'],
						'signup_code' => $row['signup_code'],
						'device_id' => $_POST['device_id'],
						'bday'	=> $row['bday'],
						'gender' => $row['gender'],
						'country' => $row['country']

					);
				}else{
					$reply = array('login' => 'Login failed!', 'password' => crypt('st',$_POST['password']));
				}
			}else{
				$reply = array('login' => 'user dont exist!');
			}


		// }
		echo json_encode($reply);
	}
	?>