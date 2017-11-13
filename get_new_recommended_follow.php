<?php


	include("jp_library/jp_lib.php");
	include("functions.php");

	$_POST['user_id'] = "205";
	$_POST['coordinates'] = "14.6275647,121.0645482";
	$_POST['city'] = "Makati";
	if(isset($_POST['user_id'])){

		$array = array();
		$list = array();
		$u_blocks = array();
		$list_of_words = array();

		$list['places'] = array();
		$location['location'] = array();
		$words['words'] = array();

		// Get users who blocked by user
		$block['select'] = "DISTINCT user_id";
		$block['table'] = "veeds_users_block";
		$block['where'] = "user_id_block = '".$_POST['user_id']."'";
		
		if(jp_count($block) > 0){
			$result_block = jp_get($block);
			while($row = mysqli_fetch_assoc($result_block)){
				$u_blocks[] = $row['user_id'];
			}
		}		

		$block1['select'] = "DISTINCT user_id_block";
		$block1['table'] = "veeds_users_block";
		$block1['where'] = "user_id = '".$_POST['user_id']."'";
		// echo implode(" ", $block1);
		if(jp_count($block1) > 0){
			$result_block1 = jp_get($block1);
			while($row1 = mysqli_fetch_assoc($result_block1)){
				if(!in_array($row1, $u_blocks)){
					$u_blocks[] = $row1['user_id_block'];	
				}
			}
		}

		if(count($u_blocks) > 0){
			$u_extend = " AND user_id_follow NOT IN (".implode(",", $u_blocks).")";
		}else{
			$u_extend = "";
		}

		// Get users not followed by user. User excluded if its in blocked list		
		$search['select'] = "DISTINCT user_id_follow";
		$search['table'] = "veeds_users_follow";
    	$search['where'] = "user_id = ".$_POST['user_id']." 
    						AND user_id_follow != '".$_POST['user_id']."' 
    						AND approved = 1".$u_extend;
    	$search['filters'] = "ORDER BY user_id_follow ASC";
    	// echo implode(" ", $search);
    	if(jp_count($search) > 0){
			$result = jp_get($search);
			while($row = mysqli_fetch_assoc($result)){
				$array['users_follow'][] = $row['user_id_follow'];
			}
		}else{
			$array['users_follow'][] = "''";
		}

		// Get tags, hashtags, and place id based on user visited places and posts
		$search1['select'] = "e.tags, h.hashtags";
		$search1['table'] = "veeds_establishment e, veeds_users_visit_history h";
		$search1['where'] = "e.place_id = h.place_id AND h.user_id = '".$_POST['user_id']."'";
		$search1['filters'] = "GROUP BY e.place_name, e.location";
		// echo implode(" ", $search1);
		if(jp_count($search1) > 0){
			$result1 = jp_get($search1);
			while($row1 = mysqli_fetch_assoc($result1)){
				$array['tags'][] = $row1['tags'];
				$array['hashtags'][] = $row1['hashtags'];
			}
		}else{
			$array['tags'][] = "''";
			$array['hashtags'][] = "''";
		}

		$vals = implode(",", $array['tags']);
		$vals_strip = str_replace(",", " ", $vals);
		$vals_explode = explode(" ", $vals_strip);

		$result = array_combine($vals_explode, array_fill(0, count($vals_explode), 0));

		foreach($vals_explode as $word) {
		    $result[$word]++;
		}

		$users = implode(",", $array['users_follow']);
		$hashtags_implode = implode(" ", $array['hashtags']);

/*
	Get places based on location
*/		
		$search16['select'] = "h.place_id, location_id, place_name, e.coordinates, h.user_id, h.video_id, video_thumb, date_upload, date_expiry";
		$search16['table'] = "veeds_establishment e, veeds_users_visit_history h, veeds_videos v";
		$search16['where'] = "h.place_id = e.place_id
								AND h.place_id = v.place_id
								AND e.coordinates = '".$_POST['coordinates']."'
								AND h.user_id IN (".$users.")";
								// echo implode(" ", $search16);
		if(jp_count($search16) > 0 AND jp_count($search16) != NULL){

			$return16 = jp_get($search16);
			while ($row16 = mysqli_fetch_assoc($return16)) {
				
				$row16 = array(
								'place_id' => $row16['place_id'],
								'location_id' => $row16['place_id'],
								'place_name' => $row16['place_name'],
								'coordinates' => $row16['coordinates'],
								'user_id' => $row16['user_id'],
								'video_id' => $row16['video_id'],
								'video_thumb' => $row16['video_thumb'],
								'date_upload' => $row16['date_upload'],
								'date_expiry' => $row16['date_expiry'],
								'logged_id' => $_POST['user_id']
							);
				$list['places'][] = $row16;
			}
		}else{
			// $array = array_fill(1, 1, "first");
			// $list['places'][] = $array;

			// $hostname = "https://maps.googleapis.com/maps/api/place/textsearch/json?query=".$_POST['coordinates']."&key=AIzaSyDVdJtIAvhmHE7e2zoxA_Y9qWRpp6eE2o8"; 

			$hostname = "https://maps.googleapis.com/maps/api/place/nearbysearch/json?location=".$_POST['coordinates']."&rankby=distance&types=establishment&key=AIzaSyDVdJtIAvhmHE7e2zoxA_Y9qWRpp6eE2o8";
	
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $hostname);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			$response = curl_exec($ch);
			curl_close($ch);
			$response_a = json_decode($response);
			
			for ($i=0; $i < 1; $i++) { 

				$coor['lat'] = $response_a->results[$i]->geometry->location->lat;
				$coor['lng'] = $response_a->results[$i]->geometry->location->lng;
				$coor['tags'] = $response_a->results[$i]->types;

				$place['place_id'] = $response_a->results[$i]->place_id;
				$place['place_name'] = $response_a->results[$i]->name;
				$place['address'] = $response_a->results[$i]->vicinity;			
				$place['tags'] = str_replace(",point_of_interest,establishment", "", implode(",", $coor['tags']));
				$place['coordinates'] = $coor['lat'].",".$coor['lng'];
				$place['category'] = "Places"; // new line
				$place['logged_id'] = $_POST['user_id'];

				$place = array(
								'place_id' => $place['place_id'],
								'place_name' => $place['place_name'],
								'coordinates' => $place['coordinates'],
								'user_id' => "",
								'video_id' => "",
								'video_thumb' => "",
								'date_upload' => "",
								'date_expiry' => "",
								'logged_id' => $_POST['user_id']
							);
				$list['places'][] = $place;
			}
		}
