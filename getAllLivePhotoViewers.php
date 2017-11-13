<?php
	include("jp_library/jp_lib.php");

	if (isset($_POST)) {
		$search['select'] = "user_id";
		$search['table'] = "veeds_live_photo_viewer";
		$search['where'] = "video_id = '".$_POST['video_id']."'";
		if (jp_count($search) > 0) {
			$result = jp_get($search);
			$list = array();
			$list['users'] = array();
			while ($row = mysqli_fetch_assoc($result)) {
				if ($_POST['user_id'] != $row['user_id']) {
					$search2['select'] = "firstname, lastname, username, personal_information, user_id, profile_pic, private";
					$search2['table'] = "veeds_users";
					$search2['where'] = "disabled = 0 AND user_id = ".$row['user_id']; 
					$result1 = jp_get($search2);
					$row1 = mysqli_fetch_assoc($result1);

					$search3['select'] = "user_id";
					$search3['table'] = "veeds_users_follow";
					$search3['where'] = "user_id = '".$_POST['user_id']."' AND user_id_follow = '".$row1['user_id']."' AND approved = 1"; 
					$count2 = jp_count($search3);
					if($count2 > 0){
						$row1['followed'] = 1;
					}else{
						$search3['where'] = "user_id = '".$_POST['user_id']."' AND user_id_follow = '".$row1['user_id']."' AND approved = 0"; 
						$count2 = jp_count($search3);
						if($count2 > 0)
							$row1['followed'] = 2;
						else
							$row1['followed'] = 0;
					}

					$search3['select'] = "user_id";
					$search3['table'] = "veeds_users_block";
					$search3['where'] = "user_id = '".$_POST['user_id']."' AND user_id_block = '".$row1['user_id']."'"; 
					$count2 = jp_count($search3);
					if($count2 > 0){
						$row1['blocked'] = true;
					}else{
						$row1['blocked'] = false;
					}

					$list['users'][] = $row1;
				} 	
			}
			echo json_encode($list);
		}
	}


?>