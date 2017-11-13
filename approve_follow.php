<?php
	include("jp_library/jp_lib.php");
	include("GCM.php");
	
	if($_POST['user_id']){
		$stat['approved'] = 1;
		$data['data'] = $stat;
		$data['table'] = "veeds_users_follow";
		$data['where'] = "user_id_follow = '".$_POST['logged_id']."' AND user_id = '".$_POST['user_id']."'";
		
		if(jp_update($data)){

				$followType = "accepted";
				$search['select'] = "user_id";
				$search['table'] = "notifications";
				$search['where'] = "user_id = '".$_POST['user_id']."' AND type = '".$followType."' AND activity_user = '".$_POST['logged_id']."'";
				$notifList['list'] = array();
				$delete =  "";
				if (jp_count($search) > 0) {
					jp_delete($search);
					$delete = "deleted";
				}

			//save notification info on database if the one liking the video is not the owner of the video 
			$notif_data['user_id'] = $_POST['user_id'];
			$notif_data['type'] = "accepted";
			$notif_data['activity_user'] = $_POST['logged_id'];
			$notif_data['notif_datetime'] = date('Y-m-d H:i:s');
			$notif['data'] = $notif_data;
			$notif['table'] = "notifications";
			if(jp_add($notif)){
				$receiver['select'] = "notifications";
				$receiver['table'] = "veeds_users";
				$receiver['where'] = "user_id = ". $_POST['user_id'];
				$receiver_result = jp_get($receiver);
				$receiver_row = mysqli_fetch_assoc($receiver_result);
					
				//fetch liker data to construct push data
				$user['select'] = "CONCAT(firstname,' ', lastname) AS name, profile_pic";
				$user['table'] = "veeds_users";
				$user['where'] = "user_id = ".$_POST['logged_id'];
				$user_result = jp_get($user);
				$user_row = mysqli_fetch_assoc($user_result);
					
				if($receiver_row['notifications'] == 1){
					//get all device ids of video owner
					$devices['select'] = "google_device_id";
					$devices['table'] = "veeds_user_devices";
					$devices['where'] = "user_id = ".$_POST['user_id'];
		
					$device_ids = array();
					$device_result = jp_get($devices);
					while($device_row = mysqli_fetch_array($device_result)){
						$device_ids[] = $device_row['google_device_id'];
					}
					
					//push data
					$push = array();
					$push['reciever_id'] = $notif_data['user_id'];
					$push['user_id'] = $_POST['logged_id'];
					$push['body'] = $user_row['name']." has approved your follow request.";
					$push['type'] = "accepted";
					$push['image'] = 'http://ec2-52-40-31-134.us-west-2.compute.amazonaws.com/veeds/profile_pics/'.$user_row['profile_pic'];
					
					// sendGoogleCloudMessage($push, $device_ids);
				}
			}
			if (isset($_POST['notif_id'])) {
				$search['select'] = "notif_id";
				$search['table'] = "notifications";
				$search['where'] = "notif_id = '".$_POST['notif_id']."'";
			
				if (jp_count($search) > 0) {
					jp_delete($search);
					$reply['notif'] = true;
				} else {
					$reply['notif'] = false;
				}
			}
			if (isset($_POST['type'])) {
				$search['select'] = "type, notif_id, user_id, activity_user, notif_datetime";
				$search['table'] = "notifications";
				$search['where'] = "type = '".$_POST['type']."' AND user_id = '".$_POST['logged_id']."' AND activity_user = '".$_POST['user_id']."'";
				
				
				if (jp_count($search) > 0) {
					jp_delete($search);
					$reply['type'] = true;
				} else {
					$reply['type'] = false;
				}
		}
			$result = array('follow' => 1, 'delete' => $reply, 'notif' => $_POST['notif_id'], 'type' => $_POST['type']);
		}else{
			$result = array('follow' => 0, 'delete' => $reply);
		}
			
		echo json_encode($result);
		
	}
	
?>