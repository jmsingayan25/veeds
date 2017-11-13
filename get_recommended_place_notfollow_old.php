<?php

	include("jp_library/jp_lib.php");

	// $_POST['user_id'] = "205";
	if(isset($_POST['user_id'])){

		$array = array();
		$list = array();
		
		$search['select'] = "DISTINCT user_id_follow";
		$search['table'] = "veeds_users_follow";
    	$search['where'] = "user_id_follow NOT IN (SELECT DISTINCT user_id_follow 
    												FROM veeds_users_follow 
    												WHERE user_id = '".$_POST['user_id']."' 
    												AND user_id_follow != '".$_POST['user_id']."')";
    	$search['filters'] = "ORDER BY user_id_follow ASC";
    	// echo implode(" ", $search);
    	if(jp_count($search) > 0){
			$result = jp_get($search);
			while($row = mysqli_fetch_assoc($result)){
				$array['users_notfollow'][] = $row['user_id_follow'];
			}
		}else{
			$array['users_notfollow'][] = "''";
		}

		$search1['select'] = "e.tags";
		$search1['table'] = "veeds_establishment e, veeds_videos v";
		$search1['where'] = "e.coordinates = v.coordinates AND v.user_id = '".$_POST['user_id']."'";
		$search1['filters'] = "GROUP BY e.place_name, e.location";

		if(jp_count($search1) > 0){
			$result1 = jp_get($search1);
			while($row1 = mysqli_fetch_assoc($result1)){
				$array['tags'][] = $row1['tags'];
			}
		}else{
			$array['tags'][] = "''";
		}

		$vals = implode(",", $array['tags']);
		$vals_strip = str_replace(",", " ", $vals);
		$vals_explode = explode(" ", $vals_strip);

		$result = array_combine($vals_explode, array_fill(0, count($vals_explode), 0));

		foreach($vals_explode as $word) {
		    $result[$word]++;
		}

		$list['places'] = array();
		$array['location'] = array();
		$users = implode(",", $array['users_notfollow']);

		foreach($result as $word => $count){
		    if($count >= 1){
		  
		    	$search2['select'] = "DISTINCT e.location_id, COUNT(DISTINCT v.user_id) as count, e.place_name, e.location, e.coordinates, e.tags, v.place_id, v.video_id, v.video_file, v.video_thumb, v.date_upload, v.video_length, v.landscape_file";
				$search2['table'] = "veeds_establishment e, veeds_videos v";
				$search2['where'] = "e.coordinates = v.coordinates AND e.tags LIKE '%".$word."%'
										AND v.user_id IN (".$users.") AND v.user_id != '".$_POST['user_id']."'";
				$search2['filters'] = "GROUP BY location_id ORDER BY `count` DESC";
				// $search2['filters'] = "GROUP BY location_id HAVING count >= 5";
				// echo implode(" ", $search2);
				if(jp_count($search2) > 0){
					$result2 = jp_get($search2);
					while($row2 = mysqli_fetch_assoc($result2)){
						if(!in_array($row2, $list['places'])){
							$array['location'][] = $row2['location_id'];
							$row2['logged_id'] = $_POST['user_id'];
							$row2['count'] = (int)$row2['count'];
							$list['places'][] = $row2;	
						}	
					}
				}else{
					if(!in_array("''", $array['location']))
						$array['location'][] = "''";
				}
		    }else{
		    	if(!in_array("''", $array['location']))
					$array['location'][] = "''";
			}
		}

		$location_id = implode(",", $array['location']);
		$search3['select'] = "DISTINCT u.firstname, u.lastname, u.username, u.personal_information, u.profile_pic, e.location_id";
		$search3['table'] = "veeds_users u, veeds_videos v, veeds_establishment e";
		$search3['where'] = "u.user_id = v.user_id AND v.coordinates = e.coordinates 
								AND e.location_id IN (".$location_id.")
								AND v.user_id IN (".$users.")";
		// echo implode(" ", $search3);
		$result3 = jp_get($search3);
		while($row3 = mysqli_fetch_assoc($result3)){
			$row3['logged_id'] = $_POST['user_id'];
			$list['users'][] = $row3;
		}
		echo json_encode($list);
	}
?>
