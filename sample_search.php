<?php
/*

	Search users, hashtags and places details related to the keyword  

*/
	include("jp_library/jp_lib.php");

	if(empty($_POST['count']) || !isset($_POST['count'])){
		$_POST['count'] = 0;
	}

	$_POST['user_id'] = "276";
	$_POST['keyword'] = "testuser";
	$_POST['coordinates'] = "14.590843,121.126404";
	if(isset($_POST['user_id']) && isset($_POST['keyword'])){
		
		$list = array();
		$u_blocks = array();
		$list['users'] = array();
		
		$search['select'] = "user_id_follow";
		$search['table'] = "veeds_users_follow";
		$search['where'] = "user_id = '".$_POST['user_id']."'";

		if(jp_count($search) > 0) {
			echo "With following";
		}else if(jp_count($search) < 10){
			
			$start = $_POST['count'] * 10;
			$search1['select'] = "DISTINCT user_id, firstname, lastname, username, personal_information, profile_pic";
			$search1['table'] = "veeds_users";
			$search1['where'] = "user_id != '".$_POST['user_id']."'
								AND (firstname LIKE '%".$_POST['keyword']."%' 
								OR lastname LIKE '%".$_POST['keyword']."%'
								OR username LIKE '%".$_POST['keyword']."%')";
			$search1['filters'] = "LIMIT ".$start.", 10";
			if(jp_count($search1)){

				$result1 = jp_get($search1);
				while ($row1 = mysqli_fetch_assoc($result1)) {
					
					$search2['select'] = "user_id";
					$search2['table'] = "veeds_users_follow";
					$search2['where'] = "user_id = '".$_POST['user_id']."' AND user_id_follow = '".$row1['user_id']."' AND approved = 1";
					$count2 = jp_count($search2);
					if($count2 > 0){
						$row1['followed'] = 1;
					}else{
						$search2['where'] = "user_id = '".$_POST['user_id']."' AND user_id_follow = '".$row1['user_id']."' AND approved = 0";
						$count2 = jp_count($search2);
						if($count2 > 0){
							$row1['followed'] = 2;
						}else{
							$row1['followed'] = 0;
						}
					}

					$search3['select'] = "user_id";
					$search3['table'] = "veeds_users_block";
					// $search3['where'] = "user_id = '".$_POST['user_id']."' AND user_id_block = '".$row10['user_id']."'"; 
					$search3['where'] = "user_id = '".$_POST['user_id']."' AND user_id_block = '".$row1['user_id']."'"; 
					$count3 = (int)jp_count($search3);
					if($count3 > 0){
						$row1['blocked'] = true;
					}else{
						$row1['blocked'] = false;
					}

					$row1['logged_id'] = $_POST['user_id'];

					// if(!in_array($row1, $list['users'])){
						// $list['users'][] = $row1;	
					// } 
				}
			}

			$hostname = "https://maps.googleapis.com/maps/api/place/textsearch/json?query=".str_replace(" ", "+", $_POST['keyword'])."&location=".$_POST['coordinates']."&radius=500&pagetoken=5&key=AIzaSyDVdJtIAvhmHE7e2zoxA_Y9qWRpp6eE2o8"; 

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $hostname);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			$response = curl_exec($ch);
			curl_close($ch);
			$response_a = json_decode($response);
			
			for ($i=0; $i < count($response_a->results); $i++) { 

				$coor['lat'] = $response_a->results[$i]->geometry->location->lat;
				$coor['lng'] = $response_a->results[$i]->geometry->location->lng;
				$coor['tags'] = $response_a->results[$i]->types;

				$place['place_id'] = $response_a->results[$i]->place_id;
				$place['place_name'] = $response_a->results[$i]->name;
				$place['address'] = $response_a->results[$i]->formatted_address;			
				$place['tags'] = str_replace(",point_of_interest,establishment", "", implode(",", $coor['tags']));
				$place['coordinates'] = $coor['lat'].",".$coor['lng'];
				$place['logged_id'] = $_POST['user_id'];

				// $list['places'][] = $place;
			}

			$start = $_POST['count'] * 10;
			$search4['select'] = "DISTINCT e.place_id, e.place_name, e.location as address, e.tags, e.coordinates";
			$search4['table'] = "veeds_establishment e, veeds_users_visit_history h, veeds_users u";
			$search4['where'] = "h.user_id = u.user_id
									AND h.place_id = e.place_id
									AND (place_name LIKE '%".$_POST['keyword']."%' 
									OR location LIKE '%".$_POST['keyword']."%'
									OR username LIKE '%".$_POST['keyword']."%'
									OR place_name SOUNDS LIKE '%".$_POST['keyword']."%' 
									OR location SOUNDS LIKE '%".$_POST['keyword']."%'
									OR username SOUNDS LIKE '%".$_POST['keyword']."%')";
			$search4['filters'] = "LIMIT ".$start.", 10";
			if(jp_count($search4) > 0){
				$result4 = jp_get($search4);
				while ($row4 = mysqli_fetch_assoc($result4)) {

					$row4['logged_id'] = $_POST['user_id'];

					// $list['places'][] = $row4; 
				}
			}

			$start = $_POST['count'] * 5;
			$search7['select'] = "DISTINCT u.user_id, firstname, lastname, username, personal_information, profile_pic, hashtags, video_id";
			$search7['table'] = "veeds_users_visit_history h, veeds_users u";
			$search7['where'] = "u.user_id = h.user_id
									AND hashtags LIKE '%".$_POST['keyword']."%'";
			$search7['filters'] = "LIMIT ".$start.", 5";
			// echo implode(" ", $search7);
			if(jp_count($search7) > 0){

				// $list['hashtags'] = array();
				$result7 = jp_get($search7);
				while ($row7 = mysqli_fetch_assoc($result7)) {

					$search8['select'] = "DISTINCT video_name, description, video_file, video_thumb, date_upload, date_expiry, view_count, video_length, landscape_file";
					$search8['table'] = "veeds_videos";
					$search8['where'] = "video_id = '".$row7['video_id']."'";
					// $search8['where'] = "video_id = '".$row2['video_id']."'
					// 					 AND DATE_FORMAT(date_expiry,'%Y-%m-%d %H:%i %s') > NOW()";
					// echo implode(" ", $search8);
					if(jp_count($search8) > 0){

						$result8 = jp_get($search8);
						while ($row8 = mysqli_fetch_assoc($result8)) {

							$search12['select'] = "user_id";
							$search12['table'] = "veeds_users_follow";
							$search12['where'] = "user_id = '".$_POST['user_id']."' AND user_id_follow = '".$row7['user_id']."' AND approved = 1";
							$count12 = jp_count($search12);
							if($count12 > 0){
								$row7['followed'] = 1;
							}else{
								$search12['where'] = "user_id = '".$_POST['user_id']."' AND user_id_follow = '".$row7['user_id']."' AND approved = 0";
								$count12 = jp_count($search12);
								if($count12 > 0){
									$row7['followed'] = 2;
								}else{
									$row7['followed'] = 0;
								}
							}

							$search13['select'] = "user_id";
							$search13['table'] = "veeds_users_block";
							// $search2['where'] = "user_id = '".$_POST['user_id']."' AND user_id_block = '".$row10['user_id']."'"; 
							$search13['where'] = "user_id = '".$_POST['user_id']."' AND user_id_block = '".$row['user_id']."'"; 
							$count13 = (int)jp_count($search13);
							if($count13 > 0){
								$row7['blocked'] = true;
							}else{
								$row7['blocked'] = false;
							}
							
							$row7['video_name'] = $row8['video_name'];
							$row7['description'] = $row8['description'];
							$row7['video_file'] = $row8['video_file'];
							$row7['video_thumb'] = $row8['video_thumb'];
							$row7['date_upload'] = $row8['date_upload'];
							$row7['date_expiry'] = $row8['date_expiry'];
							$row7['view_count'] = (int)$row8['view_count'];
							$row7['video_length'] = (int)$row8['video_length'];
							$row7['landscape_file'] = $row8['landscape_file'];
							$row7['logged_id'] = $_POST['user_id'];

							$list['hashtags'][] = $row7;
						}
					}
				}
			}

			$start = $_POST['count'] * 5;
			$search9['select'] = "DISTINCT user_id, firstname, lastname, username, personal_information, profile_pic";
			$search9['table'] = "veeds_users";
			$search9['where'] = "username LIKE '%".$_POST['keyword']."%' 
									OR username SOUNDS LIKE '%".$_POST['keyword']."%'";
			$search9['filters'] = "LIMIT ".$start.", 5";
			// echo implode(" ", $search9);
			if(jp_count($search9) > 0){

				// $list['hashtags'] = array();
				$result9 = jp_get($search9);
				while ($row9 = mysqli_fetch_assoc($result9)) {

					$search10['select'] = "hashtags, video_id";
					$search10['table'] = "veeds_users_visit_history";
					$search10['where'] = "user_id = '".$row9['user_id']."' AND hashtags != ''";
					// echo implode(" ", $search10);
					if(jp_count($search10) > 0){

						$result10 = jp_get($search10);
						while ($row10 = mysqli_fetch_assoc($result10)) {
							
							$search11['select'] = "DISTINCT video_name, description, video_file, video_thumb, date_upload, date_expiry, view_count, video_length, landscape_file";
							$search11['table'] = "veeds_videos";
							$search11['where'] = "video_id = '".$row10['video_id']."'";
							// $search11['where'] = "video_id = '".$row10['video_id']."'
							// 					 AND DATE_FORMAT(date_expiry,'%Y-%m-%d %H:%i %s') > NOW()";
							// echo implode(" ", $search11);
							if (jp_count($search11) > 0) {
								
								$result11 = jp_get($search11);
								while ($row11 = mysqli_fetch_assoc($result11)) {
									
									$search12['select'] = "user_id";
									$search12['table'] = "veeds_users_follow";
									$search12['where'] = "user_id = '".$_POST['user_id']."' AND user_id_follow = '".$row9['user_id']."' AND approved = 1";
									$count12 = jp_count($search12);
									if($count12 > 0){
										$row9['followed'] = 1;
									}else{
										$search12['where'] = "user_id = '".$_POST['user_id']."' AND user_id_follow = '".$row9['user_id']."' AND approved = 0";
										$count12 = jp_count($search12);
										if($count12 > 0){
											$row9['followed'] = 2;
										}else{
											$row9['followed'] = 0;
										}
									}

									$search13['select'] = "user_id";
									$search13['table'] = "veeds_users_block";
									// $search2['where'] = "user_id = '".$_POST['user_id']."' AND user_id_block = '".$row10['user_id']."'"; 
									$search13['where'] = "user_id = '".$_POST['user_id']."' AND user_id_block = '".$row9['user_id']."'"; 
									$count13 = (int)jp_count($search13);
									if($count13 > 0){
										$row9['blocked'] = true;
									}else{
										$row9['blocked'] = false;
									}

									$row9['hashtags'] = $row10['hashtags'];
									$row9['video_id'] = $row10['video_id'];
									$row9['video_name'] = $row11['video_name'];
									$row9['description'] = $row11['description'];
									$row9['video_file'] = $row11['video_file'];
									$row9['video_thumb'] = $row11['video_thumb'];
									$row9['date_upload'] = $row11['date_upload'];
									$row9['date_expiry'] = $row11['date_expiry'];
									$row9['view_count'] = (int)$row11['view_count'];
									$row9['video_length'] = (int)$row11['video_length'];
									$row9['landscape_file'] = $row11['landscape_file'];
									$row9['logged_id'] = $_POST['user_id'];

									$list['hashtags'][] = $row9;
								}
							}
						}
					}
				}
			}
		}
		echo json_encode($list);
	}
?>