<?php

	include("jp_library/jp_lib.php");

	$_POST['user_id'] = "70";
	if(isset($_POST['user_id'])){

		$array = array();
		$list = array();
		
		$search['select'] = "DISTINCT user_id_follow";
		$search['table'] = "veeds_users_follow";
    	$search['where'] = "user_id_follow NOT IN (SELECT DISTINCT user_id_follow 
    												FROM veeds_users_follow 
    												WHERE user_id = '".$_POST['user_id']."' 
    												AND user_id_follow != '".$_POST['user_id']."')";
    	$search['filters'] = "ORDER BY user_id_follow ASC";
    	// echo implode(" ", $search);
    	if(jp_count($search) > 0){
			$result = jp_get($search);
			while($row = mysqli_fetch_assoc($result)){
				$array['users_notfollow'][] = $row['user_id_follow'];
			}
		}else{
			$array['users_notfollow'][] = "''";
		}

		$search1['select'] = "e.tags, h.hashtags, h.place_id";
		$search1['table'] = "veeds_establishment e, veeds_users_visit_history h";
		$search1['where'] = "e.place_id = h.place_id AND h.user_id = '".$_POST['user_id']."'";
		$search1['filters'] = "GROUP BY e.place_name, e.location";
		// echo implode(" ", $search1);
		if(jp_count($search1) > 0){
			$result1 = jp_get($search1);
			while($row1 = mysqli_fetch_assoc($result1)){
				$array['tags'][] = $row1['tags'];
				$array['hashtags'][] = $row1['hashtags'];
				$array['place_id'][] = $row1['place_id'];
			}
		}else{
			$array['tags'][] = "''";
			$array['hashtags'][] = "''";
			$array['place_id'][] = "''";
		}

		$vals = implode(",", $array['tags']);
		$vals_strip = str_replace(",", " ", $vals);
		$vals_explode = explode(" ", $vals_strip);

		$result = array_combine($vals_explode, array_fill(0, count($vals_explode), 0));

		foreach($vals_explode as $word) {
		    $result[$word]++;
		}

		$list['places'] = array();
		$array['location_id'] = array();
		$place_id = implode(",", $array['place_id']);
		$users = implode(",", $array['users_notfollow']);
		$hashtags_implode = implode(" ", $array['hashtags']);
		
		foreach($result as $word => $count) {
		    if($count >= 1 || isset($hashtags_implode)){
		    	if($word != "''"){
		    		$string_tags = "AND e.tags LIKE '%".$word."%'";
		    	}else{
		    		$string_tags = "";
		    	}
		  		$hashtags = explode(" ",$hashtags_implode);
				for($i = 0; $i < count($hashtags); $i++){
					if(substr($hashtags[$i],0,1) == "#"){
						// echo $hashtags[$i];
						$string_hashtag = "OR (h.hashtags LIKE '%".$hashtags[$i]."%' OR h.hashtags LIKE '".$hashtags[$i]."%')";
					}else{
						$string_hashtag = "";
					}
				
					$search2['select'] = "DISTINCT h.place_id, e.location_id, COUNT(DISTINCT h.user_id) as count, e.place_name, e.location, e.coordinates, e.tags, h.date_visit, h.video_id, v.video_file, v.video_thumb, v.date_upload, v.video_length, v.landscape_file";
					$search2['table'] = "veeds_users_visit_history h, veeds_establishment e, veeds_videos v";
					$search2['where'] = "h.place_id = e.place_id 
											AND h.video_id = v.video_id
											".$string_tags." ".$string_hashtag." 
											AND h.user_id IN (".$users.") 
											AND h.place_id NOT LIKE '%".$place_id."%'";
					$search2['filters'] = "GROUP BY h.place_id ORDER BY `count` DESC";
					// $search2['select'] = "DISTINCT h.place_id, e.location_id, COUNT(DISTINCT h.user_id) as count, e.place_name, e.location, e.coordinates, e.tags";
					// $search2['table'] = "veeds_users_visit_history h, veeds_establishment e";
					// $search2['where'] = "h.place_id = e.place_id 
					// 						".$string_tags." ".$string_hashtag." 
					// 						AND h.user_id IN (".$users.") 
					// 						AND h.place_id NOT LIKE '%".$place_id."%'";
					// $search2['filters'] = "GROUP BY h.place_id ORDER BY `count` DESC";
					// echo implode(" ", $search2).";<br>";
					if(jp_count($search2) > 0){
						$result2 = jp_get($search2);
						while($row2 = mysqli_fetch_assoc($result2)){
							if(!in_array($row2, $list['places'])){
								if(!empty($row2['location_id'])){
									$array['location_id'][] = $row2['location_id'];
								}

								// $search4['select'] = "total_count";
								// $search4['table'] = "veeds_users_place_history";
								// $search4['where'] = "place_id LIKE '%".$row2['place_id']."%'";
								// // echo implode(" ", $search4).";<br>";
								// if(jp_count($search4) > 0){
								// 	$result4 = jp_get($search4);
								// 	$row_result4 = mysqli_fetch_assoc($result4);

								// 	$row2['visitor_count'] = (int)$row_result4['total_count'];
								// }else{
								// 	$row2['visitor_count'] = 0;
								// }

								$row2['logged_id'] = $_POST['user_id'];
								$row2['count'] = (int)$row2['count'];
								$list['places'][] = $row2;	
							}	
						}
					}else{
						if(!in_array("''", $array['location_id']))
							$array['location_id'][] = "''";
					}
				}
		    }else{
				if(!in_array("''", $array['location_id']))
					$array['location_id'][] = "''";
			}
		}

		$location_id = implode(",", $array['location_id']);
		$search3['select'] = "DISTINCT u.user_id, u.firstname, u.lastname, u.username, u.personal_information, u.profile_pic, h.place_id, e.location_id";
		$search3['table'] = "veeds_users u, veeds_establishment e, veeds_users_visit_history h";
		$search3['where'] = "u.user_id = h.user_id 
								AND h.place_id = e.place_id 
								AND e.location_id IN (".$location_id.")
								AND h.user_id IN (".$users.")";
		// echo implode(" ", $search3);
		$result3 = jp_get($search3);
		while($row3 = mysqli_fetch_assoc($result3)){
			$list['users'][] = $row3;
		}
		echo json_encode($list);
	}
?>
