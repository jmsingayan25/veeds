<?php 
	include("jp_library/jp_lib.php");
	$reply = array();
	// $con = new mysqli("localhost","veeds_user","!O6y8q38zx>~kJL","veeds");
	if (isset($_POST)) {
		if (isset($_POST['notif_id'])) {
			$search['select'] = "notif_id";
			$search['table'] = "notifications";
			$search['where'] = "notif_id = '".$_POST['notif_id']."'";
			
			if (jp_count($search) > 0) {
				jp_delete($search);
				$reply['notif'] = true;
			} else {
				$reply['notif'] = false;
			}
		}
		if (isset($_POST['type'])) {
			$search['select'] = "type, notif_id, user_id, activity_user, notif_datetime";
			$search['table'] = "notifications";
			$search['where'] = "type = '".$_POST['type']."' AND user_id = '".$_POST['logged_id']."' AND activity_user = '".$_POST['user_id']."'";
			
			
			if (jp_count($search) > 0) {
				jp_delete($search);
				$reply['type'] = true;
			} else {
				$reply['type'] = false;
			}
		}
		if (isset($_POST['user_id'])) {
			// $stat['approved'] = 0;
			// $search3['data'] = $stat;
			$search3['table'] = "veeds_users_follow";
			$search3['where'] = "user_id_follow = ".$_POST['logged_id']." AND user_id = ".$_POST['user_id']."";
			// $sql = "SELECT * FROM veeds_users_follow WHERE approved = 0 AND user_id = ".$_POST['user_id']." AND user_id_follow = ".$_POST['logged_id']."";
		
			
			//  $result = mysqli_query($con,$sql);
			// if (!$result) {
			//     echo 'Could not run query: ' . mysql_error();
			//     exit;
			// }
			// while($row = mysqli_fetch_assoc($result)) {
			// 	$reply[] = $row;
			// }
			// $reply['follow'] = array('deleted' => true, 'search' => $sql, 'res' => $result);
			if (jp_count($search3) > 0) {
				$result = jp_get($search3);
				while($row = mysqli_fetch_array($result)) {
					$reply['row'] = $row;
				}
				jp_delete($search3);
				$reply['follow'] = array('deleted' => true, 'search' => $search3);
			} else {
				
				$reply['follow'] = array('deleted' => false, 'search' => $search3);
			}
		}
		// $list = array();
		// $result = mysqli_query($con, "SHOW COLUMNS FROM veeds_users_follow");
		// if (!$result) {
		//     echo 'Could not run query: ' . mysql_error();
		//     exit;
		// }
		// while( $row = mysqli_fetch_assoc($result)) {
		// 	$list[] = $row;
		// }
		// echo json_encode($list);
	} else {
		$reply = array('deleted' => 'user not existing!');
	}
	echo json_encode($reply);
?>