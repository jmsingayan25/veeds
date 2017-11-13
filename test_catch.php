<?php

	include("jp_library/jp_lib.php");
	include("GCM.php");
	include("class_place.php");

	$_POST['user_id'] = "183";

	$placeIdDetail = new classPlaceID;
	$array = array();

	$search1['select'] = "e.place_id, h.hashtags";
	$search1['table'] = "veeds_establishment e, veeds_users_visit_history h";
	$search1['where'] = "e.place_id = h.place_id AND h.user_id = '".$_POST['user_id']."'";
	$search1['filters'] = "GROUP BY e.place_name, e.location";
	// echo implode(" ", $search1);
	if(jp_count($search1) > 0){
		$result1 = jp_get($search1);
		while($row1 = mysqli_fetch_assoc($result1)){
			// $array['place_id'][] = $row1['place_id'];
			$placeIdDetail->setPlaceId($row1['place_id']);
			$placeid = $placeIdDetail->getPlaceTypes();
			// echo implode(",", $placeid);
			echo $placeid;
			// $array['hashtags'][] = $row1['hashtags'];
		}
	}else{
		$array['place_id'][] = "''";
		$array['hashtags'][] = "''";
	}

	// $vals = implode(",", $array['place_id']);
	
	echo json_encode($array);
?>