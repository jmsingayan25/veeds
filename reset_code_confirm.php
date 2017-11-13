<?php

/*

	Confirm reset code and password

*/

	include("jp_library/jp_lib.php");

	if(isset($_POST['email']) && isset($_POST['code']) && isset($_POST['password'])){

		$reply = array();

		$search['select'] = "user_id";
		$search['table'] = "veeds_users";
		$search['where'] = "email = '".$_POST['email']."' AND reset_code = '".$_POST['code']."' AND reset_check = 0";
		// $search2['where'] = "username = '".$_POST['username']."' AND reset_code = '".$_POST['code']."' AND reset_check = 0";

		if(jp_count($search) > 0){

			$code = array("password" => crypt('st',$_POST['password']), "reset_check" => 1, "reset_code" => "");

			$data['data'] = $code;
			$data['table'] = "veeds_users";
			$data['where'] = "email = '".$_POST['email']."' AND reset_code = '".$_POST['code']."' AND reset_check = 0";

			if(jp_update($data)){
				$reply = array('reply' => 'success');
			}else{
				$reply = array('reply' => 'failed');
			}
		}
		echo json_encode($reply);
	}

	

?>