/*
	Get place based on region
*/
		$search19['select'] = "h.place_id, location_id, place_name, e.coordinates, h.user_id, h.video_id, video_thumb, date_upload, date_expiry";
		$search19['table'] = "veeds_establishment e, veeds_users_visit_history h, veeds_videos v";
		$search19['where'] = "h.place_id = e.place_id
								AND h.place_id = v.place_id
								AND e.location LIKE '%".$_POST['city']."%'
								AND h.user_id IN (".$users.")";
								// echo implode(" ", $search19);
		if(jp_count($search19) > 0){

			$return19 = jp_get($search19);
			while ($row19 = mysqli_fetch_assoc($return19)) {
				
				$row19 = array(
								'place_id' => $row19['place_id'],
								'location_id' => $row19['place_id'],
								'place_name' => $row19['place_name'],
								'coordinates' => $row19['coordinates'],
								'user_id' => $row19['user_id'],
								'video_id' => $row19['video_id'],
								'video_thumb' => $row19['video_thumb'],
								'date_upload' => $row19['date_upload'],
								'date_expiry' => $row19['date_expiry'],
								'logged_id' => $_POST['user_id']
							);
				$list['places'][] = $row19;
			}
		}else{
			// $array = array_fill(1, 1, "second");
			// $list['places'][] = $array;

			$hostname = "https://maps.googleapis.com/maps/api/place/textsearch/json?query=".str_replace(" ", "+", $_POST['city'])."&key=AIzaSyDVdJtIAvhmHE7e2zoxA_Y9qWRpp6eE2o8"; 
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
			
			for ($i=0; $i < 1; $i++) { 

				$coor['lat'] = $response_a->results[$i]->geometry->location->lat;
				$coor['lng'] = $response_a->results[$i]->geometry->location->lng;
				$coor['tags'] = $response_a->results[$i]->types;

				$place['place_id'] = $response_a->results[$i]->place_id;
				$place['place_name'] = $response_a->results[$i]->name;
				$place['address'] = $response_a->results[$i]->formatted_address;			
				$place['tags'] = str_replace(",point_of_interest,establishment", "", implode(",", $coor['tags']));
				$place['coordinates'] = $coor['lat'].",".$coor['lng'];
				$place['category'] = "Places"; // new line
				$place['logged_id'] = $_POST['user_id'];

				$place = array(
								'place_id' => $place['place_id'],
								'place_name' => $place['place_name'],
								'coordinates' => $place['coordinates'],
								'user_id' => "",
								'video_id' => "",
								'video_thumb' => "",
								'date_upload' => "",
								'date_expiry' => "",
								'logged_id' => $_POST['user_id']
							);
				
				$list['places'][] = $place;
			}
		}

		foreach($result as $tags => $count) {
			if($tags == "''"){
				$tags = "";
			}
		  	if($count >= 1 && $tags != ''){		
/* 
		Display places and post thumbnail based on not followed users who visited a particular place and its type of establishment based on count of tags. Places who already visited by the user will be excluded
*/
				$search2['select'] = "place_id, location_id, place_name, location, tags, coordinates";
 				$search2['table'] = "veeds_establishment";
 				$search2['where'] = "place_id NOT IN (SELECT place_id FROM veeds_users_visit_history 
																WHERE user_id = '".$_POST['user_id']."')
 										AND tags LIKE '%".$tags."%'";
 				// $search2['filters'] = "GROUP BY place_id";
 				// echo implode(" ", $search2);
 				if(jp_count($search2) > 0){

 					$result2 = jp_get($search2);
 					while ($row2 = mysqli_fetch_assoc($result2)) {
 						
 						$search3['select'] = "place_id, user_id, video_id, date_visit";
 						$search3['table'] = "veeds_users_visit_history";
 						$search3['where'] = "place_id LIKE '%".$row2['place_id']."%'
 												AND user_id IN (".$users.") 
 												AND date_visit IN (SELECT MAX(date_visit)
																	FROM veeds_users_visit_history
				 													WHERE user_id IN (".$users.")
																	GROUP BY place_id)";
 						// $search3['where'] = "place_id LIKE '%".$row2['place_id']."%'
 						// 						AND user_id IN (".$users.") 
 						// 						AND date_visit IN (SELECT MAX(date_visit)
							// 										FROM veeds_users_visit_history
				 		// 											WHERE user_id IN (".$users.")
							// 										GROUP BY place_id)
							// 					AND date_visit BETWEEN (NOW() - INTERVAL 7 MONTH) AND NOW()";
						// echo implode(" ", $search3);
 						if(jp_count($search3) > 0){

 							$result3 = jp_get($search3);
 							while ($row3 = mysqli_fetch_assoc($result3)) {
 								
 								$search4['select'] = "user_id, video_id, video_name, video_file, video_thumb, date_upload, date_expiry";
 								$search4['table'] = "veeds_videos";
 								// $search4['where'] = "place_id LIKE '%".$row3['place_id']."%' 
 								// 						AND video_id = '".$row3['video_id']."'
 								// 						AND user_id = '".$row3['user_id']."'
 								// 						AND DATE_FORMAT(date_expiry,'%Y-%m-%d %H:%i %s') > NOW()";
 								$search4['where'] = "place_id LIKE '%".$row3['place_id']."%' 
 														AND video_id = '".$row3['video_id']."'
 														AND user_id = '".$row3['user_id']."'";
 								// echo implode(" ", $search4)."<br>";
 								if(jp_count($search4) > 0){

 									$result4 = jp_get($search4);
 									while ($row4 = mysqli_fetch_assoc($result4)) {
 										
 										$row3['user_id'] = $row4['user_id'];
 										$row3['video_id'] = $row4['video_id'];
 										// $row3['video_name'] = $row4['video_name'];
 										// $row3['video_file'] = $row4['video_file'];
 										$row3['video_thumb'] = $row4['video_thumb'];
 										$row3['date_upload'] = $row4['date_upload'];
 										$row3['date_expiry'] = $row4['date_expiry'];
 									}
 								// }else{
 								// 	$row3['user_id'] = "";
									// $row3['video_id'] = "";
									// $row3['video_name'] = "";
									// $row3['video_file'] = "";
									// $row3['video_thumb'] = "";
									// $row3['date_upload'] = "";
									// $row3['date_expiry'] = "";
 								// }

 								$row3 = array(
 												'place_id' => $row3['place_id'],
												'location_id' => $row2['location_id'],
												'place_name' => $row2['place_name'],
												// 'location' => $row2['location'],
												'coordinates' => $row2['coordinates'],
												// 'tags' => $row2['tags'],
												'user_id' => $row3['user_id'],
												'video_id' => $row3['video_id'],
												// 'video_name' => $row3['video_name'],
												// 'video_file' => $row3['video_file'],
												'video_thumb' => $row3['video_thumb'],
												'date_upload' => $row3['date_upload'],
												'date_expiry' => $row3['date_expiry'],
												// 'total_count' => (int)$row2['total_count'],
												'logged_id' => $_POST['user_id']
 											);

 								if(!in_array($row3['location_id'],$location['location'])){
									$location['location'][] = $row3['location_id'];
								}
								if(!in_array($row3, $list['places'])){
									// $list['places'][] = $row3;	
								}	
								}
 							}
 						}
 					}
 				}
		    }
		}

		if(isset($hashtags_implode)){
	 		$hashtags = explode(" ",$hashtags_implode);

	 		// Get string that starts with # symbol
			for($i = 0; $i < count($hashtags); $i++){
				if(substr($hashtags[$i],0,1) == "#"){
					$words['words'][] = str_replace("#", "", $hashtags[$i]);
					// Get hashtags used by not followed users
					$search5['select'] = "hashtags";
					$search5['table'] = "veeds_users_visit_history";
					$search5['where'] = "user_id IN (".$users.") AND hashtags != ''";

					// echo implode(" ", $search5).";<br>";
					$result5 = jp_get($search5);
					while ($row5 = mysqli_fetch_assoc($result5)){
						// Hashtags used by the user
						$hashtags[$i] = str_replace("#", "", $hashtags[$i]);
						// Hashtags used by not followed user
						$row5['hashtags'] = str_replace("#", "", $row5['hashtags']);

						// $str_word = substr($hashtags[$i], 1, 3);
						// Compare hashtags between the user and not followed user and get similar hashtags
						$hashtags_word = checkHashtagExist(strtolower($hashtags[$i]),strtolower($row5['hashtags']));
						if(!empty($hashtags_word)){
							$hashtags_word = implode("", $hashtags_word);
						}

						if($hashtags_word != "" || $hashtags_word != NULL){
							if(substr($hashtags_word,0,1) == "#"){
								$hashtags_word = str_replace("#", "", $hashtags_word);
							}
							// echo $hashtags_word;
/* 
		Display places and post thumbnail based on not followed users who visited a particular place and its type of establishment based on hashtags. Places who already visited by the user will be excluded
*/							
							$search6['select'] = "location_id, place_id, place_name, location, tags, coordinates";
 							$search6['table'] = "veeds_establishment";
 							$search6['where'] = "place_id NOT IN (SELECT place_id FROM veeds_users_visit_history 
																			WHERE user_id = '".$_POST['user_id']."')";
							$search6['filters'] = "GROUP BY place_id";

							if(jp_count($search6) > 0){

								$result6 = jp_get($search6);
								while ($row6 = mysqli_fetch_assoc($result6)) {
									
									$search7['select'] = "h.place_id, user_id, video_id, date_visit";
									$search7['table'] = "veeds_users_visit_history h, veeds_establishment e";
									$search7['where'] = "h.place_id = e.place_id
															AND h.place_id LIKE '%".$row6['place_id']."%'
															AND user_id IN (".$users.") 
															AND (hashtags LIKE '%".$hashtags_word."%' OR place_name LIKE '%".$hashtags_word."%')
 															AND date_visit IN (SELECT MAX(date_visit)
																				FROM veeds_users_visit_history
							 													WHERE user_id IN (".$users.")
																				GROUP BY place_id)";
									// $search7['where'] = "h.place_id = e.place_id
									// 						AND h.place_id LIKE '%".$row6['place_id']."%'
									// 						AND user_id IN (".$users.") 
									// 						AND (hashtags LIKE '%".$hashtags_word."%' OR place_name LIKE '%".$hashtags_word."%')
 								// 							AND date_visit IN (SELECT MAX(date_visit)
									// 											FROM veeds_users_visit_history
							 	// 												WHERE user_id IN (".$users.")
									// 											GROUP BY place_id)
									// 						AND date_visit BETWEEN (NOW() - INTERVAL 7 MONTH) AND NOW()";

									if(jp_count($search7) > 0){

										$result7 = jp_get($search7);
										while ($row7 = mysqli_fetch_assoc($result7)) {
											
											$search8['select'] = "user_id, video_id, video_name, video_file, video_thumb, date_upload, date_expiry";
											$search8['table'] = "veeds_videos";
											// $search8['where'] = "place_id LIKE '%".$row7['place_id']."%'
											// 						AND user_id = '".$row7['user_id']."'
											// 						AND video_id = '".$row7['video_id']."'
											// 						AND DATE_FORMAT(date_expiry,'%Y-%m-%d %H:%i %s') > NOW()";
											$search8['where'] = "place_id LIKE '%".$row7['place_id']."%'
																	AND user_id = '".$row7['user_id']."'
																	AND video_id = '".$row7['video_id']."'";

											if(jp_count($search8) > 0){

			 									$result8 = jp_get($search8);
			 									while ($row8 = mysqli_fetch_assoc($result8)) {
			 										
			 										$row7['user_id'] = $row8['user_id'];
			 										$row7['video_id'] = $row8['video_id'];
			 										$row7['video_name'] = $row8['video_name'];
			 										$row7['video_file'] = $row8['video_file'];
			 										$row7['video_thumb'] = $row8['video_thumb'];
			 										$row7['date_upload'] = $row8['date_upload'];
			 										$row7['date_expiry'] = $row8['date_expiry'];
			 									}
			 								// }else{
			 								// 	$row7['user_id'] = "";
												// $row7['video_id'] = "";
												// $row7['video_name'] = "";
												// $row7['video_file'] = "";
												// $row7['video_thumb'] = "";
												// $row7['date_upload'] = "";
												// $row7['date_expiry'] = "";
			 								// }

			 								$row7 = array(
 												'place_id' => $row7['place_id'],
												'location_id' => $row6['location_id'],
												'place_name' => $row6['place_name'],
												// 'location' => $row6['location'],
												'coordinates' => $row6['coordinates'],
												// 'tags' => $row6['tags'],
												'user_id' => $row7['user_id'],
												'video_id' => $row7['video_id'],
												// 'video_name' => $row7['video_name'],
												// 'video_file' => $row7['video_file'],
												'video_thumb' => $row7['video_thumb'],
												'date_upload' => $row7['date_upload'],
												'date_expiry' => $row7['date_expiry'],
												// 'total_count' => (int)$row6['total_count'],
												'logged_id' => $_POST['user_id']
 											);

			 								if(!in_array($row6['location_id'],$location['location'])){
												$location['location'][] = $row6['location_id'];
											}
											if(!in_array($row7, $list['places'])){
												// $list['places'][] = $row7;	
											}	
											}
										}
									}
								}
							}
						}
						// Compare hashtags between the user and not followed user and get similar hashtags
						$hashtags_word1 = checkHashtagExist1(strtolower($hashtags[$i]),strtolower($row5['hashtags']));
						if(!empty($hashtags_word1)){
							$hashtags_word1 = implode("", $hashtags_word1);
						}

						if($hashtags_word1 != "" || $hashtags_word1 != NULL){
							if(substr($hashtags_word1,0,1) == "#"){
								$hashtags_word1 = str_replace("#", "", $hashtags_word1);
							}
							// echo $hashtags_word1;
/* 
		Display places and post thumbnail based on not followed users who visited a particular place and its type of establishment based on hashtags. Places who already visited by the user will be excluded
*/	
							$search9['select'] = "location_id, place_id, place_name, location, tags, coordinates";
 							$search9['table'] = "veeds_establishment";
 							$search9['where'] = "place_id NOT IN (SELECT place_id FROM veeds_users_visit_history 
							 												WHERE user_id = '".$_POST['user_id']."')";
							$search9['filters'] = "GROUP BY place_id";

							if(jp_count($search9) > 0){

								$result9 = jp_get($search9);
								while ($row9 = mysqli_fetch_assoc($result9)) {

									$search10['select'] = "h.place_id, user_id, video_id, date_visit";
									$search10['table'] = "veeds_users_visit_history h, veeds_establishment e";
									$search10['where'] = "h.place_id = e.place_id
							 								AND h.place_id LIKE '%".$row9['place_id']."%'
							 								AND user_id IN (".$users.") 
							 								AND (hashtags LIKE '%".$hashtags_word1."%' OR place_name LIKE '%".$hashtags_word1."%')
 						 									AND date_visit IN (SELECT MAX(date_visit)
							 													FROM veeds_users_visit_history
							  													WHERE user_id IN (".$users.")
							 													GROUP BY place_id)";
									// $search10['where'] = "h.place_id = e.place_id
							 	// 							AND h.place_id LIKE '%".$row9['place_id']."%'
							 	// 							AND user_id IN (".$users.") 
							 	// 							AND (hashtags LIKE '%".$hashtags_word1."%' OR place_name LIKE '%".$hashtags_word1."%')
 						 	// 								AND date_visit IN (SELECT MAX(date_visit)
							 	// 												FROM veeds_users_visit_history
							  // 													WHERE user_id IN (".$users.")
							 	// 												GROUP BY place_id)
							 	// 							AND date_visit BETWEEN (NOW() - INTERVAL 7 MONTH) AND NOW()";

							 		if(jp_count($search10) > 0){

							 			$result10 = jp_get($search10);
							 			while ($row10 = mysqli_fetch_assoc($result10)) {
							 				
							 				$search11['select'] = "user_id, video_id, video_name, video_file, video_thumb, date_upload, date_expiry";
							 				$search11['table'] = "veeds_videos";
							 				// $search11['where'] = "place_id LIKE '%".$row10['place_id']."%'
							 				// 						AND user_id = '".$row10['user_id']."'
							 				// 						AND video_id = '".$row10['video_id']."'
							 				// 						AND DATE_FORMAT(date_expiry,'%Y-%m-%d %H:%i %s') > NOW()";
							 				$search11['where'] = "place_id LIKE '%".$row10['place_id']."%'
							 										AND user_id = '".$row10['user_id']."'
							 										AND video_id = '".$row10['video_id']."'";

							 				if(jp_count($search11) > 0){

			 									$result11 = jp_get($search11);
			 									while ($row11 = mysqli_fetch_assoc($result11)) {
			 										
			 										$row10['user_id'] = $row11['user_id'];
			 										$row10['video_id'] = $row11['video_id'];
			 										$row10['video_name'] = $row11['video_name'];
			 										$row10['video_file'] = $row11['video_file'];
			 										$row10['video_thumb'] = $row11['video_thumb'];
			 										$row10['date_upload'] = $row11['date_upload'];
			 										$row10['date_expiry'] = $row11['date_expiry'];
			 									}
			 								// }else{
			 								// 	$row10['user_id'] = "";
												// $row10['video_id'] = "";
												// $row10['video_name'] = "";
												// $row10['video_file'] = "";
												// $row10['video_thumb'] = "";
												// $row10['date_upload'] = "";
												// $row10['date_expiry'] = "";
			 								// }

			 								$row10 = array(
 												'place_id' => $row10['place_id'],
												'location_id' => $row9['location_id'],
												'place_name' => $row9['place_name'],
												// 'location' => $row9['location'],
												'coordinates' => $row9['coordinates'],
												// 'tags' => $row9['tags'],
												'user_id' => $row10['user_id'],
												'video_id' => $row10['video_id'],
												// 'video_name' => $row10['video_name'],
												// 'video_file' => $row10['video_file'],
												'video_thumb' => $row10['video_thumb'],
												'date_upload' => $row10['date_upload'],
												'date_expiry' => $row10['date_expiry'],
												// 'total_count' => (int)$row9['total_count'],
												'logged_id' => $_POST['user_id']
 											);

			 								if(!in_array($row9['location_id'],$location['location'])){
												$location['location'][] = $row9['location_id'];
											}
											if(!in_array($row10, $list['places'])){
												// $list['places'][] = $row10;	
											}
											}
							 			}
							 		}
								}
							}
						}
					}
			    }
			}

			// Get words from veeds_dictionary
			$search12['select'] = "word";
			$search12['table'] = "veeds_dictionary";

			$result12 = jp_get($search12);

			while ($row12 = mysqli_fetch_assoc($result12)) {
				$row12['word'] = str_replace("\n", "", $row12['word']);
				$list_of_words[] = $row12['word'];
			}

			$cut_words = array_map('strtolower', $list_of_words);
			$hashtags_implode = str_replace("#", "", $hashtags_implode);

			$very_new_word = str_replace($cut_words, "", strtolower($hashtags_implode));
			$explode_very_new_word = explode(" ", $very_new_word);
			$new_array_words = array_merge($list_of_words, $explode_very_new_word);

			foreach ($new_array_words as $key => $value) {
			    if (strlen($value) < 3) {
			        unset($new_array_words[$key]);
			        // echo $value."<br>";
			    }
			}

			$hashtags_word = implode(" ", $words['words']);
			foreach ($new_array_words as $final_word) {

				// Compare words from veeds_dictionary to hashtag and return matched word
				if (stristr($hashtags_word,$final_word) !== false) {
					
					$search13['select'] = "location_id, place_id, place_name, location, tags, coordinates";
					$search13['table'] = "veeds_establishment";
					$search13['where'] = "place_id NOT IN (SELECT place_id FROM veeds_users_visit_history 
							  										WHERE user_id = '".$_POST['user_id']."')";
					$search13['filters'] = "GROUP BY place_id";

					if(jp_count($search13) > 0){

						$result13 = jp_get($search13);
						while ($row13 = mysqli_fetch_assoc($result13)) {
							
							$search14['select'] = "h.place_id, user_id, video_id, date_visit";
							$search14['table'] = "veeds_users_visit_history h, veeds_establishment e";
							$search14['where'] = "h.place_id = e.place_id
							  								AND h.place_id LIKE '%".$row13['place_id']."%'
							  								AND user_id IN (".$users.") 
							  								AND (hashtags LIKE '%".$final_word."%' OR place_name LIKE '%".$final_word."%')
 						  									AND date_visit IN (SELECT MAX(date_visit)
							  													FROM veeds_users_visit_history
							   													WHERE user_id IN (".$users.")
							  													GROUP BY place_id)";
							// $search14['where'] = "h.place_id = e.place_id
							//   								AND h.place_id LIKE '%".$row13['place_id']."%'
							//   								AND user_id IN (".$users.") 
							//   								AND (hashtags LIKE '%".$final_word."%' OR place_name LIKE '%".$final_word."%')
 						//   									AND date_visit IN (SELECT MAX(date_visit)
							//   													FROM veeds_users_visit_history
							//    													WHERE user_id IN (".$users.")
							//   													GROUP BY place_id)
							//   								AND date_visit BETWEEN (NOW() - INTERVAL 7 MONTH) AND NOW()";

							 if(jp_count($search14) > 0){

							 	$result14 = jp_get($search14);
							 	while ($row14 = mysqli_fetch_assoc($result14)) {
							 		
							 		$search15['select'] = "user_id, video_id, video_name, video_file, video_thumb, date_upload, date_expiry";
							 		$search15['table'] = "veeds_videos";
			 		 				// $search15['where'] = "place_id LIKE '%".$row14['place_id']."%'
					 					// 					AND user_id = '".$row14['user_id']."'
					 					// 					AND video_id = '".$row14['video_id']."'
					 					// 					AND DATE_FORMAT(date_expiry,'%Y-%m-%d %H:%i %s') > NOW()";
					 				$search15['where'] = "place_id LIKE '%".$row14['place_id']."%'
					 										AND user_id = '".$row14['user_id']."'
					 										AND video_id = '".$row14['video_id']."'";

	 				 				if(jp_count($search15) > 0){

	 									$result15 = jp_get($search15);
	 									while ($row15 = mysqli_fetch_assoc($result15)) {
	 										
	 										$row14['user_id'] = $row15['user_id'];
	 										$row14['video_id'] = $row15['video_id'];
	 										$row14['video_name'] = $row15['video_name'];
	 										$row14['video_file'] = $row15['video_file'];
	 										$row14['video_thumb'] = $row15['video_thumb'];
	 										$row14['date_upload'] = $row15['date_upload'];
	 										$row14['date_expiry'] = $row15['date_expiry'];
	 									}
	 								// }else{
	 								// 	$row14['user_id'] = "";
										// $row14['video_id'] = "";
										// $row14['video_name'] = "";
										// $row14['video_file'] = "";
										// $row14['video_thumb'] = "";
										// $row14['date_upload'] = "";
										// $row14['date_expiry'] = "";
	 								// }

									$row14 = array(
													'place_id' => $row14['place_id'],
													'location_id' => $row13['location_id'],
													'place_name' => $row13['place_name'],
													// 'location' => $row13['location'],
													'coordinates' => $row13['coordinates'],
													// 'tags' => $row13['tags'],
													'user_id' => $row14['user_id'],
													'video_id' => $row14['video_id'],
													// 'video_name' => $row14['video_name'],
													// 'video_file' => $row14['video_file'],
													'video_thumb' => $row14['video_thumb'],
													'date_upload' => $row14['date_upload'],
													'date_expiry' => $row14['date_expiry'],
													// 'total_count' => (int)$row13['total_count'],
													'logged_id' => $_POST['user_id']
												);

	 								if(!in_array($row13['location_id'],$location['location'])){
										$location['location'][] = $row13['location_id'];
									}
									if(!in_array($row14, $list['places'])){
										// $list['places'][] = $row14;	
									}
									}
							 	}
							}
						}
					}
				}
			}
		}

		if(!empty($location['location'])){
			$location_ext = " AND e.location_id IN (".implode(",", $location['location']).")";
		}else{
			$location_ext = "";
		}

		// Display followed users that has been visited a specific place
		$search22['select'] = "u.user_id, u.firstname, u.lastname, u.username, u.personal_information, u.profile_pic, h.place_id, e.location_id";
		$search22['table'] = "veeds_users u, veeds_establishment e, veeds_users_visit_history h";
		$search22['where'] = "u.user_id = h.user_id 
								AND h.place_id = e.place_id 
								AND h.user_id IN (".$users.") ".$location_ext;
		$search22['filters'] = "GROUP BY u.user_id";
		// echo implode(" ", $search22);
		if(jp_count($search22) > 0){
			$result22 = jp_get($search22);
			while($row22 = mysqli_fetch_assoc($result22)){
				$list['users'][] = $row22;
			}
		}
		// $hostname = "https://maps.googleapis.com/maps/api/geocode/json?latlng=".$_POST['coordinates']."&result_type=locality&key=AIzaSyBwvPNKjNnnNSAvhpYSAyrYy3Ds3LLsmaE";

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
		
		// for ($i=0; $i < count($response_a->results); $i++) { 

		// 	$coor['lat'] = $response_a->results[$i]->geometry->location->lat;
		// 	$coor['lng'] = $response_a->results[$i]->geometry->location->lng;

		// 	$place['place_id'] = $response_a->results[$i]->place_id;
		// 	$place['address'] = $response_a->results[$i]->address_components[0]->long_name;	
		// 	$place['coordinates'] = $coor['lat'].",".$coor['lng'];

		// 	$list['region_based'][] = $place;

		// }


		echo json_encode($list);
	}


?>