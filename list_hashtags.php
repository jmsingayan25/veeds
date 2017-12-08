<?php

	include("jp_library/jp_lib.php");

	if(empty($_POST['count']) || !isset($_POST['count'])){
		$_POST['count'] = 0;
	}

	$list = array();

	$search['select'] = "DISTINCT hashtag";
	$search['table'] = "veeds_hashtag";

	if(jp_count($search) > 0){
		$result = jp_get($search);
		while ($row = mysqli_fetch_assoc($result)) {
			$list['hashtag'][] = $row;
		}
	}

	echo json_encode($list);

?>