<?php

	include("jp_library/jp_lib.php");
	include("test_class_place.php");


	if(empty($_POST['count']) || !isset($_POST['count'])){
		$_POST['count'] = 0;
	}

	$_POST['user_id'] = "183";
	$_POST['keyword'] = "Mandaluyong, Metro Manila, Philippines";
	if(isset($_POST['user_id']) && isset($_POST['keyword'])){

		// $explode_city = explode(",", $_POST['keyword']);
		$list = array();

		$search2['select'] = "country_name";
		$search2['table'] = "veeds_countries";
		$search2['filters'] = "ORDER BY country_id";

		$result2 = jp_get($search2);
		while ($row2 = mysqli_fetch_assoc($result2)) {
			$list_of_countries[] = $row2['country_name'];
		}

		foreach ($list_of_countries as $key => $value) {
			// echo $value."<br>";
			if(strpos($_POST['keyword'], $value) !== FALSE){
				// echo $value;
				$_POST['keyword'] = str_replace($value, "", $_POST['keyword']);
				$_POST['keyword'] = rtrim($_POST['keyword'],", ");
			}
		}

		// echo $_POST['keyword'];

		$explode_city = explode(",", $_POST['keyword']);
		for($i = 0; $i < count($explode_city); $i++){

			if(strpos($explode_city[$i], 'City') !== FALSE){
				$explode_city[$i] = str_replace(" City", "", $explode_city[$i]);
			}

			$search['select'] = "location_id, v.user_id, v.place_id, e.place_name, v.location as address, v.coordinates, firstname, lastname, username, profile_pic, video_id, video_name, description, v.video_file, video_thumb, date_upload, date_expiry, view_count, like_count, video_length, landscape_file";
			$search['table'] = "veeds_establishment e, veeds_users u, veeds_videos v";
			$search['where'] = "v.place_id = e.place_id 
								AND v.user_id = u.user_id
								AND (v.location LIKE '%".$explode_city[$i]."%'
								OR e.place_name LIKE '%".$explode_city[$i]."%')";
			$search['filters'] = "GROUP BY location_id ORDER BY date_upload DESC";
			// echo implode(" ", $search);
			if(jp_count($search) > 0){

				$result = jp_get($search);
				while ($row = mysqli_fetch_assoc($result)) {

					$search1['select'] = "like_type";
					$search1['table'] = "veeds_videos_likes";
					$search1['where'] = "video_id = '".$row['video_id']."' 
											AND user_id = '".$row['user_id']."'
											AND user_id_liker = '".$_POST['user_id']."'";

					// echo implode(" ", $search1)."<br>";
					if(jp_count($search1) > 0){

						$result1 = jp_get($search1);
						while ($row1 = mysqli_fetch_assoc($result1)) {
							
							$row['like_type'] = $row1['like_type'];
						}
					}else{
						$row['like_type'] = "";
					}
					
					// sleep(2);
					// $placeIdDetail->setPlaceId($row['place_id']);
					// $placeName = $placeIdDetail->getPlaceName();

					$row = array(
									'category' => "Places",
									'location_id' => $row['location_id'],
									'place_id' => $row['place_id'],
									// 'place_name' => $placeName,
									'place_name' => $row['place_name'],
									'user_id' => $row['user_id'],
									'firstname' => $row['firstname'],
									'lastname' => $row['lastname'],
									'username' => $row['username'],
									'profile_pic' => $row['profile_pic'],
									'video_id' => $row['video_id'],
									'video_name' => $row['video_name'],
									'description' => $row['description'],
									'video_file' => $row['video_file'],
									'video_thumb' => $row['video_thumb'],
									'date_upload' => $row['date_upload'],
									'date_expiry' => $row['date_expiry'],
									'view_count' => (int)$row['view_count'],
									'like_count' => (int)$row['like_count'],
									'address' => $row['address'],
									'video_length' => (int)$row['video_length'],
									'landscape_file' => $row['landscape_file'],
									'like_type' => $row['like_type'],
									'logged_id' => $_POST['user_id']
								);
					$list[] = $row;	
				}
			}				
		}
		echo json_encode($list);
	}


?>