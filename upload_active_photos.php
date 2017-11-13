<?php
	include("jp_library/jp_lib.php");
	include("GCM.php");
	
	if(isset($_FILES)){
		$array = array();
	
		$filename = "veeds_".time();
		$filename = jp_upload($_FILES['video'],$filename,"videos");
		
		$filename2 = "veeds_".time();
		$filename2 = jp_upload($_FILES['thumb'],$filename2,"thumbnails");
		if(isset($_FILES['video2'])){
			$filename3 = "veeds_l_".time();
			$filename3 = jp_upload($_FILES['video2'],$filename3,"videos");
		}
		
		if((!isset($_GET['landscape']) && !empty($filename)) || (isset($_GET['landscape']) && !empty($filename) && isset($filename3))){
			$_GET['video_thumb'] = $filename2;
			$_GET['video_file'] = $filename;
			if(isset($filename3))
				$_GET['landscape_file'] = $filename3;
			$_GET['location'] = str_replace("'",';quote;',$_GET['location']);
			$_GET['video_name'] = str_replace("'",';quote;',$_GET['video_name']);
			$_GET['description'] = str_replace("'",';quote;',$_GET['description']);
			$_GET['date_upload'] = date('Y-m-d H:i:s');
			$_GET['description'] .= " ";
			$_GET['view_count'] = 0;
			$_GET['like_count'] = 0;
			// $_GET['orientation'] = str_replace("'",';quote;',$_GET['orientation']);
			$data['data'] = $_GET;
			$data['table'] = "veeds_active_videos";
			jp_add($data);
			$video_id = jp_last_added();

			if(isset($_GET['user_id']) && isset($_GET['location'])){
				if(!empty($_GET['location'])){
					$data1['data'] = array('user_id' => $_GET['user_id'], 'location' => $_GET['location']);
					$data1['table'] = "veeds_users_location";
					jp_add($data1);
				}	
			}

			$search['select'] = "post";
			$search['table'] = "veeds_users";
			$search['where'] = "user_id = '".$_GET['user_id']."'";

			$result = jp_get($search);
			$row = mysqli_fetch_assoc($result);

			$post_count = array();
			$post_count['post'] = $row['post'] + 1;
			$post['table'] = "veeds_users";
			$post['data'] = $post_count;
			$post['where'] = "user_id = '".$_GET['user_id']."'";
			jp_update($post);
			
			if(isset($_GET['location']) || isset($_GET['video_name']) || isset($_GET['description'])){

				$code = array("location" => $_GET['location'], "video_name" => $_GET['video_name'], "description" => $_GET['description']);

				$search['data'] = $code;
				$search['table'] = "veeds_videos";
				$search['where'] = "timestamp = ".$_GET['time_stamp_key'];
				$array[] = $search;
				jp_update($search);
			}else{
				$array[] = array("location" => $_GET['location'], "video_name" => $_GET['video_name'], "description" => $_GET['description']);
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
			
			$result = array('reply' => '1', 'video' => $filename, 'title' => $_GET['video_name'],
											'description' => $_GET['description'], 'user_id' => $_GET['user_id'],
											'date_upload' => $_GET['date_upload'], 'location' => $_GET['location'],
											'video_id' => $video_id, 'array' => $array);		}else
			$result = array('reply' => '2');
		
		echo json_encode($result);
	}else{
		$result = array('reply' => '0');
		echo json_encode($result);
	}
	
?>