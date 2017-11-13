<?php

	function checkHashtagExist($stringtofind, $stringwheretofind){

		$temp1 = array();
		$temp2 = array();
		$store = array();
		$b = 0;

		for($a = 0; $a < strlen($stringtofind); $a++){
			$temp1[$a] = $stringtofind[$a];
			while($b < strlen($stringwheretofind)){
				$temp2[$b] = $stringwheretofind[$b];
				// echo "compare charactertofind: ".$temp1[$a]." == characterwheretofind: ".$temp2[$b]."<br>";

				if($temp1[$a] === $temp2[$b]){
					$store[] = $temp1[$a];
					$b++;
					break;
				}else{
					unset($store);
				}
				$b++;
			}

			if(empty($store) || is_null($store)){
				$b = 0;
			}			
		}
		if(!empty($store) && strlen(implode("", $store)) > 2){
			return $store;	
		}else{
			return false;
		}	
	}

	function checkHashtagExist1($stringtofind, $stringwheretofind){

		$temp1 = array();
		$temp2 = array();
		$store = array();
		$b = 0;

		for($a = 0; $a < strlen($stringtofind); $a++){
			$temp1[$a] = $stringtofind[$a];
			while($b < strlen($stringwheretofind)){
				$temp2[$b] = $stringwheretofind[$b];
				// echo "compare charactertofind: ".$temp1[$a]." == characterwheretofind: ".$temp2[$b]."<br>";

				if($temp1[$a] === $temp2[$b]){
					$store[] = $temp1[$a];
					$b++;
					break;
				}else{
					reset($store);
				}
				$b++;
			}

			if(empty($store) || is_null($store)){
				$b = 0;
			}			
		}
		if(!empty($store) && strlen(implode("", $store)) > 2){
			return $store;	
		}else{
			return false;
		}	
	}

	function countDataExist($x){

		if(count($x) < 1){
			throw new Exception('Unexpected Error!!!');
		}
		return true;
	}
// 	function getPlaceIdNameCoordinates($coordinates){

// 		$hostname = "https://maps.googleapis.com/maps/api/place/nearbysearch/json?location=".$coordinates."&rankby=distance&key=AIzaSyCejKQigOLZfb2selRtQS93pfuaWgYMUBw"; 
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
// 		if(count($response_a->results) > 0){
// 			$coor['lat'] = $response_a->results[0]->geometry->location->lat;
// 			$coor['lng'] = $response_a->results[0]->geometry->location->lng;

// 			$placeId = $response_a->results[0]->place_id;
// 			$placeName = $response_a->results[0]->name;		
// 			$placeCoordinates = $coor['lat'].",".$coor['lng'];
			
// 			return array($placeId, $placeName, $placeCoordinates);
// 		}else{
// 			return false;
// 		}
// 	}

// 	function getCityIdNameCoordinates($city){

// 		$hostname = "https://maps.googleapis.com/maps/api/place/textsearch/json?query=".str_replace(" ", "+", $city)."&key=AIzaSyCejKQigOLZfb2selRtQS93pfuaWgYMUBw"; 
// 		// echo $hostname;
// 		$ch = curl_init();
// 		curl_setopt($ch, CURLOPT_URL, $hostname);
// 		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
// 		curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
// 		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
// 		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
// 		$response = curl_exec($ch);
// 		curl_close($ch);
// 		$response_a = json_decode($response);
		
// 		if(count($response_a->results) > 0){
// 			$coor['lat'] = $response_a->results[0]->geometry->location->lat;
// 			$coor['lng'] = $response_a->results[0]->geometry->location->lng;

// 			$placeId = $response_a->results[0]->place_id;
// 			$placeName = $response_a->results[0]->name;
// 			$placeCoordinates = $coor['lat'].",".$coor['lng'];

// 			return array($placeId, $placeName, $placeCoordinates);
// 		}else{
// 			return false;
// 		}
// 	}

	function getPlaceIdDetails($placeId){

		sleep(2);
		$hostname = "https://maps.googleapis.com/maps/api/place/details/json?placeid=".str_replace(" ", "+", $placeId)."&key=AIzaSyBLYHLLfWNbEdHvRJKdArowB8tjmFb1xkE";

		// echo $hostname."<br>";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $hostname);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		$response = curl_exec($ch);
		curl_close($ch);
		$response_a = json_decode($response);

		$coor['lat'] = $response_a->result->geometry->location->lat;
		$coor['lng'] = $response_a->result->geometry->location->lng;

		$placeName = $response_a->result->name;
		$placeCoordinates = $coor['lat'].",".$coor['lng'];

		return array($placeName, $placeCoordinates);
	}

?>