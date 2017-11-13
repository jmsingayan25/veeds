<?php

	include("jp_library/jp_lib.php");

	if(empty($_POST['count']) || !isset($_POST['count'])){
		$_POST['count'] = 0;
	}
	if(isset($_POST['user_id'])){

		$list = array();

		$start = $_POST['count'] * 5;
		$search['select'] = "DISTINCT hashtag";
		$search['table'] = "veeds_hashtag";
		$search['where'] = "user_id = '".$_POST['user_id']."' AND hashtag LIKE '%".$_POST['keyword']."%'";
		$search['filter'] = "LIMIT ".$start.", 5";

		$result = jp_get($search);
		while ($row = mysqli_fetch_assoc($result)) {
			$list['hashtag'][] = $row;
		}
		echo json_encode($list);
	}


?>