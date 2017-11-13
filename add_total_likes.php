<?php 
	include("jp_library/jp_lib.php");

	if (isset($_POST['user_id'])) {


		$search['select'] = "user_id, user_total_likes";
		$search['table'] = "veeds_users_likes";
		$search['where'] = "user_id = '".$_POST['user_id']"'";

		if (jp_count($search) > 0) {
			$result = jp_get($search);
			$row = mysql_fetch_array($result);

			$reply = array('user_id' => $row['user_id'],
						   'total_likes' => $row['user_total_likes']);
		} else {
			$reply = array('reply' = 'no data fetched!');
		}

	}
	echo json_encode($reply);

?>