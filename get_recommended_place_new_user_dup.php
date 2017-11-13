<?php

	include("jp_library/jp_lib.php");
	include("functions.php");

	$_POST['user_id'] = "267";
	$_POST['coordinates'] = "14.6196396,121.0513918";
	$_POST['city'] = "Quezon City";
	if(isset($_POST['user_id'])){

		$array = array();
		$list = array();
		$u_blocks = array();
		$list_of_words = array();

		$list['places'] = array();
		$location['location'] = array();
		$words['words'] = array();

		$search0['select'] = "user_id_follow";
		$search0['table'] = "veeds_users_follow";
		$search0['where'] = "user_id = '".$_POST['user_id']."'
								AND user_id_follow != '".$_POST['user_id']."' 
								AND approved = 1";

		if(jp_count($search0) > 10){
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
			$search2['select'] = "h.place_id, location_id, place_name, e.coordinates, h.user_id, h.video_id, video_thumb, date_upload, date_expiry";
			$search2['table'] = "veeds_establishment e, veeds_users_visit_history h, veeds_videos v";
			// $search2['where'] = "h.place_id = e.place_id
			// 						AND h.place_id = v.place_id
			// 						AND e.coordinates = '".$_POST['coordinates']."'
			// 						AND h.user_id IN (".$users.",".$_POST['user_id'].")
			// 						AND date_visit IN (SELECT MAX(date_visit) 
			// 													FROM veeds_users_visit_history
	  //             												WHERE user_id IN (".$users.") 
	  //             												GROUP BY place_id)";
			$search2['where'] = "h.place_id = e.place_id
									AND h.place_id = v.place_id
									AND e.coordinates = '".$_POST['coordinates']."'
									AND h.user_id IN (".$users.",".$_POST['user_id'].")
									AND date_visit IN (SELECT MAX(date_visit) 
																FROM veeds_users_visit_history
	              												WHERE user_id IN (".$users.") 
	              												GROUP BY place_id)";
	    	$search2['filters'] = "GROUP BY h.place_id LIMIT 1";
			// echo implode(" ", $search2);				
			if(jp_count($search2) > 0){

				$return2 = jp_get($search2);
				while ($row2 = mysqli_fetch_assoc($return2)) {
					
					// $row2['logged_id'] = $_POST['user_id'];

					if(!in_array($row2['location_id'],$location['location'])){
						$location['location'][] = $row2['location_id'];
					}

					$row2 = array(
									'place_id' => $row2['place_id'],
									'place_name' => $row2['place_name'],
									'coordinates' => $row2['coordinates'],
									'user_id' => $row2['user_id'],
									'video_id' => $row2['video_id'],
									'video_thumb' => $row2['video_thumb'],
									'date_upload' => $row2['date_upload'],
									'date_expiry' => $row2['date_expiry'],
									'logged_id' => $_POST['user_id']
								);
					$list['places'][] = $row2;
				}
			}else{
				// $array = array_fill(1, 1, "first");
				// $array = array(
				// 				'place_id' => "",
				// 				'place_name' => "",
				// 				'coordinates' => "",
				// 				'user_id' => "",
				// 				'video_id' => "",
				// 				'video_thumb' => "",
				// 				'date_upload' => "",
				// 				'date_expiry' => "",
				// 				'logged_id' => $_POST['user_id']
				// 			);

				// $list['places'][] = $array;

				// $hostname = "https://maps.googleapis.com/maps/api/place/textsearch/json?query=".$_POST['coordinates']."&key=AIzaSyDVdJtIAvhmHE7e2zoxA_Y9qWRpp6eE2o8";

				$hostname = "https://maps.googleapis.com/maps/api/place/nearbysearch/json?location=".$_POST['coordinates']."&rankby=distance&key=AIzaSyDVdJtIAvhmHE7e2zoxA_Y9qWRpp6eE2o8"; 
		
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
			$search3['select'] = "h.place_id, location_id, place_name, e.coordinates, h.user_id, h.video_id, video_thumb, date_upload, date_expiry";
			$search3['table'] = "veeds_establishment e, veeds_users_visit_history h, veeds_videos v";
			$search3['where'] = "h.place_id = e.place_id
									AND h.place_id = v.place_id
									AND e.location LIKE '%".$_POST['city']."%'
									AND h.user_id IN (".$users.")
									AND date_visit IN (SELECT MAX(date_visit) 
														FROM veeds_users_visit_history
	      												WHERE user_id IN (".$users.") 
	      												GROUP BY place_id)";
			$search3['filters'] = "GROUP BY h.place_id LIMIT 1";
						
			if(jp_count($search3) > 0){

				$return3 = jp_get($search3);
				while ($row3 = mysqli_fetch_assoc($return3)) {
					
					if(!in_array($row3['location_id'],$location['location'])){
						$location['location'][] = $row3['location_id'];
					}

					$row3 = array(
									'place_id' => $row3['place_id'],
									'place_name' => $row3['place_name'],
									'coordinates' => $row3['coordinates'],
									'user_id' => $row3['user_id'],
									'video_id' => $row3['video_id'],
									'video_thumb' => $row3['video_thumb'],
									'date_upload' => $row3['date_upload'],
									'date_expiry' => $row3['date_expiry'],
									'logged_id' => $_POST['user_id']
								);
					$list['places'][] = $row3;
				}
			}else{
				// $array = array_fill(1, 1, "second");
				// $array = array(
				// 				'place_id' => "",
				// 				'place_name' => "",
				// 				'coordinates' => "",
				// 				'user_id' => "",
				// 				'video_id' => "",
				// 				'video_thumb' => "",
				// 				'date_upload' => "",
				// 				'date_expiry' => "",
				// 				'logged_id' => $_POST['user_id']
				// 			);

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
					$search4['select'] = "h.place_id, location_id, place_name, e.coordinates, h.user_id, h.video_id, video_thumb, date_upload, date_expiry";
					$search4['table'] = "veeds_establishment e, veeds_users_visit_history h, veeds_videos v";
					// $search4['where'] = "h.place_id = e.place_id
					// 						AND h.place_id = v.place_id
					// 						AND h.user_id IN (".$users.")
					// 						AND h.place_id NOT IN (SELECT place_id FROM veeds_users_visit_history 
					// 												WHERE user_id = '".$_POST['user_id']."')
					// 						AND e.tags LIKE '%".$tags."%'
					// 						AND date_visit IN (SELECT MAX(date_visit) 
					// 											FROM veeds_users_visit_history
	    //           												WHERE user_id IN (".$users.") 
	    //           												GROUP BY place_id)
					// 						AND date_visit BETWEEN (NOW() - INTERVAL 7 DAY) AND NOW() 
	    //           							AND DATE_FORMAT(date_expiry,'%Y-%m-%d %H:%i %s') > NOW()";
					$search4['where'] = "h.place_id = e.place_id
											AND h.place_id = v.place_id
											AND h.user_id IN (".$users.")
											AND h.place_id NOT IN (SELECT place_id FROM veeds_users_visit_history 
																	WHERE user_id = '".$_POST['user_id']."')
											AND e.tags LIKE '%".$tags."%'
											AND date_visit IN (SELECT MAX(date_visit) 
																FROM veeds_users_visit_history
	              												WHERE user_id IN (".$users.") 
	              												GROUP BY place_id)";
	              	$search4['filters'] = "GROUP BY h.place_id";
	              	// echo implode(" ", $search4);	
	              	if(jp_count($search4) > 0){

	              		$result4 = jp_get($search4);
	              		while ($row4 = mysqli_fetch_assoc($result4)) {
	      
	              			if(!in_array($row4['location_id'],$location['location'])){
								$location['location'][] = $row4['location_id'];
							}

							$row4 = array(
											'place_id' => $row4['place_id'],
											'place_name' => $row4['place_name'],
											'coordinates' => $row4['coordinates'],
											'user_id' => $row4['user_id'],
											'video_id' => $row4['video_id'],
											'video_thumb' => $row4['video_thumb'],
											'date_upload' => $row4['date_upload'],
											'date_expiry' => $row4['date_expiry'],
											'logged_id' => $_POST['user_id']
										);

	              			if(!in_array($row4, $list['places'])){
	              				$list['places'][] = $row4;
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

						$result5 = jp_get($search5);
						while ($row5 = mysqli_fetch_assoc($result5)){
							// Hashtags used by the user
							$hashtags[$i] = str_replace("#", "", $hashtags[$i]);
							// Hashtags used by not followed user
							$row5['hashtags'] = str_replace("#", "", $row5['hashtags']);
							// Compare hashtags between the user and not followed user and get similar hashtags
							$hashtags_word = checkHashtagExist(strtolower($hashtags[$i]),strtolower($row5['hashtags']));
							if(!empty($hashtags_word)){
								$hashtags_word = implode("", $hashtags_word);
							}

							if($hashtags_word != "" || $hashtags_word != NULL){
								if(substr($hashtags_word,0,1) == "#"){
									$hashtags_word = str_replace("#", "", $hashtags_word);
								}
/* 
		Display places and post thumbnail based on not followed users who visited a particular place and its type of establishment based on hashtags. Places who already visited by the user will be excluded
*/							
								$search6['select'] = "h.place_id, location_id, place_name, e.coordinates, h.user_id, h.video_id, video_thumb, date_upload, date_expiry";
								$search6['table'] = "veeds_establishment e, veeds_users_visit_history h, veeds_videos v";
								// $search6['where'] = "h.place_id = e.place_id
								// 						AND h.place_id = v.place_id
								// 						AND h.user_id IN (".$users.")
								// 						AND h.place_id NOT IN (SELECT place_id 
								// 												FROM veeds_users_visit_history 
								// 												WHERE user_id = '".$_POST['user_id']."')
								// 						AND (hashtags LIKE '%".$hashtags_word."%' OR place_name LIKE '%".$hashtags_word."%')
					 		// 							AND date_visit IN (SELECT MAX(date_visit)
						 	// 												FROM veeds_users_visit_history
						  // 													WHERE user_id IN (".$users.")
						 	// 												GROUP BY place_id)
								// 						AND date_visit BETWEEN (NOW() - INTERVAL 7 DAY) AND NOW() 
						 	// 							AND DATE_FORMAT(date_expiry,'%Y-%m-%d %H:%i %s') > NOW()";
								$search6['where'] = "h.place_id = e.place_id
														AND h.place_id = v.place_id
														AND h.user_id IN (".$users.")
														AND h.place_id NOT IN (SELECT place_id 
																				FROM veeds_users_visit_history 
																				WHERE user_id = '".$_POST['user_id']."')
														AND (hashtags LIKE '%".$hashtags_word."%' OR place_name LIKE '%".$hashtags_word."%')
					 									AND date_visit IN (SELECT MAX(date_visit)
						 													FROM veeds_users_visit_history
						  													WHERE user_id IN (".$users.")
						 													GROUP BY place_id)";
						 		$search6['filters'] = "GROUP BY h.place_id";
						 		// echo implode(" ", $search6);
						 		if(jp_count($search6) > 0){

						 			$result6 = jp_get($search6);
						 			while ($row6 = mysqli_fetch_assoc($result6)) {
						 				
						 				// $row6['logged_id'] = $_POST['user_id'];

						 				if(!in_array($row6['location_id'],$location['location'])){
											$location['location'][] = $row6['location_id'];
										}

										$row6 = array(
														'place_id' => $row6['place_id'],
														'place_name' => $row6['place_name'],
														'coordinates' => $row6['coordinates'],
														'user_id' => $row6['user_id'],
														'video_id' => $row6['video_id'],
														'video_thumb' => $row6['video_thumb'],
														'date_upload' => $row6['date_upload'],
														'date_expiry' => $row6['date_expiry'],
														'logged_id' => $_POST['user_id']
													);

						 				if(!in_array($row6, $list['places'])){
						 					$list['places'][] = $row6;
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

	/* 
			Display places and post thumbnail based on not followed users who visited a particular place and its type of establishment based on hashtags. Places who already visited by the user will be excluded
	*/	
								$search7['select'] = "h.place_id, location_id, place_name, e.coordinates, h.user_id, h.video_id, video_thumb, date_upload, date_expiry";
								$search7['table'] = "veeds_establishment e, veeds_users_visit_history h, veeds_videos v";
								// $search7['where'] = "h.place_id = e.place_id
								// 						AND h.place_id = v.place_id
								// 						AND h.user_id IN (".$users.")
								// 						AND h.place_id NOT IN (SELECT place_id 
								// 												FROM veeds_users_visit_history 
								// 												WHERE user_id = '".$_POST['user_id']."')
								// 						AND (hashtags LIKE '%".$hashtags_word1."%' OR place_name LIKE '%".$hashtags_word1."%')
					 		// 							AND date_visit IN (SELECT MAX(date_visit)
						 	// 												FROM veeds_users_visit_history
						  // 													WHERE user_id IN (".$users.")
						 	// 												GROUP BY place_id)
								// 						AND date_visit BETWEEN (NOW() - INTERVAL 7 DAY) AND NOW() 
						 	// 							AND DATE_FORMAT(date_expiry,'%Y-%m-%d %H:%i %s') > NOW()";
								$search7['where'] = "h.place_id = e.place_id
														AND h.place_id = v.place_id
														AND h.user_id IN (".$users.")
														AND h.place_id NOT IN (SELECT place_id 
																				FROM veeds_users_visit_history 
																				WHERE user_id = '".$_POST['user_id']."')
														AND (hashtags LIKE '%".$hashtags_word1."%' OR place_name LIKE '%".$hashtags_word1."%')
					 									AND date_visit IN (SELECT MAX(date_visit)
						 													FROM veeds_users_visit_history
						  													WHERE user_id IN (".$users.")
						 													GROUP BY place_id)";
								$search7['filters'] = "GROUP BY h.place_id";
								// echo implode(" ", $search7);
								if(jp_count($search7) > 0){

						 			$result7 = jp_get($search7);
						 			while ($row7 = mysqli_fetch_assoc($result7)) {
						 				
						 				// $row7['logged_id'] = $_POST['user_id'];

						 				if(!in_array($row7['location_id'],$location['location'])){
											$location['location'][] = $row7['location_id'];
										}

										$row7 = array(
														'place_id' => $row7['place_id'],
														'place_name' => $row7['place_name'],
														'coordinates' => $row7['coordinates'],
														'user_id' => $row7['user_id'],
														'video_id' => $row7['video_id'],
														'video_thumb' => $row7['video_thumb'],
														'date_upload' => $row7['date_upload'],
														'date_expiry' => $row7['date_expiry'],
														'logged_id' => $_POST['user_id']
													);
						 				if(!in_array($row7, $list['places'])){
						 					$list['places'][] = $row7;
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
				    }
				}

				$hashtags_word = implode(" ", $words['words']);
				foreach ($new_array_words as $final_word) {

					// Compare words from veeds_dictionary to hashtag and return matched word
					if (stristr($hashtags_word,$final_word) !== false) {
						// echo $final_word."<br>";
						$search8['select'] = "h.place_id, location_id, place_name, e.coordinates, h.user_id, h.video_id, video_thumb, date_upload, date_expiry";
						$search8['table'] = "veeds_establishment e, veeds_users_visit_history h, veeds_videos v";
						// $search8['where'] = "h.place_id = e.place_id
						// 						AND h.place_id = v.place_id
						// 						AND h.user_id IN (".$users.")
						// 						AND h.place_id NOT IN (SELECT place_id 
						// 												FROM veeds_users_visit_history 
						// 												WHERE user_id = '".$_POST['user_id']."')
						// 						AND (hashtags LIKE '%".$final_word."%' OR place_name LIKE '%".$final_word."%')
			 		// 							AND date_visit IN (SELECT MAX(date_visit)
				 	// 												FROM veeds_users_visit_history
				  // 													WHERE user_id IN (".$users.")
				 	// 												GROUP BY place_id)
						// 						AND date_visit BETWEEN (NOW() - INTERVAL 7 DAY) AND NOW() 
				 	// 							AND DATE_FORMAT(date_expiry,'%Y-%m-%d %H:%i %s') > NOW()";
						$search8['where'] = "h.place_id = e.place_id
												AND h.place_id = v.place_id
												AND h.user_id IN (".$users.")
												AND h.place_id NOT IN (SELECT place_id 
																		FROM veeds_users_visit_history 
																		WHERE user_id = '".$_POST['user_id']."')
												AND (hashtags LIKE '%".$final_word."%' OR place_name LIKE '%".$final_word."%')
			 									AND date_visit IN (SELECT MAX(date_visit)
				 													FROM veeds_users_visit_history
				  													WHERE user_id IN (".$users.")
				 													GROUP BY place_id)";
						$search8['filters'] = "GROUP BY h.place_id";
						// echo implode(" ", $search8)."<br>";
						if(jp_count($search8) > 0){

				 			$result8 = jp_get($search8);
				 			while ($row8 = mysqli_fetch_assoc($result8)) {
				 				
				 				// $row8['logged_id'] = $_POST['user_id'];

				 				if(!in_array($row8['location_id'],$location['location'])){
									$location['location'][] = $row8['location_id'];
								}

								$row8 = array(
												'place_id' => $row8['place_id'],
												'place_name' => $row8['place_name'],
												'coordinates' => $row8['coordinates'],
												'user_id' => $row8['user_id'],
												'video_id' => $row8['video_id'],
												'video_thumb' => $row8['video_thumb'],
												'date_upload' => $row8['date_upload'],
												'date_expiry' => $row8['date_expiry'],
												'logged_id' => $_POST['user_id']
											);

				 				if(!in_array($row8, $list['places'])){
				 					$list['places'][] = $row8;
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
			$search9['select'] = "u.user_id, u.firstname, u.lastname, u.username, u.personal_information, u.profile_pic, h.place_id, e.location_id";
			$search9['table'] = "veeds_users u, veeds_establishment e, veeds_users_visit_history h";
			$search9['where'] = "u.user_id = h.user_id 
									AND h.place_id = e.place_id 
									AND h.user_id IN (".$users.") 
									AND disabled = 0 ".$location_ext;
			$search9['filters'] = "GROUP BY u.user_id";

			// echo implode(" ", $search9);

			if(jp_count($search9) > 0){
				
				$result9 = jp_get($search9);
				while($row9 = mysqli_fetch_assoc($result9)){

					$search10['select'] = "user_id";
					$search10['table'] = "veeds_users_follow";
					$search10['where'] = "user_id = '".$_POST['user_id']."' AND user_id_follow = '".$row9['user_id']."' AND approved = 1";
					$count10 = jp_count($search10);
					if($count10 > 0){
						$row9['followed'] = 1;
					}else{
						$search10['where'] = "user_id = '".$_POST['user_id']."' AND user_id_follow = '".$row9['user_id']."' AND approved = 0";
						$count10 = jp_count($search10);
						if($count10 > 0){
							$row9['followed'] = 2;
						}else{
							$row9['followed'] = 0;
						}
					}

					$search11['select'] = "user_id";
					$search11['table'] = "veeds_users_block";
					// $search2['where'] = "user_id = '".$_POST['user_id']."' AND user_id_block = '".$row10['user_id']."'"; 
					$search11['where'] = "user_id = '".$_POST['user_id']."' AND user_id_block = '".$row9['user_id']."'"; 
					$count11 = (int)jp_count($search11);
					if($count11 > 0){
						$row9['blocked'] = true;
					}else{
						$row9['blocked'] = false;
					}

					$row9['logged_id'] = $_POST['user_id'];
					$list['users'][] = $row9;
				}
			}


		}else if(jp_count($search0) < 10){
/*
	Get places based on location
*/		
			$search2['select'] = "h.place_id, location_id, place_name, e.coordinates, h.user_id, h.video_id, video_thumb, date_upload, date_expiry";
			$search2['table'] = "veeds_establishment e, veeds_users_visit_history h, veeds_videos v";
			// $search2['where'] = "h.place_id = e.place_id
			// 						AND h.place_id = v.place_id
			// 						AND e.coordinates = '".$_POST['coordinates']."'
			// 						AND h.user_id IN (".$users.",".$_POST['user_id'].")
			// 						AND date_visit IN (SELECT MAX(date_visit) 
			// 													FROM veeds_users_visit_history
	  //             												WHERE user_id IN (".$users.") 
	  //             												GROUP BY place_id)";
			$search2['where'] = "h.place_id = e.place_id
									AND h.place_id = v.place_id
									AND e.coordinates = '".$_POST['coordinates']."'";
	    	$search2['filters'] = "GROUP BY h.place_id LIMIT 1";
			// echo implode(" ", $search2);				
			if(jp_count($search2) > 0){

				$return2 = jp_get($search2);
				while ($row2 = mysqli_fetch_assoc($return2)) {

					if(!in_array($row2['location_id'],$location['location'])){
						$location['location'][] = $row2['location_id'];
					}

					$row2 = array(
									'place_id' => $row2['place_id'],
									'place_name' => $row2['place_name'],
									'coordinates' => $row2['coordinates'],
									'user_id' => $row2['user_id'],
									'video_id' => $row2['video_id'],
									'video_thumb' => $row2['video_thumb'],
									'date_upload' => $row2['date_upload'],
									'date_expiry' => $row2['date_expiry'],
									'logged_id' => $_POST['user_id']
								);
					$list['places'][] = $row2;
				}
			}else{

				$hostname = "https://maps.googleapis.com/maps/api/place/nearbysearch/json?location=".$_POST['coordinates']."&rankby=distance&key=AIzaSyDVdJtIAvhmHE7e2zoxA_Y9qWRpp6eE2o8"; 
		
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
			$search3['select'] = "h.place_id, location_id, place_name, e.coordinates, h.user_id, h.video_id, video_thumb, date_upload, date_expiry";
			$search3['table'] = "veeds_establishment e, veeds_users_visit_history h, veeds_videos v";
			$search3['where'] = "h.place_id = e.place_id
									AND h.place_id = v.place_id
									AND e.location LIKE '%".$_POST['city']."%'";
			$search3['filters'] = "GROUP BY h.place_id LIMIT 1";
						
			if(jp_count($search3) > 0){

				$return3 = jp_get($search3);
				while ($row3 = mysqli_fetch_assoc($return3)) {

					if(!in_array($row3['location_id'],$location['location'])){
						$location['location'][] = $row3['location_id'];
					}

					$row3 = array(
									'place_id' => $row3['place_id'],
									'place_name' => $row3['place_name'],
									'coordinates' => $row3['coordinates'],
									'user_id' => $row3['user_id'],
									'video_id' => $row3['video_id'],
									'video_thumb' => $row3['video_thumb'],
									'date_upload' => $row3['date_upload'],
									'date_expiry' => $row3['date_expiry'],
									'logged_id' => $_POST['user_id']
								);
					$list['places'][] = $row3;
				}
			}else{
				
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

			$search12['select'] = "h.place_id, location_id, place_name, e.coordinates, h.user_id, h.video_id, video_thumb, date_upload, date_expiry";
			$search12['table'] = "veeds_establishment e, veeds_users_visit_history h, veeds_videos v";
			$search12['where'] = "h.place_id = e.place_id
									AND h.place_id = v.place_id
									AND h.place_id NOT IN (SELECT place_id 
															FROM veeds_users_visit_history 
															WHERE user_id = '".$_POST['user_id']."')";
			$search12['filters'] = "GROUP BY h.place_id LIMIT 1";
			// echo implode(" ", $search12);
			if(jp_count($search12) > 0){

				$result12 = jp_get($search12);
				while ($row12 = mysqli_fetch_assoc($result12)) {
					
					if(!in_array($row12['location_id'],$location['location'])){
						$location['location'][] = $row12['location_id'];
					}

					$row12 = array(
									'place_id' => $row12['place_id'],
									'place_name' => $row12['place_name'],
									'coordinates' => $row12['coordinates'],
									'user_id' => $row12['user_id'],
									'video_id' => $row12['video_id'],
									'video_thumb' => $row12['video_thumb'],
									'date_upload' => $row12['date_upload'],
									'date_expiry' => $row12['date_expiry'],
									'logged_id' => $_POST['user_id']
								);

	 				if(!in_array($row12, $list['places'])){
	 					$list['places'][] = $row12;
	 				}
				}
			}

			if(!empty($location['location'])){
				$location_ext = " AND e.location_id IN (".implode(",", $location['location']).")";
			}else{
				$location_ext = "";
			}

			$search13['select'] = "u.user_id, u.firstname, u.lastname, u.username, u.personal_information, u.profile_pic, h.place_id, e.location_id";
			$search13['table'] = "veeds_users u, veeds_establishment e, veeds_users_visit_history h";
			$search13['where'] = "u.user_id = h.user_id 
									AND h.place_id = e.place_id 
									AND disabled = 0 ".$location_ext;
			$search13['filters'] = "GROUP BY u.user_id";

			if(jp_count($search13) > 0){

				$result13 = jp_get($search13);
				while ($row13 = mysqli_fetch_assoc($result13)) {
					
					$search14['select'] = "user_id";
					$search14['table'] = "veeds_users_follow";
					$search14['where'] = "user_id = '".$_POST['user_id']."' AND user_id_follow = '".$row13['user_id']."' AND approved = 1";
					$count14 = jp_count($search14);
					if($count14 > 0){
						$row13['followed'] = 1;
					}else{
						$search14['where'] = "user_id = '".$_POST['user_id']."' AND user_id_follow = '".$row13['user_id']."' AND approved = 0";
						$count14 = jp_count($search14);
						if($count14 > 0){
							$row13['followed'] = 2;
						}else{
							$row13['followed'] = 0;
						}
					}

					$search15['select'] = "user_id";
					$search15['table'] = "veeds_users_block";
					// $search2['where'] = "user_id = '".$_POST['user_id']."' AND user_id_block = '".$row10['user_id']."'"; 
					$search15['where'] = "user_id = '".$_POST['user_id']."' AND user_id_block = '".$row13['user_id']."'"; 
					$count15 = (int)jp_count($search15);
					if($count15 > 0){
						$row13['blocked'] = true;
					}else{
						$row13['blocked'] = false;
					}

					$row13['logged_id'] = $_POST['user_id'];
					$list['users'][] = $row13;
				}
			}
		}
		echo json_encode($list);
	}

?>
