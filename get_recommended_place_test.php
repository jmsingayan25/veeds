<?php

	include("jp_library/jp_lib.php");
	include("functions.php");

	$_POST['user_id'] = "183";
	if(isset($_POST['user_id'])){

		$array = array();
		$list = array();
		$u_blocks = array();
		$list_of_words = array();

		$list['users'] = array();
		$array['location_id'] = array();
		$words['words'] = array();
		$list['places'] = array();

		$block['select'] = "DISTINCT user_id";
		$block['table'] = "veeds_users_block";
		$block['where'] = "user_id_block = ".$_POST['user_id'];
		$result_block = jp_get($block);
		while($row = mysqli_fetch_assoc($result_block)){
			$u_blocks[] = $row['user_id'];
		}

		if(count($u_blocks) > 0){
			$u_extend = " AND user_id_follow NOT IN (".implode(",", $u_blocks).")";
		}else{
			$u_extend = "";
		}

		// Get users followed by the user. User from the block list will not be included
		$search['select'] = "DISTINCT user_id_follow";
		$search['table'] = "veeds_users_follow";
    	$search['where'] = "user_id = '".$_POST['user_id']."' 
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

		// Get type of place the user visited and hashtags
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
		// echo $hashtags_implode;

/*
	Display places based on followed users visited and the most number of type of place the user visited
*/
		foreach($result as $tags => $count) {
			if($tags == "''"){
				$tags = "";
			}
		  	if($count >= 1 && $tags != ''){
				
				$search2['select'] = "DISTINCT h.place_id, e.location_id, e.place_name, e.location, e.coordinates, e.tags, h.user_id, h.video_id, p.total_count";
				$search2['table'] = "veeds_users_visit_history h, veeds_establishment e, veeds_users_place_history p";
				$search2['where'] = "h.place_id = e.place_id
										AND h.place_id = p.place_id 
										AND h.user_id IN (".$users.") 
										AND e.tags LIKE '%".$tags."%'
										AND h.place_id NOT IN (SELECT place_id 
																FROM veeds_users_visit_history 
																WHERE user_id = '".$_POST['user_id']."')
										AND h.date_visit IN (SELECT MAX(date_visit)
																FROM veeds_users_visit_history
																WHERE user_id IN (".$users.")
																GROUP BY place_id)";
										// AND h.date_visit BETWEEN (NOW() - INTERVAL 7 DAY) AND NOW()
				$search2['filters'] = "GROUP BY h.place_id ORDER BY p.total_count DESC";
				// echo implode(" ", $search2).";<br>";
				if(jp_count($search2) > 0){
					$result2 = jp_get($search2);
					while($row2 = mysqli_fetch_assoc($result2)){

						$list['places'] = array();

						$search3['select'] = "video_name, video_file, video_thumb, date_upload";
						$search3['table'] = "veeds_videos";
						$search3['where'] = "video_id = '".$row2['video_id']."'
											AND DATE_FORMAT(date_expiry,'%Y-%m-%d %H:%i %s') > NOW()";

						if(jp_count($search3) > 0){
							$result3 = jp_get($search3);
							$row3 = mysqli_fetch_assoc($result3);

							$row2['video_name'] = $row3['video_name'];
							$row2['video_file'] = $row3['video_file'];
							$row2['video_thumb'] = $row3['video_thumb'];
							$row2['date_upload'] = $row3['date_upload'];
						}
						
						$row2['logged_id'] = $_POST['user_id'];
						$row2['total_count'] = (int)$row2['total_count'];

						if(!in_array($row2['location_id'],$array['location_id'])){
							$array['location_id'][] = $row2['location_id'];
						}
						if(!in_array($row2, $list['places'])){
							$list['places'][] = $row2;	
						}	
					}
				}
		    }
		}

/*
	Get places based on words from dictionary that match from the hashtag used
*/
		if(isset($hashtags_implode)){
	 		$hashtags = explode(" ",$hashtags_implode);
			for($i = 0; $i < count($hashtags); $i++){
				if(substr($hashtags[$i],0,1) == "#"){
					$words['words'][] = str_replace("#", "", $hashtags[$i]);

					// Get hashtags that followed users used
					$search4['select'] = "hashtags";
					$search4['table'] = "veeds_users_visit_history";
					$search4['where'] = "user_id IN (".$users.") AND hashtags != ''";

					$result4 = jp_get($search4);
					while ($row4 = mysqli_fetch_assoc($result4)){
						// Hashtag used by the user
						$hashtags[$i] = str_replace("#", "", $hashtags[$i]);
						// Hashtag used by the followed users
						$row4['hashtags'] = str_replace("#", "", $row4['hashtags']); 

						// Compare hashtags between the user and not followed user and get similar hashtags
						$hashtags_word = checkHashtagExist(strtolower($hashtags[$i]),strtolower($row4['hashtags']));
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
							$search2['select'] = "DISTINCT h.place_id, e.location_id, e.place_name, e.location, e.coordinates, e.tags, h.user_id, v.video_id, v.video_name, v.video_file, v.video_thumb, v.date_upload, p.total_count";
							$search2['table'] = "veeds_users_visit_history h, veeds_establishment e, veeds_videos v, veeds_users_place_history p";
							$search2['where'] = "h.place_id = e.place_id 
													AND h.place_id = p.place_id 
													AND h.video_id = v.video_id 
													AND h.user_id IN (".$users.") 
													AND (h.hashtags LIKE '%".$hashtags_word."%' OR e.place_name LIKE '%".$hashtags_word."%')
													AND h.place_id NOT IN (SELECT place_id FROM veeds_users_visit_history 
																			WHERE user_id = '".$_POST['user_id']."')
													AND h.date_visit IN (SELECT MAX(date_visit)
																FROM veeds_users_visit_history
																WHERE user_id IN (".$users.")
																GROUP BY place_id)";
							$search2['filters'] = "GROUP BY h.place_id ORDER BY p.total_count DESC";
							// echo implode(" ", $search2);
							if(jp_count($search2) > 0){
								$result2 = jp_get($search2);
								while($row2 = mysqli_fetch_assoc($result2)){
									$row2['logged_id'] = $_POST['user_id'];
									$row2['total_count'] = (int)$row2['total_count'];
									if(!in_array($row2['location_id'],$array['location_id'])){
										$array['location_id'][] = $row2['location_id'];
									}
									if(!in_array($row2, $list['places'])){
										$list['places'][] = $row2;	
									}	
								}
							}
						}
					}
				}
			}

			// Get words from veeds_dictionary
			$search5['select'] = "word";
			$search5['table'] = "veeds_dictionary";

			$result5 = jp_get($search5);

			while ($row5 = mysqli_fetch_assoc($result5)) {
				$row5['word'] = str_replace("\n", "", $row5['word']);
				$list_of_words[] = $row5['word'];
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

			foreach ($new_array_words as $final_word) {

				// Compare words from veeds_dictionary to hashtag and return matched word
				$hashtags_word = implode(" ", $words['words']);

				if (stristr($hashtags_word,$final_word) !== false) {

					echo $final_word."<br>";

					$search6['select'] = "DISTINCT h.place_id, e.location_id, e.place_name, e.location, e.coordinates, e.tags, h.user_id, h.video_id, p.total_count";
					$search6['table'] = "veeds_users_visit_history h, veeds_establishment e,  veeds_users_place_history p";
					$search6['where'] = "h.place_id = e.place_id 
											AND h.place_id = p.place_id 
											AND h.user_id IN (".$users.") 
											AND (h.hashtags LIKE '%".$final_word."%' OR e.place_name LIKE '%".$final_word."%')
											AND h.place_id NOT IN (SELECT place_id FROM veeds_users_visit_history 
																	WHERE user_id = '".$_POST['user_id']."')
											AND h.date_visit IN (SELECT MAX(date_visit)
														FROM veeds_users_visit_history
														WHERE user_id IN (".$users.")
														GROUP BY place_id)";
											// AND h.date_visit BETWEEN (NOW() - INTERVAL 7 DAY) AND NOW()
					$search6['filters'] = "GROUP BY h.place_id ORDER BY p.total_count DESC";
					// echo implode(" ", $search6).";<br>";
					if(jp_count($search6) > 0){
						$result6 = jp_get($search6);
						while($row6 = mysqli_fetch_assoc($result6)){

							$search7['select'] = "video_name, video_file, video_thumb, date_upload";
							$search7['table'] = "veeds_videos";
							$search7['where'] = "video_id = '".$row2['video_id']."'
												AND DATE_FORMAT(date_expiry,'%Y-%m-%d %H:%i %s') > NOW()";

							if(jp_count($search7) > 0){
								$result7 = jp_get($search7);
								$row7 = mysqli_fetch_assoc($result7);

								$row6['video_name'] = $row7['video_name'];
								$row6['video_file'] = $row7['video_file'];
								$row6['video_thumb'] = $row7['video_thumb'];
								$row6['date_upload'] = $row7['date_upload'];
							}

							$row6['logged_id'] = $_POST['user_id'];
							$row6['total_count'] = (int)$row6['total_count'];

							if(!in_array($row6['location_id'],$array['location_id'])){
								$array['location_id'][] = $row6['location_id'];
							}
							if(!in_array($row6, $list['places'])){
								$list['places'][] = $row6;	
							}	
						}
					}
				}
			}	
	    }

		// $location_id = implode(",", $array['location_id']);
		if(!empty($array['location_id'])){
			$location_ext = " AND e.location_id IN (".implode(",", $array['location_id']).")";
		}else{
			$location_ext = "";
		}

/*
	Display users with its visited place
*/		
		$search8['select'] = "DISTINCT u.user_id, u.firstname, u.lastname, u.username, u.personal_information, u.profile_pic, h.place_id, e.location_id";
		$search8['table'] = "veeds_users u, veeds_establishment e, veeds_users_visit_history h";
		$search8['where'] = "u.user_id = h.user_id 
								AND h.place_id = e.place_id 
								AND h.user_id IN (".$users.") ".$location_ext;
		$search8['filters'] = "GROUP BY u.user_id";
		// echo implode(" ", $search8);
		$result8 = jp_get($search8);
		while($row8 = mysqli_fetch_assoc($result8)){
			$list['users'][] = $row8;
		}
		// echo json_encode($list);
	}


	

	// $stringtofind = "afternoonRainy";
	// $stringwheretofind = "#rainyafternoon";
	// $new_string = implode("", checkHashtagExist($stringtofind, $stringwheretofind));
	// echo $new_string;
	// echo preg_match_all('/((?:^|[A-Z])[a-z]+)/',$stringtofind,$matches);
	// echo implode("", $matches);
	// function checkHashtagExist($stringtofind, $stringwheretofind){

	// 	$temp1 = array();
	// 	$temp2 = array();
	// 	$store = array();
	// 	// $b = 1;

	// 	for($a = 1; $a < strlen($stringtofind); $a++){
	// 		$temp1[$a] = $stringtofind[$a];
	// 		for($b = 1; $b < strlen($stringwheretofind); $b++){
	// 			$temp2[$b] = $stringwheretofind[$b];
	// 			echo "compare character stringtofind: ".$temp1[$a]." == stringwheretofind: ".$temp2[$b]."<br>";

	// 			if($temp1[$a] == $temp2[$b]){
	// 				$store[] = $temp1[$a];
	// 				break;
	// 			}else{
	// 				unset($store);
	// 			}
	// 		}
	// 		if(empty($store)){
	// 			$b = 1;
	// 		}			
	// 	}
	// 	return $store;
	// }

	// function checkHashtagExist($stringtofind, $stringwheretofind){

	// 	$store = "";
	// 	$b = 0;

	// 	for($a = 0; $a < strlen($stringtofind); $a++){
	// 		$temp1 = $stringtofind[$a];
	// 		while($b < strlen($stringwheretofind)){
	// 			$temp2 = $stringwheretofind[$b];
	// 			echo "compare charactertofind: ".$temp1[$a]." == ".$temp2[$b]." :characterwheretofind<br>";

	// 			if($temp1 == $temp2){
	// 				$store += $temp1;
	// 				$b++;
	// 				break;
	// 			}else{
	// 				$store = "";
	// 			}
	// 			$b++;
	// 		}
	// 		if($store == ""){
	// 			$b = 0;

	// 		}			
	// 	}
	// 	return $store;
	// }

	// function checkHashtagExist($stringtofind, $stringwheretofind){

	// 	$temp1 = array();
	// 	$temp2 = array();
	// 	$store = array();

	// 	for($a = 1; $a < strlen($stringtofind); $a++){
	// 		$temp1[$a] = $stringtofind[$a];
	// 		for($b = 1; $b < strlen($stringwheretofind); $b++){
	// 			$temp2[$b] = $stringwheretofind[$b];
	// 			echo "compare character temp1:'".$temp1[$a]."' == temp2:'".$temp2[$b]."'<br>";

	// 			if($temp1[$a] == $temp2[$b]){
	// 				$store[] = $temp1[$a];
	// 				$b++;
	// 				break;
	// 			}
	// 		}			
	// 	}
	// 	return $store;
	// }

	

// }
?>
