<?php


	include("jp_library/jp_lib.php");
	// include("functions.php");

	$_POST['user_id'] = "183";
	$_POST['coordinates'] = "14.60336,121.06973";
	$_POST['city'] = "SM Mall of Asia";
	
	$list = array();
	// $search2['where'] = "h.place_id = e.place_id
	// 						AND h.place_id = v.place_id
	// 						AND e.coordinates = '".$_POST['coordinates']."'
	// 						AND h.user_id IN (".$users.",".$_POST['user_id'].")
	// 						AND date_visit IN (SELECT MAX(date_visit) 
	// 													FROM veeds_users_visit_history
 //          												WHERE user_id IN (".$users.") 
 //          												GROUP BY place_id)";
	// $search2['filters'] = "GROUP BY h.place_id LIMIT 1";
	// // echo implode(" ", $search2);				
	// if(jp_count($search2) > 0){

	// 	$return2 = jp_get($search2);
	// 	while ($row2 = mysqli_fetch_assoc($return2)) {
			
	// 		if(!in_array($row2['location_id'],$location['location'])){
	// 			$location['location'][] = $row2['location_id'];
	// 		}

	// 		$row2 = array(
	// 						'location_id' => $row2['location_id'],
	// 						'place_id' => $row2['place_id'],
	// 						'place_name' => $row2['place_name'],
	// 						'coordinates' => $row2['coordinates'],
	// 						'user_id' => $row2['user_id'],
	// 						'video_id' => $row2['video_id'],
	// 						'video_thumb' => $row2['video_thumb'],
	// 						'date_upload' => $row2['date_upload'],
	// 						'date_expiry' => $row2['date_expiry'],
	// 						'logged_id' => $_POST['user_id']
	// 					);
	// 		$list['places'][] = $row2;
	// 	}
	// }else{

		// $placeID = getCityIdNameCoordinates($_POST['city'])[0];
		// $placeName = getCityIdNameCoordinates($_POST['city'])[1];
		// $coordinates = getCityIdNameCoordinates($_POST['city'])[2];

		// echo $placeID." ".$placeName." ".$coordinates;
			// $code = array('place_id' => $placeID);
			// $data['data'] = $code;
			// $data['table'] = "veeds_establishment";

			// // $reply = array('data' => $data);

			// if(jp_add($data)){
			// 	$reply = array('reply' => 'Add Place Success');
			// }else{
			// 	$reply = array('reply' => 'Add Place Failed');
			// }

	// 		$search14['select'] = "place_id, location_id";
	// 		$search14['table'] = "veeds_establishment";
	// 		$search14['where'] = "place_id = '".$placeID."'";

	// 		if(jp_count($search14) > 0){

	// 			$result14 = jp_get($search14);
	// 			while ($row14 = mysqli_fetch_assoc($result14)) {

	// 				$place = array(
	// 								'location_id' => $row14['location_id'],
	// 								'place_id' => $row14['place_id'],
	// 								'place_name' => $placeName,
	// 								'coordinates' => $coordinates,
	// 								'user_id' => "",
	// 								'video_id' => "",
	// 								'video_thumb' => "",
	// 								'date_upload' => "",
	// 								'date_expiry' => "",
	// 								'logged_id' => $_POST['user_id']
	// 							);				
	// 			}
	// 		}

	// $list['places'][] = $place;

	// echo json_encode($list);

	// function getPlaceIdNameCoordinates($coordinates){

// 		$hostname = "https://maps.googleapis.com/maps/api/place/nearbysearch/json?location=".$_POST['coordinates']."&rankby=distance&key=AIzaSyCejKQigOLZfb2selRtQS93pfuaWgYMUBw"; 
// // echo $hostname;
// 		$ch = curl_init();
// 		curl_setopt($ch, CURLOPT_URL, $hostname);
// 		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
// 		curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
// 		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
// 		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
// 		$response = curl_exec($ch);
// 		curl_close($ch);
// 		$response_a = json_decode($response);
// 	// 	if(count($response_a->results) > 0){
// 			$coor['lat'] = $response_a->results[0]->geometry->location->lat;
// 			$coor['lng'] = $response_a->results[0]->geometry->location->lng;

