<?php 
	
	// $con = new mysqli("localhost","veeds_user","!O6y8q38zx>~kJL","veeds");

	$con = new mysqli("localhost","root","","veeds2");
	$list = array();
	if (isset($_POST)) {
		$sql_query = "DELETE FROM `veeds_users_block` WHERE `user_id` = '".$_POST['user_id']."' AND `user_id_block` = '".$_POST['user_id_block']."' ";
		
		if (mysqli_query($con, $sql_query)) {
			$select = "SELECT * FROM veeds_users_block WHERE user_id = '".$_POST['user_id']."'";
			if (mysqli_query($con, $select)) {
				$result = mysqli_query($con, $select);
				while ($row = mysqli_fetch_array($result)) {
					$getUser = "SELECT user_id, username FROM veeds_users WHERE user_id = '".$row['user_id_block']."'";
					$query = mysqli_query($con, $getUser);
					$user = mysqli_fetch_array($query);
					$list[] = $user;
				}

			}
			
			//$count = jp_count($search);
			// $list['search'] = $search;
			// if (jp_count($search) > 0) {
			// 	$result = jp_get($search);
			// 	$list['result'] = $result;
			// 	while ($row = mysqli_fetch_assoc($result)) {
			// 		$search2['select'] = "user_id, username";
			// 		$search2['table'] = "veeds_users";
			// 		$search2['where'] = "user_id = '".$row['user_id_block']."'";
			// 		if (jp_count($search2) > 0) {
			// 			$result1 = jp_get($search2);
			// 			$row1 = mysqli_fetch_array($result1);
			// 			$list[] = $row1;
			// 		}
			// 	}
			// }

			echo json_encode($list);
		}
	}
	

?>