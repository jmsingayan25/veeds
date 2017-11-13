<?php

	include("jp_library/jp_lib.php");
	include("functions.php");

	$_POST['user_id'] = "182";
	if(isset($_POST['user_id'])){

		$array = array();
		$list = array();
		$u_blocks = array();
		$list_of_words = array();

		$list['places'] = array();
		$list['users'] = array();
		$array['place_id'] = array();
		$words['words'] = array();

		$search0['select'] = "user_id_follow";
		$search0['table'] = "veeds_users_follow";
		$search0['where'] = "user_id = '".$_POST['user_id']."'
								AND user_id_follow != '".$_POST['user_id']."' 
								AND approved = 1";

		if(jp_count($search0) > 10){
			echo "Above 10";
			// Get users blocked by the user
			$block['select'] = "DISTINCT user_id";
			$block['table'] = "veeds_users_block";
			$block['where'] = "user_id_block = '".$_POST['user_id']."'";
			
			if(jp_count($block) > 0){
				$result_block = jp_get($block);
				while($row = mysqli_fetch_assoc($result_block)){
					$u_blocks[] = $row['user_id'];
				}
			}		

			// Get users blocked by the user
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

			// Get users followed by the user. User excluded if in the blocked list
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

			// Get tags, hashtag and place id based on places visited by the user
			$search1['select'] = "e.tags, h.hashtags, h.place_id";
			$search1['table'] = "veeds_establishment e, veeds_users_visit_history h";
			$search1['where'] = "e.place_id = h.place_id AND h.user_id = '".$_POST['user_id']."'";
			$search1['filters'] = "GROUP BY e.place_name, e.location";

			if(jp_count($search1) > 0){
				$result1 = jp_get($search1);
				while($row1 = mysqli_fetch_assoc($result1)){
					$array['tags'][] = $row1['tags'];
					$array['hashtags'][] = $row1['hashtags'];
				}
			}else{
				$array['tags'][] = "";
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

			foreach($result as $tags => $count) {
				if($tags == "''"){
					$tags = "";
				}
				// Count number of times each tag has been used
			  	if($count >= 1 && $tags != ''){
				
	 /*
	 		Display places and post thumbnail based on followed users who visited a particular place and its type of establishment based on count of tags. Place who already visited by the user will be not be included
	*/

	 			// 	$search2['select'] = "DISTINCT h.place_id, e.location_id, e.place_name, e.location, e.coordinates, e.tags, h.user_id, v.video_id, v.video_name, v.video_file, v.video_thumb, v.date_upload, p.total_count";
					// $search2['table'] = "veeds_users_visit_history h, veeds_establishment e, veeds_videos v, veeds_users_place_history p";
					// $search2['where'] = "h.place_id = e.place_id
					// 						AND h.place_id = p.place_id 
					// 						AND h.video_id = v.video_id
					// 						AND h.user_id IN (".$users.") 
					// 						AND e.tags LIKE '%".$tags."%'
					// 						AND h.place_id NOT IN (SELECT place_id FROM veeds_users_visit_history 
					// 												WHERE user_id = '".$_POST['user_id']."')
					// 						AND h.date_visit IN (SELECT MAX(date_visit)
					// 												FROM veeds_users_visit_history
					// 												WHERE user_id IN (".$users.")
					// 												GROUP BY place_id)";
					// $search2['filters'] = "GROUP BY h.place_id ORDER BY p.total_count DESC";

					// if(jp_count($search2) > 0){
					// 	$result2 = jp_get($search2);
					// 	while($row2 = mysqli_fetch_assoc($result2)){
					// 		$row2['logged_id'] = $_POST['user_id'];
					// 		$row2['total_count'] = (int)$row2['total_count'];
					// 		if(!in_array($row2['location_id'],$array['location_id'])){
					// 			$array['location_id'][] = $row2['location_id'];
					// 		}
					// 		if(!in_array($row2, $list['places'])){
					// 			$list['places'][] = $row2;	
					// 		}	
					// 	}
					// }

	 				$search2['select'] = "e.place_id, location_id, place_name, location, tags, coordinates, total_count";
	 				$search2['table'] = "veeds_establishment e, veeds_users_place_history p";
	 				$search2['where'] = "e.place_id = p.place_id
	 										AND e.place_id NOT IN (SELECT place_id FROM veeds_users_visit_history 
																	WHERE user_id = '".$_POST['user_id']."')
	 										AND tags LIKE '%".$tags."%'";
	 				$search2['filters'] = "GROUP BY e.place_id ORDER BY total_count DESC";

	 				if(jp_count($search2) > 0){

	 					$result2 = jp_get($search2);
	 					while ($row2 = mysqli_fetch_assoc($result2)) {
	 						
	 						$search3['select'] = "place_id, user_id, video_id, date_visit";
	 						$search3['table'] = "veeds_users_visit_history";
	 						// $search3['where'] = "place_id LIKE '%".$row2['place_id']."%'
	 						// 						AND user_id IN (".$users.") 
	 						// 						AND date_visit IN (SELECT MAX(date_visit)
								// 										FROM veeds_users_visit_history
					 		// 											WHERE user_id IN (".$users.")
								// 										GROUP BY place_id)";
	 						$search3['where'] = "place_id LIKE '%".$row2['place_id']."%'
	 												AND user_id IN (".$users.") 
	 												AND date_visit IN (SELECT MAX(date_visit)
																		FROM veeds_users_visit_history
					 													WHERE user_id IN (".$users.")
																		GROUP BY place_id)
													AND date_visit BETWEEN (NOW() - INTERVAL 7 DAY) AND NOW()";

	 						if(jp_count($search3) > 0){

	 							$result3 = jp_get($search3);
	 							while ($row3 = mysqli_fetch_assoc($result3)) {
	 								
	 								$search4['select'] = "user_id, video_id, video_name, video_file, video_thumb, date_upload, date_expiry";
	 								$search4['table'] = "veeds_videos";
	 								$search4['where'] = "place_id LIKE '%".$row3['place_id']."%' 
	 														AND video_id = '".$row3['video_id']."'
	 														AND user_id = '".$row3['user_id']."'
	 														AND DATE_FORMAT(date_expiry,'%Y-%m-%d %H:%i %s') > NOW()";
	 								// $search4['where'] = "place_id LIKE '%".$row3['place_id']."%' 
	 								// 						AND video_id = '".$row3['video_id']."'
	 								// 						AND user_id = '".$row3['user_id']."'";

	 								if(jp_count($search4) > 0){

	 									$result4 = jp_get($search4);
	 									while ($row4 = mysqli_fetch_assoc($result4)) {
	 										
	 										$row3['user_id'] = $row4['user_id'];
	 										$row3['video_id'] = $row4['video_id'];
	 										$row3['video_name'] = $row4['video_name'];
	 										$row3['video_file'] = $row4['video_file'];
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
													'location' => $row2['location'],
													'coordinates' => $row2['coordinates'],
													'tags' => $row2['tags'],
													'user_id' => $row3['user_id'],
													'video_id' => $row3['video_id'],
													'video_name' => $row3['video_name'],
													'video_file' => $row3['video_file'],
													'video_thumb' => $row3['video_thumb'],
													'date_upload' => $row3['date_upload'],
													'date_expiry' => $row3['date_expiry'],
													'total_count' => (int)$row2['total_count'],
													'logged_id' => $_POST['user_id']
	 											);

	 								if(!in_array($row3['place_id'],$array['place_id'])){
										$array['place_id'][] = $row3['place_id'];
									}
									if(!in_array($row3, $list['places'])){
										$list['places'][] = $row3;	
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

		 		// Get string that start with # symbol
				for($i = 0; $i < count($hashtags); $i++){
					if(substr($hashtags[$i],0,1) == "#"){
						$words['words'][] = str_replace("#", "", $hashtags[$i]);
						// Get hashtags that followed users used
						$search5['select'] = "hashtags";
						$search5['table'] = "veeds_users_visit_history";
						$search5['where'] = "user_id IN (".$users.") AND hashtags != ''";

						$result5 = jp_get($search5);
						while ($row5 = mysqli_fetch_assoc($result5)){
							// Hashtag used by the user
							$hashtags[$i] = str_replace("#", "", $hashtags[$i]);
							// Hashtag used by the followed users
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
	 		Display places with thumbnail based on followed users who visited a particular place and its type of establishment based on hashtag. Place who already visited by the user will not be included
	*/
								// $search4['select'] = "DISTINCT h.place_id, e.location_id, e.place_name, e.location, e.coordinates, e.tags, h.user_id, v.video_id, v.video_name, v.video_file, v.video_thumb, v.date_upload, p.total_count";
								// $search4['table'] = "veeds_users_visit_history h, veeds_establishment e, veeds_videos v, veeds_users_place_history p";
								// $search4['where'] = "h.place_id = e.place_id 
								// 						AND h.place_id = p.place_id 
								// 						AND h.video_id = v.video_id 
								// 						AND h.user_id IN (".$users.") 
								// 						AND (h.hashtags LIKE '%".$hashtags_word."%' OR e.place_name LIKE '%".$hashtags_word."%')
								// 						AND h.place_id NOT IN (SELECT place_id FROM veeds_users_visit_history 
								// 												WHERE user_id = '".$_POST['user_id']."')
								// 						AND h.date_visit IN (SELECT MAX(date_visit)
								// 									FROM veeds_users_visit_history
								// 									WHERE user_id IN (".$users.")
								// 									GROUP BY place_id)";
								// $search4['filters'] = "GROUP BY h.place_id ORDER BY p.total_count DESC";

								// if(jp_count($search4) > 0){
								// 	$result4 = jp_get($search4);
								// 	while($row4 = mysqli_fetch_assoc($result4)){
								// 		$row4['logged_id'] = $_POST['user_id'];
								// 		$row4['total_count'] = (int)$row4['total_count'];
								// 		if(!in_array($row4['location_id'],$array['location_id'])){
								// 			$array['location_id'][] = $row4['location_id'];
								// 		}
								// 		if(!in_array($row4, $list['places'])){
								// 			// $list['places'][] = $row4;	
								// 		}	
								// 	}
								// }

	 							$search6['select'] = "location_id, e.place_id, place_name, location, tags, coordinates, total_count";
	 							$search6['table'] = "veeds_establishment e, veeds_users_place_history p";
	 							$search6['where'] = "e.place_id = p.place_id
	 													AND e.place_id NOT IN (SELECT place_id FROM veeds_users_visit_history 
																				WHERE user_id = '".$_POST['user_id']."')";
								$search6['filters'] = "GROUP BY e.place_id ORDER BY total_count DESC";

								if(jp_count($search6) > 0){

									$result6 = jp_get($search6);
									while ($row6 = mysqli_fetch_assoc($result6)) {
										
										$search7['select'] = "h.place_id, user_id, video_id, date_visit";
										$search7['table'] = "veeds_users_visit_history h, veeds_establishment e";
										// $search7['where'] = "h.place_id = e.place_id
										// 						AND h.place_id LIKE '%".$row6['place_id']."%'
										// 						AND user_id IN (".$users.") 
										// 						AND (hashtags LIKE '%".$hashtags_word."%' OR place_name LIKE '%".$hashtags_word."%')
	 								// 							AND date_visit IN (SELECT MAX(date_visit)
										// 											FROM veeds_users_visit_history
								 	// 												WHERE user_id IN (".$users.")
										// 											GROUP BY place_id)";
										$search7['where'] = "h.place_id = e.place_id
																AND h.place_id LIKE '%".$row6['place_id']."%'
																AND user_id IN (".$users.") 
																AND (hashtags LIKE '%".$hashtags_word."%' OR place_name LIKE '%".$hashtags_word."%')
	 															AND date_visit IN (SELECT MAX(date_visit)
																					FROM veeds_users_visit_history
								 													WHERE user_id IN (".$users.")
																					GROUP BY place_id)
																AND date_visit BETWEEN (NOW() - INTERVAL 7 DAY) AND NOW()";

										if(jp_count($search7) > 0){

											$result7 = jp_get($search7);
											while ($row7 = mysqli_fetch_assoc($result7)) {
												
												$search8['select'] = "user_id, video_id, video_name, video_file, video_thumb, date_upload, date_expiry";
												$search8['table'] = "veeds_videos";
												$search8['where'] = "place_id LIKE '%".$row7['place_id']."%'
																		AND user_id = '".$row7['user_id']."'
																		AND video_id = '".$row7['video_id']."'
																		AND DATE_FORMAT(date_expiry,'%Y-%m-%d %H:%i %s') > NOW()";
												// $search8['where'] = "place_id LIKE '%".$row7['place_id']."%'
												// 						AND user_id = '".$row7['user_id']."'
												// 						AND video_id = '".$row7['video_id']."'";

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
													'location' => $row6['location'],
													'coordinates' => $row6['coordinates'],
													'tags' => $row6['tags'],
													'user_id' => $row7['user_id'],
													'video_id' => $row7['video_id'],
													'video_name' => $row7['video_name'],
													'video_file' => $row7['video_file'],
													'video_thumb' => $row7['video_thumb'],
													'date_upload' => $row7['date_upload'],
													'date_expiry' => $row7['date_expiry'],
													'total_count' => (int)$row6['total_count'],
													'logged_id' => $_POST['user_id']
	 											);

				 								if(!in_array($row7['place_id'],$array['place_id'])){
													$array['place_id'][] = $row7['place_id'];
												}
												if(!in_array($row7, $list['places'])){
													$list['places'][] = $row7;	
												}
												}	
											}
										}
									}
								}
							}

							// Compare hashtags from the user and followed user if there is similarity
							$hashtags_word1 = checkHashtagExist1(strtolower($hashtags[$i]),strtolower($row5['hashtags']));
							if(!empty($hashtags_word1)){
								$hashtags_word1 = implode("", $hashtags_word1);
							}

							if($hashtags_word1 != "" || $hashtags_word1 != NULL){
								if(substr($hashtags_word1,0,1) == "#"){
									$hashtags_word1 = str_replace("#", "", $hashtags_word1);
								}
								// echo $hashtags_word1."<br>";
	 /*
	 		Display places with thumbnail based on followed users who visited a particular place and its type of establishment based on hashtag. Place who already visited by the user will not be included
	*/
								// $search5['select'] = "DISTINCT h.place_id, e.location_id, e.place_name, e.location, e.coordinates, e.tags, h.user_id, v.video_id, v.video_name, v.video_file, v.video_thumb, v.date_upload, p.total_count";
								// $search5['table'] = "veeds_users_visit_history h, veeds_establishment e, veeds_videos v, veeds_users_place_history p";
								// $search5['where'] = "h.place_id = e.place_id 
								// 						AND h.place_id = p.place_id 
								// 						AND h.video_id = v.video_id 
								// 						AND h.user_id IN (".$users.") 
								// 						AND (h.hashtags LIKE '%".$hashtags_word1."%' OR e.place_name LIKE '%".$hashtags_word1."%')
								// 						AND h.place_id NOT IN (SELECT place_id FROM veeds_users_visit_history 
								// 												WHERE user_id = '".$_POST['user_id']."')
								// 						AND h.date_visit IN (SELECT MAX(date_visit)
								// 									FROM veeds_users_visit_history
								// 									WHERE user_id IN (".$users.")
								// 									GROUP BY place_id)";
								// $search5['filters'] = "GROUP BY h.place_id ORDER BY p.total_count DESC";

								// if(jp_count($search5) > 0){
								// 	$result5 = jp_get($search5);
								// 	while($row5 = mysqli_fetch_assoc($result5)){
								// 		$row5['logged_id'] = $_POST['user_id'];
								// 		$row5['total_count'] = (int)$row5['total_count'];
								// 		if(!in_array($row5['location_id'],$array['location_id'])){
								// 			$array['location_id'][] = $row5['location_id'];
								// 		}
								// 		if(!in_array($row5, $list['places'])){
								// 			// $list['places'][] = $row5;	
								// 		}	
								// 	}
								// }

	 							$search9['select'] = "location_id, e.place_id, place_name, location, tags, coordinates, total_count";
	 							$search9['table'] = "veeds_establishment e, veeds_users_place_history p";
	 							$search9['where'] = "e.place_id = p.place_id
	 						 							AND e.place_id NOT IN (SELECT place_id FROM veeds_users_visit_history 
								 												WHERE user_id = '".$_POST['user_id']."')";
								$search9['filters'] = "GROUP BY e.place_id ORDER BY total_count DESC";

								if(jp_count($search9) > 0){

									$result9 = jp_get($search9);
									while ($row9 = mysqli_fetch_assoc($result9)) {

										$search10['select'] = "h.place_id, user_id, video_id, date_visit";
										$search10['table'] = "veeds_users_visit_history h, veeds_establishment e";
										// $search10['where'] = "h.place_id = e.place_id
								 	// 							AND h.place_id LIKE '%".$row9['place_id']."%'
								 	// 							AND user_id IN (".$users.") 
								 	// 							AND (hashtags LIKE '%".$hashtags_word1."%' OR place_name LIKE '%".$hashtags_word1."%')
	 						 	// 								AND date_visit IN (SELECT MAX(date_visit)
								 	// 												FROM veeds_users_visit_history
								  // 													WHERE user_id IN (".$users.")
								 	// 												GROUP BY place_id)";
										$search10['where'] = "h.place_id = e.place_id
								 								AND h.place_id LIKE '%".$row9['place_id']."%'
								 								AND user_id IN (".$users.") 
								 								AND (hashtags LIKE '%".$hashtags_word1."%' OR place_name LIKE '%".$hashtags_word1."%')
	 						 									AND date_visit IN (SELECT MAX(date_visit)
								 													FROM veeds_users_visit_history
								  													WHERE user_id IN (".$users.")
								 													GROUP BY place_id)
								 								AND date_visit BETWEEN (NOW() - INTERVAL 7 DAY) AND NOW()";

								 		if(jp_count($search10) > 0){

								 			$result10 = jp_get($search10);
								 			while ($row10 = mysqli_fetch_assoc($result10)) {
								 				
								 				$search11['select'] = "user_id, video_id, video_name, video_file, video_thumb, date_upload, date_expiry";
								 				$search11['table'] = "veeds_videos";
								 				$search11['where'] = "place_id LIKE '%".$row10['place_id']."%'
								 										AND user_id = '".$row10['user_id']."'
								 										AND video_id = '".$row10['video_id']."'
								 										AND DATE_FORMAT(date_expiry,'%Y-%m-%d %H:%i %s') > NOW()";
								 				// $search11['where'] = "place_id LIKE '%".$row10['place_id']."%'
								 				// 						AND user_id = '".$row10['user_id']."'
								 				// 						AND video_id = '".$row10['video_id']."'";

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
													'location' => $row9['location'],
													'coordinates' => $row9['coordinates'],
													'tags' => $row9['tags'],
													'user_id' => $row10['user_id'],
													'video_id' => $row10['video_id'],
													'video_name' => $row10['video_name'],
													'video_file' => $row10['video_file'],
													'video_thumb' => $row10['video_thumb'],
													'date_upload' => $row10['date_upload'],
													'date_expiry' => $row10['date_expiry'],
													'total_count' => (int)$row9['total_count'],
													'logged_id' => $_POST['user_id']
	 											);

				 								if(!in_array($row10['place_id'],$array['place_id'])){
													$array['place_id'][] = $row10['place_id'];
												}
												if(!in_array($row10, $list['places'])){
													$list['places'][] = $row10;	
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
						// echo $final_word."<br>";
						// $search7['select'] = "DISTINCT h.place_id, e.location_id, e.place_name, e.location, e.coordinates, e.tags, h.user_id, v.video_id, v.video_name, v.video_file, v.video_thumb, v.date_upload, p.total_count";
						// $search7['table'] = "veeds_users_visit_history h, veeds_establishment e, veeds_videos v, veeds_users_place_history p";
						// $search7['where'] = "h.place_id = e.place_id 
						// 						AND h.place_id = p.place_id 
						// 						AND h.video_id = v.video_id 
						// 						AND h.user_id IN (".$users.") 
						// 						AND (h.hashtags LIKE '%".$final_word."%' OR e.place_name LIKE '%".$final_word."%')
						// 						AND h.place_id NOT IN (SELECT place_id FROM veeds_users_visit_history 
						// 												WHERE user_id = '".$_POST['user_id']."')
						// 						AND h.date_visit IN (SELECT MAX(date_visit)
						// 									FROM veeds_users_visit_history
						// 									WHERE user_id IN (".$users.")
						// 									GROUP BY place_id)";
						// $search7['filters'] = "GROUP BY h.place_id ORDER BY p.total_count DESC";

						// if(jp_count($search7) > 0){
						// 	$result7 = jp_get($search7);
						// 	while($row7 = mysqli_fetch_assoc($result7)){
						// 		$row7['logged_id'] = $_POST['user_id'];
						// 		$row7['total_count'] = (int)$row7['total_count'];
						// 		if(!in_array($row7['location_id'],$array['location_id'])){
						// 			$array['location_id'][] = $row7['location_id'];
						// 		}
						// 		if(!in_array($row7, $list['places'])){
						// 			// $list['places'][] = $row7;	
						// 		}	
						// 	}
						// }

						$search13['select'] = "location_id, e.place_id, place_name, location, tags, coordinates, total_count";
						$search13['table'] = "veeds_establishment e, veeds_users_place_history p";
						$search13['where'] = "e.place_id = p.place_id
												AND e.place_id NOT IN (SELECT place_id FROM veeds_users_visit_history 
								  												WHERE user_id = '".$_POST['user_id']."')";
						$search13['filters'] = "GROUP BY e.place_id ORDER BY total_count DESC";

						if(jp_count($search13) > 0){

							$result13 = jp_get($search13);
							while ($row13 = mysqli_fetch_assoc($result13)) {
								
								$search14['select'] = "h.place_id, user_id, video_id, date_visit";
								$search14['table'] = "veeds_users_visit_history h, veeds_establishment e";
								// $search14['where'] = "h.place_id = e.place_id
								//   								AND h.place_id LIKE '%".$row13['place_id']."%'
								//   								AND user_id IN (".$users.") 
								//   								AND (hashtags LIKE '%".$final_word."%' OR place_name LIKE '%".$final_word."%')
	 						//   									AND date_visit IN (SELECT MAX(date_visit)
								//   													FROM veeds_users_visit_history
								//    													WHERE user_id IN (".$users.")
								//   													GROUP BY place_id)";
								$search14['where'] = "h.place_id = e.place_id
								  								AND h.place_id LIKE '%".$row13['place_id']."%'
								  								AND user_id IN (".$users.") 
								  								AND (hashtags LIKE '%".$final_word."%' OR place_name LIKE '%".$final_word."%')
	 						  									AND date_visit IN (SELECT MAX(date_visit)
								  													FROM veeds_users_visit_history
								   													WHERE user_id IN (".$users.")
								  													GROUP BY place_id)
								  								AND date_visit BETWEEN (NOW() - INTERVAL 7 DAY) AND NOW()";

								 if(jp_count($search14) > 0){

								 	$result14 = jp_get($search14);
								 	while ($row14 = mysqli_fetch_assoc($result14)) {
								 		
								 		$search15['select'] = "user_id, video_id, video_name, video_file, video_thumb, date_upload, date_expiry";
								 		$search15['table'] = "veeds_videos";
				 		 				$search11['where'] = "place_id LIKE '%".$row14['place_id']."%'
						 										AND user_id = '".$row14['user_id']."'
						 										AND video_id = '".$row14['video_id']."'
						 										AND DATE_FORMAT(date_expiry,'%Y-%m-%d %H:%i %s') > NOW()";
						 				// $search15['where'] = "place_id LIKE '%".$row14['place_id']."%'
						 				// 						AND user_id = '".$row14['user_id']."'
						 				// 						AND video_id = '".$row14['video_id']."'";

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
														'location' => $row13['location'],
														'coordinates' => $row13['coordinates'],
														'tags' => $row13['tags'],
														'user_id' => $row14['user_id'],
														'video_id' => $row14['video_id'],
														'video_name' => $row14['video_name'],
														'video_file' => $row14['video_file'],
														'video_thumb' => $row14['video_thumb'],
														'date_upload' => $row14['date_upload'],
														'date_expiry' => $row14['date_expiry'],
														'total_count' => (int)$row13['total_count'],
														'logged_id' => $_POST['user_id']
													);

		 								if(!in_array($row14['place_id'],$array['place_id'])){
											$array['place_id'][] = $row14['place_id'];
										}
										if(!in_array($row14, $list['places'])){
											$list['places'][] = $row14;	
										}
										}
								 	}
								}
							}
						}
					}
				}
			}

			// $location_id = implode(",", $array['location_id']);
			if(!empty($array['place_id'])){
				$place_ext = " AND h.place_id IN (".implode(",", $array['place_id']).")";
			}else{
				$place_ext = "";
			}

			// Display followed users that has been visited a specific place
			$search16['select'] = "u.user_id, u.firstname, u.lastname, u.username, u.personal_information, u.profile_pic, h.place_id, e.location_id";
			$search16['table'] = "veeds_users u, veeds_establishment e, veeds_users_visit_history h";
			$search16['where'] = "u.user_id = h.user_id 
									AND h.place_id = e.place_id 
									AND h.user_id IN (".$users.") ".$place_ext;
			$search16['filters'] = "GROUP BY u.user_id";
			// echo implode(" ", $search16);
			if(jp_count($search16) > 0){
				$result16 = jp_get($search16);
				while($row16 = mysqli_fetch_assoc($result16)){
					$list['users'][] = $row16;
				}
			}
		}else if(jp_count($search0) < 10){
			echo "Below 10";

			$search1['select'] = "h.place_id, user_id, video_id, total_count";
			$search1['table'] = "veeds_users_visit_history h, veeds_users_place_history p";
			$search1['where'] = "h.place_id = p.place_id 
									AND h.date_visit IN (SELECT MAX(date_visit)
														FROM veeds_users_visit_history
														GROUP BY place_id)";
			$search1['filters'] = "GROUP BY h.place_id ORDER BY total_count DESC";		
			
			if(jp_count($search1) > 0){

				$result1 = jp_get($search1);
				while ($row1 = mysqli_fetch_assoc($result1)) {
					
					$search2['select'] = "location_id, place_name, location, coordinates, tags";
					$search2['table'] = "veeds_establishment";
					$search2['where'] = "place_id = '".$row1['place_id']."'";

					if(jp_count($search2) > 0){

						$result2 = jp_get($search2);
						while ($row2 = mysqli_fetch_assoc($result2)) {
							
							$row1['location_id'] = $row2['location_id'];
							$row1['place_name'] = $row2['place_name'];
							$row1['location'] = $row2['location'];
							$row1['coordinates'] = $row2['coordinates'];
							$row1['tags'] = $row2['tags'];

							$search3['select'] = "video_name, video_file, video_thumb, date_upload, date_expiry";
							$search3['table'] = "veeds_videos";
							// $search3['where'] = "video_id = '".$row1['video_id']."'";
							$search3['where'] = "video_id = '".$row1['video_id']."'
													AND DATE_FORMAT(date_expiry,'%Y-%m-%d %H:%i %s') > NOW()";

							if(jp_count($search3) > 0){

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
						}
					}
				}
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
