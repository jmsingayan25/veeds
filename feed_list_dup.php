<?php
	include("jp_library/jp_lib.php");
	include("functions.php");
	include("class_place.php");
	
	if(empty($_POST['count']) || !isset($_POST['count'])){
		$_POST['count'] = 0;
	}
	$_POST['user_id'] = "271";
	$_POST['logged_id'] = "271";
	$_POST['coordinates'] = "14.5910630605843,121.12628397653";
	$_POST['city'] = "Cainta";
	if(isset($_POST['user_id'])){

		$search100['select'] = "DISTINCT a.video_id, a.video_name, a.description, a.video_id, b.firstname, a.video_thumb, b.lastname, a.date_upload, a.view_count, a.location, b.profile_pic, a.video_length, b.username, a.user_id as uploader_user_id, a.coordinates, a.landscape_file, a.timestamp, a.place_id";
		$search100['table'] = "veeds_users b, veeds_videos a";
		
		$in = $_POST['user_id'];		
		$blocks = array();
		$block['table'] = "veeds_users_block";
		$block['where'] = "user_id_block = ".$_POST['user_id'];
		$result3 = jp_get($block);
		while($row3 = mysqli_fetch_array($result3)){
			$blocks[] = $row3['user_id'];
		}
		
		$block['where'] = "user_id = ".$_POST['user_id'];
		$result3 = jp_get($block);
		while($row3 = mysqli_fetch_array($result3)){
			if(!in_array($row3['user_id_block'], $blocks))
				$blocks[] = $row3['user_id_block'];
		}
		
		$follow['table'] = "veeds_users_follow";
		$follow['where'] = "user_id = ".$_POST['user_id']." AND approved = 1";
		$result2 = jp_get($follow);
		while($row2 = mysqli_fetch_array($result2)){
			if(!in_array($row2['user_id_follow'], $blocks))
				$in .= ', '.$row2['user_id_follow'];
		}
		// $search100['where'] = "a.user_id = b.user_id 
		// 						AND a.user_id IN (".$in.")
		// 						AND a.place_id != '' 
		// 						AND a.user_id != '".$_POST['logged_id']."' 
		// 						AND b.disabled = 0"; 
		// $search100['where'] = "a.user_id = b.user_id AND a.user_id IN (".$in.") AND b.disabled = 0
		// 					AND a.user_id != '".$_POST['logged_id']."'
		// 					AND a.date_upload IN (SELECT MAX(date_upload) 
		// 					                      FROM veeds_videos
		// 					                      WHERE user_id IN (".$in.")
		// 					                      AND place_id != ''
		// 					                      GROUP BY place_id)"; 

		$search100['where'] = "a.user_id = b.user_id 
								AND a.user_id IN (".$in.")
								AND a.place_id != ''
								AND a.user_id != '".$_POST['logged_id']."'
								AND b.disabled = 0
								AND a.date_upload IN (SELECT MAX(date_upload) 
								                      FROM veeds_videos
								                      WHERE user_id IN (".$in.")
								                      AND place_id != ''
								                      GROUP BY place_id)
								OR a.video_id IN (SELECT DISTINCT video_id 
													FROM veeds_video_tags 
													WHERE user_id IN (".$in.")
								)";
		$start = $_POST['count'] * 100;
		$search100['filters'] = "GROUP BY a.place_id ORDER BY a.date_upload DESC LIMIT ".$start.", 100";
		// $search100['filters'] = "ORDER BY a.date_upload DESC LIMIT 1";
		// echo implode(" ", $search100);
		$result = jp_get($search100);
		
		$list = array();
		// $list['videos'] = array();
		
		while($row = mysqli_fetch_assoc($result)){

			$file = $row['landscape_file'];
			$fileimage = $row['video_thumb'];
			// $filesVideo = '/wamp/www/veeds/videos/'.$file;
			$filesVideo = '/xampp/htdocs/veeds/videos/'.$file;
			$filesImage = '/xampp/htdocs/veeds/thumbnails/'.$fileimage;

			if ((!is_null($row['landscape_file']) && file_exists($filesVideo)) || (!is_null($row['video_thumb']) && file_exists($filesImage))) {
				include('video_checks.php');	
				$row['video_name'] = str_replace(';quote;',"'", $row['video_name']);
				$row['description'] = str_replace(';quote;',"'", $row['description']);
				$row['user_id'] = $_POST['user_id'];
				$row['view_count'] = (int) $row['view_count'];
				// if (!is_null($row['active_album'])) {
			 //        $id = intval($row['user_id']);
			 //        $list['videos'][$id] = $row;
		  //     	} else {
				// if(!in_array($row['place_id'], $list['videos']))
		       		 $list['videos'][] = $row;
		      
		      	// }					
			}		
		}

		$list['paging'] = $_POST['count'];

		$in = "";	
		
		// $list = array();
		
		$follower_extend = "";
		$followed_extend = "";
		
		if(isset($_POST['logged_id'])){
			$u_blocks = array();
		
			$block['table'] = "veeds_users_block";
			$block['where'] = "user_id_block = ".$_POST['logged_id'];
			$result3 = jp_get($block);
			while($row3 = mysqli_fetch_array($result3)){
				$u_blocks[] = $row3['user_id'];
			}
		
			if(count($u_blocks) > 0){
				$follower_extend = " AND user_id NOT IN (".implode(",", $u_blocks).")";
				$followed_extend = " AND user_id_follow NOT IN (".implode(",", $u_blocks).")";
			}
		}
		
		$follow2['table'] = "veeds_users_follow";
		
		$follow2['where'] = "user_id = ".$_POST['user_id']." AND approved = 1".$followed_extend;
			$list['count_followed'] = jp_count($follow2);
		
		$follow2['where'] = "user_id_follow = ".$_POST['user_id']." AND approved = 1".$follower_extend;
			$list['count_follower'] = jp_count($follow2);
		
		$follow['table'] = "veeds_users_follow";
		// echo $_POST['followed'];
		// if(isset($_POST['followed'])){
			
			$follow['where'] = "user_id = ".$_POST['user_id']." AND approved = 1".$followed_extend;
			$count_followed = jp_count($follow);
			$result2 = jp_get($follow);
			while($row2 = mysqli_fetch_array($result2)){
				if(empty($in))
					$in = $row2['user_id_follow'];
				else	
					$in .= ', '.$row2['user_id_follow'];
			}
		// }
		// elseif(isset($_POST['request'])){
			
		// 	$follow['where'] = "user_id_follow = ".$_POST['user_id']." AND approved = 0".$follower_extend;
		// 	$result2 = jp_get($follow);
		// 	while($row2 = mysqli_fetch_array($result2)){
		// 		if(empty($in))
		// 			$in = $row2['user_id'];
		// 		else	
		// 			$in .= ', '.$row2['user_id'];
		// 	}
		// }else{
			
		// 	$follow['where'] = "user_id_follow = ".$_POST['user_id']." AND approved = 1".$follower_extend;
		// 	$count_follower = jp_count($follow);
		// 	$result2 = jp_get($follow);
		// 	while($row2 = mysqli_fetch_array($result2)){
		// 		if(empty($in))
		// 			$in = $row2['user_id'];
		// 		else	
		// 			$in .= ', '.$row2['user_id'];
		// 	}
		// }
		
		// $search['select'] = "firstname, lastname, username, personal_information, user_id, profile_pic";
		// $search['table'] = "veeds_users";
		// $search['where'] = "user_id IN (".$in.") AND disabled = 0"; 
		// $start = $_POST['count'] * 10;
		// $search['filters'] = "LIMIT ".$start.", 10";

		$search1['select'] = "firstname, lastname, username, personal_information, user_id, profile_pic";
		$search1['table'] = "veeds_users";
		$search1['where'] = "user_id IN (".$in.") AND disabled = 0"; 
		// $start = $_POST['count'] * 10;
		// $search1['filters'] = "LIMIT ".$start.", 10";
		
		$result = jp_get($search1);
			
		// $list['users'] = array();
		//if(isset($count_followed))
		//	$list['followed_count'] = $count_followed;
		
		//if(isset($count_follower))
		//	$list['follower_count'] = $count_follower;
		
		if(isset($_POST['logged_id']))
			$user_check = $_POST['logged_id'];
		else
			$user_check = $_POST['user_id'];
		if($in != ""){
			while($row = mysqli_fetch_assoc($result)){
				$search2['select'] = "user_id";
				$search2['table'] = "veeds_users_follow";
				$search2['where'] = "user_id = '".$user_check."' AND user_id_follow = '".$row['user_id']."' AND approved = 1";

				// $result2 = jp_get($search2);
				// $row2 = mysqli_fetch_assoc($result2);

				$count2 = jp_count($search2);
				if($count2 > 0){
					$row['followed'] = 1;
				}else{
					$search2['where'] = "user_id = '".$user_check."' AND user_id_follow = '".$row['user_id']."' AND approved = 0"; 
					$count2 = jp_count($search2);
					if($count2 > 0)
						$row['followed'] = 2;
					else
						$row['followed'] = 0;
				}
				
				$search3['select'] = "date_upload";
				$search3['table'] = "veeds_videos";
				$search3['where'] = "user_id = '".$row['user_id']."' 
										AND date_upload IN (SELECT MAX(date_upload) 
															FROM veeds_videos 
															WHERE user_id = '".$row['user_id']."')"; 

				$result3 = jp_get($search3);
				$row3 = mysqli_fetch_assoc($result3);

				// $row['video_id'] = $row3['video_id'];
				// $row['video_name'] = $row3['video_name'];
				// $row['video_file'] = $row3['video_file'];
				// $row['video_thumb'] = $row3['video_thumb'];
				$row['date_upload'] = $row3['date_upload'];
				$row['logged_id'] = $_POST['logged_id'];
				// $list['users'][] = $row;
			}
		}


		$nearbySearchCoordinatesObj = new classPlaceNearbySearch;
		$nearbySearchCoordinatesCityObj = new classPlaceNearbySearch;
		$nearbySearchCoordinatesObj1 = new classPlaceNearbySearch;
		$nearbySearchCoordinatesCityObj1 = new classPlaceNearbySearch;

		$array = array();
		$list_of_words = array();

		// $list['places'] = array();
		$location['location'] = array();
		$location1['location'] = array();
		$words['words'] = array();
		$words1['words'] = array();

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

		$search39['select'] = "DISTINCT user_id_follow";
		$search39['table'] = "veeds_users_follow";
		// $search['where'] = "user_id_follow != '".$_POST['user_id']."'".$u_extend;
		$search39['where'] = "user_id_follow != '".$_POST['user_id']."' 
    						  AND user_id_follow NOT IN (SELECT DISTINCT user_id_follow 
	    												FROM veeds_users_follow 
	    												WHERE user_id = '".$_POST['user_id']."' 
	    												AND user_id_follow != '".$_POST['user_id']."'
	    												AND approved = 1)".$u_extend;
		$search39['filters'] = "ORDER BY user_id_follow ASC";
		echo implode(" ", $search39)."<br>";
		if(jp_count($search39) > 0){
			$result1 = jp_get($search39);
			while($row = mysqli_fetch_assoc($result1)){
				$array['users_not_follow'][] = $row['user_id_follow'];
			}
		}else{
			$array['users_not_follow'][] = "''";
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
		$users_not_follow = implode(",", $array['users_not_follow']);
		$hashtags_implode = implode(" ", $array['hashtags']);

/*
	Get places based on location
*/		
		sleep(2);
		$nearbySearchCoordinatesObj->setCoordinates($_POST['coordinates']);
		$placeID = $nearbySearchCoordinatesObj->getPlaceId();

		$search2['select'] = "h.place_id, location_id, place_name, e.coordinates, h.user_id, h.video_id, video_thumb, date_upload, date_expiry";
		$search2['table'] = "veeds_establishment e, veeds_users_visit_history h, veeds_videos v";
		// $search2['where'] = "h.place_id = e.place_id
		// 						AND h.video_id = v.video_id
		// 						AND e.coordinates = '".$_POST['coordinates']."'
		// 						AND h.user_id IN (".$users.",".$_POST['user_id'].")
		// 						AND date_visit IN (SELECT MAX(date_visit) 
		// 													FROM veeds_users_visit_history
  //             												WHERE user_id IN (".$users.") 
  //             												GROUP BY place_id)";
		$search2['where'] = "h.place_id = e.place_id
								AND h.video_id = v.video_id
								AND h.place_id != ''
								AND h.place_id = '".$placeID."'
								AND h.user_id IN (".$users.")
								AND date_visit IN (SELECT MAX(date_visit) 
															FROM veeds_users_visit_history
              												WHERE user_id IN (".$users.") 
              												GROUP BY place_id)";
    	$search2['filters'] = "GROUP BY h.place_id LIMIT 1";
		// echo implode(" ", $search2);				
		if(jp_count($search2) > 0){

			$return2 = jp_get($search2);
			while ($row2 = mysqli_fetch_assoc($return2)) {
				
				if(!in_array($row2['location_id'],$location['location'])){
					$location['location'][] = $row2['location_id'];

					// sleep(2);
					// $placeIdDetail->setPlaceId($row2['place_id']);
					// $placeName = $placeIdDetail->getPlaceName();

					$row2 = array(
									'location_id' => $row2['location_id'],
									'place_id' => $row2['place_id'],
									// 'place_name' => $placeName,
									// 'place_name' => $row2['place_name'],
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
			}
		}
		// else{
			
		// 	sleep(2);
		// 	$nearbySearchCoordinatesObj->setCoordinates($_POST['coordinates']);
		// 	$placeID = $nearbySearchCoordinatesObj->getPlaceId();
		// 	$placeName = $nearbySearchCoordinatesObj->getPlaceName();
		// 	$placeCoordinates = $nearbySearchCoordinatesObj->getPlaceCoordinates();
		// 	$placeAddress = $nearbySearchCoordinatesObj->getPlaceAddress();

		// 	$search3['select'] = "location_id, place_id";
		// 	$search3['table'] = "veeds_establishment";
		// 	$search3['where'] = "place_id = '".$placeID."'";
		// 	$search3['filters'] = "GROUP BY place_id LIMIT 1";
	
		// 	if(jp_count($search3) > 0){

		// 		$result3 = jp_get($search3);
		// 		while ($row3 = mysqli_fetch_assoc($result3)) {
		// 			if(!in_array($row3['location_id'],$location['location'])){
		// 				$location['location'][] = $row3['location_id'];

		// 				$row3 = array(
		// 								'location_id' => $row3['location_id'],
		// 								'place_id' => $row3['place_id'],
		// 								// 'place_name' => $placeName,
		// 								'coordinates' => $placeCoordinates,
		// 								'user_id' => "",
		// 								'video_id' => "",
		// 								'video_thumb' => "",
		// 								'date_upload' => "",
		// 								'date_expiry' => "",
		// 								'logged_id' => $_POST['user_id']
		// 							);	
		// 				$list['places'][] = $row3;	
		// 			}	
		// 		}
		// 	}else{

		// 		if($placeID != ''){

		// 			// $code = array('place_id' => $placeID);
		// 			$code = array('place_id' => $placeID, 'coordinates' => $placeCoordinates, 'location' => $placeAddress);
		// 			$data['data'] = $code;
		// 			$data['table'] = "veeds_establishment";
		// 			jp_add($data);
					
		// 			$search4['select'] = "location_id, place_id";
		// 			$search4['table'] = "veeds_establishment";
		// 			$search4['where'] = "place_id = '".$placeID."'";
		// 			$search4['filters'] = "GROUP BY place_id LIMIT 1";

		// 			if(jp_count($search4) > 0){

		// 				$result4 = jp_get($search4);
		// 				while ($row4 = mysqli_fetch_assoc($result4)) {
		// 					if(!in_array($row4['location_id'],$location['location'])){
		// 						$location['location'][] = $row4['location_id'];

		// 						$row4 = array(
		// 										'location_id' => $row4['location_id'],
		// 										'place_id' => $row4['place_id'],
		// 										// 'place_name' => $placeName,
		// 										'coordinates' => $placeCoordinates,
		// 										'user_id' => "",
		// 										'video_id' => "",
		// 										'video_thumb' => "",
		// 										'date_upload' => "",
		// 										'date_expiry' => "",
		// 										'logged_id' => $_POST['user_id']
		// 									);	
		// 						$list['places'][] = $row4;	
		// 					}
		// 				}
		// 			}
		// 		}
		// 	}	
		// }
/*
	Get place based on region
*/
		$search5['select'] = "h.place_id, location_id, place_name, e.coordinates, h.user_id, h.video_id, video_thumb, date_upload, date_expiry";
		$search5['table'] = "veeds_establishment e, veeds_users_visit_history h, veeds_videos v";
		// $search5['where'] = "h.place_id = e.place_id
		// 						AND h.video_id = v.video_id
		// 						AND h.place_id != ''
		// 						AND e.location LIKE '%".$_POST['city']."%'
		// 						AND h.user_id IN (".$users.")
		// 						AND date_visit IN (SELECT MAX(date_visit) 
		// 											FROM veeds_users_visit_history
  //     												WHERE user_id IN (".$users.") 
  //     												GROUP BY place_id)
  //     							AND AND DATE_FORMAT(date_expiry,'%Y-%m-%d %H:%i %s') > NOW()";
		$search5['where'] = "h.place_id = e.place_id
								AND h.video_id = v.video_id
								AND h.place_id != ''
								AND e.location LIKE '%".$_POST['city']."%'
								AND h.user_id IN (".$users.")
								AND date_visit IN (SELECT MAX(date_visit) 
													FROM veeds_users_visit_history
      												WHERE user_id IN (".$users.") 
      												GROUP BY place_id)";
		// $search5['filters'] = "GROUP BY h.place_id LIMIT 1";
		// echo implode(" ", $search5);		
		if(jp_count($search5) > 0){	// display place id if no posts associated to the city existed in database

			$return5 = jp_get($search5);
			while ($row5 = mysqli_fetch_assoc($return5)) {

				if(!in_array($row5['location_id'],$location['location'])){
					$location['location'][] = $row5['location_id'];

					// sleep(2);
					// $placeIdDetail->setPlaceId($row5['place_id']);
					// $placeName = $placeIdDetail->getPlaceName();

					$row5 = array(
									'location_id' => $row5['location_id'],
									'place_id' => $row5['place_id'],
									// 'place_name' => $placeName,
									// 'place_name' => $row5['place_name'],
									'coordinates' => $row5['coordinates'],
									'user_id' => $row5['user_id'],
									'video_id' => $row5['video_id'],
									'video_thumb' => $row5['video_thumb'],
									'date_upload' => $row5['date_upload'],
									'date_expiry' => $row5['date_expiry'],
									'logged_id' => $_POST['user_id']
								);
					$list['places'][] = $row5;
				}
			}
		}
		// else{ 

		// 	$search6['select'] = "place_id, place_name, coordinates, location_id";
		// 	$search6['table'] = "veeds_establishment";
		// 	$search6['where'] = "location LIKE '%".$_POST['city']."%' AND place_id != ''";
		// 	$search6['filters'] = "GROUP BY place_id LIMIT 1";

		// 	// display place id if there is an existing data on the database regardless if there is no post associated
		// 	if(jp_count($search6) > 0){	

		// 		$result6 = jp_get($search6);
		// 		while ($row6 = mysqli_fetch_assoc($result6)) {
					
		// 			if(!in_array($row6['location_id'],$location['location'])){
		// 				$location['location'][] = $row6['location_id'];

		// 				// sleep(2);
		// 				// $placeIdDetail->setPlaceId($row6['place_id']);
		// 				// $placeName = $placeIdDetail->getPlaceName();

		// 				$row6 = array(
		// 								'location_id' => $row6['location_id'],
		// 								'place_id' => $row6['place_id'],
		// 								// 'place_name' => $placeName,
		// 								// 'place_name' => $row6['place_name'],
		// 								'coordinates' => $row6['coordinates'],
		// 								'user_id' => "",
		// 								'video_id' => "",
		// 								'video_thumb' => "",
		// 								'date_upload' => "",
		// 								'date_expiry' => "",
		// 								'logged_id' => $_POST['user_id']
		// 							);
		// 				$list['places'][] = $row6;
		// 			}
		// 		}
		// 	// get place id from google
		// 	}else{
				
		// 		sleep(2);
		// 		$nearbySearchCoordinatesCityObj->setCoordinatesCity($_POST['coordinates'],$_POST['city']);
		// 		$cityPlaceID = $nearbySearchCoordinatesCityObj->getPlaceId();
		// 		$cityPlaceName = $nearbySearchCoordinatesCityObj->getPlaceName();
		// 		$cityPlaceCoordinates = $nearbySearchCoordinatesCityObj->getPlaceCoordinates();
		// 		$cityPlaceAddress = $nearbySearchCoordinatesCityObj->getPlaceAddress();
				
		// 		$search7['select'] = "location_id, place_id";
		// 		$search7['table'] = "veeds_establishment";
		// 		$search7['where'] = "place_id = '".$cityPlaceID."'";
		// 		$search7['filters'] = "GROUP BY place_id LIMIT 1";
		// 		// display place id if there is existing data on the database
		// 		if(jp_count($search7) > 0){

		// 			$result7 = jp_get($search7);
		// 			while ($row7 = mysqli_fetch_assoc($result7)) {
		// 				if(!in_array($row7['location_id'],$location['location'])){
		// 					$location['location'][] = $row7['location_id'];

		// 					$row7 = array(
		// 									'location_id' => $row7['location_id'],
		// 									'place_id' => $row7['place_id'],
		// 									// 'place_name' => $cityPlaceName,
		// 									'coordinates' => $cityPlaceCoordinates,
		// 									'user_id' => "",
		// 									'video_id' => "",
		// 									'video_thumb' => "",
		// 									'date_upload' => "",
		// 									'date_expiry' => "",
		// 									'logged_id' => $_POST['user_id']
		// 								);
		// 					$list['places'][] = $row7;
		// 				}		
		// 			}
		// 		}else{

		// 			if($cityPlaceID != ''){

		// 				// $code = array('place_id' => $cityPlaceID);
		// 				$code = array('place_id' => $cityPlaceID, 'coordinates' => $cityPlaceCoordinates, 'location' => $cityPlaceAddress);
		// 				$data['data'] = $code;
		// 				$data['table'] = "veeds_establishment";
		// 				jp_add($data);

		// 				// $newCityPlaceID = jp_last_added();

		// 				$search8['select'] = "location_id, place_id";
		// 				$search8['table'] = "veeds_establishment";
		// 				$search8['where'] = "place_id = '".$cityPlaceID."'";
		// 				$search8['filters'] = "GROUP BY place_id LIMIT 1";

		// 				if(jp_count($search8) > 0){

		// 					$result8 = jp_get($search8);
		// 					while ($row8 = mysqli_fetch_assoc($result8)) {
		// 						if(!in_array($row8['location_id'],$location['location'])){
		// 							$location['location'][] = $row8['location_id'];

		// 							$row8 = array(
		// 										'location_id' => $row8['location_id'],
		// 										'place_id' => $row8['place_id'],
		// 										// 'place_name' => $cityPlaceName,
		// 										'coordinates' => $cityPlaceCoordinates,
		// 										'user_id' => "",
		// 										'video_id' => "",
		// 										'video_thumb' => "",
		// 										'date_upload' => "",
		// 										'date_expiry' => "",
		// 										'logged_id' => $_POST['user_id']
		// 									);	
		// 							$list['places'][] = $row8;
		// 						}	
		// 					}
		// 				}
		// 			}
		// 		}
		// 	}
		// }

		foreach($result as $tags => $count) {
			if($tags == "''"){
				$tags = "";
			}
		  	if($count >= 1 && $tags != ''){		
/* 
		Display places and post thumbnail based on not followed users who visited a particular place and its type of establishment based on count of tags. Places who already visited by the user will be excluded
*/
				$search9['select'] = "h.place_id, location_id, place_name, e.coordinates, h.user_id, h.video_id, video_thumb, date_upload, date_expiry";
				$search9['table'] = "veeds_establishment e, veeds_users_visit_history h, veeds_videos v";
				// $search9['where'] = "h.place_id = e.place_id
				// 						AND h.video_id = v.video_id
				// 						AND h.user_id IN (".$users.")
				// 						AND h.place_id NOT IN (SELECT place_id FROM veeds_users_visit_history 
				// 												WHERE user_id = '".$_POST['user_id']."')
				// 						AND e.tags LIKE '%".$tags."%'
				// 						AND date_visit IN (SELECT MAX(date_visit) 
				// 											FROM veeds_users_visit_history
    //           												WHERE user_id IN (".$users.") 
    //           												GROUP BY place_id)
    //           							AND DATE_FORMAT(date_expiry,'%Y-%m-%d %H:%i %s') > NOW()";
				$search9['where'] = "h.place_id = e.place_id
										AND h.video_id = v.video_id
										AND h.place_id != ''
										AND h.user_id IN (".$users.")
										AND h.place_id NOT IN (SELECT place_id FROM veeds_users_visit_history 
																WHERE user_id = '".$_POST['user_id']."')
										AND e.tags LIKE '%".$tags."%'
										AND date_visit IN (SELECT MAX(date_visit) 
															FROM veeds_users_visit_history
              												WHERE user_id IN (".$users.") 
              												GROUP BY place_id)";
              	$search9['filters'] = "GROUP BY h.place_id ORDER BY COUNT(DISTINCT h.user_id) DESC";
              	// echo implode(" ", $search9);	
              	if(jp_count($search9) > 0){

              		$result9 = jp_get($search9);
              		while ($row9 = mysqli_fetch_assoc($result9)) {

              			if(!in_array($row9['location_id'],$location['location'])){
							$location['location'][] = $row9['location_id'];
						
							// $placeIdDetail->setPlaceId($row9['place_id']);
							// $placeName = $placeIdDetail->getPlaceName();

							$row9 = array(
								'location_id' => $row9['location_id'],
								'place_id' => $row9['place_id'],
								// 'place_name' => $placeName,
								// 'place_name' => $row9['place_name'],
								'coordinates' => $row9['coordinates'],
								'user_id' => $row9['user_id'],
								'video_id' => $row9['video_id'],
								'video_thumb' => $row9['video_thumb'],
								'date_upload' => $row9['date_upload'],
								'date_expiry' => $row9['date_expiry'],
								'logged_id' => $_POST['user_id']
							);

	              			// if(!in_array($row9, $list['places'])){
	              				$list['places'][] = $row9;
	              			// }
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
					$search10['select'] = "hashtags";
					$search10['table'] = "veeds_users_visit_history";
					$search10['where'] = "user_id IN (".$users.") AND hashtags != ''";

					$result10 = jp_get($search10);
					while ($row10 = mysqli_fetch_assoc($result10)){
						// Hashtags used by the user
						$hashtags[$i] = str_replace("#", "", $hashtags[$i]);
						// Hashtags used by not followed user
						$row10['hashtags'] = str_replace("#", "", $row10['hashtags']);
						// Compare hashtags between the user and not followed user and get similar hashtags
						$hashtags_word = checkHashtagExist(strtolower($hashtags[$i]),strtolower($row10['hashtags']));
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
							$search11['select'] = "h.place_id, location_id, place_name, e.coordinates, h.user_id, h.video_id, video_thumb, date_upload, date_expiry";
							$search11['table'] = "veeds_establishment e, veeds_users_visit_history h, veeds_videos v";
							// $search11['where'] = "h.place_id = e.place_id
							// 						AND h.video_id = v.video_id
							// 						AND h.user_id IN (".$users.")
							// 						AND h.place_id NOT IN (SELECT place_id 
							// 												FROM veeds_users_visit_history 
							// 												WHERE user_id = '".$_POST['user_id']."')
							// 						AND hashtags LIKE '%".$hashtags_word."%'
				 		// 							AND date_visit IN (SELECT MAX(date_visit)
					 	// 												FROM veeds_users_visit_history
					  // 													WHERE user_id IN (".$users.")
					 	// 												GROUP BY place_id)
					 	// 							AND DATE_FORMAT(date_expiry,'%Y-%m-%d %H:%i %s') > NOW()";
							$search11['where'] = "h.place_id = e.place_id
													AND h.video_id = v.video_id
													AND h.place_id != ''
													AND h.user_id IN (".$users.")
													AND h.place_id NOT IN (SELECT place_id 
																			FROM veeds_users_visit_history 
																			WHERE user_id = '".$_POST['user_id']."')
													AND hashtags LIKE '%".$hashtags_word."%'
				 									AND date_visit IN (SELECT MAX(date_visit)
					 													FROM veeds_users_visit_history
					  													WHERE user_id IN (".$users.")
					 													GROUP BY place_id)";
					 		$search11['filters'] = "GROUP BY h.place_id ORDER BY COUNT(DISTINCT h.user_id) DESC";
					 		// echo implode(" ", $search11);
					 		if(jp_count($search11) > 0){

					 			$result11 = jp_get($search11);
					 			while ($row11 = mysqli_fetch_assoc($result11)) {

					 				if(!in_array($row11['location_id'],$location['location'])){
										$location['location'][] = $row11['location_id'];

										// $placeIdDetail->setPlaceId($row11['place_id']);
										// $placeName = $placeIdDetail->getPlaceName();

										$row11 = array(
													'location_id' => $row11['location_id'],
													'place_id' => $row11['place_id'],
													// 'place_name' => $placeName,
													// 'place_name' => $row11['place_name'],
													'coordinates' => $row11['coordinates'],
													'user_id' => $row11['user_id'],
													'video_id' => $row11['video_id'],
													'video_thumb' => $row11['video_thumb'],
													'date_upload' => $row11['date_upload'],
													'date_expiry' => $row11['date_expiry'],
													'logged_id' => $_POST['user_id']
												);

						 				// if(!in_array($row11, $list['places'])){
						 					$list['places'][] = $row11;
						 				// }
									}
					 			}
					 		}
						}
						// Compare hashtags between the user and not followed user and get similar hashtags
						$hashtags_word1 = checkHashtagExist1(strtolower($hashtags[$i]),strtolower($row10['hashtags']));
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
							$search12['select'] = "h.place_id, location_id, place_name, e.coordinates, h.user_id, h.video_id, video_thumb, date_upload, date_expiry";
							$search12['table'] = "veeds_establishment e, veeds_users_visit_history h, veeds_videos v";
							// $search12['where'] = "h.place_id = e.place_id
							// 						AND h.video_id = v.video_id
							// 						AND h.user_id IN (".$users.")
							// 						AND h.place_id NOT IN (SELECT place_id 
							// 												FROM veeds_users_visit_history 
							// 												WHERE user_id = '".$_POST['user_id']."')
							// 						AND hashtags LIKE '%".$hashtags_word1."%'
				 		// 							AND date_visit IN (SELECT MAX(date_visit)
					 	// 												FROM veeds_users_visit_history
					  // 													WHERE user_id IN (".$users.")
					 	// 												GROUP BY place_id)
					 	// 							AND DATE_FORMAT(date_expiry,'%Y-%m-%d %H:%i %s') > NOW()";
							$search12['where'] = "h.place_id = e.place_id
													AND h.place_id != ''
													AND h.video_id = v.video_id
													AND h.user_id IN (".$users.")
													AND h.place_id NOT IN (SELECT place_id 
																			FROM veeds_users_visit_history 
																			WHERE user_id = '".$_POST['user_id']."')
													AND hashtags LIKE '%".$hashtags_word1."%'
				 									AND date_visit IN (SELECT MAX(date_visit)
					 													FROM veeds_users_visit_history
					  													WHERE user_id IN (".$users.")
					 													GROUP BY place_id)";
							$search12['filters'] = "GROUP BY h.place_id ORDER BY COUNT(DISTINCT h.user_id) DESC";
							// echo implode(" ", $search12);
							if(jp_count($search12) > 0){

					 			$result12 = jp_get($search12);
					 			while ($row12 = mysqli_fetch_assoc($result12)) {

					 				if(!in_array($row12['location_id'],$location['location'])){
										$location['location'][] = $row12['location_id'];
									
										// $placeIdDetail->setPlaceId($row12['place_id']);
										// $placeName = $placeIdDetail->getPlaceName();

										$row12 = array(
													'location_id' => $row12['location_id'],
													'place_id' => $row12['place_id'],
													// 'place_name' => $placeName,
													// 'place_name' => $row12['place_name'],
													'coordinates' => $row12['coordinates'],
													'user_id' => $row12['user_id'],
													'video_id' => $row12['video_id'],
													'video_thumb' => $row12['video_thumb'],
													'date_upload' => $row12['date_upload'],
													'date_expiry' => $row12['date_expiry'],
													'logged_id' => $_POST['user_id']
												);
						 				// if(!in_array($row12, $list['places'])){
						 					$list['places'][] = $row12;
						 				// }
									}
					 			}
					 		}
						}
					}
			    }
			}

			// Get words from veeds_dictionary
			$search13['select'] = "word";
			$search13['table'] = "veeds_dictionary";

			$result13 = jp_get($search13);

			while ($row13 = mysqli_fetch_assoc($result13)) {
				$row13['word'] = str_replace("\n", "", $row13['word']);
				$list_of_words[] = $row13['word'];
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
					$search14['select'] = "h.place_id, location_id, place_name, e.coordinates, h.user_id, h.video_id, video_thumb, date_upload, date_expiry";
					$search14['table'] = "veeds_establishment e, veeds_users_visit_history h, veeds_videos v";
					// $search14['where'] = "h.place_id = e.place_id
					// 						AND h.video_id = v.video_id
					// 						AND h.user_id IN (".$users.")
					// 						AND h.place_id NOT IN (SELECT place_id 
					// 												FROM veeds_users_visit_history 
					// 												WHERE user_id = '".$_POST['user_id']."')
					// 						AND (hashtags LIKE '%".$final_word."%' OR place_name LIKE '%".$final_word."%')
		 		// 							AND date_visit IN (SELECT MAX(date_visit)
			 	// 												FROM veeds_users_visit_history
			  // 													WHERE user_id IN (".$users.")
			 	// 												GROUP BY place_id)
			 	// 							AND DATE_FORMAT(date_expiry,'%Y-%m-%d %H:%i %s') > NOW()";
					$search14['where'] = "h.place_id = e.place_id
											AND h.video_id = v.video_id
											AND h.place_id != ''
											AND h.user_id IN (".$users.")
											AND h.place_id NOT IN (SELECT place_id 
																	FROM veeds_users_visit_history 
																	WHERE user_id = '".$_POST['user_id']."')
											AND (hashtags LIKE '%".$final_word."%' OR place_name LIKE '%".$final_word."%')
		 									AND date_visit IN (SELECT MAX(date_visit)
			 													FROM veeds_users_visit_history
			  													WHERE user_id IN (".$users.")
			 													GROUP BY place_id)";
					$search14['filters'] = "GROUP BY h.place_id ORDER BY COUNT(DISTINCT h.user_id) DESC";
					// echo implode(" ", $search14)."<br>";
					if(jp_count($search14) > 0){

			 			$result14 = jp_get($search14);
			 			while ($row14 = mysqli_fetch_assoc($result14)) {

			 				if(!in_array($row14['location_id'],$location['location'])){
								$location['location'][] = $row14['location_id'];
							
								// $placeIdDetail->setPlaceId($row14['place_id']);
								// $placeName = $placeIdDetail->getPlaceName();

								$row14 = array(
											'location_id' => $row14['location_id'],
											'place_id' => $row14['place_id'],
											// 'place_name' => $placeName,
											// 'place_name' => $row14['place_name'],
											'coordinates' => $row14['coordinates'],
											'user_id' => $row14['user_id'],
											'video_id' => $row14['video_id'],
											'video_thumb' => $row14['video_thumb'],
											'date_upload' => $row14['date_upload'],
											'date_expiry' => $row14['date_expiry'],
											'logged_id' => $_POST['user_id']
										);

				 				// if(!in_array($row14, $list['places'])){
				 					$list['places'][] = $row14;
				 				// }
							}
			 			}
			 		}
				}
			}
		}

		$search38['select'] = "COUNT(DISTINCT place_id) as place_count";
		$search38['table'] = "veeds_users_visit_history";
		$search38['where'] = "user_id = '".$_POST['user_id']."'";

		$count_result = jp_get($search38);
		$place_count = mysqli_fetch_assoc($count_result);

		//	$vals = number of unique tags of establishment
		//	User has followed users and has visited 	less than 10 unique places
		// if($hashtags_implode == "''" && $users != "''" && $post_count['place_count'] < 10){
		// if($hashtags_implode == "''" && $users != "''" && $vals == "''"){
		if($users != "''" && $place_count['place_count'] < 10){

			$search37['select'] = "h.place_id, location_id, place_name, e.coordinates, h.user_id, h.video_id, video_thumb, date_upload, date_expiry";
			$search37['table'] = "veeds_establishment e, veeds_users_visit_history h, veeds_videos v";
			// $search37['where'] = "h.place_id = e.place_id
			// 						AND h.video_id = v.video_id
			// 						AND h.user_id IN (".$users.")
			// 						AND h.place_id NOT IN (SELECT place_id 
			// 												FROM veeds_users_visit_history 
			// 												WHERE user_id = '".$_POST['user_id']."')
			// 						AND (hashtags LIKE '%".$final_word."%' OR place_name LIKE '%".$final_word."%')
			// 							AND date_visit IN (SELECT MAX(date_visit)
	 	// 												FROM veeds_users_visit_history
	  // 													WHERE user_id IN (".$users.")
	 	// 												GROUP BY place_id)
	 	// 							AND DATE_FORMAT(date_expiry,'%Y-%m-%d %H:%i %s') > NOW()";
			$search37['where'] = "h.place_id = e.place_id
									AND h.video_id = v.video_id
									AND h.place_id != ''
									AND h.user_id IN (".$users.")
									AND h.place_id NOT IN (SELECT place_id 
															FROM veeds_users_visit_history 
															WHERE user_id = '".$_POST['user_id']."')
									AND date_visit IN (SELECT MAX(date_visit)
	 													FROM veeds_users_visit_history
	  													WHERE user_id IN (".$users.")
	 													GROUP BY place_id)";
			$search37['filters'] = "GROUP BY h.place_id ORDER BY COUNT(DISTINCT h.user_id) DESC";
			// echo implode(" ", $search37)."<br>";
			if(jp_count($search37) > 0){

	 			$result37 = jp_get($search37);
	 			while ($row37 = mysqli_fetch_assoc($result37)) {

	 				if(!in_array($row37['location_id'],$location['location'])){
						$location['location'][] = $row37['location_id'];
					
						// $placeIdDetail->setPlaceId($row37['place_id']);
						// $placeName = $placeIdDetail->getPlaceName();

						$row37 = array(
									'location_id' => $row37['location_id'],
									'place_id' => $row37['place_id'],
									// 'place_name' => $placeName,
									// 'place_name' => $row37['place_name'],
									'coordinates' => $row37['coordinates'],
									'user_id' => $row37['user_id'],
									'video_id' => $row37['video_id'],
									'video_thumb' => $row37['video_thumb'],
									'date_upload' => $row37['date_upload'],
									'date_expiry' => $row37['date_expiry'],
									'logged_id' => $_POST['user_id']
								);

		 				// if(!in_array($row37, $list['places'])){
		 					$list['places'][] = $row37;
		 				// }
					}
	 			}
	 		}
		}
		
		// $search37['select'] = "h.place_id, location_id, place_name, e.coordinates, h.user_id, h.video_id, video_thumb, date_upload, date_expiry";
		// $search37['table'] = "veeds_establishment e, veeds_users_visit_history h, veeds_videos v";
		// // $search37['where'] = "h.place_id = e.place_id
		// // 						AND h.video_id = v.video_id
		// // 						AND h.user_id IN (".$users.")
		// // 						AND h.place_id NOT IN (SELECT place_id 
		// // 												FROM veeds_users_visit_history 
		// // 												WHERE user_id = '".$_POST['user_id']."')
		// // 						AND (hashtags LIKE '%".$final_word."%' OR place_name LIKE '%".$final_word."%')
		// // 							AND date_visit IN (SELECT MAX(date_visit)
 	// // 												FROM veeds_users_visit_history
  // // 													WHERE user_id IN (".$users.")
 	// // 												GROUP BY place_id)
 	// // 							AND DATE_FORMAT(date_expiry,'%Y-%m-%d %H:%i %s') > NOW()";
		// $search37['where'] = "h.place_id = e.place_id
		// 						AND h.video_id = v.video_id
		// 						AND h.place_id != ''
		// 						AND h.user_id IN (".$users.")
		// 						AND h.place_id NOT IN (SELECT place_id 
		// 												FROM veeds_users_visit_history 
		// 												WHERE user_id = '".$_POST['user_id']."')
		// 						AND date_visit IN (SELECT MAX(date_visit)
 	// 												FROM veeds_users_visit_history
  // 													WHERE user_id IN (".$users.")
 	// 												GROUP BY place_id)";
		// $search37['filters'] = "GROUP BY h.place_id";
		// // echo implode(" ", $search37)."<br>";
		// if(jp_count($search37) > 0){

 	// 		$result37 = jp_get($search37);
 	// 		while ($row37 = mysqli_fetch_assoc($result37)) {

 	// 			if(!in_array($row37['location_id'],$location['location'])){
		// 			$location['location'][] = $row37['location_id'];
				
		// 			// $placeIdDetail->setPlaceId($row37['place_id']);
		// 			// $placeName = $placeIdDetail->getPlaceName();

		// 			$row37 = array(
		// 						'location_id' => $row37['location_id'],
		// 						'place_id' => $row37['place_id'],
		// 						// 'place_name' => $placeName,
		// 						// 'place_name' => $row37['place_name'],
		// 						'coordinates' => $row37['coordinates'],
		// 						'user_id' => $row37['user_id'],
		// 						'video_id' => $row37['video_id'],
		// 						'video_thumb' => $row37['video_thumb'],
		// 						'date_upload' => $row37['date_upload'],
		// 						'date_expiry' => $row37['date_expiry'],
		// 						'logged_id' => $_POST['user_id']
		// 					);

	 // 				// if(!in_array($row37, $list['places'])){
	 // 					$list['places'][] = $row37;
	 // 				// }
		// 		}
 	// 		}
 	// 	}

		if(!empty($location['location'])){
			$location_ext = " AND e.location_id IN (".implode(",", $location['location']).")";
		}else{
			$location_ext = "";
		}

		// Display followed users that has been visited a specific place
		$search15['select'] = "u.user_id, u.firstname, u.lastname, u.username, u.personal_information, u.profile_pic, h.place_id, e.location_id";
		$search15['table'] = "veeds_users u, veeds_establishment e, veeds_users_visit_history h";
		$search15['where'] = "u.user_id = h.user_id 
								AND h.place_id = e.place_id 
								AND h.user_id IN (".$users.") 
								AND disabled = 0 ".$location_ext;
		$search15['filters'] = "GROUP BY u.user_id";

		// echo implode(" ", $search15);

		if(jp_count($search15) > 0){
			
			$result15 = jp_get($search15);
			while($row15 = mysqli_fetch_assoc($result15)){

				$search16['select'] = "user_id";
				$search16['table'] = "veeds_users_follow";
				$search16['where'] = "user_id = '".$_POST['user_id']."' AND user_id_follow = '".$row15['user_id']."' AND approved = 1";
				$count16 = jp_count($search16);
				if($count16 > 0){
					$row15['followed'] = 1;
				}else{
					$search16['where'] = "user_id = '".$_POST['user_id']."' AND user_id_follow = '".$row15['user_id']."' AND approved = 0";
					$count16 = jp_count($search16);
					if($count10 > 0){
						$row15['followed'] = 2;
					}else{
						$row15['followed'] = 0;
					}
				}

				$search17['select'] = "user_id";
				$search17['table'] = "veeds_users_block";
				// $search2['where'] = "user_id = '".$_POST['user_id']."' AND user_id_block = '".$row10['user_id']."'"; 
				$search17['where'] = "user_id = '".$_POST['user_id']."' AND user_id_block = '".$row15['user_id']."'"; 
				$count17 = (int)jp_count($search17);
				if($count17 > 0){
					$row15['blocked'] = true;
				}else{
					$row15['blocked'] = false;
				}

				$search18['select'] = "date_upload, coordinates";
				$search18['table'] = "veeds_videos";
				$search18['where'] = "user_id = '".$row15['user_id']."' 
										AND date_upload IN (SELECT MAX(date_upload) 
															FROM veeds_videos 
															WHERE user_id = '".$row15['user_id']."')"; 

				$result18 = jp_get($search18);
				$row18 = mysqli_fetch_assoc($result18);
				// $row15['logged_id'] = $_POST['user_id'];
				$row15 = array(
								'firstname' => $row15['firstname'],
								'lastname' => $row15['lastname'],
								'username' => $row15['username'],
								'personal_information' => $row15['personal_information'],
								'user_id' => $row15['user_id'],
								'profile_pic' => $row15['profile_pic'],
								'followed' => $row15['followed'],
								'date_upload' => $row18['date_upload'],
								'coordinates' => $row18['coordinates'],
								'logged_id' => $_POST['user_id']
							);
				$list['users'][] = $row15;
			}
		}

/*

	RECOMMENDED PLACES AND USERS FOR NOT FOLLOWED

*/

/*
	Get places based on location
*/		
		sleep(2);
		$nearbySearchCoordinatesObj1->setCoordinates($_POST['coordinates']);
		$placeID = $nearbySearchCoordinatesObj1->getPlaceId();

		$search19['select'] = "h.place_id, location_id, place_name, e.coordinates, h.user_id, h.video_id, video_thumb, date_upload, date_expiry";
		$search19['table'] = "veeds_establishment e, veeds_users_visit_history h, veeds_videos v";
		// $search19['where'] = "h.place_id = e.place_id
		// 						AND h.video_id = v.video_id
		// 						AND e.coordinates = '".$_POST['coordinates']."'
		// 						AND h.user_id IN (".$users.",".$_POST['user_id'].")
		// 						AND date_visit IN (SELECT MAX(date_visit) 
		// 													FROM veeds_users_visit_history
  //             												WHERE user_id IN (".$users.") 
  //             												GROUP BY place_id)";
		$search19['where'] = "h.place_id = e.place_id
								AND h.video_id = v.video_id
								AND h.place_id != ''
								AND h.place_id = '".$placeID."'
								AND h.user_id IN (".$users_not_follow.")
								AND date_visit IN (SELECT MAX(date_visit) 
															FROM veeds_users_visit_history
              												WHERE user_id IN (".$users_not_follow.") 
              												GROUP BY place_id)";
    	$search19['filters'] = "GROUP BY h.place_id LIMIT 1";
		// echo implode(" ", $search19);				
		if(jp_count($search19) > 0){

			$return19 = jp_get($search19);
			while ($row19 = mysqli_fetch_assoc($return19)) {
				
				if(!in_array($row19['location_id'],$location1['location'])){
					$location1['location'][] = $row19['location_id'];

					// sleep(19);
					// $placeIdDetail->setPlaceId($row19['place_id']);
					// $placeName = $placeIdDetail->getPlaceName();

					$row19 = array(
									'location_id' => $row19['location_id'],
									'place_id' => $row19['place_id'],
									// 'place_name' => $placeName,
									// 'place_name' => $row19['place_name'],
									'coordinates' => $row19['coordinates'],
									'user_id' => $row19['user_id'],
									'video_id' => $row19['video_id'],
									'video_thumb' => $row19['video_thumb'],
									'date_upload' => $row19['date_upload'],
									'date_expiry' => $row19['date_expiry'],
									'logged_id' => $_POST['user_id']
								);
					$list['world_places'][] = $row19;
				}
			}
		}
		// else{
			
		// 	sleep(2);
		// 	$nearbySearchCoordinatesObj1->setCoordinates($_POST['coordinates']);
		// 	$placeID = $nearbySearchCoordinatesObj1->getPlaceId();
		// 	$placeName = $nearbySearchCoordinatesObj1->getPlaceName();
		// 	$placeCoordinates = $nearbySearchCoordinatesObj1->getPlaceCoordinates();
		// 	$placeAddress = $nearbySearchCoordinatesObj1->getPlaceAddress();

		// 	$search20['select'] = "location_id, place_id";
		// 	$search20['table'] = "veeds_establishment";
		// 	$search20['where'] = "place_id = '".$placeID."'";
		// 	$search20['filters'] = "GROUP BY place_id LIMIT 1";
	
		// 	if(jp_count($search20) > 0){

		// 		$result20 = jp_get($search20);
		// 		while ($row20 = mysqli_fetch_assoc($result20)) {
		// 			if(!in_array($row20['location_id'],$location1['location'])){
		// 				$location1['location'][] = $row20['location_id'];

		// 				$row20 = array(
		// 								'location_id' => $row20['location_id'],
		// 								'place_id' => $row20['place_id'],
		// 								// 'place_name' => $placeName,
		// 								'coordinates' => $placeCoordinates,
		// 								'user_id' => "",
		// 								'video_id' => "",
		// 								'video_thumb' => "",
		// 								'date_upload' => "",
		// 								'date_expiry' => "",
		// 								'logged_id' => $_POST['user_id']
		// 							);	
		// 				$list['world_places'][] = $row20;	
		// 			}	
		// 		}
		// 	}else{

		// 		if($placeID != ''){

		// 			// $code = array('place_id' => $placeID);
		// 			$code = array('place_id' => $placeID, 'coordinates' => $placeCoordinates, 'location' => $placeAddress);
		// 			$data['data'] = $code;
		// 			$data['table'] = "veeds_establishment";
		// 			jp_add($data);
					
		// 			$search21['select'] = "location_id, place_id";
		// 			$search21['table'] = "veeds_establishment";
		// 			$search21['where'] = "place_id = '".$placeID."'";
		// 			$search21['filters'] = "GROUP BY place_id LIMIT 1";

		// 			if(jp_count($search21) > 0){

		// 				$result21 = jp_get($search21);
		// 				while ($row21 = mysqli_fetch_assoc($result21)) {
		// 					if(!in_array($row21['location_id'],$location1['location'])){
		// 						$location1['location'][] = $row21['location_id'];

		// 						$row21 = array(
		// 										'location_id' => $row21['location_id'],
		// 										'place_id' => $row21['place_id'],
		// 										// 'place_name' => $placeName,
		// 										'coordinates' => $placeCoordinates,
		// 										'user_id' => "",
		// 										'video_id' => "",
		// 										'video_thumb' => "",
		// 										'date_upload' => "",
		// 										'date_expiry' => "",
		// 										'logged_id' => $_POST['user_id']
		// 									);	
		// 						$list['world_places'][] = $row21;	
		// 					}
		// 				}
		// 			}
		// 		}
		// 	}	
		// }
/*
	Get place based on region
*/
		$search22['select'] = "h.place_id, location_id, place_name, e.coordinates, h.user_id, h.video_id, video_thumb, date_upload, date_expiry";
		$search22['table'] = "veeds_establishment e, veeds_users_visit_history h, veeds_videos v";
		// $search22['where'] = "h.place_id = e.place_id
		// 						AND h.video_id = v.video_id
		// 						AND h.place_id != ''
		// 						AND e.location LIKE '%".$_POST['city']."%'
		// 						AND h.user_id IN (".$users_not_follow.")
		// 						AND date_visit IN (SELECT MAX(date_visit) 
		// 											FROM veeds_users_visit_history
  //     												WHERE user_id IN (".$users_not_follow.") 
  //     												GROUP BY place_id)
  //     							AND AND DATE_FORMAT(date_expiry,'%Y-%m-%d %H:%i %s') > NOW()";
		$search22['where'] = "h.place_id = e.place_id
								AND h.video_id = v.video_id
								AND h.place_id != ''
								AND e.location LIKE '%".$_POST['city']."%'
								AND h.user_id IN (".$users_not_follow.")
								AND date_visit IN (SELECT MAX(date_visit) 
													FROM veeds_users_visit_history
      												WHERE user_id IN (".$users_not_follow.") 
      												GROUP BY place_id)
      							AND AND DATE_FORMAT(date_expiry,'%Y-%m-%d %H:%i %s') > NOW()";
		$search22['filters'] = "GROUP BY h.place_id LIMIT 1";
					
		if(jp_count($search22) > 0){	// display place id if no posts associated to the city existed in database

			$return22 = jp_get($search22);
			while ($row22 = mysqli_fetch_assoc($return22)) {

				if(!in_array($row22['location_id'],$location1['location'])){
					$location1['location'][] = $row22['location_id'];

					// sleep(2);
					// $placeIdDetail->setPlaceId($row22['place_id']);
					// $placeName = $placeIdDetail->getPlaceName();

					$row22 = array(
									'location_id' => $row22['location_id'],
									'place_id' => $row22['place_id'],
									// 'place_name' => $placeName,
									// 'place_name' => $row22['place_name'],
									'coordinates' => $row22['coordinates'],
									'user_id' => $row22['user_id'],
									'video_id' => $row22['video_id'],
									'video_thumb' => $row22['video_thumb'],
									'date_upload' => $row22['date_upload'],
									'date_expiry' => $row22['date_expiry'],
									'logged_id' => $_POST['user_id']
								);
					$list['world_places'][] = $row22;
				}
			}
		}
		// else{ 

		// 	$search23['select'] = "place_id, place_name, coordinates, location_id";
		// 	$search23['table'] = "veeds_establishment";
		// 	$search23['where'] = "location LIKE '%".$_POST['city']."%' AND place_id != ''";
		// 	$search23['filters'] = "GROUP BY place_id LIMIT 1";

		// 	// display place id if there is an existing data on the database regardless if there is no post associated
		// 	if(jp_count($search23) > 0){	

		// 		$result23 = jp_get($search23);
		// 		while ($row23 = mysqli_fetch_assoc($result23)) {
					
		// 			if(!in_array($row23['location_id'],$location1['location'])){
		// 				$location1['location'][] = $row23['location_id'];

		// 				// sleep(2);
		// 				// $placeIdDetail->setPlaceId($row23['place_id']);
		// 				// $placeName = $placeIdDetail->getPlaceName();

		// 				$row23 = array(
		// 								'location_id' => $row23['location_id'],
		// 								'place_id' => $row23['place_id'],
		// 								// 'place_name' => $placeName,
		// 								// 'place_name' => $row23['place_name'],
		// 								'coordinates' => $row23['coordinates'],
		// 								'user_id' => "",
		// 								'video_id' => "",
		// 								'video_thumb' => "",
		// 								'date_upload' => "",
		// 								'date_expiry' => "",
		// 								'logged_id' => $_POST['user_id']
		// 							);
		// 				$list['world_places'][] = $row23;
		// 			}
		// 		}
		// 	// get place id from google
		// 	}else{
				
		// 		sleep(2);
		// 		$nearbySearchCoordinatesCityObj1->setCoordinatesCity($_POST['coordinates'],$_POST['city']);
		// 		$cityPlaceID = $nearbySearchCoordinatesCityObj1->getPlaceId();
		// 		$cityPlaceName = $nearbySearchCoordinatesCityObj1->getPlaceName();
		// 		$cityPlaceCoordinates = $nearbySearchCoordinatesCityObj1->getPlaceCoordinates();
		// 		$cityPlaceAddress = $nearbySearchCoordinatesCityObj1->getPlaceAddress();
				
		// 		$search24['select'] = "location_id, place_id";
		// 		$search24['table'] = "veeds_establishment";
		// 		$search24['where'] = "place_id = '".$cityPlaceID."'";
		// 		$search24['filters'] = "GROUP BY place_id LIMIT 1";
		// 		// display place id if there is existing data on the database
		// 		if(jp_count($search24) > 0){

		// 			$result24 = jp_get($search24);
		// 			while ($row24 = mysqli_fetch_assoc($result24)) {
		// 				if(!in_array($row24['location_id'],$location1['location'])){
		// 					$location1['location'][] = $row24['location_id'];

		// 					$row24 = array(
		// 									'location_id' => $row24['location_id'],
		// 									'place_id' => $row24['place_id'],
		// 									// 'place_name' => $cityPlaceName,
		// 									'coordinates' => $cityPlaceCoordinates,
		// 									'user_id' => "",
		// 									'video_id' => "",
		// 									'video_thumb' => "",
		// 									'date_upload' => "",
		// 									'date_expiry' => "",
		// 									'logged_id' => $_POST['user_id']
		// 								);
		// 					$list['world_places'][] = $row24;
		// 				}		
		// 			}
		// 		}else{

		// 			if($cityPlaceID != ''){

		// 				// $code = array('place_id' => $cityPlaceID);
		// 				$code = array('place_id' => $cityPlaceID, 'coordinates' => $cityPlaceCoordinates, 'location' => $cityPlaceAddress);
		// 				$data['data'] = $code;
		// 				$data['table'] = "veeds_establishment";
		// 				jp_add($data);

		// 				// $newCityPlaceID = jp_last_added();

		// 				$search25['select'] = "location_id, place_id";
		// 				$search25['table'] = "veeds_establishment";
		// 				$search25['where'] = "place_id = '".$cityPlaceID."'";
		// 				$search25['filters'] = "GROUP BY place_id LIMIT 1";

		// 				if(jp_count($search25) > 0){

		// 					$result25 = jp_get($search25);
		// 					while ($row25 = mysqli_fetch_assoc($result25)) {
		// 						if(!in_array($row25['location_id'],$location1['location'])){
		// 							$location1['location'][] = $row25['location_id'];

		// 							$row25 = array(
		// 										'location_id' => $row25['location_id'],
		// 										'place_id' => $row25['place_id'],
		// 										// 'place_name' => $cityPlaceName,
		// 										'coordinates' => $cityPlaceCoordinates,
		// 										'user_id' => "",
		// 										'video_id' => "",
		// 										'video_thumb' => "",
		// 										'date_upload' => "",
		// 										'date_expiry' => "",
		// 										'logged_id' => $_POST['user_id']
		// 									);	
		// 							$list['world_places'][] = $row25;
		// 						}	
		// 					}
		// 				}
		// 			}
		// 		}
		// 	}
		// }

		foreach($result as $tags => $count) {
			if($tags == "''"){
				$tags = "";
			}
		  	if($count >= 1 && $tags != ''){		
/* 
		Display places and post thumbnail based on not followed users who visited a particular place and its type of establishment based on count of tags. Places who already visited by the user will be excluded
*/
				$search26['select'] = "h.place_id, location_id, place_name, e.coordinates, h.user_id, h.video_id, video_thumb, date_upload, date_expiry";
				$search26['table'] = "veeds_establishment e, veeds_users_visit_history h, veeds_videos v";
				// $search26['where'] = "h.place_id = e.place_id
				// 						AND h.video_id = v.video_id
				// 						AND h.user_id IN (".$users_not_follow.")
				// 						AND h.place_id NOT IN (SELECT place_id FROM veeds_users_visit_history 
				// 												WHERE user_id = '".$_POST['user_id']."')
				// 						AND e.tags LIKE '%".$tags."%'
				// 						AND date_visit IN (SELECT MAX(date_visit) 
				// 											FROM veeds_users_visit_history
    //           												WHERE user_id IN (".$users_not_follow.") 
    //           												GROUP BY place_id)
    //           							AND DATE_FORMAT(date_expiry,'%Y-%m-%d %H:%i %s') > NOW()";
				$search26['where'] = "h.place_id = e.place_id
										AND h.video_id = v.video_id
										AND h.place_id != ''
										AND h.user_id IN (".$users_not_follow.")
										AND h.place_id NOT IN (SELECT place_id FROM veeds_users_visit_history 
																WHERE user_id = '".$_POST['user_id']."')
										AND e.tags LIKE '%".$tags."%'
										AND date_visit IN (SELECT MAX(date_visit) 
															FROM veeds_users_visit_history
              												WHERE user_id IN (".$users_not_follow.") 
              												GROUP BY place_id)";
              	$search26['filters'] = "GROUP BY h.place_id ORDER BY COUNT(DISTINCT h.user_id) DESC";
              	// echo implode(" ", $search26)."<br>";	
              	if(jp_count($search26) > 0){

              		$result26 = jp_get($search26);
              		while ($row26 = mysqli_fetch_assoc($result26)) {

              			if(!in_array($row26['location_id'],$location1['location'])){
							$location1['location'][] = $row26['location_id'];
						
							// $placeIdDetail->setPlaceId($row26['place_id']);
							// $placeName = $placeIdDetail->getPlaceName();

							$row26 = array(
								'location_id' => $row26['location_id'],
								'place_id' => $row26['place_id'],
								// 'place_name' => $placeName,
								// 'place_name' => $row26['place_name'],
								'coordinates' => $row26['coordinates'],
								'user_id' => $row26['user_id'],
								'video_id' => $row26['video_id'],
								'video_thumb' => $row26['video_thumb'],
								'date_upload' => $row26['date_upload'],
								'date_expiry' => $row26['date_expiry'],
								'logged_id' => $_POST['user_id']
							);

	              			// if(!in_array($row26, $list['places'])){
	              				$list['world_places'][] = $row26;
	              			// }
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
					$search27['select'] = "hashtags";
					$search27['table'] = "veeds_users_visit_history";
					$search27['where'] = "user_id IN (".$users_not_follow.") AND hashtags != ''";

					$result27 = jp_get($search27);
					while ($row27 = mysqli_fetch_assoc($result27)){
						// Hashtags used by the user
						$hashtags[$i] = str_replace("#", "", $hashtags[$i]);
						// Hashtags used by not followed user
						$row27['hashtags'] = str_replace("#", "", $row27['hashtags']);
						// Compare hashtags between the user and not followed user and get similar hashtags
						$hashtags_word2 = checkHashtagExist(strtolower($hashtags[$i]),strtolower($row27['hashtags']));
						if(!empty($hashtags_word2)){
							$hashtags_word2 = implode("", $hashtags_word2);
						}

						if($hashtags_word2 != "" || $hashtags_word2 != NULL){
							if(substr($hashtags_word2,0,1) == "#"){
								$hashtags_word2 = str_replace("#", "", $hashtags_word2);
							}
/* 
		Display places and post thumbnail based on not followed users who visited a particular place and its type of establishment based on hashtags. Places who already visited by the user will be excluded
*/							
							$search29['select'] = "h.place_id, location_id, place_name, e.coordinates, h.user_id, h.video_id, video_thumb, date_upload, date_expiry";
							$search29['table'] = "veeds_establishment e, veeds_users_visit_history h, veeds_videos v";
							// $search29['where'] = "h.place_id = e.place_id
							// 						AND h.video_id = v.video_id
							// 						AND h.user_id IN (".$users_not_follow.")
							// 						AND h.place_id NOT IN (SELECT place_id 
							// 												FROM veeds_users_visit_history 
							// 												WHERE user_id = '".$_POST['user_id']."')
							// 						AND hashtags LIKE '%".$hashtags_word2."%'
				 		// 							AND date_visit IN (SELECT MAX(date_visit)
					 	// 												FROM veeds_users_visit_history
					  // 													WHERE user_id IN (".$users_not_follow.")
					 	// 												GROUP BY place_id)
					 	// 							AND DATE_FORMAT(date_expiry,'%Y-%m-%d %H:%i %s') > NOW()";
							$search29['where'] = "h.place_id = e.place_id
													AND h.video_id = v.video_id
													AND h.place_id != ''
													AND h.user_id IN (".$users_not_follow.")
													AND h.place_id NOT IN (SELECT place_id 
																			FROM veeds_users_visit_history 
																			WHERE user_id = '".$_POST['user_id']."')
													AND hashtags LIKE '%".$hashtags_word2."%'
				 									AND date_visit IN (SELECT MAX(date_visit)
					 													FROM veeds_users_visit_history
					  													WHERE user_id IN (".$users_not_follow.")
					 													GROUP BY place_id)";
					 		$search29['filters'] = "GROUP BY h.place_id ORDER BY COUNT(DISTINCT h.user_id) DESC";
					 		// echo implode(" ", $search29);
					 		if(jp_count($search29) > 0){

					 			$result29 = jp_get($search29);
					 			while ($row29 = mysqli_fetch_assoc($result29)) {

					 				if(!in_array($row29['location_id'],$location1['location'])){
										$location1['location'][] = $row29['location_id'];

										// $placeIdDetail->setPlaceId($row29['place_id']);
										// $placeName = $placeIdDetail->getPlaceName();

										$row29 = array(
													'location_id' => $row29['location_id'],
													'place_id' => $row29['place_id'],
													// 'place_name' => $placeName,
													// 'place_name' => $row29['place_name'],
													'coordinates' => $row29['coordinates'],
													'user_id' => $row29['user_id'],
													'video_id' => $row29['video_id'],
													'video_thumb' => $row29['video_thumb'],
													'date_upload' => $row29['date_upload'],
													'date_expiry' => $row29['date_expiry'],
													'logged_id' => $_POST['user_id']
												);

						 				// if(!in_array($row29, $list['places'])){
						 					$list['world_places'][] = $row29;
						 				// }
									}
					 			}
					 		}
						}
						// Compare hashtags between the user and not followed user and get similar hashtags
						$hashtags_word3 = checkHashtagExist1(strtolower($hashtags[$i]),strtolower($row27['hashtags']));
						if(!empty($hashtags_word3)){
							$hashtags_word3 = implode("", $hashtags_word3);
						}

						if($hashtags_word3 != "" || $hashtags_word3 != NULL){
							if(substr($hashtags_word3,0,1) == "#"){
								$hashtags_word3 = str_replace("#", "", $hashtags_word3);
							}

/* 
		Display places and post thumbnail based on not followed users who visited a particular place and its type of establishment based on hashtags. Places who already visited by the user will be excluded
*/	
							$search30['select'] = "h.place_id, location_id, place_name, e.coordinates, h.user_id, h.video_id, video_thumb, date_upload, date_expiry";
							$search30['table'] = "veeds_establishment e, veeds_users_visit_history h, veeds_videos v";
							// $search30['where'] = "h.place_id = e.place_id
							// 						AND h.video_id = v.video_id
							// 						AND h.user_id IN (".$users_not_follow.")
							// 						AND h.place_id NOT IN (SELECT place_id 
							// 												FROM veeds_users_visit_history 
							// 												WHERE user_id = '".$_POST['user_id']."')
							// 						AND hashtags LIKE '%".$hashtags_word3."%'
				 		// 							AND date_visit IN (SELECT MAX(date_visit)
					 	// 												FROM veeds_users_visit_history
					  // 													WHERE user_id IN (".$users_not_follow.")
					 	// 												GROUP BY place_id)
					 	// 							AND DATE_FORMAT(date_expiry,'%Y-%m-%d %H:%i %s') > NOW()";
							$search30['where'] = "h.place_id = e.place_id
													AND h.place_id != ''
													AND h.video_id = v.video_id
													AND h.user_id IN (".$users_not_follow.")
													AND h.place_id NOT IN (SELECT place_id 
																			FROM veeds_users_visit_history 
																			WHERE user_id = '".$_POST['user_id']."')
													AND hashtags LIKE '%".$hashtags_word3."%'
				 									AND date_visit IN (SELECT MAX(date_visit)
					 													FROM veeds_users_visit_history
					  													WHERE user_id IN (".$users_not_follow.")
					 													GROUP BY place_id)";
							$search30['filters'] = "GROUP BY h.place_id ORDER BY COUNT(DISTINCT h.user_id) DESC";
							// echo implode(" ", $search30);
							if(jp_count($search30) > 0){

					 			$result30 = jp_get($search30);
					 			while ($row30 = mysqli_fetch_assoc($result30)) {

					 				if(!in_array($row30['location_id'],$location1['location'])){
										$location1['location'][] = $row30['location_id'];
									
										// $placeIdDetail->setPlaceId($row30['place_id']);
										// $placeName = $placeIdDetail->getPlaceName();

										$row30 = array(
													'location_id' => $row30['location_id'],
													'place_id' => $row30['place_id'],
													// 'place_name' => $placeName,
													// 'place_name' => $row30['place_name'],
													'coordinates' => $row30['coordinates'],
													'user_id' => $row30['user_id'],
													'video_id' => $row30['video_id'],
													'video_thumb' => $row30['video_thumb'],
													'date_upload' => $row30['date_upload'],
													'date_expiry' => $row30['date_expiry'],
													'logged_id' => $_POST['user_id']
												);
						 				// if(!in_array($row30, $list['places'])){
						 					$list['world_places'][] = $row30;
						 				// }
									}
					 			}
					 		}
						}
					}
			    }
			}

			// Get words from veeds_dictionary
			$search31['select'] = "word";
			$search31['table'] = "veeds_dictionary";

			$result31 = jp_get($search31);

			while ($row31 = mysqli_fetch_assoc($result31)) {
				$row31['word'] = str_replace("\n", "", $row31['word']);
				$list_of_words[] = $row31['word'];
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
					$search32['select'] = "h.place_id, location_id, place_name, e.coordinates, h.user_id, h.video_id, video_thumb, date_upload, date_expiry";
					$search32['table'] = "veeds_establishment e, veeds_users_visit_history h, veeds_videos v";
					// $search32['where'] = "h.place_id = e.place_id
					// 						AND h.video_id = v.video_id
					// 						AND h.user_id IN (".$users_not_follow.")
					// 						AND h.place_id NOT IN (SELECT place_id 
					// 												FROM veeds_users_visit_history 
					// 												WHERE user_id = '".$_POST['user_id']."')
					// 						AND (hashtags LIKE '%".$final_word."%' OR place_name LIKE '%".$final_word."%')
		 		// 							AND date_visit IN (SELECT MAX(date_visit)
			 	// 												FROM veeds_users_visit_history
			  // 													WHERE user_id IN (".$users_not_follow.")
			 	// 												GROUP BY place_id)
			 	// 							AND DATE_FORMAT(date_expiry,'%Y-%m-%d %H:%i %s') > NOW()";
					$search32['where'] = "h.place_id = e.place_id
											AND h.video_id = v.video_id
											AND h.place_id != ''
											AND h.user_id IN (".$users_not_follow.")
											AND h.place_id NOT IN (SELECT place_id 
																	FROM veeds_users_visit_history 
																	WHERE user_id = '".$_POST['user_id']."')
											AND (hashtags LIKE '%".$final_word."%' OR place_name LIKE '%".$final_word."%')
		 									AND date_visit IN (SELECT MAX(date_visit)
			 													FROM veeds_users_visit_history
			  													WHERE user_id IN (".$users_not_follow.")
			 													GROUP BY place_id)";
					$search32['filters'] = "GROUP BY h.place_id ORDER BY COUNT(DISTINCT h.user_id) DESC";
					// echo implode(" ", $search32)."<br>";
					if(jp_count($search32) > 0){

			 			$result32 = jp_get($search32);
			 			while ($row32 = mysqli_fetch_assoc($result32)) {

			 				if(!in_array($row32['location_id'],$location1['location'])){
								$location1['location'][] = $row32['location_id'];
							
								// $placeIdDetail->setPlaceId($row32['place_id']);
								// $placeName = $placeIdDetail->getPlaceName();

								$row32 = array(
											'location_id' => $row32['location_id'],
											'place_id' => $row32['place_id'],
											// 'place_name' => $placeName,
											// 'place_name' => $row32['place_name'],
											'coordinates' => $row32['coordinates'],
											'user_id' => $row32['user_id'],
											'video_id' => $row32['video_id'],
											'video_thumb' => $row32['video_thumb'],
											'date_upload' => $row32['date_upload'],
											'date_expiry' => $row32['date_expiry'],
											'logged_id' => $_POST['user_id']
										);

				 				// if(!in_array($row32, $list['places'])){
				 					$list['world_places'][] = $row32;
				 				// }
							}
			 			}
			 		}
				}
			}
		}

		$search38['select'] = "h.place_id, location_id, place_name, e.coordinates, h.user_id, h.video_id, video_thumb, date_upload, date_expiry";
		$search38['table'] = "veeds_establishment e, veeds_users_visit_history h, veeds_videos v";
		// $search38['where'] = "h.place_id = e.place_id
		// 						AND h.video_id = v.video_id
		// 						AND h.user_id IN (".$users_not_follow.")
		// 						AND h.place_id NOT IN (SELECT place_id 
		// 												FROM veeds_users_visit_history 
		// 												WHERE user_id = '".$_POST['user_id']."')
		// 						AND (hashtags LIKE '%".$final_word."%' OR place_name LIKE '%".$final_word."%')
		// 							AND date_visit IN (SELECT MAX(date_visit)
 	// 												FROM veeds_users_visit_history
  // 													WHERE user_id IN (".$users_not_follow.")
 	// 												GROUP BY place_id)
 	// 							AND DATE_FORMAT(date_expiry,'%Y-%m-%d %H:%i %s') > NOW()";
		$search38['where'] = "h.place_id = e.place_id
								AND h.video_id = v.video_id
								AND h.place_id != ''
								AND h.user_id IN (".$users_not_follow.")
								AND h.place_id NOT IN (SELECT place_id 
														FROM veeds_users_visit_history 
														WHERE user_id = '".$_POST['user_id']."')
								AND date_visit IN (SELECT MAX(date_visit)
													FROM veeds_users_visit_history
													WHERE user_id IN (".$users_not_follow.")
													GROUP BY place_id)";
		$search38['filters'] = "GROUP BY h.place_id ORDER BY COUNT(DISTINCT h.user_id) DESC";
		// echo implode(" ", $search38)."<br>";
		if(jp_count($search38) > 0){

 			$result38 = jp_get($search38);
 			while ($row38 = mysqli_fetch_assoc($result38)) {

 				if(!in_array($row38['location_id'],$location1['location'])){
					$location1['location'][] = $row38['location_id'];
				
					// $placeIdDetail->setPlaceId($row38['place_id']);
					// $placeName = $placeIdDetail->getPlaceName();

					$row38 = array(
								'location_id' => $row38['location_id'],
								'place_id' => $row38['place_id'],
								// 'place_name' => $placeName,
								// 'place_name' => $row38['place_name'],
								'coordinates' => $row38['coordinates'],
								'user_id' => $row38['user_id'],
								'video_id' => $row38['video_id'],
								'video_thumb' => $row38['video_thumb'],
								'date_upload' => $row38['date_upload'],
								'date_expiry' => $row38['date_expiry'],
								'logged_id' => $_POST['user_id']
							);

	 				// if(!in_array($row38, $list['places'])){
	 					$list['world_places'][] = $row38;
	 				// }
				}
 			}
 		}

		if(!empty($location1['location'])){
			$location_ext1 = " AND e.location_id IN (".implode(",", $location1['location']).")";
		}else{
			$location_ext1 = "";
		}

		// Display followed users that has been visited a specific place
		$search33['select'] = "u.user_id, u.firstname, u.lastname, u.username, u.personal_information, u.profile_pic, h.place_id, e.location_id";
		$search33['table'] = "veeds_users u, veeds_establishment e, veeds_users_visit_history h";
		$search33['where'] = "u.user_id = h.user_id 
								AND h.place_id = e.place_id 
								AND h.user_id IN (".$users_not_follow.") 
								AND disabled = 0 ".$location_ext1;
		$search33['filters'] = "GROUP BY u.user_id";

		// echo implode(" ", $search33);

		if(jp_count($search33) > 0){
			
			$result33 = jp_get($search33);
			while($row33 = mysqli_fetch_assoc($result33)){

				$search34['select'] = "user_id";
				$search34['table'] = "veeds_users_follow";
				$search34['where'] = "user_id = '".$_POST['user_id']."' AND user_id_follow = '".$row33['user_id']."' AND approved = 1";
				$count34 = jp_count($search34);
				if($count34 > 0){
					$row33['followed'] = 1;
				}else{
					$search34['where'] = "user_id = '".$_POST['user_id']."' AND user_id_follow = '".$row33['user_id']."' AND approved = 0";
					$count34 = jp_count($search34);
					if($count34 > 0){
						$row33['followed'] = 2;
					}else{
						$row33['followed'] = 0;
					}
				}

				$search35['select'] = "user_id";
				$search35['table'] = "veeds_users_block";
				// $search2['where'] = "user_id = '".$_POST['user_id']."' AND user_id_block = '".$row10['user_id']."'"; 
				$search35['where'] = "user_id = '".$_POST['user_id']."' AND user_id_block = '".$row33['user_id']."'"; 
				$count35 = (int)jp_count($search35);
				if($count35 > 0){
					$row33['blocked'] = true;
				}else{
					$row33['blocked'] = false;
				}

				$search36['select'] = "date_upload, coordinates";
				$search36['table'] = "veeds_videos";
				$search36['where'] = "user_id = '".$row33['user_id']."' 
										AND date_upload IN (SELECT MAX(date_upload) 
															FROM veeds_videos 
															WHERE user_id = '".$row33['user_id']."')"; 

				$result36 = jp_get($search36);
				$row36 = mysqli_fetch_assoc($result36);
				// $row33['logged_id'] = $_POST['user_id'];
				$row33 = array(
								'firstname' => $row33['firstname'],
								'lastname' => $row33['lastname'],
								'username' => $row33['username'],
								'personal_information' => $row33['personal_information'],
								'user_id' => $row33['user_id'],
								'profile_pic' => $row33['profile_pic'],
								'followed' => $row33['followed'],
								'date_upload' => $row36['date_upload'],
								'coordinates' => $row36['coordinates'],
								'logged_id' => $_POST['user_id']
							);
				$list['world_users'][] = $row33;
			}
		}
		echo json_encode($list);	
	}
?>