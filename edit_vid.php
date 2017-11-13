<?php
	include("jp_library/jp_lib.php");
	include("GCM.php");
	
	if(isset($_POST)){
		$_POST['video_name'] = str_replace("'",';quote;',$_POST['video_name']);
		$_POST['description'] = str_replace("'",';quote;',$_POST['description']);
		$_POST['description'] .= " ";
			
		$data['data'] = $_POST;
		$data['table'] = "veeds_videos";
		$data['where'] = "video_id = ".$_POST['video_id'];
		
		if(jp_update($data)){
			
			$_POST['description'] = str_replace("@", " @", $_POST['description']);
			$_POST['description'] = str_replace("#", " #", $_POST['description']);
			
			
			$tags = explode('@', $_POST['description']);
			if(count($tags) > 0){
				for($i = 1; $i < count($tags); $i++){
					$username = explode(' ', $tags[$i]);
					$search['select'] = "user_id";
					$search['table'] = "veeds_users";
					$search['where'] = "username = '".$username[0]."'";
					
					if(jp_count($search) > 0){
						$result2 = jp_get($search);
						$row = mysqli_fetch_array($result2);
						$data2['data'] = array('video_id' => $_POST['video_id'], 'user_id' => $row['user_id']);
						$data2['table'] = "veeds_video_tags";
						jp_add($data2);
						
						if($_POST['user_id'] != $row['user_id']){
							//save notification info on database if the one tagged is not the same user as the one who uploaded the video
							$notif_data['user_id'] = $row['user_id'];
							$notif_data['type'] = "tag";
							$notif_data['activity_user'] = $_POST['user_id'];
							$notif_data['notif_datetime'] = date('Y-m-d H:i:s');
							$notif_data['video_id'] = $_POST['video_id'];
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
								$user['where'] = "user_id = ".$_POST['user_id'];
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
									$push['video_id'] = $_POST['video_id'];
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
			
			$result = array('reply' => '1', 'title' => $_POST['video_name'], 'description' => $_POST['description']);
		}else
			$result = array('reply' => '2');
		
		echo json_encode($result);
	}else{
		$result = array('reply' => '0');
		echo json_encode($result);
	}
	
?>