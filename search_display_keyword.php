<?php
/*

	Display keyword based on what is user typed

*/
	include("jp_library/jp_lib.php");

	if(empty($_POST['count']) || !isset($_POST['count'])){
		$_POST['count'] = 0;
	}

	// $_POST['user_id'] = "271";
	// $_POST['keyword'] = "allen";
	if(isset($_POST['user_id']) && isset($_POST['keyword'])){

		$search['select'] = "keyword";
		$search['table'] = "veeds_users_history";
		$search['where'] = "user_id = '".$_POST['user_id']."' AND keyword LIKE '%".$_POST['keyword']."%'";
		$search['filters'] = "GROUP BY keyword";
		// echo implode(" ", $search);
		$result = jp_get($search);
		while ($row = mysqli_fetch_assoc($result)) {
			$list['keyword'][] = $row;
		}
	}


?>