<?php
	include("jp_library/jp_lib.php");
	include("GCM.php");
	
	if($_POST['user_id']){
		$data['select'] = "user_id";
		$data['table'] = "veeds_users_follow";
		$data['where'] = "user_id = '".$_POST['logged_id']."' AND user_id_follow = '".$_POST['user_id']."'";
		
		if(jp_count($data) > 0){
			jp_delete($data);
			$result = array('followed' => 0);
		}else{
			$_POST['user_id_follow'] = $_POST['user_id'];
			$_POST['user_id'] = $_POST['logged_id'];
			$data2['select'] = "private";
			$data2['table'] = "veeds_users";
			$data2['where'] = "user_id = '".$_POST['user_id_follow']."'";
			$result = jp_get($data2);
			$row = mysqli_fetch_assoc($result);
			if($row['private'] == 1){
				$_POST['approved'] = 0;
				$data['data'] = $_POST;
				jp_add($data);
				
				$followType = "request";
				$search['select'] = "user_id";
				$search['table'] = "notifications";
				$search['where'] = "user_id = '".$_POST['user_id_follow']."' AND type = '".$followType."' AND activity_user = '".$_POST['logged_id']."'";
				$notifList['list'] = array();
				$delete =  "";
				if (jp_count($search) > 0) {
					jp_delete($search);
					$delete = "deleted";
				}
				$result = array('followed' => 2, 'search' => $search, 'logged_id' => $_POST['logged_id'], 'user_id' => $_POST['user_id'], 'post' => $_POST, 'delete' , $delete);
				//save notification info on database if the one liking the video is not the owner of the video 
				$notif_data['user_id'] = $_POST['user_id_follow'];
				$notif_data['type'] = "request";
				$notif_data['activity_user'] = $_POST['user_id'];
				$notif_data['notif_datetime'] = date('Y-m-d H:i:s');
				$notif['data'] = $notif_data;
				$notif['table'] = "notifications";
				if(jp_add($notif)){
					$receiver['select'] = "notifications";
					$receiver['table'] = "veeds_users";
					$receiver['where'] = "user_id = ". $_POST['user_id_follow'];
					$receiver_result = jp_get($receiver);
					$receiver_row = mysqli_fetch_assoc($receiver_result);
					
					//fetch liker data to construct push data
					$user['select'] = "CONCAT(firstname,' ', lastname) AS name, profile_pic";
					$user['table'] = "veeds_users";
					$user['where'] = "user_id = ".$_POST['user_id'];
					$user_result = jp_get($user);
					$user_row = mysqli_fetch_assoc($user_result);
					
					if($receiver_row['notifications'] == 1){
						//get all device ids of video owner
						$devices['select'] = "google_device_id";
						$devices['table'] = "veeds_user_devices";
						$devices['where'] = "user_id = ".$_POST['user_id_follow'];
		
						$device_ids = array();
						$device_result = jp_get($devices);
						while($device_row = mysqli_fetch_array($device_result)){
							$device_ids[] = $device_row['google_device_id'];
						}
					
						//push data
						$push = array();
						$push['reciever_id'] = $notif_data['user_id'];
						$push['user_id'] = $_POST['user_id'];
						$push['body'] = $user_row['name']." has requested to follow you.";
						$push['type'] = "request";
						$push['image'] = 'http://ec2-52-40-31-134.us-west-2.compute.amazonaws.com/veeds/profile_pics/'.$user_row['profile_pic'];
					
						sendGoogleCloudMessage($push, $device_ids);
					}
				}	
				
			}else{
				$data['data'] = $_POST;
				jp_add($data);
				$followType = "started_following";
				$search['select'] = "user_id";
				$search['table'] = "notifications";
				$search['where'] = "user_id = '".$_POST['user_id_follow']."' AND type = '".$followType."' AND activity_user = '".$_POST['logged_id']."'";
				$notifList['list'] = array();
				if (jp_count($search) > 0) {
					jp_delete($search);
				}


				//save notification info on database if the one liking the video is not the owner of the video 
				$notif_data['user_id'] = $_POST['user_id_follow'];
				$notif_data['type'] = "started_following";
				$notif_data['activity_user'] = $_POST['user_id'];
				$notif_data['notif_datetime'] = date('Y-m-d H:i:s');
				$notif['data'] = $notif_data;
				$notif['table'] = "notifications";
				if(jp_add($notif)){
					$receiver['select'] = "notifications";
					$receiver['table'] = "veeds_users";
					$receiver['where'] = "user_id = ". $_POST['user_id_follow'];
					$receiver_result = jp_get($receiver);
					$receiver_row = mysqli_fetch_assoc($receiver_result);
					
					//fetch liker data to construct push data
					$user['select'] = "CONCAT(firstname,' ', lastname) AS name, profile_pic";
					$user['table'] = "veeds_users";
					$user['where'] = "user_id = ".$_POST['user_id'];
					$user_result = jp_get($user);
					$user_row = mysqli_fetch_assoc($user_result);
					
					if($receiver_row['notifications'] == 1){
						//get all device ids of video owner
						$devices['select'] = "google_device_id";
						$devices['table'] = "veeds_user_devices";
						$devices['where'] = "user_id = ".$_POST['user_id_follow'];
		
						$device_ids = array();
						$device_result = jp_get($devices);
						while($device_row = mysqli_fetch_array($device_result)){
							$device_ids[] = $device_row['google_device_id'];
						}
					
						//push data
						$push = array();
						$push['reciever_id'] = $notif_data['user_id'];
						$push['user_id'] = $_POST['user_id'];
						$push['body'] = $user_row['name']." started following you.";
						$push['type'] = "started_following";
						$push['image'] = 'http://ec2-52-40-31-134.us-west-2.compute.amazonaws.com/veeds/profile_pics/'.$user_row['profile_pic'];
					
						sendGoogleCloudMessage($push, $device_ids);
					}
				}	
				
				$result = array('followed' => 1, 'data' => $data);
			}
		}
			
		echo json_encode($result);
		
	}
	
?>