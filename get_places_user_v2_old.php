<?php
	
	include("jp_library/jp_lib.php");

	$_POST['user_id'] = "182";
	$_POST['place_name'] = "Jollibee";
	$_POST['location'] = "Robinsons Galleria, Epifanio de los Santos Ave, Ortigas Center, Pasig, 1605 Metro Manila, Philippines";
	if(isset($_POST['location']) && isset($_POST['place_name'])){

		$place_name = str_replace("'","\'",$_POST['place_name']);
		$location = str_replace("'","\'",$_POST['location']);
		$array = array();
		$list = array();

		$search['select'] = "DISTINCT user_id_follow";
		$search['table'] = "veeds_users_follow";
    	$search['where'] = "user_id = ".$_POST['user_id']." AND approved = 1";

		if(jp_count($search) > 0){
			$result = jp_get($search);
			while($row = mysqli_fetch_assoc($result)){
				$array['users_follow'][] = $row['user_id_follow'];
			}
		}else{
			$array['users_follow'][] = "''";
		}

		$search1['select'] = "DISTINCT place_id";
		$search1['table'] = "veeds_establishment";
		$search1['where'] = "place_name = '".$place_name."' AND location = '".$location."'";
		// echo implode(" ", $search1);
		$result1 = jp_get($search1);
		$row = mysqli_fetch_assoc($result1);
		$place_id = $row['place_id'];

		$users = implode(",", $array['users_follow']);
		$search2['select'] = "DISTINCT u.user_id, u.firstname, u.lastname, u.email, u.bday, u.gender, u.username, u.profile_pic, u.cover_photo, u.country, u.private, COUNT(DISTINCT DATE_FORMAT(h.date_visit,'%m-%d-%y')) as visit_count";
		$search2['table'] = "veeds_users u, veeds_users_visit_history h, veeds_establishment e";
		$search2['where'] = "u.user_id = h.user_id 
								AND h.place_id = e.place_id 
								AND h.place_id = '".$place_id."' 
								AND h.user_id IN (".$users.",".$_POST['user_id'].")";
		$search2['filters'] = "GROUP BY h.user_id";
		// $search2['filters'] = "HAVING COUNT(v.user_id) >= 5";
		// echo implode(" ", $search2);
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
			$row2['visit_count'] = (int)$row2['visit_count'];
			$list['users'][] = $row2;
		}

		$search3['select'] = "h.place_id, e.place_name, e.location, e.coordinates, h.video_id, v.video_name, v.description, v.video_file, v.video_thumb, v.date_upload, v.view_count, v.like_count, v.video_length, v.landscape_file, h.user_id";
		$search3['table'] = "veeds_users u, veeds_videos v, veeds_establishment e, veeds_users_visit_history h";
		$search3['where'] = "h.user_id = u.user_id
								AND h.video_id = v.video_id
								AND h.place_id = e.place_id
								AND h.place_id = '".$place_id."'
								AND h.user_id IN (".$users.")
								AND h.date_visit BETWEEN (NOW() - INTERVAL 7 DAY) AND NOW()";
								// AND h.date_visit BETWEEN (NOW() - INTERVAL 7 DAY) AND NOW()
		$search3['filters'] = "ORDER BY h.date_visit DESC";	
		// $search3['filters'] = "ORDER BY date_upload DESC HAVING COUNT(v.user_id) >= 5";						
		// echo implode(" ", $search3);
		$result3 = jp_get($search3);
		while($row3 = mysqli_fetch_assoc($result3)){
			$row3['view_count'] = (int)$row3['view_count'];
			$row3['video_length'] = (int)$row3['video_length'];
			$row3['like_count'] = (int)$row3['like_count'];
			$list['videos'][] = $row3;
		}
		echo json_encode($list);
	}
?>