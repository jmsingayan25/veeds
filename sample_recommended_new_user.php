<?php

	include("jp_library/jp_lib.php");


	$_POST['user_id'] = "276";
	if(isset($_POST['user_id'])){

		$list = array();

		$search['select'] = "user_id_follow";
		$search['table'] = "veeds_users_follow";
		$search['where'] = "user_id = '".$_POST['user_id']."'";

		if(jp_count($search) > 10){
			echo "With following";
		}else if(jp_count($search) < 10){

			// $search1['select'] = "DISTINCT h.place_id, e.location_id, e.place_name, e.location, e.coordinates, e.tags, h.user_id, v.video_id, v.video_name, v.video_file, v.video_thumb, v.date_upload, v.date_expiry, p.total_count";
			// $search1['table'] = "veeds_users_visit_history h, veeds_establishment e, veeds_videos v, veeds_users_place_history p";
			// $search1['where'] = "h.place_id = e.place_id
			// 						AND h.place_id = p.place_id 
			// 						AND h.video_id = v.video_id
			// 						AND h.date_visit IN (SELECT MAX(date_visit)
			// 												FROM veeds_users_visit_history
			// 												GROUP BY place_id)
			// 						AND h.date_visit BETWEEN (NOW() - INTERVAL 7 DAY) AND NOW()
			// 						AND DATE_FORMAT(v.date_expiry,'%Y-%m-%d %H:%i %s') > NOW()";
			// echo implode(" ", $search1);

			// $search1['select'] = "DISTINCT e.place_id, location_id, place_name, location, coordinates, tags, total_count";
			// $search1['table'] = "veeds_establishment e, veeds_users_place_history p";
			// $search1['where'] = "e.place_id = p.place_id";
			// $search1['filters'] = "GROUP BY e.place_id ORDER BY total_count DESC";

			// if(jp_count($search1) > 0){

			// 	$result1 = jp_get($search1);
			// 	while ($row1 = mysqli_fetch_assoc($result1)) {

			// 		$search2['select'] = "user_id, video_id, place_id, date_visit";
			// 		$search2['table'] = "veeds_users_visit_history";
			// 		$search2['where'] = "place_id LIKE '%".$row1['place_id']."%'
			// 								AND date_visit IN (SELECT MAX(date_visit)
			// 												FROM veeds_users_visit_history
			// 												GROUP BY place_id)";

			// 		if(jp_count($search2) > 0){

			// 			$result2 = jp_get($search2);
			// 			while ($row2 = mysqli_fetch_assoc($result2)) {
							
			// 				$search3['select'] = "user_id, video_id, video_name, video_file, video_thumb, date_upload, date_expiry";
			// 				$search3['table'] = "veeds_videos";
			// 				$search3['where'] = "video_id = '".$row2['video_id']."' AND user_id = '".$row2['user_id']."'
			// 										AND DATE_FORMAT(date_expiry,'%Y-%m-%d %H:%i %s') > NOW()";

			// 				if(jp_count($search3) > 0){

			// 					$result3 = jp_get($search3);
			// 					while ($row3 = mysqli_fetch_assoc($result3)) {
									
			// 						$row2['user_id'] = $row3['user_id'];
			// 						$row2['video_id'] = $row3['video_id'];
			// 						$row2['video_name'] = $row3['video_name'];
			// 						$row2['video_file'] = $row3['video_file'];
			// 						$row2['video_thumb'] = $row3['video_thumb'];
			// 						$row2['date_upload'] = $row3['date_upload'];
			// 						$row2['date_expiry'] = $row3['date_expiry'];
			// 					}
			// 				}else{
			// 					$row2['user_id'] = "";
			// 					$row2['video_id'] = "";
			// 					$row2['video_name'] = "";
			// 					$row2['video_file'] = "";
			// 					$row2['video_thumb'] = "";
			// 					$row2['date_upload'] = "";
			// 					$row2['date_expiry'] = "";
			// 				}

			// 				$row2 = array(
			// 								'place_id' => $row2['place_id'],
			// 								'location_id' => $row1['location_id'],
			// 								'place_name' => $row1['place_name'],
			// 								'location' => $row1['location'],
			// 								'coordinates' => $row1['coordinates'],
			// 								'tags' => $row1['tags'],
			// 								'user_id' => $row2['user_id'],
			// 								'video_id' => $row2['video_id'],
			// 								'video_name' => $row2['video_name'],
			// 								'video_file' => $row2['video_file'],
			// 								'video_thumb' => $row2['video_thumb'],
			// 								'date_upload' => $row2['date_upload'],
			// 								'date_expiry' => $row2['date_expiry'],
			// 								'total_count' => (int)$row1['total_count'],
			// 								'logged_id' => $_POST['user_id']
			// 							);

			// 				$list['places'][] = $row2;
			// 			}
			// 		}
			// 	}
			// }

			$search1['select'] = "h.place_id, user_id, video_id, total_count";
			$search1['table'] = "veeds_users_visit_history h, veeds_users_place_history p";
			$search1['where'] = "h.place_id = p.place_id 
									AND h.date_visit IN (SELECT MAX(date_visit)
														FROM veeds_users_visit_history
														GROUP BY place_id)";
			$search1['filters'] = "GROUP BY h.place_id ORDER BY total_count DESC";		
						
			$result1 = jp_get($search1);
			while ($row1 = mysqli_fetch_assoc($result1)) {
				
				$search2['select'] = "location_id, place_name, location, coordinates, tags";
				$search2['table'] = "veeds_establishment";
				$search2['where'] = "place_id = '".$row1['place_id']."'";

				$result2 = jp_get($search2);
				while ($row2 = mysqli_fetch_assoc($result2)) {
					
					$row1['location_id'] = $row2['location_id'];
					$row1['place_name'] = $row2['place_name'];
					$row1['location'] = $row2['location'];
					$row1['coordinates'] = $row2['coordinates'];
					$row1['tags'] = $row2['tags'];
				}

				$search3['select'] = "video_name, video_file, video_thumb, date_upload, date_expiry";
				$search3['table'] = "veeds_videos";
				$search3['where'] = "video_id = '".$row1['video_id']."'";

				$result3 = jp_get($search3);
				while ($row3 = mysqli_fetch_assoc($result3)) {
					
					$row1['video_name'] = $row3['video_name'];
					$row1['video_file'] = $row3['video_file'];
					$row1['video_thumb'] = $row3['video_thumb'];
					$row1['date_upload'] = $row3['date_upload'];
					$row1['date_expiry'] = $row3['date_expiry'];
				}

				$row1 = array(
							'place_id' => $row1['place_id'],
							'location_id' => $row1['location_id'],
							'place_name' => $row1['place_name'],
							'location' => $row1['location'],
							'coordinates' => $row1['coordinates'],
							'tags' => $row1['tags'],
							'user_id' => $row1['user_id'],
							'video_id' => $row1['video_id'],
							'video_name' => $row1['video_name'],
							'video_file' => $row1['video_file'],
							'video_thumb' => $row1['video_thumb'],
							'date_upload' => $row1['date_upload'],
							'date_expiry' => $row1['date_expiry'],
							'total_count' => (int)$row1['total_count'],
							'logged_id' => $_POST['user_id']
							);

				$list['places'][] = $row1;
			}

			$search2['select'] = "u.user_id, u.firstname, u.lastname, u.username, u.personal_information, u.profile_pic, h.place_id, e.location_id";	
			$search2['table'] = "veeds_users u, veeds_establishment e, veeds_users_visit_history h";	
			$search2['where'] = "u.user_id = h.user_id 
									AND h.place_id = e.place_id
									AND date_visit IN (SELECT MAX(date_visit)
														FROM veeds_users_visit_history
														GROUP BY user_id)";	
			$search2['filters'] = "GROUP BY u.user_id";
		
			if(jp_count($search2) > 0){

				$result2 = jp_get($search2);
				while ($row2 = mysqli_fetch_assoc($result2)) {
					$list['users'][] = $row2;
				}
			}
		}

		echo json_encode($list);
	}


?>