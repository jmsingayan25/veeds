<?php

	include("jp_library/jp_lib.php");
	include("functions.php");
	include("class_place.php");

	$_POST['user_id'] = "183";
	$_POST['coordinates'] = "13.807652,123.850005";
	$_POST['city'] = "Masbate City";

	if(isset($_POST['user_id'])){

		$placeObj = new classPlaceID;
		$placeObj1 = new classPlaceID;
		// $placeObj2 = new classPlaceID;
		$placeObj3 = new classPlaceID;
		$placeObjForTag = new classPlaceID;
		
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
		$search1['select'] = "h.hashtags, h.place_id";
		$search1['table'] = "veeds_establishment e, veeds_users_visit_history h";
		$search1['where'] = "e.place_id = h.place_id AND h.user_id = '".$_POST['user_id']."'";
		$search1['filters'] = "GROUP BY e.place_name, e.location";
		// echo implode(" ", $search1);
		if(jp_count($search1) > 0){
			$result1 = jp_get($search1);
			while($row1 = mysqli_fetch_assoc($result1)){

				$placeObjForTag->setPlaceId($row1['place_id']);
				$placeTypes = $placeObjForTag->getPlaceTypes();

				$array['tags'][] = implode(",", $placeTypes);
				$array['hashtags'][] = $row1['hashtags'];
			}
		}else{
			$array['tags'][] = "''";
			$array['hashtags'][] = "''";
		}

		$vals = implode(",", $array['tags']);
		$vals_remove = str_replace(",point_of_interest,establishment", "", $vals);

		$vals_strip = str_replace(",", " ", $vals_remove);
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
		  	if($count >= 1 && $tags != ''){		

		  		
/* 
		Display places and post thumbnail based on not followed users who visited a particular place and its type of establishment based on count of tags. Places who already visited by the user will be excluded
*/
				$search4['select'] = "DISTINCT h.place_id, location_id, h.user_id, h.video_id, video_thumb, date_upload, date_expiry";
				$search4['table'] = "veeds_establishment e, veeds_users_visit_history h, veeds_videos v";
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
          //     	$search4['where'] = "h.place_id = e.place_id
										// AND h.place_id = v.place_id
										// AND h.user_id IN (".$users.")
										// AND h.place_id NOT IN (SELECT place_id FROM veeds_users_visit_history 
										// 						WHERE user_id = '".$_POST['user_id']."')
										// AND e.tags LIKE '%".$tags."%'
										// AND date_visit IN (SELECT MAX(date_visit) 
										// 					FROM veeds_users_visit_history
          //     												WHERE user_id IN (".$users.") 
          //     												GROUP BY place_id)
										// AND date_visit BETWEEN (NOW() - INTERVAL 7 DAY) AND NOW() 
          //     							AND DATE_FORMAT(date_expiry,'%Y-%m-%d %H:%i %s') > NOW()";
				$search4['filters'] = "GROUP BY h.place_id";
              	// echo implode(" ", $search4).";<br>";	
              	if(jp_count($search4) > 0){

              		$result4 = jp_get($search4);
              		while ($row4 = mysqli_fetch_assoc($result4)) {

              			if(!in_array($row4['location_id'],$location['location'])){
							$location['location'][] = $row4['location_id'];
						}

						$placeObj->setPlaceId($row4['place_id']);
						$placeName = $placeObj->getPlaceName();
						$placeCoordinates = $placeObj->getPlaceCoordinates();

						$row4 = array(
										'location_id' => $row4['location_id'],
										'place_id' => $row4['place_id'],
										'place_name' => $placeName,
										'coordinates' => $placeCoordinates,
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
// 					$search5['select'] = "hashtags";
// 					$search5['table'] = "veeds_users_visit_history";
// 					$search5['where'] = "user_id IN (".$users.") AND hashtags != ''";

// 					$result5 = jp_get($search5);
// 					while ($row5 = mysqli_fetch_assoc($result5)){
// 						// Hashtags used by the user
// 						$hashtags[$i] = str_replace("#", "", $hashtags[$i]);
// 						// Hashtags used by not followed user
// 						$row5['hashtags'] = str_replace("#", "", $row5['hashtags']);
// 						// Compare hashtags between the user and not followed user and get similar hashtags
// 						$hashtags_word = checkHashtagExist(strtolower($hashtags[$i]),strtolower($row5['hashtags']));
// 						if(!empty($hashtags_word)){
// 							$hashtags_word = implode("", $hashtags_word);
// 						}

// // 						if($hashtags_word != "" || $hashtags_word != NULL){
// // 							if(substr($hashtags_word,0,1) == "#"){
// // 								$hashtags_word = str_replace("#", "", $hashtags_word);
// // 							}
// // 							echo $hashtags_word."<br>";
// // /* 
// // 		Display places and post thumbnail based on not followed users who visited a particular place and its type of establishment based on hashtags. Places who already visited by the user will be excluded
// // */							
// // 							$search6['select'] = "h.place_id, location_id, h.user_id, h.video_id, video_thumb, date_upload, date_expiry";
// // 							$search6['table'] = "veeds_establishment e, veeds_users_visit_history h, veeds_videos v";
// // 							// $search6['where'] = "h.place_id = e.place_id
// // 							// 						AND h.place_id = v.place_id
// // 							// 						AND h.user_id IN (".$users.")
// // 							// 						AND h.place_id NOT IN (SELECT place_id 
// // 							// 												FROM veeds_users_visit_history 
// // 							// 												WHERE user_id = '".$_POST['user_id']."')
// // 							// 						AND (hashtags LIKE '%".$hashtags_word."%' OR place_name LIKE '%".$hashtags_word."%')
// // 				 		// 							AND date_visit IN (SELECT MAX(date_visit)
// // 					 	// 												FROM veeds_users_visit_history
// // 					  // 													WHERE user_id IN (".$users.")
// // 					 	// 												GROUP BY place_id)
// // 							// 						AND date_visit BETWEEN (NOW() - INTERVAL 7 DAY) AND NOW() 
// // 					 	// 							AND DATE_FORMAT(date_expiry,'%Y-%m-%d %H:%i %s') > NOW()";
// // 							$search6['where'] = "h.place_id = e.place_id
// // 													AND h.place_id = v.place_id
// // 													AND h.user_id IN (".$users.")
// // 													AND h.place_id NOT IN (SELECT place_id 
// // 																			FROM veeds_users_visit_history 
// // 																			WHERE user_id = '".$_POST['user_id']."')
// // 													AND (hashtags LIKE '%".$hashtags_word."%' OR place_name LIKE '%".$hashtags_word."%')
// // 				 									AND date_visit IN (SELECT MAX(date_visit)
// // 					 													FROM veeds_users_visit_history
// // 					  													WHERE user_id IN (".$users.")
// // 					 													GROUP BY place_id)";
// // 					 		$search6['filters'] = "GROUP BY h.place_id";
// // 					 		// echo implode(" ", $search6)."<br>";
// // 					 		if(jp_count($search6) > 0){

// // 					 			$result6 = jp_get($search6);
// // 					 			while ($row6 = mysqli_fetch_assoc($result6)) {

// // 					 				if(!in_array($row6['location_id'],$location['location'])){
// // 										$location['location'][] = $row6['location_id'];
// // 									}

// // 									// $placeObj1->setPlaceId($row6['place_id']);
// // 									// $placeName1 = $placeObj1->getPlaceName();
// // 									// $placeCoordinates1 = $placeObj1->getPlaceCoordinates();

// // 									$row6 = array(
// // 													'location_id' => $row6['location_id'],
// // 													'place_id' => $row6['place_id'],
// // 													// 'place_name' => $placeName1,
// // 													// 'coordinates' => $placeCoordinates1,
// // 													'user_id' => $row6['user_id'],
// // 													'video_id' => $row6['video_id'],
// // 													'video_thumb' => $row6['video_thumb'],
// // 													'date_upload' => $row6['date_upload'],
// // 													'date_expiry' => $row6['date_expiry'],
// // 													'logged_id' => $_POST['user_id']
// // 												);

// // 					 				if(!in_array($row6, $list['places'])){
// // 					 					// $list['places'][] = $row6;
// // 					 				}
// // 					 			}
// // 					 		}
// // 						}
// // 						// Compare hashtags between the user and not followed user and get similar hashtags
// // 						$hashtags_word1 = checkHashtagExist1(strtolower($hashtags[$i]),strtolower($row5['hashtags']));
// // 						if(!empty($hashtags_word1)){
// // 							$hashtags_word1 = implode("", $hashtags_word1);
// // 						}

// // 						if($hashtags_word1 != "" || $hashtags_word1 != NULL){
// // 							if(substr($hashtags_word1,0,1) == "#"){
// // 								$hashtags_word1 = str_replace("#", "", $hashtags_word1);
// // 							}
// // 							echo $hashtags_word1."<br>";
// // /* 
// // 		Display places and post thumbnail based on not followed users who visited a particular place and its type of establishment based on hashtags. Places who already visited by the user will be excluded
// // */	
// // 							$search7['select'] = "h.place_id, location_id, place_name, e.coordinates, h.user_id, h.video_id, video_thumb, date_upload, date_expiry";
// // 							$search7['table'] = "veeds_establishment e, veeds_users_visit_history h, veeds_videos v";
// // 							// $search7['where'] = "h.place_id = e.place_id
// // 							// 						AND h.place_id = v.place_id
// // 							// 						AND h.user_id IN (".$users.")
// // 							// 						AND h.place_id NOT IN (SELECT place_id 
// // 							// 												FROM veeds_users_visit_history 
// // 							// 												WHERE user_id = '".$_POST['user_id']."')
// // 							// 						AND (hashtags LIKE '%".$hashtags_word1."%' OR place_name LIKE '%".$hashtags_word1."%')
// // 				 		// 							AND date_visit IN (SELECT MAX(date_visit)
// // 					 	// 												FROM veeds_users_visit_history
// // 					  // 													WHERE user_id IN (".$users.")
// // 					 	// 												GROUP BY place_id)
// // 							// 						AND date_visit BETWEEN (NOW() - INTERVAL 7 DAY) AND NOW() 
// // 					 	// 							AND DATE_FORMAT(date_expiry,'%Y-%m-%d %H:%i %s') > NOW()";
// // 							$search7['where'] = "h.place_id = e.place_id
// // 													AND h.place_id = v.place_id
// // 													AND h.user_id IN (".$users.")
// // 													AND h.place_id NOT IN (SELECT place_id 
// // 																			FROM veeds_users_visit_history 
// // 																			WHERE user_id = '".$_POST['user_id']."')
// // 													AND (hashtags LIKE '%".$hashtags_word1."%' OR place_name LIKE '%".$hashtags_word1."%')
// // 				 									AND date_visit IN (SELECT MAX(date_visit)
// // 					 													FROM veeds_users_visit_history
// // 					  													WHERE user_id IN (".$users.")
// // 					 													GROUP BY place_id)";
// // 							$search7['filters'] = "GROUP BY h.place_id";
// // 							// echo implode(" ", $search7);
// // 							if(jp_count($search7) > 0){

// // 					 			$result7 = jp_get($search7);
// // 					 			while ($row7 = mysqli_fetch_assoc($result7)) {

// // 					 				if(!in_array($row7['location_id'],$location['location'])){
// // 										$location['location'][] = $row7['location_id'];
// // 									}

// // 									// $placeObj2->setPlaceId($row7['place_id']);
// // 									// $placeName2 = $placeObj2->getPlaceName();
// // 									// $placeCoordinates2 = $placeObj2->getPlaceCoordinates();

// // 									$row7 = array(
// // 													'location_id' => $row7['location_id'],
// // 													'place_id' => $row7['place_id'],
// // 													// 'place_name' => $placeName2,
// // 													// 'coordinates' => $placeCoordinates2,
// // 													'user_id' => $row7['user_id'],
// // 													'video_id' => $row7['video_id'],
// // 													'video_thumb' => $row7['video_thumb'],
// // 													'date_upload' => $row7['date_upload'],
// // 													'date_expiry' => $row7['date_expiry'],
// // 													'logged_id' => $_POST['user_id']
// // 												);
// // 					 				if(!in_array($row7, $list['places'])){
// // 					 					// $list['places'][] = $row7;
// // 					 				}
// // 					 			}
// // 					 		}
// // 						}
// 					}
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

			// $dictionary_word = word from dictionary
			// $hashtags_word = word from the user hashtag

			$hashtags_word = implode(" ", $words['words']);
			foreach ($new_array_words as $dictionary_word) {
				// Compare words from veeds_dictionary to hashtag and return matched word
				if (stristr($hashtags_word,$dictionary_word) !== false) {
				// if (preg_match("/\b".$dictionary_word."\b/i", $hashtags_word)) {
					echo $dictionary_word."<br>";
					$search8['select'] = "h.place_id, location_id, place_name, e.coordinates, h.user_id, h.video_id, video_thumb, date_upload, date_expiry";
					$search8['table'] = "veeds_establishment e, veeds_users_visit_history h, veeds_videos v";
					// $search8['where'] = "h.place_id = e.place_id
					// 						AND h.place_id = v.place_id
					// 						AND h.user_id IN (".$users.")
					// 						AND h.place_id NOT IN (SELECT place_id 
					// 												FROM veeds_users_visit_history 
					// 												WHERE user_id = '".$_POST['user_id']."')
					// 						AND (hashtags LIKE '%".$dictionary_word."%' OR place_name LIKE '%".$dictionary_word."%')
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
											AND (hashtags LIKE '%".$dictionary_word."%' OR place_name LIKE '%".$dictionary_word."%')
											AND hashtags != ''
		 									AND date_visit IN (SELECT MAX(date_visit)
			 													FROM veeds_users_visit_history
			  													WHERE user_id IN (".$users.")
			 													GROUP BY place_id)";
					$search8['filters'] = "GROUP BY h.place_id";
					// echo implode(" ", $search8)."<br>";
					if(jp_count($search8) > 0){

			 			$result8 = jp_get($search8);
			 			while ($row8 = mysqli_fetch_assoc($result8)) {

			 				if(!in_array($row8['location_id'],$location['location'])){
								$location['location'][] = $row8['location_id'];
							}

							$placeObj3->setPlaceId($row8['place_id']);
							$placeName3 = $placeObj3->getPlaceName();
							$placeCoordinates3 = $placeObj3->getPlaceCoordinates();

							$row8 = array(
											'location_id' => $row8['location_id'],
											'place_id' => $row8['place_id'],
											'place_name' => $placeName3,
											'coordinates' => $placeCoordinates3,
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
				// $list['users'][] = $row9;
			}
		}
		echo json_encode($list);
	}
?>
