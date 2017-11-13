<?php
	include("jp_library/jp_lib.php");

	if(isset($_POST['sender_id']) && isset($_POST['receiver_id']) && isset($_POST['message'])){

		$result = array();
		$date = date('Y-m-d H:i:s');

		$data['data'] = array('sender_id' => $_POST['sender_id'], 'receiver_id' => $_POST['receiver_id'], 'message' => $_POST['message'], 'date_created' => $date);
		$data['table'] = "messaging";
		
		if(jp_add($data)){
			$result = array('reply' => '1', 'sender_id' => $_POST['sender_id'], 'receiver_id' => $_POST['receiver_id'], 'message' => $_POST['message'], 'date_created' => $date);
		}else{
			$result = array('reply' => '0');
		}
		echo json_encode($result);
	}else{
		$result = array('post' => $_POST);
		echo json_encode($result);
	}
?>