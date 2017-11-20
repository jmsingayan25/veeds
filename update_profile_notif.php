<?php

	include("jp_library/jp_lib.php");

	$_POST['user_id'] = "182";
	$_POST['notifications'] = "1";

	if(isset($_POST['user_id'])){

		$reply = array();

		$search['select'] = "notifications";
		$search['table'] = "veeds_users";
		$search['where'] = "user_id = '".$_POST['user_id']."'";

		$result = jp_get($search);
		$row = mysqli_fetch_assoc($result);
		if($row['notifications'] == 0)
			$_POST['notifications'] = false;
		else
			$_POST['notifications'] = true;

		$data['data'] = $_POST;
		// $data['table'] = "veeds_users";
		// $data['where'] = "user_id = '".$_POST['user_id']."'";

		$reply = array('data' => $data);
		// if(jp_update($data)){
		// 	$reply = array('reply' => 1, 'notification' => $_POST['notifications']);
		// }else{
		// 	$reply = array('reply' => 0, 'error' => 'Failed to toggle.');
		// }

		echo json_encode($reply);
	}

?>