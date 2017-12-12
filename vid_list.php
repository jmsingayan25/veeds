<?php
	include("jp_library/jp_lib.php");

	if(empty($_POST['count']) || !isset($_POST['count'])){
		$_POST['count'] = 0;
	}
	// $_POST['user_id'] = "205";
	if(isset($_POST['user_id'])){

		$list = array();
		$u_blocks = array();
		$array = array();
		//get user who block current user
		$block['table'] = "veeds_users_block";
		$block['where'] = "user_id_block = ".$_POST['user_id'];

		if(jp_count($block) > 0){
			$result = jp_get($block);
			while($row = mysqli_fetch_array($result)){
				$u_blocks['user'][] = $row['user_id'];
			}
		}else{
			$u_blocks['user'][] = "''";
		}
		//get user who blocked by current user
		$block1['table'] = "veeds_users_block";
		$block1['where'] = "user_id = ".$_POST['user_id'];
		
		$count = (int)jp_count($block1);
		$list['blocked'] = $count;

		if(jp_count($block1) > 0){
			$result = jp_get($block1);
			while($row = mysqli_fetch_array($result)){
				$u_blocks['block'][] = $row['user_id_block'];
			}
		}else{
			$u_blocks['block'][] = "''";
		}

		$u_blocks_data = array_merge($u_blocks['user'],$u_blocks['block']);

		if(count($u_blocks_data) > 0){
			$follower_extend = " AND user_id NOT IN (".implode(",",$u_blocks['block']).")";
			$follower_extend2 = " AND v.user_id NOT IN (".implode(",", $u_blocks_data).")";
			$followed_extend = " AND user_id_follow NOT IN (".implode(",", $u_blocks['user']).")";
		}else{
			$follower_extend = "";
			$follower_extend2 = "";
			$followed_extend = "";
		}

		$follow2['table'] = "veeds_users_follow";

		$follow2['where'] = "user_id = ".$_POST['user_id']." AND approved = 1".$followed_extend;
		$count = (int)jp_count($follow2);
			$list['count_followed'] = $count;

		$follow2['where'] = "user_id_follow = ".$_POST['user_id']." AND approved = 1".$follower_extend;
		$count = (int)jp_count($follow2);
			$list['count_follower'] = $count;
		//get user videos
		$search['select'] = "video_id";
		$search['table'] = "veeds_videos";
		$search['where'] = "user_id = '".$_POST['user_id']."'";
		$search['filters'] = "ORDER BY video_id DESC";

		$count = (int)jp_count($search);

		if($count > 0){
			$result = jp_get($search);
			while ($row1 = mysqli_fetch_assoc($result)) {
				$array['own'][] = $row1['video_id'];
			}
			$list['video_count'] = $count;
		}else{
			$array['own'][] = "''";
			$list['video_count'] = 0;
		}
		//get videos where user is tagged
		$search['select'] = "DISTINCT t.video_id";
		$search['table'] = "veeds_video_tags t, veeds_videos v";
		$search['where'] = "t.video_id = v.video_id AND t.user_id = ".$_POST['user_id']." 
				    AND v.video_id NOT IN (".implode(",",$array['own']).")".$follower_extend2;
		$search['filters'] = "ORDER BY t.video_id DESC";

		$count = (int)jp_count($search);

		if($count > 0){
			$result = jp_get($search);
			while ($row2 = mysqli_fetch_assoc($result)) {
				$array['tag'][] = $row2['video_id'];
			}
			$list['tag_video_count'] = $count;
		}else{
			$array['tag'][] = "''";
			$list['tag_video_count'] = 0;
		}
		//displays info of the user and the video
		$array_data = array_merge($array['own'],$array['tag']);
		$start = $_POST['count'] * 15;
		$search['select'] = "DISTINCT v.video_id, v.video_name, v.video_file, v.description, v.video_thumb, v.date_upload, v.video_length, v.view_count, v.location, v.place_id, v.landscape_file, v.timestamp, v.user_id as uploader_user_id, u.username, u.firstname, u.lastname, u.profile_pic, u.personal_information";
		$search['table'] = "veeds_videos v, veeds_users u";
		$search['where'] = "v.user_id = u.user_id AND v.video_id IN (".implode(",",$array_data).")";
		$search['filters'] = "ORDER BY find_in_set(v.video_id,'".implode(",",$array_data)."') LIMIT ".$start.", 15";
		// echo implode(" ", $search);
		$result = jp_get($search);

		while($row = mysqli_fetch_assoc($result)){

			$file = $row['landscape_file'];
			$fileImage = $row['video_thumb'];
			// $filesVideo = '/wamp/www/veeds/videos/'.$file;
			$filesVideo = '/xampp/htdocs/veeds/videos/'.$file;
			$filesImage = '/xampp/htdocs/veeds/thumbnails/'.$fileImage;
			if ((!is_null($row['landscape_file']) && file_exists($filesVideo)) || (!is_null($row['video_thumb']) && file_exists($filesImage))){
				include('video_checks.php');

				if($row['landscape_file'] == NULL){
					$row['landscape_file'] = "";
				}
				
				$row['video_name'] = str_replace(';quote;',"'",$row['video_name']);
				$row['description'] = str_replace(';quote;',"'", $row['description']);
				$row['view_count'] = (int)$row['view_count'];
						
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
							$file_thumb = $row1['video_thumb'];
							// $filesThumb = '/wamp/www/veeds/thumbnails/'.$file_thumb;
							$filesThumb = '/xampp/htdocs/veeds/thumbnails/'.$file_thumb;
							if (!is_null($row1['video_thumb']) && file_exists($filesThumb)){
								$thumb['video_thumb'][] = $row1['video_thumb'];
							//$list['thumb'][] = $row1;
							}
						}
					}
					
					$search5['select'] = "location, time_stamp_key";
					$search5['table'] = "veeds_active_videos";
					//$search2['where'] = "time_stamp_key IN (".implode(",",array_filter($array['timestamp'])).")";
					$search5['where'] = "time_stamp_key = ".$row['timestamp'];
					$search5['filters'] = "GROUP BY time_stamp_key, location";
					// echo implode(" ", $search5);
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
							$row2['user_id'] = $_POST['user_id'];
							$list['active'][] = $row2;
							unset($thumb);
						}
					}
				} else {
					$list['videos'][] = $row;
				}
			
			}
		}	

		$search2['select'] = "user_id";
		$search2['table'] = "veeds_users_follow";
		$search2['where'] = "user_id_follow = '".$_POST['user_id']."' and approved = 0".$follower_extend;
		$count2 = (int)jp_count($search2);
		$list['pending_followers'] = $count2;

		$search2['select'] = "notif_id";
		$search2['table'] = "notifications";
		$search2['where'] = "user_id = '".$_POST['user_id']."' AND notif_datetime > DATE_SUB(NOW(), INTERVAL 24 HOUR) AND notif_datetime <= NOW()";
		$count = (int)jp_count($search2);
		$list['notif_count'] = $count;
		
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
	}
?>
