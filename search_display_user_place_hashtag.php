<?php
/*

	Search users, hashtags and places details related to the keyword  

*/
	include("jp_library/jp_lib.php");

	if(empty($_POST['count']) || !isset($_POST['count'])){
		$_POST['count'] = 0;
	}

	if(isset($_POST['user_id']) && isset($_POST['keyword'])){
		
		$_POST['keyword'] = str_replace("`", "'", $_POST['keyword']);

		$list = array();
		$u_blocks = array();
		$list['users'] = array();
		$list['hashtags'] = array();
		
		$block['select'] = "user_id_block";
		$block['table'] = "veeds_users_block";
		$block['where'] = "user_id = ".$_POST['user_id'];
		
		if(jp_count($block) > 0){
			$result_block = jp_get($block);
			while($row3 = mysqli_fetch_assoc($result_block)){
				$u_blocks[] = $row3['user_id_block'];
			}
		}


		$block['select'] = "user_id";
		$block['table'] = "veeds_users_block";
		$block['where'] = "user_id_block = ".$_POST['user_id'];

		if(jp_count($block) > 0){
			$result_block = jp_get($block);
			while($row3 = mysqli_fetch_assoc($result_block)){
				if(!in_array($row3, $u_blocks))
					$u_blocks[] = $row3['user_id'];
			}
		}

		if(count($u_blocks) > 0){
			$u_extend_place = " AND u.user_id NOT IN (".implode(",", $u_blocks).")";
			$u_extend_names = " AND user_id NOT IN (".implode(",", $u_blocks).")";
		}else{
			$u_extend_place = "";
			$u_extend_names = "";
		}

		$start = $_POST['count'] * 5;
		$search['select'] = "DISTINCT user_id, firstname, lastname, username, personal_information, profile_pic, private";
		$search['table'] = "veeds_users";
		$search['where'] = "(LOWER(firstname) LIKE '%".strtolower($_POST['keyword'])."%' 
							OR LOWER(lastname) LIKE '%".strtolower($_POST['keyword'])."%'
							OR LOWER(username) LIKE '%".strtolower($_POST['keyword'])."%')".$u_extend_names;
		$search['filters'] = "LIMIT ".$start.", 5";

		if(jp_count($search) > 0){
			$result = jp_get($search);
			while ($row = mysqli_fetch_assoc($result)) {

				$search1['select'] = "user_id";
				$search1['table'] = "veeds_users_follow";
				$search1['where'] = "user_id = '".$_POST['user_id']."' AND user_id_follow = '".$row['user_id']."' AND approved = 1";
				$count1 = jp_count($search1);
				if($count1 > 0){
					$row['followed'] = 1;
				}else{
					$search1['where'] = "user_id = '".$_POST['user_id']."' AND user_id_follow = '".$row['user_id']."' AND approved = 0";
					$count1 = jp_count($search1);
					if($count1 > 0){
						$row['followed'] = 2;
					}else{
						$row['followed'] = 0;
					}
				}

				$search2['select'] = "user_id";
				$search2['table'] = "veeds_users_block";
				// $search2['where'] = "user_id = '".$_POST['user_id']."' AND user_id_block = '".$row10['user_id']."'"; 
				$search2['where'] = "user_id = '".$_POST['user_id']."' AND user_id_block = '".$row['user_id']."'"; 
				$count2 = (int)jp_count($search2);
				if($count2 > 0){
					$row['blocked'] = true;
				}else{
					$row['blocked'] = false;
				}

				if($row['private'] == 0){
					$row['private'] = false;
				}else{
					$row['private'] = true;
				}

				$row['category'] = "Users"; // new line
				$row['logged_id'] = $_POST['user_id'];

				if(!in_array($row, $list['users'])){
					$list['users'][] = $row;	
				} 
			}
		}

		$start = $_POST['count'] * 5;
		$search1['select'] = "DISTINCT u.user_id, u.firstname, u.lastname, u.username, u.personal_information, u.profile_pic, private";
		$search1['table'] = "veeds_users u, veeds_users_visit_history h, veeds_establishment e";
		$search1['where'] = "u.user_id = h.user_id
								AND e.place_id = h.place_id
								AND (LOWER(h.hashtags) LIKE '%".strtolower($_POST['keyword'])."%'
								OR LOWER(e.place_name) LIKE '%".strtolower($_POST['keyword'])."%'
								OR LOWER(h.hashtags) SOUNDS LIKE '%".strtolower($_POST['keyword'])."%'
								OR LOWER(e.place_name) SOUNDS LIKE '%".strtolower($_POST['keyword'])."%')".$u_extend_place;
		$search1['filters'] = "LIMIT ".$start.", 5";
		
		if(jp_count($search1) > 0){
			$result = jp_get($search1);
			while ($row = mysqli_fetch_assoc($result)) {

				$search2['select'] = "user_id";
				$search2['table'] = "veeds_users_follow";
				$search2['where'] = "user_id = '".$_POST['user_id']."' AND user_id_follow = '".$row['user_id']."' AND approved = 1";
				$count1 = jp_count($search2);
				if($count1 > 0){
					$row['followed'] = 1;
				}else{
					$search2['where'] = "user_id = '".$_POST['user_id']."' AND user_id_follow = '".$row['user_id']."' AND approved = 0";
					$count1 = jp_count($search2);
					if($count1 > 0){
						$row['followed'] = 2;
					}else{
						$row['followed'] = 0;
					}
				}

				$search3['select'] = "user_id";
				$search3['table'] = "veeds_users_block";
				// $search2['where'] = "user_id = '".$_POST['user_id']."' AND user_id_block = '".$row10['user_id']."'"; 
				$search3['where'] = "user_id = '".$_POST['user_id']."' AND user_id_block = '".$row['user_id']."'"; 
				$count2 = (int)jp_count($search3);
				if($count2 > 0){
					$row['blocked'] = true;
				}else{
					$row['blocked'] = false;
				}

				if($row['private'] == 0){
					$row['private'] = false;
				}else{
					$row['private'] = true;
				}

				$row['category'] = "Users"; // new line
				$row['logged_id'] = $_POST['user_id'];
				
				if(!in_array($row, $list['users'])){
					$list['users'][] = $row;	
				}
			}
		}
		
		sleep(2);
		$hostname = "https://maps.googleapis.com/maps/api/place/textsearch/json?query=".str_replace(" ", "+", $_POST['keyword'])."&location=".$_POST['coordinates']."&radius=500&key=AIzaSyCWURxddYHkkejBOFqA31s3yiRXr2BzEWM"; 

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $hostname);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		$response = curl_exec($ch);
		curl_close($ch);
		$response_a = json_decode($response);
		
		for ($i=0; $i < count($response_a->results); $i++) { 

			$coor['lat'] = $response_a->results[$i]->geometry->location->lat;
			$coor['lng'] = $response_a->results[$i]->geometry->location->lng;
			$coor['tags'] = $response_a->results[$i]->types;

			$place['place_id'] = $response_a->results[$i]->place_id;
			$place['place_name'] = $response_a->results[$i]->name;
			$place['address'] = $response_a->results[$i]->formatted_address;			
			$place['tags'] = str_replace(",point_of_interest,establishment", "", implode(",", $coor['tags']));
			$place['coordinates'] = $coor['lat'].",".$coor['lng'];
			$place['category'] = "Places"; // new line
			$place['logged_id'] = $_POST['user_id'];

			$list['places'][] = $place;
		}

		$start = $_POST['count'] * 10;
		$search1['select'] = "DISTINCT e.place_id, e.place_name, e.location as address, e.tags, e.coordinates";
		$search1['table'] = "veeds_establishment e, veeds_users_visit_history h, veeds_users u";
		$search1['where'] = "h.user_id = u.user_id
								AND h.place_id = e.place_id
								AND (place_name LIKE '%".$_POST['keyword']."%' 
								OR location LIKE '%".$_POST['keyword']."%'
								OR hashtags LIKE '%".$_POST['keyword']."%'
								OR place_name SOUNDS LIKE '%".$_POST['keyword']."%' 
								OR location SOUNDS LIKE '%".$_POST['keyword']."%')";
		$search1['filters'] = "LIMIT ".$start.", 10";

		if(jp_count($search1) > 0){
			$result1 = jp_get($search1);
			while ($row1 = mysqli_fetch_assoc($result1)) {

				$row1['category'] = "Places";
				$row1['logged_id'] = $_POST['user_id'];

				$list['places'][] = $row1; 
			}
		}

		$search7['select'] = "DISTINCT hashtag";
		$search7['table'] = "veeds_hashtag";
		$search7['where'] = "hashtag LIKE '%".$_POST['keyword']."%'";

		if(jp_count($search7) > 0){

			$result7 = jp_get($search7);
			while ($row7 = mysqli_fetch_assoc($result7)) {

				$start = $_POST['count'] * 10;
				$search8['select'] = "DISTINCT date_upload, description";
				$search8['table'] = "veeds_videos";
				$search8['where'] = "description LIKE '%".$row7['hashtag']."%'".$u_extend_names;
				// $search8['where'] = "description LIKE '%".$row7['hashtag']."%'
				// 						AND DATE_FORMAT(date_expiry,'%Y-%m-%d %H:%i %s') > NOW()";
				$search8['filters'] = "ORDER BY date_upload DESC LIMIT ".$start.", 10";

				if(jp_count($search8) > 0){

					$result8 = jp_get($search8);
					while ($row8 = mysqli_fetch_assoc($result8)) {

						$desc_explode = explode(" ", $row8['description']);
						for($i = 0; $i < count($desc_explode); $i++){
							if(stripos($desc_explode[$i], $_POST['keyword']) !== FALSE){

								$row8 = array(
												'date_upload' => $row8['date_upload'],
												'hashtags' => $desc_explode[$i],
												'category' => "Hashtags",
												'logged_id' => $_POST['user_id']
											);
								
								if(!in_array($row8, $list['hashtags'])){
									$list['hashtags'][] = $row8; 
								}
							}
						}
					}
				}
			}
		}

		echo json_encode($list);
	}

?>