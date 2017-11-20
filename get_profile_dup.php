<?php
	include("jp_library/jp_lib.php");
	
	if(empty($_POST['count']) || !isset($_POST['count'])){
		$_POST['count'] = 0;
	}

	$_POST['user_id'] = "182";
	$_POST['logged_id'] = "183";

	if(isset($_POST['user_id'])){
		$user_id = $_POST['user_id'];
		$search['select'] = "a.video_id, a.video_name, a.description, a.video_id, b.firstname, b.lastname, a.date_upload, a.view_count, a.video_thumb, a.location, b.profile_pic, a.video_length, b.username, a.user_id AS uploader_user_id, a.landscape_file, a.timestamp";
		$search['table'] = "veeds_users b, veeds_videos a";
		$search['where'] = "a.user_id = '".$_POST['user_id']."' 
							AND a.user_id = b.user_id"; 
		$start = $_POST['count'] * 30;
		
		$search['filters'] = "ORDER BY a.date_upload DESC LIMIT ".$start.", 30";
			
		$result = jp_get($search);
		
		$list = array();
		$search2['select'] = "firstname, lastname, username, personal_information, profile_pic, private, user_id, total_Likes, cover_photo, video_file, post";
		$search2['table'] = "veeds_users";
		$search2['where'] = "user_id = '".$_POST['user_id']."'"; 
		$result2 = jp_get($search2);
		$row2 = mysqli_fetch_assoc($result2);
		$list['name'] = $row2['firstname']." ".$row2['lastname'];
		$list['username'] = $row2['username'];
		$list['info'] = $row2['personal_information'];
		$list['profile_pic'] = $row2['profile_pic'];
		$list['private'] = $row2['private'];
		$list['user_id'] = $row2['user_id'];
		$list['logged_id'] = $_POST['logged_id'];
		$list['total_likes'] = $row2['total_Likes'];
		$list['cover_photo'] = $row2['cover_photo'];
		$list['video_file'] = $row2['video_file'];
		$list['post'] = (int)$row2['post'];
  		$search2['select'] = "user_id";
		$search2['table'] = "veeds_users_follow";
		$search2['where'] = "user_id = '".$_POST['logged_id']."' AND user_id_follow = '".$_POST['user_id']."' AND approved = 1"; 
		$count2 = (int)jp_count($search2);
		if($count2 > 0){
			$list['followed'] = 1;
		}else{
			$search2['where'] = "user_id = '".$_POST['logged_id']."' AND user_id_follow = '".$_POST['user_id']."' AND approved = 0"; 
			$count2 = (int)jp_count($search2);
			if($count2 > 0)
				$list['followed'] = 2;
			else
				$list['followed'] = 0;
		}
		
		$u_blocks = array();
		
		$block['table'] = "veeds_users_block";
		$block['where'] = "user_id_block = ".$_POST['logged_id'];
		$result3 = jp_get($block);
		while($row3 = mysqli_fetch_array($result3)){
			$u_blocks[] = $row3['user_id'];
		}
		
		if(count($u_blocks) > 0){
			$follower_extend = " AND user_id NOT IN (".implode(",", $u_blocks).")";
			$followed_extend = " AND user_id_follow NOT IN (".implode(",", $u_blocks).")";
		}else{
			$follower_extend = "";
			$followed_extend = "";
		}
		
		$search2['where'] = "user_id_follow = '".$_POST['user_id']."' and approved = 1".$follower_extend; 
		$count2 = (int)jp_count($search2);
		if($count2 > 0){
			$list['followers'] = $count2;
		}else{
			$list['followers'] = 0;
		}
		
		$search2['where'] = "user_id = '".$_POST['user_id']."' and approved = 1".$followed_extend; 
		$count2 = (int)jp_count($search2);
		if($count2 > 0){
			$list['following'] = $count2;
		}else{
			$list['following'] = 0;
		}
		
		$search2['select'] = "user_id";
		$search2['table'] = "veeds_users_block";
		$search2['where'] = "user_id = '".$_POST['logged_id']."' AND user_id_block = '".$_POST['user_id']."'"; 
		$count2 = (int)jp_count($search2);
		if($count2 > 0){
			$list['blocked'] = true;
		}else{
			$list['blocked'] = false;
		}
		
		$list['videos'] = array();
		
		if(($list['private'] == 1 && $list['followed'] == 0 && $_POST['logged_id'] != $_POST['user_id']) || ($list['private'] == 1 && $list['followed'] == 1 && $_POST['logged_id'] != $_POST['user_id']) || ($list['private'] == 0) || ($_POST['logged_id'] == $_POST['user_id'])){
			while($row = mysqli_fetch_assoc($result)){
				if ((!is_null($row['landscape_file']) && !is_null($row['video_thumb'])) || !is_null($row['video_thumb'])) {
					$_POST['user_id'] = $_POST['logged_id'];
					include('test_video_checks.php');	
					$row['video_name'] = str_replace(';quote;',"'", $row['video_name']);
					$row['description'] = str_replace(';quote;',"'", $row['description']);
					//$row['user_id'] = $user_id;

					if(!empty($row['timestamp'])){

						$search1['select'] = "video_thumb, time_stamp_key";
						$search1['table'] = "veeds_active_videos";
						//$search1['where'] = "time_stamp_key IN (".implode(",",array_filter($array['timestamp'])).")";
						$search1['where'] = "time_stamp_key = ".$row['timestamp'];

						//echo implode(" ",$search1);
						$thumb = array();
						if(jp_count($search1) > 0){
							$result1 = jp_get($search1);
							while($row1 = mysqli_fetch_assoc($result1)){
								
								$thumb['video_thumb'][] = $row1['video_thumb'];
								//$list['thumb'][] = $row1;
							}
						}
				
						$search5['select'] = "location, time_stamp_key";
						$search5['table'] = "veeds_active_videos";
						//$search2['where'] = "time_stamp_key IN (".implode(",",array_filter($array['timestamp'])).")";
						$search5['where'] = "time_stamp_key = ".$row['timestamp'];
						$search5['filters'] = "GROUP BY time_stamp_key, location";
				
						if(jp_count($search5) > 0){
							
							$result2 = jp_get($search5);
							while($row2 = mysqli_fetch_assoc($result2)){
								
								if(!isset($thumb['video_thumb'][0])){
									$thumb['video_thumb'][0] = null;
								}else{
									$row2["thumb1"] = $thumb['video_thumb'][0];
								}
								if(!isset($thumb['video_thumb'][1])){
									$thumb['video_thumb'][1] = null;
								}else {
									$row2["thumb2"] = $thumb['video_thumb'][1];
								}
								if(!isset($thumb['video_thumb'][2])){
									$thumb['video_thumb'][2] = null;
								}else {
									$row2["thumb3"] = $thumb['video_thumb'][2];
								}
								$row2['user_id'] = $user_id;
								$list['active'][] = $row2;
								unset($thumb);	
							}
						}
					} else {
						
						$list['videos'][] = $row;
					}
				}
			}
			
		}

		

		$search2['select'] = "user_id, video_id, like_type";
		$search2['table'] = "veeds_videos_likes";
		$search2['where'] = "user_id = ".$_POST['user_id']." AND like_type = 'Liked'"; 
		$res = jp_get($search2);
		$count = (int)jp_count($search2);
		$list['likes'] = $count;

		// while ($liker = mysqli_fetch_assoc($res)) {
		// 	$list['Type']['likes'] = $liker;
		// }

		$search2['select'] = "user_id, video_id, like_type";
		$search2['table'] = "veeds_videos_likes";
		$search2['where'] = "user_id = ".$_POST['user_id']." AND like_type = 'Heart'"; 
		$res = jp_get($search2);
		$count = (int)jp_count($search2);
		$list['Heart'] = $count;

		// 

		$search2['select'] = "user_id, video_id, like_type";
		$search2['table'] = "veeds_videos_likes";
		$search2['where'] = "user_id = ".$_POST['user_id']." AND like_type = 'Happy'"; 
		$res = jp_get($search2);
		$count = (int)jp_count($search2);
		$list['Happy'] = $count;
		// while ($liker = mysqli_fetch_assoc($res)) {
		// 	$list['Type']['Happy'] = $liker;
		// }

		$search2['select'] = "user_id, video_id, like_type";
		$search2['table'] = "veeds_videos_likes";
		$search2['where'] = "user_id = ".$_POST['user_id']." AND like_type = 'Sad'"; 
		$res = jp_get($search2);
		$count = (int)jp_count($search2);
		$list['Sad'] = $count;
		// while ($liker = mysqli_fetch_assoc($res)) {
		// 	$list['Type']['Sad'] = $liker;
		// }

		$search2['select'] = "user_id, video_id, like_type";
		$search2['table'] = "veeds_videos_likes";
		$search2['where'] = "user_id = ".$_POST['user_id']." AND like_type = 'Wow'"; 
		$res = jp_get($search2);
		$count = (int)jp_count($search2);
		$list['Wow'] = $count;

		// while ($liker = mysqli_fetch_assoc($res)) {
		// 	$list['Type']['Wow'] = $liker;
		// }

		$search2['select'] = "user_id, video_id, like_type";
		$search2['table'] = "veeds_videos_likes";
		$search2['where'] = "user_id = ".$_POST['user_id']." AND like_type = 'Angry'"; 
		$res = jp_get($search2);
		$count = (int)jp_count($search2);
		$list['Angry'] = $count;
		// while ($liker = mysqli_fetch_assoc($res)) {
		// 	$list['Type']['Angry'] = $liker;
		// }

		echo json_encode($list);

	} else if (isset($_POST['username'])) {
		$user_id = $_POST['user_id'];
		$search['select'] = "a.video_id, a.video_name, a.description, a.video_id, b.firstname, b.lastname, a.date_upload, a.view_count, a.video_thumb, a.location, b.profile_pic, a.video_length, b.username, a.user_id AS uploader_user_id, a.landscape_file";
		$search['table'] = "veeds_users b, veeds_videos a";
		$search['where'] = "b.username = '".$_POST['username']."' 
							AND a.user_id = b.user_id"; 
		$start = $_POST['count'] * 10;
		
		$search['filters'] = "ORDER BY a.date_upload DESC LIMIT ".$start.", 10";
			
		$result = jp_get($search);
		$searchId['select'] = "user_id";
		$searchId['table'] = "veeds_users";
		$searchId['where'] = "username = '".$_POST['username']."'";
		$id = mysqli_fetch_assoc(jp_get($searchId));
		$list = array();
		$search2['select'] = "user_id, firstname, lastname, username, personal_information, profile_pic, private, user_id, total_Likes";
		$search2['table'] = "veeds_users";
		$search2['where'] = "username = '".$_POST['username']."'"; 
		$result2 = jp_get($search2);
		$row2 = mysqli_fetch_assoc($result2);
		$list['name'] = $row2['firstname']." ".$row2['lastname'];
		$list['username'] = $row2['username'];
		$list['info'] = $row2['personal_information'];
		$list['profile_pic'] = $row2['profile_pic'];
		$list['private'] = $row2['private'];
		$list['user_id'] = $row2['user_id'];
		$list['total_likes'] = $row2['total_Likes'];
 		$search2['select'] = "user_id";
		$search2['table'] = "veeds_users_follow";
		$search2['where'] = "user_id = '".$_POST['logged_id']."' AND approved = 1 AND user_id_follow = '".$row2['user_id']."' "; 
		$count2 = (int)jp_count($search2);
		if($count2 > 0){
			$list['followed'] = 1;
		}else{
			$search2['where'] = "user_id = '".$_POST['logged_id']."' AND user_id_follow = '".$row2['user_id']."' AND approved = 0"; 
			$count2 = (int)jp_count($search2);
			if($count2 > 0)
				$list['followed'] = 2;
			else
				$list['followed'] = 0;
		}
		
		$u_blocks = array();
		
		$block['table'] = "veeds_users_block";
		$block['where'] = "user_id_block = ".$_POST['logged_id'];
		$result3 = jp_get($block);
		while($row3 = mysqli_fetch_array($result3)){
			$u_blocks[] = $row3['user_id'];
		}
		
		if(count($u_blocks) > 0){
			$follower_extend = " AND user_id NOT IN (".implode(",", $u_blocks).")";
			$followed_extend = " AND user_id_follow NOT IN (".implode(",", $u_blocks).")";
		}else{
			$follower_extend = "";
			$followed_extend = "";
		}
		
		$search2['where'] = "user_id_follow = '".$row2['user_id']."' and approved = 1".$follower_extend; 
		$count2 = (int)jp_count($search2);
		if($count2 > 0){
			$list['followers'] = $count2;
		}else{
			$list['followers'] = 0;
		}
		
		$search2['where'] = "user_id = '".$row2['user_id']."' and approved = 1".$followed_extend; 
		$count2 = (int)jp_count($search2);
		if($count2 > 0){
			$list['following'] = $count2;
		}else{
			$list['following'] = 0;
		}
		
		$search2['select'] = "user_id";
		$search2['table'] = "veeds_users_block";
		$search2['where'] = "user_id = '".$_POST['logged_id']."' AND user_id_block = '".$row2['user_id']."'"; 
		$count2 = (int)jp_count($search2);
		if($count2 > 0){
			$list['blocked'] = true;
		}else{
			$list['blocked'] = false;
		}
		
		$list['videos'] = array();
		
		if(($list['private'] == 1 && $list['followed'] == 1 && $_POST['logged_id'] != $row2['user_id']) || ($list['private'] == 0) || ($_POST['logged_id'] == $row2['user_id'])){
		
			while($row = mysqli_fetch_assoc($result)){
				if (!is_null($row['landscape_file']) || !is_null($row['video_thumb'])) {
					$_POST['user_id'] = $_POST['logged_id'];
					include('test_video_checks.php');	
					$row['video_name'] = str_replace(';quote;',"'", $row['video_name']);
					$row['description'] = str_replace(';quote;',"'", $row['description']);
					//$row['user_id'] = $user_id;

					//$list['videos'][] = $row;
		
					if(!empty($row['timestamp'])){

						$search1['select'] = "video_thumb, time_stamp_key";
						$search1['table'] = "veeds_active_videos";
						//$search1['where'] = "time_stamp_key IN (".implode(",",array_filter($array['timestamp'])).")";
						$search1['where'] = "time_stamp_key = ".$row['timestamp'];

						//echo implode(" ",$search1);
						$thumb = array();
						if(jp_count($search1) > 0){
							$result1 = jp_get($search1);
							while($row1 = mysqli_fetch_assoc($result1)){
								$thumb['video_thumb'][] = $row1['video_thumb'];
								//$list['thumb'][] = $row1;
							}
						}
				
						$search5['select'] = "location, time_stamp_key";
						$search5['table'] = "veeds_active_videos";
						//$search2['where'] = "time_stamp_key IN (".implode(",",array_filter($array['timestamp'])).")";
						$search5['where'] = "time_stamp_key = ".$row['timestamp'];
						$search5['filters'] = "GROUP BY time_stamp_key";
				
						if(jp_count($search5) > 0){
							$result2 = jp_get($search5);
							while($row2 = mysqli_fetch_assoc($result2)){
						
								if(!isset($thumb['video_thumb'][0])){
									$thumb['video_thumb'][0] = null;
								}else{
									$row2["thumb1"] = $thumb['video_thumb'][0];
								}
								if(!isset($thumb['video_thumb'][1])){
									$thumb['video_thumb'][1] = null;
								}else {
									$row2["thumb2"] = $thumb['video_thumb'][1];
								}
								if(!isset($thumb['video_thumb'][2])){
									$thumb['video_thumb'][2] = null;
								}else {
									$row2["thumb3"] = $thumb['video_thumb'][2];
								}
								$row2['user_id'] = $user_id;
								$list['active'][] = $row2;
								unset($thumb);	
							}
						}
					} else {
						$list['videos'][] = $row;
					}
				}
			}
		}

		$search2['select'] = "user_id, video_id, like_type";
		$search2['table'] = "veeds_videos_likes";
		$search2['where'] = "user_id = ".$row2['user_id']." AND like_type = 'Liked'"; 
		$res = jp_get($search2);
		$count = (int)jp_count($search2);
		$list['likes'] = $count;

		// while ($liker = mysqli_fetch_assoc($res)) {
		// 	$list['Type']['likes'] = $liker;
		// }

		$search2['select'] = "user_id, video_id, like_type";
		$search2['table'] = "veeds_videos_likes";
		$search2['where'] = "user_id = ".$row2['user_id']." AND like_type = 'Heart'"; 
		$res = jp_get($search2);
		$count = (int)jp_count($search2);
		$list['Heart'] = $count;

		// 

		$search2['select'] = "user_id, video_id, like_type";
		$search2['table'] = "veeds_videos_likes";
		$search2['where'] = "user_id = ".$row2['user_id']." AND like_type = 'Happy'"; 
		$res = jp_get($search2);
		$count = (int)jp_count($search2);
		$list['Happy'] = $count;
		// while ($liker = mysqli_fetch_assoc($res)) {
		// 	$list['Type']['Happy'] = $liker;
		// }

		$search2['select'] = "user_id, video_id, like_type";
		$search2['table'] = "veeds_videos_likes";
		$search2['where'] = "user_id = ".$row2['user_id']." AND like_type = 'Sad'"; 
		$res = jp_get($search2);
		$count = (int)jp_count($search2);
		$list['Sad'] = $count;
		// while ($liker = mysqli_fetch_assoc($res)) {
		// 	$list['Type']['Sad'] = $liker;
		// }

		$search2['select'] = "user_id, video_id, like_type";
		$search2['table'] = "veeds_videos_likes";
		$search2['where'] = "user_id = ".$row2['user_id']." AND like_type = 'Wow'"; 
		$res = jp_get($search2);
		$count = (int)jp_count($search2);
		$list['Wow'] = $count;

		// while ($liker = mysqli_fetch_assoc($res)) {
		// 	$list['Type']['Wow'] = $liker;
		// }

		$search2['select'] = "user_id, video_id, like_type";
		$search2['table'] = "veeds_videos_likes";
		$search2['where'] = "user_id = ".$row2['user_id']." AND like_type = 'Angry'"; 
		$res = jp_get($search2);
		$count = (int)jp_count($search2);
		$list['Angry'] = $count;
		// while ($liker = mysqli_fetch_assoc($res)) {
		// 	$list['Type']['Angry'] = $liker;
		// }

		echo json_encode($list);
	}
	
?>