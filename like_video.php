<?php
	include("jp_library/jp_lib.php");
	include("GCM.php");
	
	if($_POST['video_id']){
		$data['select'] = "user_id";
		$data['table'] = "veeds_videos_likes";
		$data['where'] = "video_id = ".$_POST['video_id']." AND user_id_liker = ".$_POST['user_id_liker']." AND user_id = ".$_POST['user_id'];
		if(jp_count($data) >= 1){
			jp_delete($data);

			// $search['select'] = "*";
			// $search['table'] = "veeds_users";
			// $search['where'] = "user_id = '".$_POST['photo_owner_user_id']."'"; 
			// if (jp_count($search) > 0) {
			// 	$result = jp_get($search);
			// 	$row = mysqli_fetch_assoc($result);
			// 	$like_count = array();
			// 	$like_count['total_Likes'] = intval($row['total_Likes']) - 1;
			// 	$update['table'] = "veeds_users";
			// 	$update['data'] = $like_count;
			// 	$update['where'] = "user_id = '".$_POST['photo_owner_user_id']."'"; 
			// 		if (jp_update($update)) {
			// 			$deleted = true;
			// 		} else {
			// 			$deleted = false;
			// 		}
			// }
			
			$search1['select'] = "total_Likes";
			$search1['table'] = "veeds_users";
			$search1['where'] = "user_id = '".$_POST['user_id']."'";

			$result1 = jp_get($search1);
			$row1 = mysqli_fetch_assoc($result1);

			$like_count = array();
			$like_count['total_Likes'] = $row1['total_Likes'] - 1;

			$data1['data'] = $like_count;
			$data1['table'] = "veeds_users";
			$data1['where'] = "user_id = '".$_POST['user_id']."'";
			jp_update($data1);

			$search['select'] = "video_id";
			$search['table'] = "veeds_videos_likes";
			$search['where'] = "video_id = ".$_POST['video_id'];

			$count = jp_count($search);

			$result = array('like' => 0, 'post' => $_POST, "like_count" => $count);
			echo json_encode($result);
		} else {
			//retrieve video info
			$didSendNotif = array();



			$video['select'] = "user_id";
			$video['table'] = $_POST['table'];
			$video['where'] = "video_id = ".$_POST['video_id'];
			$video_res = jp_get($video);
			$video_row = mysqli_fetch_assoc($video_res);
			// $didSendNotif['count'] = jp_count($video_row);
			$didSendNotif['video'] = $video_row;
			$didSendNotif['video_row'] = $video_row;
			$didSendNotif['PostId'] = $_POST['user_id_liker'];

			$didSendNotif['check'] = !is_null($video_row['user_id']);

			if(!is_null($video_row['user_id'])){
				if($_POST['user_id_liker'] != $video_row['user_id']){
					//save notification info on database if the one liking the video is not the owner of the video 
					$notif_data['user_id'] = $video_row['user_id'];
					$notif_data['type'] = "like";
					$notif_data['activity_user'] = $_POST['user_id_liker'];
					$notif_data['notif_datetime'] = date('Y-m-d H:i:s');
					$notif_data['video_id'] = $_POST['video_id'];
					$notif['data'] = $notif_data;
					$notif['table'] = "notifications";
					$didSendNotif['notif'] = $notif;
					$followType = "like";
					$search['select'] = "user_id";
					$search['table'] = "notifications";
					$search['where'] = "user_id = '".$notif_data['user_id']."' AND type = '".$followType."' AND activity_user = '".$notif_data['activity_user']."'";
					$notifList['list'] = array();
					$delete =  "";
					if (jp_count($search) > 0) {
						jp_delete($search);
						$delete = "deleted";
					}

					$didSendNotif['id'] = $video_row['user_id'];
					if(jp_add($notif)){
						$receiver['select'] = "notifications";
						$receiver['table'] = "veeds_users";
						$receiver['where'] = "user_id = ".$video_row['user_id']." AND user_id != ".$_POST['user_id_liker'];
						$receiver_result = jp_get($receiver);
						$receiver_row = mysqli_fetch_assoc($receiver_result);
					
					
						//fetch liker data to construct push data
						$user['select'] = "CONCAT(firstname,' ', lastname) AS name, profile_pic";
						$user['table'] = "veeds_users";
						$user['where'] = "user_id = ".$_POST['user_id_liker'];
						$user_result = jp_get($user);
						$user_row = mysqli_fetch_assoc($user_result);
						$didSendNotif['receive'] = $receiver_row;

						if($receiver_row['notifications'] == 1){
							//get all device ids of video owner
							$devices['select'] = "google_device_id";
							$devices['table'] = "veeds_user_devices";
							$devices['where'] = "user_id = ".$video_row['user_id']." AND user_id != ".$_POST['user_id_liker'];
		
							$device_ids = array();
							$device_result = jp_get($devices);
							while($device_row = mysqli_fetch_array($device_result)){
								$device_ids[] = $device_row['google_device_id'];
							}
						
							//push data
							$push = array();
							$push['reciever_id'] = $notif_data['user_id'];
							$push['video_id'] = $_POST['video_id'];
							$push['body'] = $user_row['name']." liked your post.";
							$push['type'] = "like";
							$push['image'] = 'http://ec2-52-40-31-134.us-west-2.compute.amazonaws.com/veeds/profile_pics/'.$user_row['profile_pic'];
							$didSendNotif['device_ids'] = $device_ids;
							sendGoogleCloudMessage($push, $device_ids);
							$didSendNotif['didReceive'] = true;
							$didSendNotif['pushData'] = $push;
						}
					}
				}
			}

			// if (isset($_POST['photo_owner_user_id'])) {
			// 	$search['select'] = "total_Likes";
			// 	$search['table'] = "veeds_users";
			// 	$search['where'] = "user_id = '".$_POST['photo_owner_user_id']."'"; 
			// 	$result = jp_get($search);
			// 	$row = mysqli_fetch_array($result);
			// 	$like_count = array();
			// 	$like_count['total_Likes'] = intval($row['total_Likes']) + 1;
			// 	$update['table'] = "veeds_users";
			// 	$update['data'] = $like_count;
			// 	$update['where'] = "user_id = '".$_POST['photo_owner_user_id']."'"; 
				
			// 		if (jp_update($update)) {
			// 			$updateValue = true;
			// 		} else {
			// 			$updateValue = false;
			// 		}
			// }
			
			$search1['select'] = "total_Likes";
			$search1['table'] = "veeds_users";
			$search1['where'] = "user_id = '".$_POST['user_id']."'";

			$result1 = jp_get($search1);
			$row1 = mysqli_fetch_assoc($result1);

			$like_count = array();
			$like_count['total_Likes'] = $row1['total_Likes'] + 1;

			$data1['data'] = $like_count;
			$data1['table'] = "veeds_users";
			$data1['where'] = "user_id = '".$video_row['user_id']."'";
			jp_update($data1);

			$data['data'] = $_POST;
			jp_add($data);
			$search['select'] = "video_id";
			$search['table'] = "veeds_videos_likes";
			$search['where'] = "video_id = ".$_POST['video_id'];

			$count = jp_count($search);
			
			$result = array('like' => 1, 'like_count' => $count, 'didSend' => $didSendNotif, 'data' => $data, 'count' => $count);
			echo json_encode($result);
		}
			
		
		
	}
	
?>