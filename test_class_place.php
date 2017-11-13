<?php

	/**
	* 
	*/
	class classPlaceID{
		
		public $placeID;
		public $placeName;
		public $placeAddress;
		public $placeTypes;
		public $placeCoordinates;
		public $key = "AIzaSyBLYHLLfWNbEdHvRJKdArowB8tjmFb1xkE";
		
		public function setPlaceId($setplaceid){

			// sleep(2);
			$hostname = "https://maps.googleapis.com/maps/api/place/details/json?placeid=".str_replace(" ", "+", $setplaceid)."&key=".$this->key;

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

			if ($response_a->status == "OK"){
       
				$coor['lat'] = $response_a->result->geometry->location->lat;
				$coor['lng'] = $response_a->result->geometry->location->lng;
				// $types = $response_a->result->types;

				$placeID = $response_a->result->name;
				$placeName = $response_a->result->name;
				$placeCoordinates = $coor['lat'].",".$coor['lng'];
				$placeTypes = $response_a->result->types;
				$placeAddress = $response_a->result->vicinity;
				
				// return array('placeName'=>$this->placeName, 'placeCoordinates'=>$this->placeCoordinates);
				$this->placeID = $placeID;
				$this->placeName = $placeName;
				$this->placeAddress = $placeAddress;
				$this->placeTypes = $placeTypes;
				$this->placeCoordinates = $placeCoordinates;
		    }
		}

		public function getPlaceId(){
			return $this->placeID;
		}

		public function getPlaceName(){
			return $this->placeName;
		}

		public function getPlaceAddress(){
			return $this->placeAddress;
		}

		public function getPlaceTypes(){
			return $this->placeTypes;
		}

		public function getPlaceCoordinates(){
			return $this->placeCoordinates;
		}

	}

	class classPlaceNearbySearch extends classPlaceID{

		public function setCoordinates($coordinates){

			// sleep(2);
			$hostname = "https://maps.googleapis.com/maps/api/place/nearbysearch/json?location=".$coordinates."&rankby=distance&key=".$this->key; 
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

				$this->placeID = $placeID;
				$this->placeName = $placeName;
				$this->placeAddress = $placeAddress;
				$this->placeTypes = $placeTypes;
				$this->placeCoordinates = $placeCoordinates;
			}
		}

		public function setCoordinatesCity($coordinates,$city){

			// sleep(2);
			$hostname = "https://maps.googleapis.com/maps/api/place/nearbysearch/json?location=".$coordinates."&rankby=distance&keyword=".str_replace(" ", "+", $city)."&key=".$this->key;
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

				$this->placeID = $placeID;
				$this->placeName = $placeName;
				$this->placeAddress = $placeAddress;
				$this->placeTypes = $placeTypes;
				$this->placeCoordinates = $placeCoordinates;
			}
		}
	}

	// class classPlaceTextSearch extends classPlaceID{

	// 	public function setTextSearchPlaceId($city){

	// 		$hostname = "https://maps.googleapis.com/maps/api/place/textsearch/json?query=".str_replace(" ", "+", $city)."&key=AIzaSyBLYHLLfWNbEdHvRJKdArowB8tjmFb1xkE"; 
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

	// 		$coor['lat'] = $response_a->results[0]->geometry->location->lat;
	// 		$coor['lng'] = $response_a->results[0]->geometry->location->lng;

	// 		$cityPlaceID = $response_a->results[0]->place_id;
	// 		$cityPlaceName = $response_a->results[0]->name;
	// 		$cityCoordinates = $coor['lat'].",".$coor['lng'];

	// 		$this->placeID = $cityPlaceID;
	// 		$this->placeName = $cityPlaceName;
	// 		$this->placeCoordinates = $cityCoordinates;
	// 	}
	// }


	// $obj = new PlaceID;

	// $obj->setPlaceId("ChIJjQIKOBjIlzMRL78dU2ZoRSQ");
	// $res = $obj->getPlaceIdDetails();

	// echo "Place name is ".$res['placeName'];
	// echo "Coordinates is ".$res['placeCoordinates'];

?>