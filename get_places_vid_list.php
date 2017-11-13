<?php
	include("jp_library/jp_lib.php");

	// $_POST['user_id'] = "182";
	// $_POST['place_name'] = "Saint Pedro Poveda College";
	// $_POST['location'] = "P. Poveda Street, Ortigas Center, Quezon City, Metro Manila, Philippines";
	if (isset($_POST['user_id']) && isset($_POST['place_name']) && isset($_POST['location'])) {
		
		$list = array();
		$array = array();

		$search['select'] = "DISTINCT user_id_follow";
		$search['table'] = "veeds_users_follow";
    	$search['where'] = "user_id = ".$_POST['user_id']." AND approved = 1";

    	if(jp_count($search) > 0){
			$result = jp_get($search);
			while($row = mysqli_fetch_assoc($result)){
				$array['users_follow'][] = $row['user_id_follow'];
			}
		}else{
			$array['users_follow'][] = "''";
		}

		$place_name = str_replace("'","\'",$_POST['place_name']);
		$location = str_replace("'","\'",$_POST['location']);
		$search1['select'] = "DISTINCT coordinates";
		$search1['table'] = "veeds_establishment";
		$search1['where'] = "place_name = '".$place_name."' AND location = '".$location."'";

		$result1 = jp_get($search1);
		while($row = mysqli_fetch_assoc($result1)){
			//$array['coordinates'] = $row['coordinates'];
		// $block['table'] = "veeds_users_block";
		// $block['where'] = "user_id IN (".implode(",",$array['users_follow']).")";

		// if(jp_count($block) > 0){
		// 	$result = jp_get($block);
		// 	while($row = mysqli_fetch_array($result)){
		// 		$array['block'][] = $row['user_id_block'];
		// 	}
		// }else{
		// 	$array['block'][] = "''";
		// }
			$users = implode(",", $array['users_follow']);

			$search2['select'] = "DISTINCT e.location_id, e.place_name, e.location, e.coordinates, v.video_name, v.description, v.video_file, v.video_thumb, v.date_upload, v.view_count, v.video_length, v.landscape_file, u.user_id, u.firstname, u.lastname, u.personal_information, u.profile_pic";
			$search2['table'] = "veeds_users u, veeds_videos v, veeds_establishment e";
			$search2['where'] = "u.user_id = v.user_id 
									AND v.coordinates = e.coordinates
									AND v.coordinates = '".$row['coordinates']."'
									AND v.user_id IN (".$users.")";
			$search2['filters'] = "ORDER BY v.date_upload DESC";
			// echo implode(" ", $search2);
			$result2 = jp_get($search2);
			while($row = mysqli_fetch_assoc($result2)){
				$list['videos'][] = $row;
			}
		}

		echo json_encode($list);
	}
?>