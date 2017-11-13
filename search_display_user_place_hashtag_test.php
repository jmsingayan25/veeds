<?php

	// Search users, hashtags and places based on the keyword 

	include("jp_library/jp_lib.php");

	if(empty($_POST['count']) || !isset($_POST['count'])){
		$_POST['count'] = 0;
	}

	$_POST['user_id'] = "182";
	$_POST['keyword'] = "chicken";
	$_POST['coordinates'] = "14.590843,121.126404";
	if(isset($_POST['user_id']) && isset($_POST['keyword'])){
		
		$list = array();
		$u_blocks = array();
		$list['places'] = array();
		$list['users'] = array();

		$block['select'] = "user_id";
		$block['table'] = "veeds_users_block";
		$block['where'] = "user_id_block = ".$_POST['user_id'];
		$result_block = jp_get($block);
		while($row3 = mysqli_fetch_assoc($result_block)){
			$u_blocks[] = $row3['user_id'];
		}

		if(count($u_blocks) > 0){
			$u_extend = " AND user_id NOT IN (".implode(",", $u_blocks).")";
		}else{
			$u_extend = "";
		}

		// $search['select'] = "DISTINCT u.user_id, u.firstname, u.lastname, u.username, u.personal_information, u.profile_pic";
		// $search['table'] = "veeds_users u, veeds_users_visit_history h, veeds_establishment e";
		// $search['where'] = "u.user_id = h.user_id
		// 					AND e.place_id = h.place_id
		// 					AND u.user_id != '".$_POST['user_id']."'
							// AND (LOWER(u.firstname) LIKE '%".strtolower($_POST['keyword'])."%' 
							// OR LOWER(u.lastname) LIKE '%".strtolower($_POST['keyword'])."%'
							// OR LOWER(u.username) LIKE '%".strtolower($_POST['keyword'])."%'
		// 					OR LOWER(h.hashtags) LIKE '%".strtolower($_POST['keyword'])."%'
		// 					OR LOWER(e.place_name) LIKE '%".strtolower($_POST['keyword'])."%')".$u_extend;

		$search['select'] = "DISTINCT user_id, firstname, lastname, username, personal_information, profile_pic";
		$search['table'] = "veeds_users";
		$search['where'] = "user_id != '".$_POST['user_id']."'
							AND (firstname LIKE '%".$_POST['keyword']."%' 
							OR lastname LIKE '%".$_POST['keyword']."%'
							OR username LIKE '%".$_POST['keyword']."%')".$u_extend;
		// echo implode(" ", $search);
		if(jp_count($search) > 0){
			$result = jp_get($search);
			while ($row = mysqli_fetch_assoc($result)){

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

				$row['logged_id'] = $_POST['user_id'];

				if(!in_array($row, $list['users'])){
					$list['users'][] = $row;	
				} 
			}
		}

		// Return user based on place where user post or hashtag used by the user
		$search3['select'] = "DISTINCT u.user_id, u.firstname, u.lastname, u.username, u.personal_information, u.profile_pic";
		$search3['table'] = "veeds_users u, veeds_users_visit_history h, veeds_establishment e";
		$search3['where'] = "u.user_id = h.user_id
							AND e.place_id = h.place_id
							AND u.user_id != '".$_POST['user_id']."'
							AND (LOWER(h.hashtags) LIKE '%".strtolower($_POST['keyword'])."%'
							OR LOWER(e.place_name) LIKE '%".strtolower($_POST['keyword'])."%')".$u_extend;

		// echo implode(" ", $search);
		if(jp_count($search3) > 0){
			$result3 = jp_get($search3);
			while ($row3 = mysqli_fetch_assoc($result3)) {

				$search4['select'] = "user_id";
				$search4['table'] = "veeds_users_follow";
				$search4['where'] = "user_id = '".$_POST['user_id']."' AND user_id_follow = '".$row3['user_id']."' AND approved = 1";
				$count4 = jp_count($search4);
				if($count4 > 0){
					$row3['followed'] = 1;
				}else{
					$search4['where'] = "user_id = '".$_POST['user_id']."' AND user_id_follow = '".$row3['user_id']."' AND approved = 0";
					$count4 = jp_count($search4);
					if($count4 > 0){
						$row3['followed'] = 2;
					}else{
						$row3['followed'] = 0;
					}
				}

				$search5['select'] = "user_id";
				$search5['table'] = "veeds_users_block";
				// $search2['where'] = "user_id = '".$_POST['user_id']."' AND user_id_block = '".$row10['user_id']."'"; 
				$search5['where'] = "user_id = '".$_POST['user_id']."' AND user_id_block = '".$row3['user_id']."'"; 
				$count5 = (int)jp_count($search5);
				if($count5 > 0){
					$row3['blocked'] = true;
				}else{
					$row3['blocked'] = false;
				}

				$row3['logged_id'] = $_POST['user_id'];
				if(!in_array($row3, $list['users'])){
					$list['users'][] = $row3;	
				}
			}
		}
	
		$hostname = "https://maps.googleapis.com/maps/api/place/textsearch/json?query=".str_replace(" ", "+", $_POST['keyword'])."&location=".$_POST['coordinates']."&radius=1000&key=AIzaSyDVdJtIAvhmHE7e2zoxA_Y9qWRpp6eE2o8"; 
		// echo $hostname;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $hostname);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		$place_response = curl_exec($ch);
		curl_close($ch);
		$place_response_a = json_decode($place_response);
		// echo json_encode($place_response_a);
		for ($i=0; $i < count($place_response_a->results); $i++) { 
			$coor['lat'] = $place_response_a->results[$i]->geometry->location->lat;
			$coor['lng'] = $place_response_a->results[$i]->geometry->location->lng;
			$coor['tags'] = $place_response_a->results[$i]->types;
			// for ($j=0; $j < count($place_response_a->results->photo); $j++) { 
			// 	$coor['reference'] = $place_response_a->results[$i]->photos[$j]->photo_reference;
			// }
			// $coor['reference'] = $place_response_a->results[$i]->photos->photo_reference;	

			$place['place_id'] = $place_response_a->results[$i]->place_id;
			$place['place_name'] = $place_response_a->results[$i]->name;
			$place['address'] = $place_response_a->results[$i]->formatted_address;
			$place['tags'] = str_replace(",point_of_interest,establishment", "", implode(",", $coor['tags']));
			$place['coordinates'] = $coor['lat'].",".$coor['lng'];
			$place['logged_id'] = $_POST['user_id'];
			// $place['photos'] = $place_response_a->results[$i]->photo_reference;	
	
			// $photoname = "https://maps.googleapis.com/maps/api/place/photo?photoreference=".$coor['reference']."&maxheight=400&key=AIzaSyDVdJtIAvhmHE7e2zoxA_Y9qWRpp6eE2o8"; 

			// $place['photo_url'] = $photoname;
			// // echo $photoname;
			// $ch = curl_init();
			// curl_setopt($ch, CURLOPT_URL, $photoname);
			// curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			// curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
			// curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			// curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			// $photo_response = curl_exec($ch);
			// curl_close($ch);
			// $photo_response_a = json_decode($photo_response);

			// $place['html_attributions'] = $photo_response_a->results[$i]->photos->html_attributions;
			// $place['height'] = $photo_response_a->results[$i]->photos->height;
			// $place['width'] = $photo_response_a->results[$i]->photos->width;
			// $place['photo_reference'] = $photo_response_a->results[$i]->photos->photo_reference;

			$list['places'][] = $place;
		}

		// $search1['select'] = "DISTINCT place_id, place_name, location as address, tags, coordinates";
		// $search1['table'] = "veeds_establishment";
		// $search1['where'] = "place_name LIKE '%".$_POST['keyword']."%' 
		// 						OR location LIKE '%".$_POST['keyword']."%'";

		// Return places based on searched place name, username or address
		$search6['select'] = "DISTINCT e.place_id, e.place_name, e.location as address, e.tags, e.coordinates";
		$search6['table'] = "veeds_establishment e, veeds_users_visit_history h, veeds_users u";
		$search6['where'] = "h.user_id = u.user_id
								AND h.place_id = e.place_id
								AND (place_name LIKE '%".$_POST['keyword']."%' 
								OR location LIKE '%".$_POST['keyword']."%'
								OR username LIKE '%".$_POST['keyword']."%')";

		if(jp_count($search6) > 0){
			$result6 = jp_get($search6);
			while ($row6 = mysqli_fetch_assoc($result6)) {

				// $search2['select'] = "video_id, video_name, description, video_file, video_thumb, date_upload, date_expiry, view_count";
				// $search2['table'] = "veeds_videos";
				// $search2['where'] = "place_id = '".$row1['place_id']."'";

				// $result2 = jp_get($search2);
				// $row2 = mysqli_fetch_assoc($result2);

				// $row1['video_id'] = $row2['video_id'];
				// $row1['video_name'] = $row2['video_name'];
				// $row1['description'] = $row2['description'];
				// $row1['video_file'] = $row2['video_file'];
				// $row1['video_thumb'] = $row2['video_thumb'];
				// $row1['date_upload'] = $row2['date_upload'];
				// $row1['date_expiry'] = $row2['date_expiry'];
				// $row1['view_count'] = $row2['view_count'];
				$row6['logged_id'] = $_POST['user_id'];
				if(!in_array($row6, $list['places']))
					$list['places'][] = $row6; 
			}
		}
		
		// $search2['select'] = "DISTINCT hashtag";
		// $search2['table'] = "veeds_hashtag";
		// $search2['where'] = "hashtag LIKE '%".$_POST['keyword']."%'";
		// // echo implode(" ", $search2);
		// if(jp_count($search2) > 0){
		// 	$result2 = jp_get($search2);
		// 	while ($row2 = mysqli_fetch_assoc($result2)) {

		// 		$search3['select'] = "DISTINCT video_id";
		// 		$search3['table'] = "veeds_users_visit_history";
		// 		$search3['where'] = "hashtags LIKE '%".$row2['hashtag']."%'";
		// 		// echo implode(" ", $search3);
		// 		if(jp_count($search3) > 0){
		// 			$result3 = jp_get($search3);
		// 			while ($row3 = mysqli_fetch_assoc($result3)) {

		// 				$search4['select'] = "DISTINCT video_name, description, video_file, video_thumb, date_upload, date_expiry, view_count";
		// 				$search4['table'] = "veeds_videos";
		// 				$search4['where'] = "video_id = '".$row3['video_id']."'";
		// 				// $search4['where'] = "video_id = '".$row3['video_id']."'
		// 				// 					 AND DATE_FORMAT(date_expiry,'%Y-%m-%d %H:%i %s') > NOW()";
		// 				// echo implode(" ", $search4)."<br>";
		// 				if(jp_count($search4) > 0){
		// 					$result4 = jp_get($search4);
		// 					$row4 = mysqli_fetch_assoc($result4);

		// 					$row3['video_name'] = $row4['video_name'];
		// 					$row3['description'] = $row4['description'];
		// 					$row3['video_file'] = $row4['video_file'];
		// 					$row3['video_thumb'] = $row4['video_thumb'];
		// 					$row3['date_upload'] = $row4['date_upload'];
		// 					$row3['date_expiry'] = $row4['date_expiry'];
		// 					$row3['view_count'] = (int)$row4['view_count'];	
		// 				}
		// 				if(!in_array($row3, $list['hashtags']))
		// 					$list['hashtags'][] = $row3; 
		// 			}
		// 		}
		// 	}
		// }

		// $search2['select'] = "video_id";
		// $search2['table'] = "veeds_users_visit_history";
		// $search2['where'] = "hashtags LIKE '%".$_POST['keyword']."%'";
		// // echo implode(" ", $search2);
		// if(jp_count($search2) > 0){

		// 	$list['hashtags'] = array();
		// 	$result2 = jp_get($search2);
		// 	while ($row2 = mysqli_fetch_assoc($result2)) {

		// 		$search3['select'] = "DISTINCT video_name, description, video_file, video_thumb, date_upload, date_expiry, view_count";
		// 		$search3['table'] = "veeds_videos";
		// 		// $search3['where'] = "video_id = '".$row2['video_id']."'";
		// 		$search3['where'] = "video_id = '".$row2['video_id']."' AND DATE_FORMAT(date_expiry,'%Y-%m-%d %H:%i %s') > NOW()";
		// 		// echo implode(" ", $search3);
		// 		if(jp_count($search3) > 0){
		// 			$result3 = jp_get($search3);
		// 			while ($row3 = mysqli_fetch_assoc($result3)) {
		// 				$row2['video_name'] = $row3['video_name'];
		// 				$row2['description'] = $row3['description'];
		// 				$row2['video_file'] = $row3['video_file'];
		// 				$row2['video_thumb'] = $row3['video_thumb'];
		// 				$row2['date_upload'] = $row3['date_upload'];
		// 				$row2['date_expiry'] = $row3['date_expiry'];
		// 				$row2['view_count'] = (int)$row3['view_count'];	
		// 			}
		// 		}
		// 		if(!in_array($row2, $list['hashtags']))
		// 			$list['hashtags'][] = $row2; 
		// 	}
		// }

		// $search2['select'] = "DISTINCT hashtags, video_id";
		// $search2['table'] = "veeds_users_visit_history";
		// $search2['where'] = "hashtags LIKE '%".$_POST['keyword']."%'";
		// // echo implode(" ", $search2);
		// if(jp_count($search2) > 0){

		// 	$list['hashtags'] = array();
		// 	$result2 = jp_get($search2);
		// 	while ($row2 = mysqli_fetch_assoc($result2)) {

		// 		$search3['select'] = "DISTINCT video_name, description, video_file, video_thumb, date_upload, date_expiry, view_count";
		// 		$search3['table'] = "veeds_videos";
		// 		// $search3['where'] = "video_id = '".$row2['video_id']."'";
		// 		$search3['where'] = "video_id = '".$row2['video_id']."'
		// 							 AND DATE_FORMAT(date_expiry,'%Y-%m-%d %H:%i %s') > NOW()";

		// 		if(jp_count($search3) > 0){

		// 			$result3 = jp_get($search3);
		// 			$row3 = mysqli_fetch_assoc($result3);

		// 			$row2['video_name'] = $row3['video_name'];
		// 			$row2['description'] = $row3['description'];
		// 			$row2['video_file'] = $row3['video_file'];
		// 			$row2['video_thumb'] = $row3['video_thumb'];
		// 			$row2['date_upload'] = $row3['date_upload'];
		// 			$row2['date_expiry'] = $row3['date_expiry'];
		// 			$row2['view_count'] = (int)$row3['view_count'];
		// 			$row2['logged_id'] = $_POST['user_id'];
		// 		}
		// 		if(!in_array($row2, $list['hashtags']))
		// 			$list['hashtags'][] = $row2; 
		// 	}
		// }

		// Return hashtag based on searched username
		$search7['select'] = "user_id";
		$search7['table'] = "veeds_users";
		$search7['where'] = "username LIKE '%".$_POST['keyword']."%'";

		if(jp_count($search7) > 0){

			$result7 = jp_get($search7);
			while ($row7 = mysqli_fetch_assoc($result7)) {
			
				$search8['select'] = "hashtags";
				$search8['table'] = "veeds_users_visit_history";
				$search8['where'] = "user_id = '".$row7['user_id']."' AND hashtags != ''";
				$search8['filters'] = "ORDER BY date_visit DESC";

				if(jp_count($search8) > 0){

					$result8 = jp_get($search8);
					while ($row8 = mysqli_fetch_assoc($result8)) {
		
						$row7['hashtags'] = $row8['hashtags'];	
						$row7['logged_id'] = $_POST['user_id'];

						$list['hashtags'][] = $row7;
					}
				}
			}
		}

		// Return hashtag based on searched hashtag
		$search9['select'] = "user_id, hashtag";
		$search9['table'] = "veeds_hashtag";
		$search9['where'] = "hashtag LIKE '%".$_POST['keyword']."%' AND hashtag != ''";

		if(jp_count($search9) > 0){

			$result9 = jp_get($search9);
			while ($row9 = mysqli_fetch_assoc($result9)) {
				
				$row9['logged_id'] = $_POST['user_id'];

				$list['hashtags'][] = $row9;
			}
		}
		
		echo json_encode($list);
	}
?>