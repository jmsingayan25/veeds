<?php

	include("jp_library/jp_lib.php");
	include("functions.php");

	$_POST['user_id'] = "182";
	if(isset($_POST['user_id'])){

		$array = array();
		$list = array();
		$u_blocks = array();
		$list['places'] = array();
		$list['users'] = array();
		$array['location_id'] = array();

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

		// $place_id = implode("','", $array['place_id']);
		$users = implode(",", $array['users_follow']);
		$hashtags_implode = implode(" ", $array['hashtags']);

		foreach($result as $tags => $count) {
			if($tags == "''"){
				$tags = "";
			}
		  	if($count >= 1 && $tags != ''){
				
				$search2['select'] = "DISTINCT h.place_id, e.location_id, e.place_name, e.location, e.coordinates, e.tags, h.user_id, h.video_id, p.total_count";
				$search2['table'] = "veeds_users_visit_history h, veeds_establishment, veeds_users_place_history p";
				$search2['where'] = "h.place_id = e.place_id
										AND h.place_id = p.place_id 
										AND h.user_id IN (".$users.") 
										AND e.tags LIKE '%".$tags."%'
										AND h.place_id NOT IN (SELECT place_id FROM veeds_users_visit_history 
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

		if(isset($hashtags_implode)){
	 		$hashtags = explode(" ",$hashtags_implode);
			for($i = 0; $i < count($hashtags); $i++){
				if(substr($hashtags[$i],0,1) == "#"){

					$search4['select'] = "hashtags";
					$search4['table'] = "veeds_users_visit_history";
					$search4['where'] = "user_id IN (".$users.") AND hashtags != ''";

					// echo implode(" ", $search4).";<br>";
					$result4 = jp_get($search4);
					while ($row4 = mysqli_fetch_assoc($result4)){
						
						$hashtags[$i] = str_replace("#", "", $hashtags[$i]);
						$row4['hashtags'] = str_replace("#", "", $row4['hashtags']);
						// $hashtags_word = substr($hashtags[$i], 1, 3);
						$hashtags_word = checkHashtagExist(strtolower($hashtags[$i]),strtolower($row4['hashtags']));
						if(!empty($hashtags_word)){
							$hashtags_word = implode("", $hashtags_word); // converts array to string
						}

						if($hashtags_word != "" || $hashtags_word != NULL){
							
							if(substr($hashtags_word,0,1) == "#"){
								$hashtags_word = str_replace("#", "", $hashtags_word);
							}
							echo $hashtags_word."<br>";

							
							$search2['select'] = "DISTINCT h.place_id, e.location_id, e.place_name, e.location, e.coordinates, e.tags, h.user_id, h.video_id, p.total_count";
							$search2['table'] = "veeds_users_visit_history h, veeds_establishment e,  veeds_users_place_history p";
							$search2['where'] = "h.place_id = e.place_id 
													AND h.place_id = p.place_id 
													AND h.user_id IN (".$users.") 
													AND (h.hashtags LIKE '%".$hashtags_word."%' OR e.place_name LIKE '%".$hashtags_word."%')
													AND h.place_id NOT IN (SELECT place_id FROM veeds_users_visit_history 
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

						$hashtags_word1 = checkHashtagExist1(strtolower($hashtags[$i]),strtolower($row4['hashtags']));
						if(!empty($hashtags_word1)){
							$hashtags_word1 = implode("", $hashtags_word1); // converts array to string
						}
						if($hashtags_word1 != "" || $hashtags_word1 != NULL){
							
							if(substr($hashtags_word1,0,1) == "#"){
								$hashtags_word1 = str_replace("#", "", $hashtags_word1);
							}
							echo $hashtags_word1."<br>";

							$search2['select'] = "DISTINCT h.place_id, e.location_id, e.place_name, e.location, e.coordinates, e.tags, h.user_id, h.video_id, p.total_count";
							$search2['table'] = "veeds_users_visit_history h, veeds_establishment e,  veeds_users_place_history p";
							$search2['where'] = "h.place_id = e.place_id 
													AND h.place_id = p.place_id 
													AND h.user_id IN (".$users.") 
													AND (h.hashtags LIKE '%".$hashtags_word1."%' OR e.place_name LIKE '%".$hashtags_word1."%')
													AND h.place_id NOT IN (SELECT place_id FROM veeds_users_visit_history 
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
			    }
			}	
		}

		// $location_id = implode(",", $array['location_id']);
		if(!empty($array['location_id'])){
			$location_ext = " AND e.location_id IN (".implode(",", $array['location_id']).")";
		}else{
			$location_ext = "";
		}
		$search3['select'] = "DISTINCT u.user_id, u.firstname, u.lastname, u.username, u.personal_information, u.profile_pic, h.place_id, e.location_id";
		$search3['table'] = "veeds_users u, veeds_establishment e, veeds_users_visit_history h";
		$search3['where'] = "u.user_id = h.user_id 
								AND h.place_id = e.place_id 
								AND h.user_id IN (".$users.") ".$location_ext;
		$search3['filters'] = "GROUP BY u.user_id";
		// echo implode(" ", $search3);
		$result3 = jp_get($search3);
		while($row3 = mysqli_fetch_assoc($result3)){
			$list['users'][] = $row3;
		}
		// echo json_encode($list);
	}


	

	// $stringtofind = "#afternoonrainy";
	// $stringwheretofind = "#rainyafternoon";
	// $new_string = implode("", checkHashtagExist($stringtofind, $stringwheretofind));
	// echo $new_string;
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
