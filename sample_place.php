<?php

	include("jp_library/jp_lib.php");

	$_POST['user_id'] = "183";
	$_POST['keyword'] = "Mandaluyong, Metro Manila, Philippines";
	$_POST['coordinates'] = "14.5910712,121.1262489";

	// $list = array();
	// // $hostname = "https://maps.googleapis.com/maps/api/place/textsearch/json?query=".str_replace(" ", "+", $_POST['keyword'])."&location=".$_POST['coordinates']."&radius=500&key=AIzaSyDVdJtIAvhmHE7e2zoxA_Y9qWRpp6eE2o8"; 

	// $hostname = "https://maps.googleapis.com/maps/api/place/autocomplete/json?input=".str_replace(" ", "+", $_POST['keyword'])."&sensor=false&types=(regions)&key=AIzaSyDVdJtIAvhmHE7e2zoxA_Y9qWRpp6eE2o8";
	// // echo $hostname;
	// $ch = curl_init();
	// curl_setopt($ch, CURLOPT_URL, $hostname);
	// curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	// curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
	// curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	// curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	// $response = curl_exec($ch);
	// curl_close($ch);
	// $response_a = json_decode($response);
	
	// // for ($i=0; $i < count($response_a->predictions); $i++) { 

	// $place['city'] = $response_a->predictions[0]->terms[0]->value;
	// 	// $coor['lat'] = $response_a->results[$i]->geometry->location->lat;
	// 	// $coor['lng'] = $response_a->results[$i]->geometry->location->lng;
	// 	// $coor['tags'] = $response_a->results[$i]->types;

	// 	// $place['place_id'] = $response_a->results[$i]->place_id;
	// 	// $place['place_name'] = $response_a->results[$i]->name;
	// 	// $place['address'] = $response_a->results[$i]->formatted_address;			
	// 	// $place['tags'] = str_replace(",point_of_interest,establishment", "", implode(",", $coor['tags']));
	// 	// $place['coordinates'] = $coor['lat'].",".$coor['lng'];
	// 	// $place['category'] = "Places"; // new line
	// 	// $place['logged_id'] = $_POST['user_id'];

	// 	$list['places'][] = $place;
	// }
	echo setCoordinates($_POST['coordinates']);

	function setCoordinates($coordinates){

		// sleep(2);
		$hostname = "https://maps.googleapis.com/maps/api/place/nearbysearch/json?location=".$coordinates."&rankby=distance&key=AIzaSyBLYHLLfWNbEdHvRJKdArowB8tjmFb1xkE"; 
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

		if ($response_a->status == "OK"){

			$coor['lat'] = $response_a->results[0]->geometry->location->lat;
			$coor['lng'] = $response_a->results[0]->geometry->location->lng;

			$placeID = $response_a->results[0]->place_id;
			$placeName = $response_a->results[0]->name;		
			$placeCoordinates = $coor['lat'].",".$coor['lng'];
			$placeTypes = $response_a->results[0]->types;
			$placeAddress = $response_a->results[0]->vicinity;

			// $this->placeID = $placeID;
			// $this->placeName = $placeName;
			// $this->placeAddress = $placeAddress;
			// $this->placeTypes = $placeTypes;
			// $this->placeCoordinates = $placeCoordinates;
			return $placeID;
		}
	}

	// echo json_encode($list);

?>