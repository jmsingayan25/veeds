<?php

	include("jp_library/jp_lib.php");
	include("functions.php");

	$_POST['user_id'] = "183";
	if(isset($_POST['user_id'])){

		$array = array();
		$list = array();
		$u_blocks = array();
		$list_of_words = array();

		$list['places'] = array();
		$list['users'] = array();
		$array['place_id'] = array();
		$words['words'] = array();
		
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
    	$search['where'] = "user_id_follow != '".$_POST['user_id']."' 
    						AND user_id_follow NOT IN (SELECT DISTINCT user_id_follow 
    												FROM veeds_users_follow 
    												WHERE user_id = '".$_POST['user_id']."' 
    												AND user_id_follow != '".$_POST['user_id']."')".$u_extend;
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
 		Display places and post thumbnail based on followed users who visited a particular place and its type of establishment based on count of tags. Place who already visited by the user will be excluded
*/
 				$search2['select'] = "DISTINCT user_id, h.place_id, video_id, total_count";
 				$search2['table'] = "veeds_users_visit_history h, veeds_users_place_history p";
 				$search2['where'] = "h.place_id = p.place_id 
 										AND user_id IN (".$users.")
 										AND h.place_id NOT IN (SELECT place_id FROM veeds_users_visit_history
 																WHERE user_id = '".$_POST['user_id']."')
 										AND date_visit IN (SELECT MAX(date_visit)
															FROM veeds_users_visit_history
															WHERE user_id IN (".$users.")
															GROUP BY place_id)
										AND date_visit BETWEEN (NOW() - INTERVAL 7 DAY) AND NOW()";
				$search2['filters'] = "GROUP BY place_id ORDER BY total_count DESC";
				if(jp_count($search2) > 0){

					$result2 = jp_get($search2);
					while ($row2 = mysqli_fetch_assoc($result2)) {
						
						$search3['select'] = "DISTINCT place_id, location_id, place_name, location, coordinates, tags";
		 				$search3['table'] = "veeds_establishment";
		 				$search3['where'] = "tags LIKE '%".$tags."%' AND place_id LIKE '%".$row2['place_id']."%'";

		 				if(jp_count($search3) > 0){
		 					
		 					$result3 = jp_get($search3);
		 					while ($row3 = mysqli_fetch_assoc($result3)) {
		 						
		 						$search4['select'] = "DISTINCT user_id, video_id, video_name, video_file, video_thumb, date_upload, date_expiry";
		 						$search4['table'] = "veeds_videos";
		 						// $search4['where'] = "place_id LIKE '%".$row3['place_id']."%' 
		 						// 						AND video_id = '".$row2['video_id']."'
		 						// 						AND user_id = '".$row2['user_id']."'";
		 						$search4['where'] = "place_id LIKE '%".$row2['place_id']."%' 
		 												AND video_id = '".$row2['video_id']."'
		 												AND user_id = '".$row2['user_id']."'
		 												AND DATE_FORMAT(date_expiry,'%Y-%m-%d %H:%i %s') > NOW()";
		 						if(jp_count($search4) > 0){

		 							$result4 = jp_get($search4);
		 							while ($row4 = mysqli_fetch_assoc($result4)) {
		 								
		 								$row4 = array(
		 												'place_id' => $row2['place_id'],
		 												'location_id' => $row3['location_id'],
		 												'place_name' => $row3['place_name'],
		 												'location' => $row3['location'],
		 												'coordinates' => $row3['coordinates'],
		 												'tags' => $row3['tags'],
		 												'user_id' => $row2['user_id'],
		 												'video_id' => $row2['video_id'],
		 												'video_file' => $row4['video_file'],
		 												'video_thumb' => $row4['video_thumb'],
		 												'date_upload' => $row4['date_upload'],
		 												'date_expiry' => $row4['date_expiry'],
		 												'total_count' => (int)$row2['total_count'],
		 												'logged_id' => $_POST['user_id']
		 											);
		 								if(!in_array($row2['place_id'],$array['place_id'])){
											$array['place_id'][] = $row2['place_id'];
										}
		 								if(!in_array($row4, $list['places'])){
		 									$list['places'][] = $row4;
		 								}
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
					$search3['select'] = "hashtags";
					$search3['table'] = "veeds_users_visit_history";
					$search3['where'] = "user_id IN (".$users.") AND hashtags != ''";

					$result3 = jp_get($search3);
					while ($row3 = mysqli_fetch_assoc($result3)){
						// Hashtag used by the user
						$hashtags[$i] = str_replace("#", "", $hashtags[$i]);
						// Hashtag used by the followed users
						$row3['hashtags'] = str_replace("#", "", $row3['hashtags']); 

						// Compare hashtags between the user and not followed user and get similar hashtags
						$hashtags_word = checkHashtagExist(strtolower($hashtags[$i]),strtolower($row3['hashtags']));
						if(!empty($hashtags_word)){
							$hashtags_word = implode("", $hashtags_word);
						}

						if($hashtags_word != "" || $hashtags_word != NULL){
							if(substr($hashtags_word,0,1) == "#"){
								$hashtags_word = str_replace("#", "", $hashtags_word);
							}

 /*
 		Display places with thumbnail based on followed users who visited a particular place and its type of establishment based on hashtag. Place who already visited by the user will be excluded
*/
 							$search5['select'] = "DISTINCT user_id, h.place_id, video_id, total_count";
 							$search5['table'] = "veeds_users_visit_history h, veeds_establishment e, veeds_users_place_history p";
 							$search5['where'] = "user_id IN (".$users.")
 													AND h.place_id = e.place_id
 													AND h.place_id = p.place_id
 													AND (hashtags LIKE '%".$hashtags_word."%' OR place_name LIKE '%".$hashtags_word."%')
			 										AND h.place_id NOT IN (SELECT place_id FROM veeds_users_visit_history
			 																WHERE user_id = '".$_POST['user_id']."')
			 										AND date_visit IN (SELECT MAX(date_visit)
																		FROM veeds_users_visit_history
																		WHERE user_id IN (".$users.")
																		GROUP BY place_id)
													AND date_visit BETWEEN (NOW() - INTERVAL 7 DAY) AND NOW()";
							$search5['filters'] = "GROUP BY place_id ORDER BY total_count DESC";
							// echo implode(" ", $search5)."<br>";
							if(jp_count($search5) > 0){

								$result5 = jp_get($search5);
								while ($row5 = mysqli_fetch_assoc($result5)) {
									
									$search6['select'] = "place_id, location_id, place_name, location, tags, coordinates";
									$search6['table'] = "veeds_establishment";
									$search6['where'] = "place_id LIKE '%".$row5['place_id']."%'";

									if(jp_count($search6) > 0){

										$result6 = jp_get($search6);
										while ($row6 = mysqli_fetch_assoc($result6)) {
											
											$search7['select'] = "DISTINCT user_id, video_id, video_name, video_file, video_thumb, date_upload, date_expiry";
											$search7['table'] = "veeds_videos";
											$search7['where'] = "video_id = '".$row5['video_id']."'
																	AND DATE_FORMAT(date_expiry,'%Y-%m-%d %H:%i %s') > NOW()";

											if(jp_count($search7) > 0){

												$result7 = jp_get($search7);
												while ($row7 = mysqli_fetch_assoc($result7)) {
													
													$row7 = array(
																	'place_id' => $row5['place_id'],
					 												'location_id' => $row6['location_id'],
					 												'place_name' => $row6['place_name'],
					 												'location' => $row6['location'],
					 												'coordinates' => $row6['coordinates'],
					 												'tags' => $row6['tags'],
					 												'user_id' => $row5['user_id'],
					 												'video_id' => $row5['video_id'],
					 												'video_file' => $row7['video_file'],
					 												'video_thumb' => $row7['video_thumb'],
					 												'date_upload' => $row7['date_upload'],
					 												'date_expiry' => $row7['date_expiry'],
					 												'total_count' => (int)$row5['total_count'],
					 												'logged_id' => $_POST['user_id']
																);

													if(!in_array($row5['place_id'],$array['place_id'])){
														$array['place_id'][] = $row5['place_id'];
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
						}

						// Compare hashtags from the user and followed user if there is similarity
						$hashtags_word1 = checkHashtagExist1(strtolower($hashtags[$i]),strtolower($row3['hashtags']));
						if(!empty($hashtags_word1)){
							$hashtags_word1 = implode("", $hashtags_word1);
						}

						if($hashtags_word1 != "" || $hashtags_word1 != NULL){
							if(substr($hashtags_word1,0,1) == "#"){
								$hashtags_word1 = str_replace("#", "", $hashtags_word1);
							}

							$search8['select'] = "DISTINCT user_id, h.place_id, video_id, total_count";
 							$search8['table'] = "veeds_users_visit_history h, veeds_establishment e, veeds_users_place_history p";
 							$search8['where'] = "user_id IN (".$users.")
 													AND h.place_id = e.place_id
 													AND h.place_id = p.place_id
 													AND (hashtags LIKE '%".$hashtags_word1."%' OR place_name LIKE '%".$hashtags_word1."%')
			 										AND h.place_id NOT IN (SELECT place_id FROM veeds_users_visit_history
			 																WHERE user_id = '".$_POST['user_id']."')
			 										AND date_visit IN (SELECT MAX(date_visit)
																		FROM veeds_users_visit_history
																		WHERE user_id IN (".$users.")
																		GROUP BY place_id)
													AND date_visit BETWEEN (NOW() - INTERVAL 7 DAY) AND NOW()";
							$search8['filters'] = "GROUP BY place_id ORDER BY total_count DESC";

							if(jp_count($search8) > 0){

								$result8 = jp_get($search8);
								while ($row8 = mysqli_fetch_assoc($result8)) {
									
									$search9['select'] = "place_id, location_id, place_name, location, tags, coordinates";
									$search9['table'] = "veeds_establishment";
									$search9['where'] = "place_id LIKE '%".$row8['place_id']."%'";

									if(jp_count($search9) > 0){

										$result9 = jp_get($search9);
										while ($row9 = mysqli_fetch_assoc($result9)) {
											
											$search10['select'] = "DISTINCT user_id, video_id, video_name, video_file, video_thumb, date_upload, date_expiry";
											$search10['table'] = "veeds_videos";
											$search10['where'] = "video_id = '".$row8['video_id']."'
																	AND DATE_FORMAT(date_expiry,'%Y-%m-%d %H:%i %s') > NOW()";

											if(jp_count($search10) > 0){

												$result10 = jp_get($search10);
												while ($row10 = mysqli_fetch_assoc($result10)) {
													
													$row10 = array(
																	'place_id' => $row8['place_id'],
					 												'location_id' => $row9['location_id'],
					 												'place_name' => $row9['place_name'],
					 												'location' => $row9['location'],
					 												'coordinates' => $row9['coordinates'],
					 												'tags' => $row9['tags'],
					 												'user_id' => $row8['user_id'],
					 												'video_id' => $row8['video_id'],
					 												'video_file' => $row10['video_file'],
					 												'video_thumb' => $row10['video_thumb'],
					 												'date_upload' => $row10['date_upload'],
					 												'date_expiry' => $row10['date_expiry'],
					 												'total_count' => (int)$row8['total_count'],
					 												'logged_id' => $_POST['user_id']
																);

													if(!in_array($row8['place_id'],$array['place_id'])){
														$array['place_id'][] = $row8['place_id'];
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

					// Get words from veeds_dictionary
					$search11['select'] = "word";
					$search11['table'] = "veeds_dictionary";

					$result11 = jp_get($search11);

					while ($row11 = mysqli_fetch_assoc($result11)) {
						$row11['word'] = str_replace("\n", "", $row11['word']);
						$list_of_words[] = $row11['word'];
						// echo implode(",", $list_of_words);
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
							$search12['select'] = "DISTINCT user_id, h.place_id, video_id, total_count";
 							$search12['table'] = "veeds_users_visit_history h, veeds_establishment e, veeds_users_place_history p";
 							$search12['where'] = "user_id IN (".$users.")
 													AND h.place_id = e.place_id
 													AND h.place_id = p.place_id
 													AND (hashtags LIKE '%".$final_word."%' OR place_name LIKE '%".$final_word."%')
			 										AND h.place_id NOT IN (SELECT place_id FROM veeds_users_visit_history
			 																WHERE user_id = '".$_POST['user_id']."')
			 										AND date_visit IN (SELECT MAX(date_visit)
																		FROM veeds_users_visit_history
																		WHERE user_id IN (".$users.")
																		GROUP BY place_id)
													AND date_visit BETWEEN (NOW() - INTERVAL 7 DAY) AND NOW()";
							$search12['filters'] = "GROUP BY place_id ORDER BY total_count DESC";
							// echo implode(" ", $search12);
							if(jp_count($search12) > 0){

								$result12 = jp_get($search12);
								while ($row12 = mysqli_fetch_assoc($result12)) {
									
									$search13['select'] = "place_id, location_id, place_name, location, tags, coordinates";
									$search13['table'] = "veeds_establishment";
									$search13['where'] = "place_id LIKE '%".$row12['place_id']."%'";

									if(jp_count($search13) > 0){

										$result13 = jp_get($search13);
										while ($row13 = mysqli_fetch_assoc($result13)) {
											
											$search14['select'] = "DISTINCT user_id, video_id, video_name, video_file, video_thumb, date_upload, date_expiry";
											$search14['table'] = "veeds_videos";
											$search14['where'] = "video_id = '".$row12['video_id']."'
																	AND DATE_FORMAT(date_expiry,'%Y-%m-%d %H:%i %s') > NOW()";

											if(jp_count($search14) > 0){

												$result14 = jp_get($search14);
												while ($row14 = mysqli_fetch_assoc($result14)) {
													
													$row14 = array(
																	'place_id' => $row12['place_id'],
					 												'location_id' => $row13['location_id'],
					 												'place_name' => $row13['place_name'],
					 												'location' => $row13['location'],
					 												'coordinates' => $row13['coordinates'],
					 												'tags' => $row13['tags'],
					 												'user_id' => $row12['user_id'],
					 												'video_id' => $row12['video_id'],
					 												'video_file' => $row14['video_file'],
					 												'video_thumb' => $row14['video_thumb'],
					 												'date_upload' => $row14['date_upload'],
					 												'date_expiry' => $row14['date_expiry'],
					 												'total_count' => (int)$row12['total_count'],
					 												'logged_id' => $_POST['user_id']	
																);

													if(!in_array($row12['place_id'],$array['place_id'])){
														$array['place_id'][] = $row12['place_id'];
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
				}
			}
		}


		// $location_id = implode(",", $array['location_id']);
		if(!empty($array['place_id'])){
			$location_ext = " AND h.place_id IN (".implode(",", $array['place_id']).")";
		}else{
			$location_ext = "";
		}

		// Display followed users that has been visited a specific place
		$search15['select'] = "u.user_id, u.firstname, u.lastname, u.username, u.personal_information, u.profile_pic, h.place_id, e.location_id";
		$search15['table'] = "veeds_users u, veeds_establishment e, veeds_users_visit_history h";
		$search15['where'] = "u.user_id = h.user_id 
								AND h.place_id = e.place_id 
								AND h.user_id IN (".$users.") ".$location_ext;
		$search15['filters'] = "GROUP BY u.user_id";
		// echo implode(" ", $search15);
		$result15 = jp_get($search15);
		while($row15 = mysqli_fetch_assoc($result15)){
			$list['users'][] = $row15;
		}
		echo json_encode($list);
	}

?>
