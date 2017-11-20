<?php

	include("jp_library/jp_lib.php");

	if(empty($_POST['count']) || !isset($_POST['count'])){
		$_POST['count'] = 0;
	}

	if(isset($_POST['user_id']) && isset($_POST['keyword'])){

		$list = array();

		$search['select'] = "DISTINCT video_id, video_name, description, v.video_file, video_thumb, date_upload, date_expiry, view_count, like_count, location, video_length, landscape_file, v.user_id, place_id, firstname, lastname, username, personal_information, profile_pic";
		$search['table'] = "veeds_videos v, veeds_users u";
		$search['where'] = "v.user_id = u.user_id
							AND description LIKE '%".$_POST['keyword']."%'";
		// $search['where'] = "v.user_id = u.user_id
		// 					AND description LIKE '%".$_POST['keyword']."%' 
		// 					AND DATE_FORMAT(date_expiry,'%Y-%m-%d %H:%i %s') > NOW()";
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

				$row = array(
					'category' => "Hashtags",
					'firstname' => $row['firstname'],
					'lastname' => $row['lastname'],
					'username' => $row['username'],
					'personal_information' => $row['personal_information'],
					'profile_pic' => $row['profile_pic'],
					'video_id' => $row['video_id'],
					'video_name' => $row['video_name'],
					'description' => $row['description'],
					'video_file' => $row['video_file'],
					'user_id' => $row['user_id'],
					'video_thumb' => $row['video_thumb'],
					'date_upload' => $row['date_upload'],
					'date_expiry' => $row['date_expiry'],
					'view_count' => (int)$row['view_count'],
					'like_count' => (int)$row['like_count'],
					'place_id' => $row['place_id'],
					'location' => $row['location'],
					'video_length' => (int)$row['video_length'],
					'landscape_file' => $row['landscape_file'],
					'like_type' => $row['like_type'],
					'logged_id' => $_POST['user_id']
				);
							
				$list[] = $row;
			}
		}
		
		echo json_encode($list);
	}else{

		$reply = array('reply' => 'Data incomplete', 'post' => $_POST);
		
		echo json_encode($reply);
	}

?>