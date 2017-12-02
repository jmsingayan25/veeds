<!DOCTYPE html>

<?php
	include("jp_library/jp_lib.php");
	include("GCM.php");
	include("test_class_place.php");
?>
<html>
<body>

<form action="sample_upload.php" method="post" enctype="multipart/form-data">
    Select image to upload:
    <input type="file" name="thumb" id="thumb">
    <input type="submit" value="Upload Image" name="submit">

<?php
	
	if(isset($_POST['submit']) && isset($_FILES)){
		
		$filename = "veeds_".time();
		$filename = jp_upload($_FILES['thumb'],$filename,"thumbnails");
		
		// if(isset($_FILES['thumb'])){
		// 	$filename2 = "veeds_l_".time();
		// 	$filename2 = jp_upload($_FILES['thumb'],$filename2,"videos");
		// }
		// if(!empty($filename) && !empty($filename2)){
		if(!empty($filename)){

			$_GET['coordinates'] = "14.5841636,121.0571627";

			$placeObj = new classPlaceNearbySearch;
			$placeObj->setCoordinates($_GET['coordinates']);
			
			$_GET['place_id'] = $placeObj->getPlaceId();
			echo $_GET['place_id'];
			$_GET['user_id'] = "205";
			$_GET['location'] = "Wack-Wack SM Megamall, EDSA corner J. Vargas Ave., Brgy. Wack-Wack, Mandaluyong City, Ortigas Center, Mandaluyong, Metro Manila, Philippines";
			$_GET['description'] = " ";
			$_GET['video_name'] = " ";
			$_GET['location'] = " ";
			$_GET['orientation'] = " ";
			$_GET['video_file'] = " ";
			$_GET['video_thumb'] = $filename;
			// $_GET['landscape_file'] = $filename2;
			$_GET['location'] = str_replace("'",';quote;',$_GET['location']);
			$_GET['video_name'] = str_replace("'",';quote;',$_GET['video_name']);
			$_GET['description'] = str_replace("'",';quote;',$_GET['description']);
			$_GET['date_upload'] = date('Y-m-d H:i:s');
			$_GET['date_expiry'] = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s') . ' +1 day'));
			$_GET['description'] .= " ";
			$_GET['view_count'] = 0;
			$_GET['like_count'] = 0;
			$_GET['video_length'] = 0;
			$_GET['orientation'] = str_replace("'",';quote;',$_GET['orientation']);
			$data['data'] = $_GET;
			$data['table'] = "veeds_videos";
			// jp_add($data);
			// $video_id = jp_last_added();

			if(isset($_GET['user_id']) && isset($_GET['location'])){
				if(!empty($_GET['location'])){

					$data1['data'] = array('user_id' => $_GET['user_id'], 'location' => $_GET['location']);
					$data1['table'] = "veeds_users_location";
					// jp_add($data1);
				}	
			}

			if(isset($_GET['coordinates']) && isset($_GET['location']) && isset($_GET['place_id'])){

				$search1['select'] = "location_id, place_id";
				$search1['table'] = "veeds_establishment";
				// $search1['where'] = "location = '".$_GET['location']."'
				// 						AND coordinates = '".$_GET['coordinates']."'";
				$search1['where'] = "place_id = '".$_GET['place_id']."'";

				if(jp_count($search1) == 0){ //check if place not exist on the table

					// $tags = str_replace("establishment,point_of_interest,", "", $_GET['tags']);
					// $trim_tags = rtrim($tags,',');

					$data5['data'] = array(
										'place_id' => $_GET['place_id'],
										'place_name' => " ", 
										// 'place_name' => $_GET['place_name'], 
										'location' => $_GET['location'], 
										'tags' => " ", 
										// 'tags' => $trim_tags, 
										'coordinates' => $_GET['coordinates']
										);
					$data5['table'] = "veeds_establishment";
					// jp_add($data5);
				}
				// else{
				// 	$result3 = jp_get($search1);
				// 	while($row3 = mysqli_fetch_assoc($result3)){
				// 		if($row3['place_id'] == NULL){
				// 			$update_place_id = array();
				// 			$update_place_id['place_id'] = $_GET['place_id'];
				// 			$update['data'] = $update_place_id;
				// 			$update['table'] = "veeds_establishment";
				// 			$update['where'] = "location_id = '".$row3['location_id']."'";
				// 			jp_update($update);
				// 		}
				// 	}
				// }

				$data3['data'] = array(
									'place_id' => $_GET['place_id'], 
									'video_id' => $video_id,
									'user_id' => $_GET['user_id'], 
									'hashtags' => $_GET['description'],
									'date_visit' => date('Y-m-d H:i:s') 
									);
				$data3['table'] = "veeds_users_visit_history";
				// jp_add($data3);

				$search3['select'] = "user_id, COUNT(DISTINCT DATE_FORMAT(date_visit,'%m-%d-%y')) as visit_count";
				$search3['table'] = "veeds_users_visit_history";
				$search3['where'] = "user_id = '".$_GET['user_id']."' AND place_id = '".$_GET['place_id']."'";
				// echo implode(" ", $search3);
				$count_result = jp_get($search3);
				$visit_count = mysqli_fetch_assoc($count_result);

				if($visit_count['visit_count'] < 2){ //check if user visited the place for the first time
					// echo $visit_count['visit_count'];
					// echo $_GET['place_id'];
					$search5['select'] = "place_id";
					$search5['table'] = "veeds_users_place_history";
					$search5['where'] = "place_id = '".$_GET['place_id']."'";
					// echo implode(" ", $search5);
					if(jp_count($search5) == 0){ //check if place_id not exist on the table

						$code = array('place_id' => $_GET['place_id'], 'total_count' => 1);
						$add['data'] = $code;
						$add['table'] = "veeds_users_place_history";
						// jp_add($add);
						// echo implode(" ", $add);
						echo json_encode($code);

					}else{ //update count of place_id if place_id already exist on the table

						$search4['select'] = "total_count";
						$search4['table'] = "veeds_users_place_history";
						$search4['where'] = "place_id = '".$_GET['place_id']."'";

						$result4 = jp_get($search4);
						$row4 = mysqli_fetch_assoc($result4);

						$visit_count = array();
						$visit_count['total_count'] = $row4['total_count'] + 1;

						$visit['data'] = $visit_count;
						$visit['table'] = "veeds_users_place_history";
						$visit['where'] = "place_id = '".$_GET['place_id']."'";
						echo json_encode($visit);
						// echo implode(" ", $visit);
						// $result = array('visit' => $visit);
						// jp_update($visit);
					}
				}
			}

			$search['select'] = "post";
			$search['table'] = "veeds_users";
			$search['where'] = "user_id = '".$_GET['user_id']."'";

			$result = jp_get($search);
			$row = mysqli_fetch_assoc($result);

			$post_count = array();
			$post_count['post'] = $row['post'] + 1;

			$post['data'] = $post_count;
			$post['table'] = "veeds_users";
			$post['where'] = "user_id = '".$_GET['user_id']."'";
			jp_update($post);

			$hashtags = explode(" ",$_GET['description']);
			for($i = 0; $i < count($hashtags); $i++){
				if(substr($hashtags[$i],0,1) == "#"){

					$hash['table'] = "veeds_hashtag";
	     			$hash['data'] = array(
					     				'user_id' => $_GET['user_id'], 
					     				'hashtag' => $hashtags[$i], 
					     				'hashtag_date' => $_GET['date_upload']
					     			);
					jp_add($hash);
				}
			}
				
			$_GET['video_name'] = str_replace("@", " @", $_GET['video_name']);
			$_GET['description'] = str_replace("@", " @", $_GET['description']);
			$_GET['description'] = str_replace("#", " #", $_GET['description']);
			$pieces = explode(' ', $_GET['description']);
			$renewed = "";
			
			foreach($pieces as $piece){
				if(!empty($piece)){
					if($renewed != "")
						$renewed .= " ";
					
					$renewed .= $piece;
				}
			}
			
			$list['users'] = array();
			$tags = explode('@', $_GET['video_name']);
			if(count($tags) > 0){
				for($i = 1; $i < count($tags); $i++){
					$username = explode(' ', $tags[$i]);
					$search['select'] = "user_id";
					$search['table'] = "veeds_users";
					$search['where'] = "username = '".$username[0]."'";
					
					if(jp_count($search) > 0){
						$result2 = jp_get($search);
						$row = mysqli_fetch_array($result2);
						$data2['data'] = array('video_id' => $video_id, 'user_id' => $row['user_id']);
						$data2['table'] = "veeds_video_tags";
						jp_add($data2);
						
						if($_GET['user_id'] != $row['user_id']){
							//save notification info on database if the one tagged is not the same user as the one who uploaded the video
							$notif_data['user_id'] = $row['user_id'];
							$notif_data['type'] = "tag";
							$notif_data['activity_user'] = $_GET['user_id'];
							$notif_data['notif_datetime'] = date('Y-m-d H:i:s');
							$notif_data['video_id'] = $video_id;
							$notif['data'] = $notif_data;
							$notif['table'] = "notifications";
							if(jp_add($notif)){
								$receiver['select'] = "notifications";
								$receiver['table'] = "veeds_users";
								$receiver['where'] = "user_id = ".$row['user_id'];
								$receiver_result = jp_get($receiver);
								$receiver_row = mysqli_fetch_assoc($receiver_result);
					
								//fetch tagger data to construct push data
								$user['select'] = "CONCAT(firstname,' ', lastname) AS name, profile_pic";
								$user['table'] = "veeds_users";
								$user['where'] = "user_id = ".$_GET['user_id'];
								$user_result = jp_get($user);
								$user_row = mysqli_fetch_assoc($user_result);
								$list['users'][] = $user;
								if($receiver_row['notifications'] == 1){
									//get all device ids of tagged user
									$devices['select'] = "google_device_id";
									$devices['table'] = "veeds_user_devices";
									$devices['where'] = "user_id = ".$row['user_id'];
		
									$device_ids = array();
									$device_result = jp_get($devices);
									while($device_row = mysqli_fetch_array($device_result)){
										$device_ids[] = $device_row['google_device_id'];
									}
						
									//push data
									$push = array();
									$push['reciever_id'] = $notif_data['user_id'];
									$push['video_id'] = $video_id;
									$push['body'] = $user_row['name']." tagged you in a video.";
									$push['type'] = "tag";
									$push['image'] = 'http://ec2-52-40-31-134.us-west-2.compute.amazonaws.com/veeds/profile_pics/'.$user_row['profile_pic'];
					
									//sendGoogleCloudMessage($push, $device_ids);
								}
							}
						}
					}
				}
			}
			
			$result = array('reply' => '1', 'video' => $filename, 'place_id' => $_GET['place_id'], 'title' => $_GET['video_name'], 'description' => $_GET['description'], 'user_id' => $_GET['user_id'], 'date_upload' => $_GET['date_upload'], 'location' => $_GET['location'], 'video_id' => $video_id, 'list' => $list, 'files' => $_FILES);
		}else
			$result = array('reply' => '2');
		
		echo json_encode($result);
	}else{
		$result = array('reply' => '0');
		echo json_encode($result);
	}
	
?>
</form>
</body>
</html>


