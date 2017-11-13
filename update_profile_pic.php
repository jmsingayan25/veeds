<?php

	include("jp_library/jp_lib.php");
	
	if(isset($_POST['user_id']) && isset($_POST['profile_pic'])){

		$reply = array();

		$search['select'] = "user_id";
		$search['table'] = "veeds_users";
		$search['where'] = "user_id = '".$_POST['user_id']."'";

		if(jp_count($search) > 0){

			$code = array("profile_pic" => $_POST['profile_pic']);

			$data['data'] = $code;
			$data['table'] = "veeds_users";
			$data['where'] = "user_id = '".$_POST['user_id']."'";

			if(jp_update($data)){
				$reply = array('reply' => 1, 'message' => 'Update success');
			}else{
				$reply = array('reply' => 0, 'message' => 'Update failed');
			}
		}
		echo json_encode($reply);
	}
?>