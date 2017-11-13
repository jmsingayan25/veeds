<?php

	include("jp_library/jp_lib.php");

	if(isset($_POST['update_fbid'])){

		$reply = array();

		$code = array("fbid" => " ");

		$data['data'] = $code;
		$data['table'] = "veeds_user";
		$data['where'] = "user_id = '".$_POST['user_id']."'";

		if(jp_update($data)){
			$reply = array('reply' => 'success');
		}else
			$reply = array('reply' => 'failed');

		json_encode($reply);
	}

?>