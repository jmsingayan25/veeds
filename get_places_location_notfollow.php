<?php
	
	include("jp_library/jp_lib.php");

	if(isset($_POST['place_id'])){

		$array = array();
		$list = array();
		$u_blocks = array();

		$block['select'] = "DISTINCT user_id_block";
		$block['table'] = "veeds_users_block";
		$block['where'] = "user_id = ".$_POST['user_id'];
		$result_block = jp_get($block);
		while($row = mysqli_fetch_assoc($result_block)){
			$u_blocks[] = $row['user_id_block'];
		}

		// Get users blocked by the user
		$block['select'] = "DISTINCT user_id";
		$block['table'] = "veeds_users_block";
		$block['where'] = "user_id_block = ".$_POST['user_id'];
		$result_block = jp_get($block);
		while($row = mysqli_fetch_assoc($result_block)){
			if(!in_array($row, $u_blocks))
				$u_blocks[] = $row['user_id'];
		}

		if(count($u_blocks) > 0){
			$u_extend = " AND user_id_follow NOT IN (".implode(",", $u_blocks).")";
		}else{
			$u_extend = "";
		}

		// Get users not followed by the user. User excluded if in the blocked list
		$search['select'] = "DISTINCT user_id_follow";
		$search['table'] = "veeds_users_follow";
    	$search['where'] = "user_id_follow NOT IN (SELECT DISTINCT user_id_follow 
    												FROM veeds_users_follow 
    												WHERE user_id = '".$_POST['user_id']."' 
    												AND user_id_follow != '".$_POST['user_id']."')".$u_extend;
    	$search['filters'] = "ORDER BY user_id_follow ASC";

		if(jp_count($search) > 0){
			$result = jp_get($search);
			while($row = mysqli_fetch_assoc($result)){
				$array['users_follow'][] = $row['user_id_follow'];
			}
		}else{
			$array['users_follow'][] = "''";
		}

		// Display user info
		$users = implode(",", $array['users_follow']);
		$search2['select'] = "DISTINCT h.user_id, u.firstname, u.lastname, u.email, u.bday, u.gender, u.username, u.profile_pic, u.cover_photo, u.country, u.private";
		$search2['table'] = "veeds_users u, veeds_users_visit_history h";
		$search2['where'] = "h.user_id = u.user_id 
								AND h.user_id IN (".$users.")
								AND h.place_id = '".$_POST['place_id']."'
								AND u.disabled = 0";
		$search2['filters'] = "GROUP BY h.user_id";

		$result2 = jp_get($search2);
		while($row2 = mysqli_fetch_assoc($result2)){
			
			$search4['select'] = "user_id";
			$search4['table'] = "veeds_users_follow";
			$search4['where'] = "user_id = '".$_POST['user_id']."' AND user_id_follow = '".$row2['user_id']."' AND approved = 1";
			
			$count = jp_count($search4);
			if($count > 0){
				$row2['followed'] = 1;
			}else{
				$search2['where'] = "user_id = '".$_POST['user_id']."' AND user_id_follow = '".$row2['user_id']."' AND approved = 0";
				$count2 = jp_count($search2);
				if($count2 > 0)
					$row2['followed'] = 2;
				else
					$row2['followed'] = 0;
			}

			$search5['select'] = "user_id";
			$search5['table'] = "veeds_users_block";
			$search5['where'] = "user_id = '".$_POST['user_id']."' AND user_id_block = '".$row2['user_id']."'"; 

			$count5 = (int)jp_count($search5);
			if($count5 > 0){
				$row2['blocked'] = true;
			}else{
				$row2['blocked'] = false;
			}

			$row2['gender'] = (int)$row2['gender'];
			$row2['private'] = (int)$row2['private'];
			$row2['logged_id'] = $_POST['user_id'];

			$list['users'][] = $row2;
		}

		$search4['select'] = "location";
		$search4['table'] = "veeds_establishment";
		$search4['where'] = "place_id = '".$_POST['place_id']."'";

		if(jp_count($search4) > 0){

			$location_result = jp_get($search4);
			$location_row = mysqli_fetch_assoc($location_result);

			$explode_location = explode(",", $location_row['location']);
			for ($i=0; $i < count($explode_location); $i++) { 
				if($i < 2){

					// Display posts by followed users that is within the place
					$search3['select'] = "DISTINCT h.place_id, e.place_name, e.location, e.coordinates, v.video_id, v.video_name, v.description, v.video_file, v.video_thumb, v.date_upload, v.view_count, v.like_count, v.video_length, v.landscape_file, v.user_id";
					$search3['table'] = "veeds_users u, veeds_videos v, veeds_establishment e, veeds_users_visit_history h";
					// $search3['where'] = "h.user_id = u.user_id
					// 						AND h.video_id = v.video_id
					// 						AND h.place_id = e.place_id
					// 						AND h.user_id IN (".$users.")
					// 						AND h.place_id = '".$_POST['place_id']."'
					// 						AND DATE_FORMAT(v.date_expiry,'%Y-%m-%d %H:%i %s') > NOW()";
					$search3['where'] = "h.user_id = u.user_id
											AND h.video_id = v.video_id
											AND h.place_id = e.place_id
											AND h.user_id IN (".$users.")
											AND (h.place_id = '".$_POST['place_id']."'
											OR e.location LIKE '%".$explode_location[$i]."%')";
					$search3['filters'] = "GROUP BY v.video_id ORDER BY v.date_upload DESC";
					// $search3['filters'] = "ORDER BY date_upload DESC HAVING COUNT(v.user_id) >= 5";						
					$result3 = jp_get($search3);
					while($row3 = mysqli_fetch_assoc($result3)){

						$row3['view_count'] = (int)$row3['view_count'];
						$row3['video_length'] = (int)$row3['video_length'];
						$row3['like_count'] = (int)$row3['like_count'];
						$row3['logged_id'] = $_POST['user_id'];

						$list['videos'][] = $row3;
					}
				}
			}
		}
		echo json_encode($list);
	}
?>