<?php

	include("jp_library/jp_lib.php");

	if(isset($_POST['email']) && isset($_POST['signup_code'])){

		$search['select'] = "email, user_id";
		$search['table'] = "veeds_users";
		$search['where'] = "email = '".$_POST['email']."' AND signup_code = '".$_POST['signup_code']."'";

		if (jp_count($search) > 0) {

			$row = mysqli_fetch_assoc(jp_get($search));

			$code = array("signup_code" => "");
			$data['data'] = $code;
			$data['table'] = "veeds_users";
			$data['where'] = "email = '".$_POST['email']."' AND signup_code = '".$_POST['signup_code']."'";

			if (jp_update($data)) {
				$reply = array('reply' => 'success', 'user_id' => $id['user_id']);
			} else {
				$reply = array('reply' => 'failed');
			}
		}else{
			$reply = array('reply' => 'failed');
		}
	}

?>