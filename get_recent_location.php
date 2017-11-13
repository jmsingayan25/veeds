<?php

	include("jp_library/jp_lib.php");

	if(empty($_POST['count']) || !isset($_POST['count'])){
		$_POST['count'] = 0;
	}
	if(isset($_POST['user_id'])){

		$list = array();

		$start = $_POST['count'] * 10;
		$search['select'] = "DISTINCT location";
		$search['table'] = "veeds_videos";
		$search['where'] = "user_id = '".$_POST['user_id']."'";
		$search['filter'] = "ORDER BY date_upload DESC LIMIT ".$start.", 10";

		$result = jp_get($search);
		while ($row = mysqli_fetch_assoc($result)) {
			if(!empty($row['location'])){
				$list['location'][] = $row;
			}
		}
		echo json_encode($list);
	}


?>