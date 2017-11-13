<?php

	// Displays searched keyword associated to the user id sorted by date

	include("jp_library/jp_lib.php");

	if(empty($_POST['count']) || !isset($_POST['count'])){
		$_POST['count'] = 0;
	}
	$_POST['user_id'] = "271";
	if(isset($_POST['user_id'])){

		$list = array();

		$search['select'] = "keyword";
		$search['table'] = "veeds_users_history";
		$search['where'] = "user_id = '".$_POST['user_id']."' 
							AND search_date IN (SELECT MAX(search_date)
												FROM veeds_users_history
												WHERE user_id = '".$_POST['user_id']."'
												GROUP BY search_date)
							GROUP BY keyword";
		// echo implode(" ", $search);
		$result = jp_get($search);
		while ($row = mysqli_fetch_assoc($result)) {
			$list['keyword'][] = $row;
		}

		echo json_encode($list);
	}

?>