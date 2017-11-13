<?php
	include("jp_library/jp_lib.php");

	if(isset($_POST['sender_id']) && isset($_POST['receiver_id'])){

		$list = array();

		$search['select'] = "sender_id, receiver_id, message, date_created";
		$search['table'] = "messaging";
		$search['where'] = "sender_id = '".$_POST['sender_id']."' AND receiver_id = '".$_POST['receiver_id']."'";
		$search['filter'] = "ORDER BY date_created ASC";

		$result = jp_get($search);

		while($row = mysqli_fetch_assoc($result)){
			$list['message'][] = $row;
		}

		echo json_encode($list);
	}
?>