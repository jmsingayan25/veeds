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
			$device['data'] = array('user_id' => $user_id, 'google_device_id' => $device_id);
			$device['table'] = "veeds_user_devices";
			jp_add($device);
		}
	}

	if(isset($_POST['email'])){

		$reply = array();

		$search['select'] = "firstname, lastname, bday, gender, username";
		$search['table'] = "veeds_users";
		$search['where'] = "email = '".$_POST['email']."'";

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
			$data['table'] = "veeds_users";
			$data['where'] = "email = '".$_POST['email']."'";

			if(jp_update($data)){
				$reply = array('reply' => 1, 'message' => 'Update success');
			}else{
				$reply = array('reply' => 0, 'message' => 'Update failed');
			}
		}

		echo json_encode($reply);
	}
?>