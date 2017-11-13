<?php

	
	$_POST['user_id'] = "271";
	$_POST['keyword'] = "zark's burger";

	$list = array();
	// $hostname = "https://maps.googleapis.com/maps/api/place/textsearch/json?query=".str_replace(" ", "+", $_POST['keyword'])."&radius=10000&key=AIzaSyDVdJtIAvhmHE7e2zoxA_Y9qWRpp6eE2o8"; 
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
	
// 	$geocode = file_get_contents("http://maps.google.com/maps/api/geocode/json?address=".$_POST['keyword']."&radius=500&sensor=false");

// 	$response_a = json_decode($geocode);
// 	// echo json_encode($response_a);
// // nearbysearch/json?location=-33.8670522,151.1957362&radius=500&
// 	$name['lat'] = $response_a->results[0]->geometry->location->lat;
// 	$name['lng'] = $response_a->results[0]->geometry->location->lng;
	// $name['place_id'] = $response_a->results[0]->place_id;
	// $name['name'] = $response_a->results[0]->name;
	// $name['location'] = $response_a->results[0]->formatted_address;
	// $coor['lat'] = $response_a->results[0]->geometry->location->lat;
	// $coor['lng'] = $response_a->results[0]->geometry->location->lng;
	// $name['coordinates'] = $coor['lat'].",".$coor['lng'];
	// $name['tags'] = $response_a->results[0]->types;
	// $name['tags'] = implode(",", $name['tags']);

	// $hostname = "https://maps.googleapis.com/maps/api/place/nearbysearch/json?location=".$name['lat'].",".$name['lng']."&radius=500&key=AIzaSyDVdJtIAvhmHE7e2zoxA_Y9qWRpp6eE2o8";
	$hostname = "https://maps.googleapis.com/maps/api/place/textsearch/json?query=".str_replace(" ", "+", $_POST['keyword'])."&radius=500&key=AIzaSyDVdJtIAvhmHE7e2zoxA_Y9qWRpp6eE2o8";  
	// echo $hostname;
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $hostname);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	$response = curl_exec($ch);
	curl_close($ch);
	$response_b = json_decode($response);
	// echo count($response_b->results);
	// echo json_encode($response_b);
	for ($i=0; $i < count($response_b->results); $i++) { 
		$name1['place_id'] = $response_b->results[$i]->place_id;
		$name1['name'] = $response_b->results[$i]->name;
		$name1['location'] = $response_b->results[$i]->formatted_address;
		$coor['lat'] = $response_b->results[$i]->geometry->location->lat;
		$coor['lng'] = $response_b->results[$i]->geometry->location->lng;
		$name1['coordinates'] = $coor['lat'].",".$coor['lng'];
		$name1['tags'] = $response_b->results[$i]->types;
		$name1['tags'] = implode(",", $name1['tags']);

		$list['places'][] = $name1;
	}
	

	echo json_encode($list);


?>