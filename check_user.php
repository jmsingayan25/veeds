<?php

	include("jp_library/jp_lib.php");

	if(isset($_POST['user_id']) && isset($_POST['fbid'])){

		$reply = array();

		$search['select'] = "user_id";
		$search['table'] = "veeds_users";
		$search['where'] = "fbid = '".$_POST['fbid']."'";

		if(jp_count($search) > 0){
			$reply = array('reply' => true);
		}else{
			$reply = array('reply' => false);
		}
	}else if(isset($_POST['update_fbid'])){

		$fbid = $_POST['update_fbid'];

		$reply = array();

		$code = array('fbid' => $fbid);

		$data['data'] = $code;
		$data['table'] = "veeds_users";
		$data['where'] = "user_id = '".$_POST['user_id']."'";

		if(jp_update($data)){
			$reply = array('reply' => 1);
		}else
			$reply = array('reply' => 0);

	}else{
		$reply = array('reply' => 3);
	}
	
		echo json_encode($reply);

?>