<?php
	include("jp_library/jp_lib.php");
	$search2['select'] = "video_id, like_type, user_id_liker, user_id";
	$search2['table'] = "veeds_videos_likes";
	$search2['where'] = "video_id = ".$_POST['video_id'].""; 
	$liker = jp_get($search2);
	if(jp_count($search2) > 0){
		$list = array();
		$list['count'] = jp_count($search2);
		$list['id'][] = $_POST['user_id'];
		while ($row10 = mysqli_fetch_assoc($liker)) {
			$list['row'][] = $row10;
			if ($_POST['user_id'] != $row10['user_id_liker']) {

				$likeType = $row10['like_type'];
				$search20['select'] = "firstname, lastname, username, personal_information, user_id, profile_pic, private";
				$search20['table'] = "veeds_users";
				$search20['where'] = "disabled = 0 AND user_id = ".$row10['user_id_liker']; 
				$result11 = jp_get($search20);
				$row10 = mysqli_fetch_assoc($result11);

				$search3['select'] = "user_id";
				$search3['table'] = "veeds_users_follow";
				$search3['where'] = "user_id = '".$_POST['user_id']."' AND user_id_follow = '".$row10['user_id']."' AND approved = 1"; 
				$count2 = jp_count($search3);
				if($count2 > 0){
					$row10['followed'] = 1;
				}else{
					$search3['where'] = "user_id = '".$_POST['user_id']."' AND user_id_follow = '".$row10['user_id']."' AND approved = 0"; 
					$count2 = jp_count($search3);
					if($count2 > 0)
						$row10['followed'] = 2;
					else
						$row10['followed'] = 0;
					}

				// $search3['select'] = "user_id";
				// $search3['table'] = "veeds_users_block";
				// $search3['where'] = "user_id = '".$_POST['user_id']."' AND user_id_block = '".$row10['user_id']."'"; 
				// $count2 = jp_count($search3);
				// if($count2 > 0){
				// 	$row10['blocked'] = true;
				// }else{
				// 	$row10['blocked'] = false;
				// }
				$row10['like_type'] = $likeType;
				$list['users'][] = $row10;
			}
			
		}
		echo json_encode($list);
	}
?>