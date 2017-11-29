<?php 
	include("jp_library/jp_lib.php");

	if (isset($_POST['user_id'])) {
		$search['select'] = "user_id_block";
		$search['table'] = "veeds_users_block";
		$search['where'] = "user_id = '".$_POST['user_id']."'";
		$list = array();
		$count = jp_count($search);
		if (jp_count($search) > 0) {
			$result = jp_get($search);
			while ($row = mysqli_fetch_assoc($result)) {
				$search2['select'] = "user_id, username, profile_pic";
				$search2['table'] = "veeds_users";
				$search2['where'] = "user_id = '".$row['user_id_block']."'";
				if (jp_count($search2) > 0) {
					$result1 = jp_get($search2);
					$row1 = mysqli_fetch_array($result1);
					$list[] = $row1;
				}
			}
		}

		echo json_encode($list);
	}

?>