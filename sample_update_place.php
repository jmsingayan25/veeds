<?php

	include("jp_library/jp_lib.php");

	$_GET['user_id'] = "183";
	$_GET['place_id'] = "ChIJ4zLH-RjIlzMRDkshG9ZLDKc";
	if (isset($_GET['user_id']) && isset($_GET['place_id'])) {
		
		$reply = array();

		$search3['select'] = "user_id, COUNT(DISTINCT DATE_FORMAT(date_visit,'%m-%d-%y')) as visit_count";
		$search3['table'] = "veeds_users_visit_history";
		$search3['where'] = "user_id = '".$_GET['user_id']."' AND place_id = '".$_GET['place_id']."'";
		// echo implode(" ", $search3);
		$count_result = jp_get($search3);
		$visit_count = mysqli_fetch_assoc($count_result);

		if($visit_count['visit_count'] < 1){ //check if user visited the place for the first time

			$search5['select'] = "place_id";
			$search5['table'] = "veeds_users_place_history";
			$search5['where'] = "place_id = '".$_GET['place_id']."'";

			if(jp_count($search5) == 0){ //check if place_id not exist on the table

				$add['data'] = array('place_id' => $_GET['place_id'], 'total_count' => 1);
				$add['table'] = "veeds_users_place_history";
				// jp_add($add);

				$reply = array('message' => 'new entry');

			}else{ //update count of place_id if place_id already exist on the table

				$search4['select'] = "total_count";
				$search4['table'] = "veeds_users_place_history";
				$search4['where'] = "place_id = '".$_GET['place_id']."'";

				if(jp_count($search4) > 0){
					$result4 = jp_get($search4);
					$row4 = mysqli_fetch_assoc($result4);

					$visit_count = array();
					$visit_count['total_count'] = $row4['total_count'] + 1;
					$visit['table'] = "veeds_users_place_history";
					$visit['data'] = $visit_count;
					$visit['where'] = "place_id = '".$_GET['place_id']."'";
					// jp_update($visit);

					$reply = array('message' => 'update data');
				}else{
					$reply = array('message' => 'no update data');
				}
			}
		}else{
			$reply = array('message' => 'already visited the place');
		}
		echo json_encode($reply);
	}


?>