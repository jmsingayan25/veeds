<?php

	// Search users, hashtags and places related to the keyword details 

	include("jp_library/jp_lib.php");


	if(isset($_POST['user_id']) && isset($_POST['keyword'])){
		
		$list = array();

		if($_POST['search_column'] == 'user_id'){
			$search['select'] = "DISTINCT user_id, firstname, lastname, username, personal_information, email";
			$search['table'] = "veeds_user";
			// $search['where'] = "firstname LIKE '%".$_POST['keyword']."%' 
			// 					OR lastname LIKE '%".$_POST['keyword']."%'
			// 					OR username LIKE '%".$_POST['keyword']."%'";
			$search['where'] = "firstname = '".$_POST['keyword']."' 
			 					OR lastname = '".$_POST['keyword']."'
			 					OR username = '".$_POST['keyword']."'";

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

				$list['users'][] = $row; 
			}
		}else if($_POST['search_column'] == 'place_id'){
			$search1['select'] = "DISTINCT place_id, location_id, place_name, location, tags, coordinates";
			$search1['table'] = "veeds_establishment";
			$search1['where'] = "place_name = '".$_POST['keyword']."' 
								OR location = '".$_POST['keyword']."' ";

			$result1 = jp_get($search1);
			while ($row1 = mysqli_fetch_assoc($result1)) {
				$list['places'][] = $row1; 
			}
		}else{
			
			$search2['select'] = "DISTINCT hashtag, video_id";
			$search2['table'] = "veeds_hashtag";
			$search2['where'] = "hashtag = '".$_POST['keyword']."'";

			$result2 = jp_get($search2);
			while ($row2 = mysqli_fetch_assoc($result2)) {
				$list['hashtag'][] = $row2; 
			}
		}

		echo json_encode($list);
	}

?>