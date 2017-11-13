<?php

	include("jp_library/jp_lib.php");

	$_POST['user_id'] = "49";
	$_POST['hashtags'] = "rublou cainta";
	if(isset($_POST['hashtags'])){

		// $search['select'] = "a.video_id, a.video_name, a.description, a.video_id, b.firstname, a.video_thumb, b.lastname, b.username, a.date_upload, a.view_count, a.location, b.profile_pic, a.video_length, b.username, a.user_id";
		// $search['table'] = "veeds_users b, veeds_videos a";
		// $search['where'] = "a.user_id = b.user_id AND a.description LIKE '%#".$_POST['keyword']." %' AND b.disabled = 0 ".$extend.$p_extend;
		// $start = $_POST['count'] * 5;
		// $search['filters'] = "ORDER BY a.date_upload DESC LIMIT ".$start.", 5";

		// $result = jp_get($search);

		// $list = array();
		// $list['videos'] = array();
		// while($row = mysqli_fetch_assoc($result)){
		// 	include('video_checks.php');
		// 	$row['video_name'] = str_replace(';quote;',"'",$row['video_name']);
		// 	$row['description'] = str_replace(';quote;',"'", $row['description']);
		// 	$list['videos'][] = $row;
		// }

		// $list = array();

		// $search['select'] = "h.user_id, CONCAT(u.firstname, ' ', u.lastname) as fullname, u.username, h.place_id, e.place_name, e.location";
		// $search['table'] = "veeds_users u, veeds_users_visit_history h, veeds_establishment e";
		// $search['where'] = "h.user_id = u.user_id 
		// 					AND h.place_id = e.place_id
		// 					AND (h.hashtags LIKE '#%".$_POST['hashtags']."%' 
		// 						OR u.firstname LIKE '%".$_POST['hashtags']."%' 
		// 						OR u.lastname LIKE '%".$_POST['hashtags']."%' 
		// 						OR e.place_name LIKE '%".$_POST['hashtags']."%')";
		// // echo implode(" ", $search);
		// $result = jp_get($search);
		// while ($row = mysqli_fetch_assoc($result)) {
			
		// 	$list['hashtags'][] = $row;
		// }

		$list = array();
		$u_blocks = array();

		$block['select'] = "user_id";
		$block['table'] = "veeds_users_block";
		$block['where'] = "user_id_block = ".$_POST['user_id'];
		$result_block = jp_get($block);
		while($row3 = mysqli_fetch_assoc($result_block)){
			$u_blocks[] = $row3['user_id'];
		}

		if(count($u_blocks) > 0){
			$u_extend = " AND u.user_id NOT IN (".implode(",", $u_blocks).")";
		}else{
			$u_extend = "";
		}
	
		$search['select'] = "DISTINCT u.user_id, u.firstname, u.lastname, u.username";
		$search['table'] = "veeds_users u";
		$search['where'] = "u.firstname LIKE '%".$_POST['hashtags']."%' 
							OR u.lastname LIKE '%".$_POST['hashtags']."%'".$u_extend;
		// echo implode(" ", $search);
		$result = jp_get($search);
		while($row = mysqli_fetch_assoc($result)){

			$search1['select'] = "user_id";
			$search1['table'] = "veeds_users_follow";
			$search1['where'] = "user_id = '".$_POST['user_id']."' AND user_id_follow = '".$row['user_id']."' AND approved = 1";
			$count1 = jp_count($search1);
			if($count1 > 0){
				$row['followed'] = 1;
			}else{
				$search1['where'] = "user_id = '".$_POST['user_id']."' AND user_id_follow = '".$row['user_id']."' AND approved = 0";
				$count1 = jp_count($search1);
				if($count1 > 0){
					$row['followed'] = 2;
				}else{
					$row['followed'] = 0;
				}
			}

			$search2['select'] = "user_id";
			$search2['table'] = "veeds_users_block";
			// $search2['where'] = "user_id = '".$_POST['user_id']."' AND user_id_block = '".$row10['user_id']."'"; 
			$search2['where'] = "user_id = '".$_POST['user_id']."' AND user_id_block = '".$row['user_id']."'"; 
			$count2 = (int)jp_count($search2);
			if($count2 > 0){
				$row['blocked'] = true;
			}else{
				$row['blocked'] = false;
			}
			$list['search'][] = $row;
		}

		$search3['select'] = "DISTINCT place_id, place_name, location, coordinates, tags";
		$search3['table'] = "veeds_establishment";
		$search3['where'] = "place_name LIKE '%".$_POST['hashtags']."%'";
		// echo implode(" ", $search3);
		$result3 = jp_get($search3);
		while($row3 = mysqli_fetch_assoc($result3)) {
			$list['search'][] = $row3;
		}

		$hostname = "https://maps.googleapis.com/maps/api/place/textsearch/json?query=".str_replace(" ", "+", $_POST['hashtags'])."&radius=500&sensor=false&key=AIzaSyDVdJtIAvhmHE7e2zoxA_Y9qWRpp6eE2o8"; 
		// echo $hostname;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $hostname);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		$response = curl_exec($ch);
		curl_close($ch);
		$response_a = json_decode($response);
		
		$name['place_id'] = $response_a->results[0]->place_id;
		$name['name'] = $response_a->results[0]->name;
		$name['location'] = $response_a->results[0]->formatted_address;
		$coor['lat'] = $response_a->results[0]->geometry->location->lat;
		$coor['lng'] = $response_a->results[0]->geometry->location->lng;
		$name['coordinates'] = $coor['lat'].",".$coor['lng'];
		$name['tags'] = $response_a->results[0]->types;
		$name['tags'] = implode(",", $name['tags']);

		$list['search'][] = $name;

		$search2['select'] = "hashtag, video_id, user_id";
		$search2['table'] = "veeds_hashtag";
		$search2['where'] = "hashtag LIKE '%".$_POST['hashtags']."%'";
		// echo implode(" ", $search);
		$result2 = jp_get($search2);
		while ($row2 = mysqli_fetch_assoc($result2)){
			$list['search'][] = $row2;
		}

		echo json_encode($list);
	}

?>