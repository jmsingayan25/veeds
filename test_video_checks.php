<?php
	//$row['name'] = $row['username'];
	$search2['select'] = "video_id, like_type, user_id_liker";
	$search2['table'] = "veeds_videos_likes";
	$search2['where'] = "video_id = ".$row['video_id'].""; 
		
	$count = (int)jp_count($search2);
	$liker = jp_get($search2);
	if($count > 0){
		$row['like_count'] = $count;
		// $res = jp_get($search2);
		// $_POST['user_id'] = "49";
		while ($row20 = mysqli_fetch_assoc($liker)) {
			if ($_POST['user_id'] != $row20['user_id_liker']) {
				$search20['select'] = "firstname, lastname, username, personal_information, user_id, profile_pic, private";
				$search20['table'] = "veeds_users";
				$search20['where'] = "disabled = 0 AND user_id = ".$row20['user_id_liker']; 
				$result11 = jp_get($search20);
				while($row10 = mysqli_fetch_assoc($result11)){

					$search3['select'] = "user_id";
					$search3['table'] = "veeds_users_follow";
					$search3['where'] = "user_id = '".$_POST['user_id']."' AND user_id_follow = '".$row10['user_id']."' AND approved = 1"; 
					$count2 = (int)jp_count($search3);
					if($count2 > 0){
						$row10['followed'] = 1;
					}else{
						$search3['where'] = "user_id = '".$_POST['user_id']."' AND user_id_follow = '".$row10['user_id']."' AND approved = 0"; 
						$count2 = (int)jp_count($search3);
						if($count2 > 0){
							$row10['followed'] = 2;
						}else{
							$row10['followed'] = 0;
						}
					}

					$search3['select'] = "user_id";
					$search3['table'] = "veeds_users_block";
					// $search3['where'] = "user_id = '".$_POST['user_id']."' AND user_id_block = '".$row10['user_id']."'"; 
					$search3['where'] = "user_id = '".$_POST['user_id']."' AND user_id_block = '".$row10['user_id']."'"; 
					$count2 = (int)jp_count($search3);
					if($count2 > 0){
						$row10['blocked'] = true;
					}else{
						$row10['blocked'] = false;
					}
					$row['user_id_liker'][] = $row10;
				}
			}
		}
	}else{
		$row['like_count'] = 0;
	}	
		

	$search5['select'] = "user_id";
	$search5['table'] = "veeds_video_tags";
	$search5['where'] = "video_id = '".$row['video_id']."'";

	if(jp_count($search5) > 0){
		$result_tagged = jp_get($search5);
		while ($row1 = mysqli_fetch_assoc($result_tagged)) {
			
			$search21['select'] = "firstname, lastname, username, personal_information, user_id, profile_pic, private";
			$search21['table'] = "veeds_users";
			$search21['where'] = "disabled = 0 AND user_id = ".$row1['user_id']; 
			$result12 = jp_get($search21);
			while ($row12 = mysqli_fetch_assoc($result12)) {
				
				$search3['select'] = "user_id";
				$search3['table'] = "veeds_users_follow";
				$search3['where'] = "user_id = '".$_POST['user_id']."' AND user_id_follow = '".$row12['user_id']."' AND approved = 1"; 
				$count2 = (int)jp_count($search3);
				if($count2 > 0){
					$row12['followed'] = 1;
				}else{
					$search3['where'] = "user_id = '".$_POST['user_id']."' AND user_id_follow = '".$row12['user_id']."' AND approved = 0"; 
					$count2 = (int)jp_count($search3);
					if($count2 > 0) {
						$row12['followed'] = 2;
					}
					else {
						$row12['followed'] = 0;
					}
				}

				$search3['select'] = "user_id";
				$search3['table'] = "veeds_users_block";
				$search3['where'] = "user_id = '".$_POST['user_id']."' 
										AND user_id_block = '".$row12['user_id']."'"; 
				$count2 = (int)jp_count($search3);
				if($count2 > 0){
					$row12['blocked'] = true;
				}else{
					$row12['blocked'] = false;
				}

				$row['user_id_tagged'][] = $row12;
			}
		}
	}	
	
	$search2['where'] = "video_id = ".$row['video_id']." AND user_id_liker = ".$_POST['user_id']." AND user_id = ".$row['uploader_user_id'];

	$count = (int)jp_count($search2);
	$type = jp_get($search2);		
	if($count > 0){
		// $row['like_status'] = 1;
		$like_type = mysqli_fetch_assoc($type);
		$row['like_type'] = $like_type['like_type'];
	}else{
		$like_type = mysqli_fetch_assoc($type);
		$row['like_type'] = $like_type['like_type'];
		// $row['like_status'] = 0;
	}

	$search2['select'] = "comment_id";
	$search2['table'] = "veeds_comments";
	$search2['where'] = "video_id = '".$row['video_id']."'"; 
			
	$count = (int)jp_count($search2);
			
	if($count > 0){
		$row['comment_count'] = $count;
	}else{
		$row['comment_count'] = 0;
	}

	$search2['select'] = "user_id";
	$search2['table'] = "veeds_video_tags";
	$search2['where'] = "video_id = ".$row['video_id']." AND user_id = ".$_POST['user_id'];

	$count = (int)jp_count($search2);

	if($count > 0){
		$row['tag'] = true;
		$row['user_id'] = $_POST['user_id'];
	}else{
		$row['tag'] = false;
	}
?>