<?php

	include("jp_library/jp_lib.php");
	include("class_place.php");

	$_POST['keyword'] = "Quezon, Metro Manila, Philippines";
	$_POST['user_id'] = "183";


	$placeObj = new classPlaceID();
	$list = array();
	$array['place_id'] = array();

	// sleep(2);
	// $hostname = "https://maps.googleapis.com/maps/api/place/textsearch/json?query=".str_replace(" ", "+", $_POST['keyword'])."&key=AIzaSyBLYHLLfWNbEdHvRJKdArowB8tjmFb1xkE"; 
	// // $hostname = "https://maps.googleapis.com/maps/api/place/details/json?placeid=".str_replace(" ", "+", $row['place_id'])."&key=AIzaSyBLYHLLfWNbEdHvRJKdArowB8tjmFb1xkE";

	// echo $hostname."<br>";
	// $ch = curl_init();
	// curl_setopt($ch, CURLOPT_URL, $hostname);
	// curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	// curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
	// curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	// curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	// $response = curl_exec($ch);
	// curl_close($ch);
	// $response_a = json_decode($response);

	// if(count($response_a->results) > 0){

	// 	for ($i=0; $i < count($response_a->results); $i++) { 

	// 		$place['place_id'] = $response_a->results[$i]->place_id;
	// 		$place['place_name'] = $response_a->results[$i]->name;
	// 		$place['address'] = $response_a->results[$i]->formatted_address;

	// 		$search['select'] = "h.place_id, h.video_id, h.user_id, hashtags, date_visit, firstname, lastname, username, profile_pic, video_name, description, v.video_file, video_thumb, date_upload, date_expiry, view_count, like_count, location, video_length, landscape_file";
	// 		$search['table'] = "veeds_users_visit_history h, veeds_users u, veeds_videos v";
	// 		$search['where'] = "h.video_id = v.video_id AND h.user_id = u.user_id AND h.place_id = '".$place['place_id']."'";
	// 		// echo implode(" ", $search);
	// 		if(jp_count($search) > 0){

	// 			$result = jp_get($search);
	// 			while ($row = mysqli_fetch_assoc($result)) {
					
	// 				$row['view_count'] = (int)$row['view_count'];
	// 				$row['like_count'] = (int)$row['like_count'];
	// 				$row['video_length'] = (int)$row['video_length'];
	// 				$row['logged_id'] = $_POST['user_id'];
	// 				$list['places'][] = $row;
	// 			}
	// 		}
	// 	}
	// }

	// $search['select'] = "DISTINCT place_id";
	// $search['table'] = "veeds_users_visit_history";
	// // $search['table'] = "veeds_establishment";
	// $search['where'] = "place_id != ''";
	// // $search['filters'] = "LIMIT 5";

	// $result = jp_get($search);
	// while ($row = mysqli_fetch_assoc($result)) {

	// 	$placeObj->setPlaceId($row['place_id']);
	// 	$placeId = $placeObj->getPlaceId();
	// 	$placeAddress = $placeObj->getPlaceAddress();

	// 	$explode_city = explode(",", $_POST['keyword']); // e.g. output: ['Quezon City', 'Metro Manila', 'Philippines']
	// 	for($i = 0; $i < count($explode_city); $i++){

	// 		if(strpos($placeAddress, $explode_city[$i]) !== FALSE){
	// 			// echo $placeAddress."<br>";
	// 			// echo $place['name']." ".$place['address']." ".$place['place_id']."<br>";

	// 			$search1['select'] = "h.place_id, h.video_id, h.user_id, hashtags, date_visit, firstname, lastname, username, profile_pic, video_name, description, v.video_file, video_thumb, date_upload, date_expiry, view_count, like_count, location, video_length, landscape_file";
	// 			$search1['table'] = "veeds_users_visit_history h, veeds_users u, veeds_videos v";
	// 			$search1['where'] = "h.video_id = v.video_id AND h.user_id = u.user_id AND h.place_id = '".$placeId."'";
	// 			// echo implode(" ", $search);
	// 			if(jp_count($search1) > 0){

	// 				$result1 = jp_get($search1);
	// 				while ($row1 = mysqli_fetch_assoc($result1)) {
						
	// 					// $row1['view_count'] = (int)$row1['view_count'];
	// 					// $row1['like_count'] = (int)$row1['like_count'];
	// 					// $row1['video_length'] = (int)$row1['video_length'];
	// 					// $row1['logged_id'] = $_POST['user_id'];

	// 					$search2['select'] = "like_type";
	// 					$search2['table'] = "veeds_videos_likes";
	// 					$search2['where'] = "video_id = '".$row1['video_id']."' 
	// 											AND user_id = '".$row1['user_id']."'
	// 											AND user_id_liker = '".$_POST['user_id']."'";

	// 					// echo implode(" ", $search2)."<br>";
	// 					if(jp_count($search2) > 0){

	// 						$result2 = jp_get($search2);
	// 						while ($row2 = mysqli_fetch_assoc($result2)) {
								
	// 							$row2['like_type'] = $row2['like_type'];
	// 						}
	// 					}else{
	// 						$row2['like_type'] = "";
	// 					}
						
	// 					$row2 = array(
	// 									'category' => "Places",
	// 									'place_id' => $row1['place_id'],
	// 									'place_name' => $placeAddress,
	// 									'user_id' => $row1['user_id'],
	// 									'firstname' => $row1['firstname'],
	// 									'lastname' => $row1['lastname'],
	// 									'username' => $row1['username'],
	// 									'profile_pic' => $row1['profile_pic'],
	// 									'video_id' => $row1['video_id'],
	// 									'video_name' => $row1['video_name'],
	// 									'description' => $row1['description'],
	// 									'video_file' => $row1['video_file'],
	// 									'video_thumb' => $row1['video_thumb'],
	// 									'date_upload' => $row1['date_upload'],
	// 									'date_expiry' => $row1['date_expiry'],
	// 									'view_count' => (int)$row1['view_count'],
	// 									'like_count' => (int)$row1['like_count'],
	// 									'location' => $row1['location'],
	// 									'video_length' => (int)$row1['video_length'],
	// 									'landscape_file' => $row1['landscape_file'],
	// 									'like_type' => $row2['like_type'],
	// 									'logged_id' => $_POST['user_id']
	// 								);
						
	// 					$list['places'][] = $row2;
	// 				}
	// 			}
	// 		}
	// 	}
	// }

	$explode_city = explode(",", $_POST['keyword']);
	for($i = 0; $i < count($explode_city); $i++){
		
		if(strpos($explode_city[$i], 'City') !== FALSE){
			$explode_city[$i] = str_replace(" City", "", $explode_city[$i]);
		}
		// echo $explode_city[$i]."<br>";
		$search['select'] = "DISTINCT v.place_id, v.location as address, v.coordinates, firstname, lastname, username, profile_pic, video_id, video_name, description, v.video_file, video_thumb, date_upload, date_expiry, view_count, like_count, video_length, landscape_file, v.user_id";
		$search['table'] = "veeds_establishment e, veeds_users u, veeds_videos v";
		$search['where'] = "v.place_id = e.place_id 
							AND v.user_id = u.user_id
							AND v.location LIKE '%".$explode_city[$i]."%' OR place_name LIKE '%".$explode_city[$i]."%'";
		$search['filters'] = "GROUP BY v.place_id ORDER BY v.date_upload DESC";
		// echo implode(" ", $search);
		if(jp_count($search) > 0){

			$result = jp_get($search);
			while ($row = mysqli_fetch_assoc($result)) {
				if (!in_array($row['place_id'], $array['place_id'])) {
					$array['place_id'][] = $row['place_id'];

					$search1['select'] = "like_type";
					$search1['table'] = "veeds_videos_likes";
					$search1['where'] = "video_id = '".$row['video_id']."' 
											AND user_id = '".$row['user_id']."'
											AND user_id_liker = '".$_POST['user_id']."'";

					// echo implode(" ", $search1)."<br>";
					if(jp_count($search1) > 0){

						$result1 = jp_get($search1);
						while ($row1 = mysqli_fetch_assoc($result1)) {
							
							$row['like_type'] = $row1['like_type'];
						}
					}else{
						$row['like_type'] = "";
					}

						// $placeObj->setPlaceId($row['place_id']);
						// $placeName = $placeObj->getPlaceName();
					
					$row = array(
									'category' => "Places",
									'place_id' => $row['place_id'],
									// 'place_name' => $placeName,
									'user_id' => $row['user_id'],
									'firstname' => $row['firstname'],
									'lastname' => $row['lastname'],
									'username' => $row['username'],
									'profile_pic' => $row['profile_pic'],
									'video_id' => $row['video_id'],
									'video_name' => $row['video_name'],
									'description' => $row['description'],
									'video_file' => $row['video_file'],
									'video_thumb' => $row['video_thumb'],
									'date_upload' => $row['date_upload'],
									'date_expiry' => $row['date_expiry'],
									'view_count' => (int)$row['view_count'],
									'like_count' => (int)$row['like_count'],
									'location' => $row['address'],
									'video_length' => (int)$row['video_length'],
									'landscape_file' => $row['landscape_file'],
									'like_type' => $row['like_type'],
									'logged_id' => $_POST['user_id']
								);

					$list[] = $row;	
				}
			}
		}	
	}
		
	echo json_encode($list);
		

		// $place['name'] = $response_a->result->name; 
		// $place['address'] = $response_a->result->formatted_address;
		// $place['place_id'] =  $response_a->result->place_id;

		// // echo $placeName." ".$placdAddress." ".$placeId."<br>";
		// $list['place'][] = $place;

		// $explode_city = explode(",", $_POST['keyword']);
		// for($i = 0; $i < count($explode_city); $i++){

		// 	// $place['name'] = $response_a->result->name; 
		// 	// $place['address'] = $response_a->result->formatted_address;
		// 	// $place['place_id'] =  $response_a->result->place_id;
		// 	if(strpos($explode_city[$i], ' City') !== FALSE){
		// 		$explode_city[$i] = str_replace(" City", "", $explode_city[$i]);
		// 	}

			// if(strpos($explode_city[$i], ' Metro Manila') !== FALSE){
			// 	$explode_city[$i] = str_replace(" Metro Manila", "", $explode_city[$i]);
			// }
			// if(strpos($explode_city[$i], ' Philippines') !== FALSE){
			// 	$explode_city[$i] = str_replace(" Philippines", "", $explode_city[$i]);
			// }

			// echo $explode_city[$i]."<br>";

			// if($explode_city[$i] != ''){
					
				// $search['select'] = "place_id, video_id, user_id, hashtags, date_visit";
				// $search['table'] = "veeds_users_visit_history";
				// $search['where'] = "place_id = '".$placeId."'";

				// echo implode(" ", $search);

			// }
			

			// if(strpos($placeAddress, $explode_city[$i]) !== FALSE){
			// 	// $explode_city[$i] = str_replace(" City", "", $explode_city[$i]);
			// 	// echo $placeName." ".$placeAddress." ".$placeId."<br>";

			// 	$place['name'] = $placeName; 
			// 	$place['address'] = $placeAddress;
			// 	$place['place_id'] =  $placeId;

			// 	if(!in_array($place, $list['places'])){
			// 		$list['places'][] = $place;
			// 	}
				
			// }
			// $place['name'] = $placeName; 
			// $place['address'] = $placeAddress;
			// $place['place_id'] =  $placeId;

			// $list['places'][] = $place;
		// }
	// }

	// $hostname = "https://maps.googleapis.com/maps/api/place/textsearch/json?query=".str_replace(" ", "+", $_POST['keyword'])."&key=AIzaSyCWURxddYHkkejBOFqA31s3yiRXr2BzEWM"; 

	// echo $hostname;
	// echo json_encode($list);

?>