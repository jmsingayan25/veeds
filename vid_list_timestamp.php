<?php
	include("jp_library/jp_lib.php");

	if(isset($_POST['user_id']) && isset($_POST['uploader_user_id'])){

		$search['select'] = "a.video_id, a.video_name, a.description, a.video_thumb, a.video_file, a.date_upload, a.view_count, c.place_name, a.location, a.coordinates, a.user_id AS uploader_user_id, b.username, b.firstname, b.lastname, b.profile_pic, b.personal_information, a.landscape_file, b.user_id, a.place_id";
		$search['table'] = "veeds_videos a, veeds_users b, veeds_establishment c";
		$search['where'] = "a.user_id = '".$_POST['uploader_user_id']."' 
							AND a.user_id = b.user_id 
							AND a.place_id = c.place_id
							OR a.video_id IN (SELECT DISTINCT video_id 
													FROM veeds_video_tags 
													WHERE user_id = '".$_POST['uploader_user_id']."')";		
		$search['filters'] = "GROUP BY a.place_id ORDER BY a.date_upload DESC";
		$result = jp_get($search);

		$list = array();
		
		while ($row = mysqli_fetch_assoc($result)) {

			$file_thumb = $row['video_thumb'];
			// $filesThumb = '/wamp/www/veeds/thumbnails/'.$file_thumb;
			$filesThumb = '/xampp/htdocs/veeds/thumbnails/'.$file_thumb;
			// $filesImage = '/xampp/htdocs/veeds/images/'.$file_thumb;
			if (!is_null($row['video_thumb']) && file_exists($filesThumb)){
			
				$search2['select'] = "video_id, like_type, user_id_liker";
				$search2['table'] = "veeds_videos_likes";
				$search2['where'] = "video_id = ".$row['video_id'].""; 
					
				$count = (int)jp_count($search2);
				$liker = jp_get($search2);
				if($count > 0){
					$row['like_count'] = $count;
					// $res = jp_get($search2);
					while ($row10 = mysqli_fetch_assoc($liker)) {
						if ($_POST['user_id'] != $row10['user_id_liker']) {
							$search20['select'] = "firstname, lastname, username, personal_information, user_id, profile_pic, private";
							$search20['table'] = "veeds_users";
							$search20['where'] = "disabled = 0 AND user_id = ".$row10['user_id_liker']; 
							$result11 = jp_get($search20);
							while ($row11 = mysqli_fetch_assoc($result11)) {
								
								$search3['select'] = "user_id";
								$search3['table'] = "veeds_users_follow";
								$search3['where'] = "user_id = '".$_POST['user_id']."' AND user_id_follow = '".$row11['user_id']."' AND approved = 1"; 
								$count2 = (int)jp_count($search3);
								if($count2 > 0){
									$row11['followed'] = 1;
								}else{
									$search3['where'] = "user_id = '".$_POST['user_id']."' AND user_id_follow = '".$row11['user_id']."' AND approved = 0"; 
									$count2 = (int)jp_count($search3);
									if($count2 > 0) {
										$row11['followed'] = 2;
									}
									else {
										$row11['followed'] = 0;
									}
								}

								$search3['select'] = "user_id";
								$search3['table'] = "veeds_users_block";
								$search3['where'] = "user_id = '".$_POST['user_id']."' 
														AND user_id_block = '".$row11['user_id']."'"; 
								$count2 = (int)jp_count($search3);
								if($count2 > 0){
									$row11['blocked'] = true;
								}else{
									$row11['blocked'] = false;
								}
								$row['user_id_liker'][] = $row11;
							}
						}
					}
				}else{
					$row['like_count'] = 0;
				}	
				
				// $search5['select'] = "user_id";
				// $search5['table'] = "veeds_video_tags";
				// $search5['where'] = "video_id = '".$row['video_id']."'";

				// if(jp_count($search5) > 0){
				// 	$result_tagged = jp_get($search5);
				// 	while ($row1 = mysqli_fetch_assoc($result_tagged)) {
						
				// 		$search21['select'] = "firstname, lastname, username, personal_information, user_id, profile_pic, private";
				// 		$search21['table'] = "veeds_users";
				// 		$search21['where'] = "disabled = 0 AND user_id = ".$row1['user_id']; 
				// 		$result12 = jp_get($search21);
				// 		while ($row12 = mysqli_fetch_assoc($result12)) {
							
				// 			$search3['select'] = "user_id";
				// 			$search3['table'] = "veeds_users_follow";
				// 			$search3['where'] = "user_id = '".$_POST['user_id']."' AND user_id_follow = '".$row12['user_id']."' AND approved = 1"; 
				// 			$count2 = (int)jp_count($search3);
				// 			if($count2 > 0){
				// 				$row12['followed'] = 1;
				// 			}else{
				// 				$search3['where'] = "user_id = '".$_POST['user_id']."' AND user_id_follow = '".$row12['user_id']."' AND approved = 0"; 
				// 				$count2 = (int)jp_count($search3);
				// 				if($count2 > 0) {
				// 					$row12['followed'] = 2;
				// 				}
				// 				else {
				// 					$row12['followed'] = 0;
				// 				}
				// 			}

				// 			$search3['select'] = "user_id";
				// 			$search3['table'] = "veeds_users_block";
				// 			$search3['where'] = "user_id = '".$_POST['user_id']."' 
				// 									AND user_id_block = '".$row12['user_id']."'"; 
				// 			$count2 = (int)jp_count($search3);
				// 			if($count2 > 0){
				// 				$row12['blocked'] = true;
				// 			}else{
				// 				$row12['blocked'] = false;
				// 			}

				// 			$row['user_id_tagged'][] = $row12;
				// 		}
				// 	}
				// }
						
				$search2['where'] = "video_id = ".$row['video_id']." AND user_id_liker = ".$_POST['user_id']." AND user_id = ".$row['user_id'];
						
				$type = jp_get($search2);		
				if($count > 0){
					// $row['like_status'] = 1;
					$like_type = mysqli_fetch_assoc($type);
					$row['like_type'] = $like_type['like_type'];
				}else{
					$like_type = mysqli_fetch_assoc($type);
					$row['like_type'] = $like_type['like_type'];
					// $row['like_status'] = 0;
				}

				$search2['select'] = "comment_id";
				$search2['table'] = "veeds_comments";
				$search2['where'] = "video_id = '".$row['video_id']."'"; 
						
				$count = (int)jp_count($search2);
						
				if($count > 0){
					$row['comment_count'] = $count;
				}else{
					$row['comment_count'] = 0;
				}

				$search2['select'] = "user_id";
				$search2['table'] = "veeds_video_tags";
				$search2['where'] = "video_id = ".$row['video_id']." AND user_id = ".$_POST['uploader_user_id'];

				$count = (int)jp_count($search2);

				if($count > 0){
					$row['tag'] = true;
					$row['user_id'] = $_POST['uploader_user_id'];
				}else{
					$row['tag'] = false;
				}

				if($row['landscape_file'] == NULL){
					$row['landscape_file'] = "";
				}
				
				$row['user_id'] = $_POST['user_id'];
				$list['active'][] = $row;


			}
		}
		// $list['data'] = $_POST;
		echo json_encode($list);
	}
?>
