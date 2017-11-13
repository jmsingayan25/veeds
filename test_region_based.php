<?php


	include("jp_library/jp_lib.php");
	include("functions.php");

	$_POST['user_id'] = "205";
	$_POST['coordinates'] = "14.6274857,121.0645483";
	// $_POST['coordinates'] = "Yellow+Cab+Pizza+Co.";
	$_POST['city'] = "Rublou";
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
	Get place based on region
*/
		// $search16['select'] = "h.place_id, location_id, place_name, e.coordinates, h.user_id, h.video_id, video_thumb, date_upload, date_expiry, MAX(date_visit)";
		// $search16['table'] = "veeds_establishment e, veeds_users_visit_history h, veeds_videos v";
		// $search16['where'] = "h.place_id = e.place_id
		// 						AND h.place_id = v.place_id
		// 						AND e.coordinates = '".$_POST['coordinates']."'
		// 						AND h.user_id IN (".$users.")";
		// 						// echo implode(" ", $search16);
		// if(jp_count($search16) > 0){

		// 	$return16 = jp_get($search16);
		// 	while ($row16 = mysqli_fetch_assoc($return16)) {
				
		// 		$row16 = array(
		// 						'place_id' => $row16['place_id'],
		// 						'location_id' => $row16['place_id'],
		// 						'place_name' => $row16['place_name'],
		// 						'coordinates' => $row16['coordinates'],
		// 						'user_id' => $row16['user_id'],
		// 						'video_id' => $row16['video_id'],
		// 						'video_thumb' => $row16['video_thumb'],
		// 						'date_upload' => $row16['date_upload'],
		// 						'date_expiry' => $row16['date_expiry'],
		// 						'logged_id' => $_POST['user_id']
		// 					);
		// 		$list['places'][] = $row16;
		// 	}
		// }else{

			// $hostname = "https://maps.googleapis.com/maps/api/geocode/json?latlng=".$_POST['coordinates']."&key=AIzaSyBwvPNKjNnnNSAvhpYSAyrYy3Ds3LLsmaE";

			// echo $hostname;

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

			// $hostname = "https://maps.googleapis.com/maps/api/place/textsearch/json?query=".$_POST['coordinates']."&key=AIzaSyDVdJtIAvhmHE7e2zoxA_Y9qWRpp6eE2o8"; 

			$hostname = "https://maps.googleapis.com/maps/api/place/nearbysearch/json?location=".$_POST['coordinates']."&rankby=distance&types=establishment&key=AIzaSyDVdJtIAvhmHE7e2zoxA_Y9qWRpp6eE2o8";
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
			
			for ($i=0; $i < count($response_a->results); $i++) { 

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

				$list['places'][] = $place;
			}
			// $array = array_fill(1, 1, "first");
			// $list['places'][] = $array;
		// }
		echo json_encode($list);
	}

?>