// 			$placeId = $response_a->results[0]->place_id;
// 			$placeName = $response_a->results[0]->name;		
// 			$placeCoordinates = $coor['lat'].",".$coor['lng'];
			
	// 		return array($placeId, $placeName, $placeCoordinates);
	// 	}else{
	// 		return false;
	// 	}
		
	// }

	// function getCityIdNameCoordinates($city){

	// 	$hostname = "https://maps.googleapis.com/maps/api/place/textsearch/json?query=".str_replace(" ", "+", $city)."&key=AIzaSyCejKQigOLZfb2selRtQS93pfuaWgYMUBw"; 
	// 	// echo $hostname;
	// 	$ch = curl_init();
	// 	curl_setopt($ch, CURLOPT_URL, $hostname);
	// 	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	// 	curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
	// 	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	// 	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	// 	$response = curl_exec($ch);
	// 	curl_close($ch);
	// 	$response_a = json_decode($response);
		
	// 	if(count($response_a->results) > 0){
	// 		$coor['lat'] = $response_a->results[0]->geometry->location->lat;
	// 		$coor['lng'] = $response_a->results[0]->geometry->location->lng;

	// 		$placeId = $response_a->results[0]->place_id;
	// 		$placeName = $response_a->results[0]->name;
	// 		$placeCoordinates = $coor['lat'].",".$coor['lng'];

	// 		return array($placeId, $placeName, $placeCoordinates);
	// 	}else{
	// 		return false;
	// 	}
		
	// }

	$hostname = "https://maps.googleapis.com/maps/api/place/textsearch/json?query=".str_replace(" ", "+", $_POST['city'])."&key=AIzaSyBLYHLLfWNbEdHvRJKdArowB8tjmFb1xkE"; 
			// echo $hostname;
			$hostname = "https://maps.googleapis.com/maps/api/place/nearbysearch/json?location=".$_POST['coordinates']."&rankby=distance&key=AIzaSyBLYHLLfWNbEdHvRJKdArowB8tjmFb1xkE"; 
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $hostname);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
				$response = curl_exec($ch);
				curl_close($ch);
				$response_a = json_decode($response);
				
				if(count($response_a->results) > 0){

					$coor['lat'] = $response_a->results[0]->geometry->location->lat;
					$coor['lng'] = $response_a->results[0]->geometry->location->lng;

					$cityPlaceID = $response_a->results[0]->place_id;
					$cityPlaceName = $response_a->results[0]->name;
					$cityCoordinates = $coor['lat'].",".$coor['lng'];


					// $search16['select'] = "location_id, place_id";
					// $search16['table'] = "veeds_establishment";
					// $search16['where'] = "place_id = '".$cityPlaceID."'";
					// $search16['filters'] = "GROUP BY place_id LIMIT 1";
					// display place id if there is existing data on the database
					/*if(jp_count($search16) > 0){

						$result16 = jp_get($search16);
						while ($row16 = mysqli_fetch_assoc($result16)) {
							
							$row16 = array(
									'location_id' => $row16['location_id'],
									'place_id' => $row16['place_id'],
									'place_name' => $cityPlaceName,
									'coordinates' => $cityCoordinates,
									'user_id' => "",
									'video_id' => "",
									'video_thumb' => "",
									'date_upload' => "",
									'date_expiry' => "",
									'logged_id' => $_POST['user_id']
								);
							$list['places'][] = $row16;
						}
					}else{

						$code = array('place_id' => $cityPlaceID);
						$data['data'] = $code;
						$data['table'] = "veeds_establishment";
						jp_add($data);

						// $newCityPlaceID = jp_last_added();

						$search17['select'] = "location_id, place_id";
						$search17['table'] = "veeds_establishment";
						$search17['where'] = "place_id = '".$cityPlaceID."'";
						$search17['filters'] = "GROUP BY place_id LIMIT 1";

						if(jp_count($search17) > 0){

							$result17 = jp_get($search17);
							while ($row17 = mysqli_fetch_assoc($result17)) {
								
								$row17 = array(
												'location_id' => $row17['location_id'],
												'place_id' => $row17['place_id'],
												'place_name' => $newCityPlaceName,
												'coordinates' => $newCityCoordinates,
												'user_id' => "",
												'video_id' => "",
												'video_thumb' => "",
												'date_upload' => "",
												'date_expiry' => "",
												'logged_id' => $_POST['user_id']
											);	
								$list['places'][] = $row17;	
							}
						}
					}*/
				}else{
					$empty = array(
									'location_id' => "",
									'place_id' => "",
									'place_name' => "",
									'coordinates' => "",
									'user_id' => "",
									'video_id' => "",
									'video_thumb' => "",
									'date_upload' => "",
									'date_expiry' => "",
									'logged_id' => $_POST['user_id']
								);	
					$list['places'][] = $empty;	
				}

		echo json_encode($list);

?>