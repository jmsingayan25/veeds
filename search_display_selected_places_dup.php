<?php

	ini_set('max_execution_time', 0); 

	include("jp_library/jp_lib.php");
	include("test_class_place.php");

	if(empty($_POST['count']) || !isset($_POST['count'])){
		$_POST['count'] = 0;
	}

	$_POST['user_id'] = "183";
	$_POST['keyword'] = "Makati Medical Center Radiology Department";
	if(isset($_POST['user_id']) && isset($_POST['keyword'])){

		// $placeIdDetail = new classPlaceID;

		$list = array();	
		$u_blocks = array();

		$block['select'] = "user_id_block";
		$block['table'] = "veeds_users_block";
		$block['where'] = "user_id = ".$_POST['user_id'];
		$result_block = jp_get($block);
		
		while($row3 = mysqli_fetch_assoc($result_block)){
			$u_blocks[] = $row3['user_id_block'];
		}

		$block['select'] = "user_id";
		$block['table'] = "veeds_users_block";
		$block['where'] = "user_id_block = ".$_POST['user_id'];
		$result_block = jp_get($block);
		while($row3 = mysqli_fetch_assoc($result_block)){
			if(!in_array($row3, $u_blocks))
				$u_blocks[] = $row3['user_id'];
		}

		if(count($u_blocks) > 0){
			$u_extend_names = " AND v.user_id NOT IN (".implode(",", $u_blocks).")";
		}else{
			$u_extend_names = "";
		}

		// $search['select'] = "user_id_search";
		// $search['table'] = "veeds_users_history";
		// $search['where'] = "category = 'Places'";

		// $result = jp_get($search);
		// while ($row = mysqli_fetch_assoc($result)) {
			
		// 	$explode_city = explode(",", $row['user_id_search']);
		// 	for($i = 0; $i < count($explode_city); $i++){
		// 		echo $explode_city[$i]."<br>";

		// 		if(stripos($explode_city[$i], 'City') !== FALSE){
		// 			// echo $explode_city[$i]."<br>";

		// 			$search1['select'] = "DISTINCT place_id, place_name, location as address, tags, coordinates";
		// 			$search1['table'] = "veeds_establishment";
		// 			$search1['where'] = "location LIKE '%".$explode_city[$i]."%'";
				
		// 			if(jp_count($search1) > 0){

		// 				$result1 = jp_get($search1);
		// 				while ($row1 = mysqli_fetch_assoc($result1)) {
							
		// 					$row1 = array(
		// 								'category' => "Places",
		// 								'place_id' => $row1['place_id'],
		// 								'place_name' => $row1['place_name'],
		// 								'address' => $row1['address'],
		// 								'tags' => $row1['tags'],
		// 								'coordinates' => $row1['coordinates']
		// 								);
		// 					$list['places'][] = $row1;
		// 				}
		// 			}
		// 		}
		// 	}
		// }

		// $explode_city = explode(",", $_POST['keyword']);
		// for($i = 0; $i < count($explode_city); $i++){
		// 	// echo $explode_city[$i]."<br>";
		// 	if(stripos($explode_city[$i], 'City') !== FALSE){
		// 		$search['select'] = "place_id, place_name, location as address, tags, coordinates";
		// 		$search['table'] = "veeds_establishment";
		// 		$search['where'] = "location LIKE '%".$explode_city[$i]."%'";
		// 		// echo implode(" ", $search);
		// 		if(jp_count($search) > 0){

		// 			$result = jp_get($search);
		// 			while ($row = mysqli_fetch_assoc($result)) {
						
		// 				$row = array(
		// 							'category' => "Places",
		// 							'place_id' => $row['place_id'],
		// 							'place_name' => $row['place_name'],
		// 							'address' => $row['address'],
		// 							'tags' => $row['tags'],
		// 							'coordinates' => $row['coordinates']
		// 							);
		// 				$list['places'][] = $row;
		// 			}
		// 		}
		// 	}
		// }

		// $hostname = "https://maps.googleapis.com/maps/api/place/autocomplete/json?input=".str_replace(" ", "+", $_POST['keyword'])."&sensor=false&types=(regions)&key=AIzaSyDVdJtIAvhmHE7e2zoxA_Y9qWRpp6eE2o8";
		// // echo $hostname;
		// $ch = curl_init();
		// curl_setopt($ch, CURLOPT_URL, $hostname);
		// curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		// curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
		// curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		// curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		// $response = curl_exec($ch);
		// curl_close($ch);
		// $response_a = json_decode($response);
		
		// $city = $response_a->predictions[0]->terms[0]->value;
		// $province = $response_a->predictions[0]->terms[1]->value;
		// $country = $response_a->predictions[0]->terms[2]->value;
		
		// if(strpos($city, 'City') !== FALSE){
		// 	$city = str_replace(" City", "", $city);
		// }
		// // echo $city."<br>";
		// // echo $province."<br>";
		// // echo $country."<br>";
		// $search['select'] = "place_id, place_name, location as address, tags, coordinates";
		// $search['table'] = "veeds_establishment";
		// $search['where'] = "location LIKE '%".$city."%' AND location LIKE '%".$country."%'";
		// // echo implode(" ", $search);
		// if(jp_count($search) > 0){

		// 	$result = jp_get($search);
		// 	while ($row = mysqli_fetch_assoc($result)) {

		// 		$search1['select'] = "h.user_id, firstname, lastname, username, profile_pic, place_id";
		// 		$search1['table'] = "veeds_users u, veeds_users_visit_history h";
		// 		$search1['where'] = "h.user_id = u.user_id AND place_id = '".$row['place_id']."'";
		// 		// echo implode(" ", $search1)."<br>";
		// 		if(jp_count($search1) > 0){

		// 			$result1 = jp_get($search1);
		// 			while ($row1 = mysqli_fetch_assoc($result1)) {

		// 				$row1 = array(
		// 								'category' => "Users",
		// 								'place_id' => $row['place_id'],
		// 								'place_name' => $row['place_name'],
		// 								'address' => $row['address'],
		// 								'user_id' => $row1['user_id'],
		// 								'firstname' => $row1['firstname'],
		// 								'lastname' => $row1['lastname'],
		// 								'username' => $row1['username'],
		// 								'profile_pic' => $row1['profile_pic'],
		// 								'logged_id' => $_POST['user_id']
		// 							);

		// 				// $list['users'] = $row1;
						
		// 				$search2['select'] = "DISTINCT video_id, video_name, description, video_file, video_thumb, date_upload, date_expiry, view_count, like_count, location, video_length, landscape_file, user_id, place_id";
		// 				$search2['table'] = "veeds_videos";
		// 				// $search2['where'] = "user_id = '".$row1['user_id']."'
		// 				// 						AND place_id LIKE '%".$row['place_id']."%' 
		// 				// 						AND DATE_FORMAT(date_expiry,'%Y-%m-%d %H:%i %s') > NOW()";
		// 				$search2['where'] = "user_id = '".$row1['user_id']."'
		// 									AND place_id LIKE '%".$row['place_id']."%'";

		// 				// echo implode(" ", $search2)."<br>";

		// 				if(jp_count($search2) > 0){

		// 					$result2 = jp_get($search2);
		// 					while ($row2 = mysqli_fetch_assoc($result2)) {

		// 						$search3['select'] = "like_type";
		// 						$search3['table'] = "veeds_videos_likes";
		// 						$search3['where'] = "video_id = '".$row2['video_id']."' 
		// 												AND user_id = '".$row2['user_id']."'
		// 												AND user_id_liker = '".$_POST['user_id']."'";

		// 						// echo implode(" ", $search3)."<br>";
		// 						if(jp_count($search3) > 0){

		// 							$result3 = jp_get($search3);
		// 							while ($row3 = mysqli_fetch_assoc($result3)) {
										
		// 								$row2['like_type'] = $row3['like_type'];
		// 							}
		// 						}else{
		// 							$row2['like_type'] = "";
		// 						}
								
		// 						$row2 = array(
		// 										'category' => "Places",
		// 										'place_id' => $row['place_id'],
		// 										'place_name' => $row['place_name'],
		// 										'user_id' => $row1['user_id'],
		// 										'firstname' => $row1['firstname'],
		// 										'lastname' => $row1['lastname'],
		// 										'username' => $row1['username'],
		// 										'profile_pic' => $row1['profile_pic'],
		// 										'video_id' => $row2['video_id'],
		// 										'video_name' => $row2['video_name'],
		// 										'description' => $row2['description'],
		// 										'video_file' => $row2['video_file'],
		// 										'video_thumb' => $row2['video_thumb'],
		// 										'date_upload' => $row2['date_upload'],
		// 										'date_expiry' => $row2['date_expiry'],
		// 										'view_count' => (int)$row2['view_count'],
		// 										'like_count' => (int)$row2['like_count'],
		// 										'location' => $row2['location'],
		// 										'video_length' => (int)$row2['video_length'],
		// 										'landscape_file' => $row2['landscape_file'],
		// 										'like_type' => $row2['like_type'],
		// 										'logged_id' => $_POST['user_id']
		// 									);

		// 						$list[] = $row2;
		// 					}
		// 				}
		// 			}
		// 		}	
		// 	}
		// }	

		$search2['select'] = "country_name";
		$search2['table'] = "veeds_countries";
		$search2['filters'] = "ORDER BY country_id";

		$result2 = jp_get($search2);
		while ($row2 = mysqli_fetch_assoc($result2)) {
			$list_of_countries[] = $row2['country_name'];
		}

		foreach ($list_of_countries as $key => $value) {
			// echo $value."<br>";
			if(strpos($_POST['keyword'], $value) !== FALSE){
				// echo $value;
				$_POST['keyword'] = str_replace($value, "", $_POST['keyword']);
				$_POST['keyword'] = rtrim($_POST['keyword'],", ");
			}
		}

		$explode_city = explode(",", $_POST['keyword']);
		for($i = 0; $i < count($explode_city); $i++){
			
			if(strpos($explode_city[$i], 'City') !== FALSE){
				$explode_city[$i] = str_replace(" City", "", $explode_city[$i]);
			}
			// echo $explode_city[$i]."<br>";
			if($i < 1){

				$search['select'] = "location_id, v.user_id, v.place_id, e.place_name, v.location as address, v.coordinates, firstname, lastname, username, profile_pic, video_id, video_name, description, v.video_file, video_thumb, date_upload, date_expiry, view_count, like_count, video_length, landscape_file";
				$search['table'] = "veeds_establishment e, veeds_users u, veeds_videos v";
				$search['where'] = "v.place_id = e.place_id 
									AND v.user_id = u.user_id
									AND v.location != '' AND e.place_name != ''
									AND ('".$explode_city[$i]."' LIKE CONCAT(v.location,'%')
									OR '".$explode_city[$i]."' LIKE CONCAT(e.place_name,'%') 
									OR '".$explode_city[$i]."' LIKE CONCAT('%',v.location)
									OR '".$explode_city[$i]."' LIKE CONCAT('%',e.place_name) 
									OR OR v.location LIKE '%".$explode_city[$i]."%'
									OR e.place_name LIKE '%".$explode_city[$i]."%')".$u_extend_names;
				$search['filters'] = "GROUP BY location_id ORDER BY date_upload DESC";
				echo implode(" ", $search);
				if(jp_count($search) > 0){

					$result = jp_get($search);
					while ($row = mysqli_fetch_assoc($result)) {

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
						
						// sleep(2);
						// $placeIdDetail->setPlaceId($row['place_id']);
						// $placeName = $placeIdDetail->getPlaceName();

						$row = array(
										'category' => "Places",
										'location_id' => $row['location_id'],
										'place_id' => $row['place_id'],
										// 'place_name' => $placeName,
										'place_name' => $row['place_name'],
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
										'address' => $row['address'],
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
	}else{

		$reply = array('reply' => 'Data incomplete', 'post' => $_POST);
		
		echo json_encode($reply);
	}
?>