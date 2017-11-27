<?php
	include("jp_library/jp_lib.php");
	include("GCM.php");

	$_POST['video_id'] = "38";
	$_POST['user_id'] = "98";
	$_POST['comment'] = "hello";

	if(isset($_POST)){

		$video['select'] = "user_id";
		$video['table'] = "veeds_videos";
		$video['where'] = "video_id = ".$_POST['video_id'];
		$video_result = jp_get($video);
		$video_row = mysqli_fetch_assoc($video_result);

		$owner_id = $video_row['user_id'];

		$search1['select'] = "user_id_block";
		$search1['table'] = "veeds_users_block";
		$search1['where'] = "user_id = ".$owner_id." AND user_id_block = ".$_POST['user_id'];

		if(jp_count($search1) > 0){

			$reply = array('reply' => '2');

		}else{

			$tags = explode('@', $_POST['comment']);
			if(count($tags) > 0){
				for($i = 1; $i < count($tags); $i++){
					$username = explode(' ', $tags[$i]);
					$search['select'] = "user_id";
					$search['table'] = "veeds_users";
					$search['where'] = "username = '".$username[0]."'";

					if(jp_count($search) > 0){
						$result2 = jp_get($search);
						$row = mysqli_fetch_array($result2);

						$data2['select'] = "video_id";
						$data2['table'] = "veeds_video_tags";
						$data2['where'] = "video_id = '".$_POST['video_id']."' AND user_id = '".$row['user_id']."'";
						$count = jp_count($data2);
						if($count == 0){
							$data2['data'] = array('video_id' => $_POST['video_id'], 'user_id' => $row['user_id']);
							jp_add($data2);
						}

						if($owner_id != $row['user_id']){
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
									$device_ids = array();
									$devices['select'] = "google_device_id";
									$devices['table'] = "veeds_user_devices";
									$devices['where'] = "user_id = ".$row['user_id'];

									$device_result = jp_get($devices);

									while($device_row = mysqli_fetch_array($device_result)){
										$device_ids[] = $device_row['google_device_id'];
									}
									//push data
									$push = array();
									$push['reciever_id'] = $notif_data['user_id'];
									$push['video_id'] = $_POST['video_id'];
									$push['body'] = $user_row['name']." tagged you in a post.";
									$push['type'] = "tag";
									$push['image'] = 'http://ec2-52-40-31-134.us-west-2.compute.amazonaws.com/veeds/profile_pics/'.$user_row['profile_pic'];

									sendGoogleCloudMessage($push, $device_ids);
								}
							}
						}
					}
				}
			}

			$_POST['comment_date'] = date('Y-m-d H:i:s');
			$data['table'] = "veeds_comments";
			$data['data'] = $_POST;
			$list['commenters'] = array();
			if(jp_add($data)){
				//Only owner of the video will receive the notification
				if($owner_id != $_POST['user_id']){
					//save notification info on database if the one commenting is not the owner of the video
					$list['commenters'][] = $video_row;
					$notif_data['user_id'] = $owner_id; //owner of the video
					$notif_data['type'] = "comment";
					$notif_data['activity_user'] = $_POST['user_id']; //commenter of the video
					$notif_data['notif_datetime'] = date('Y-m-d H:i:s');
					$notif_data['video_id'] = $_POST['video_id'];
					$notif['data'] = $notif_data;
					$notif['table'] = "notifications";

					if(jp_add($notif)){
						$receiver['select'] = "user_id, notifications";
						$receiver['table'] = "veeds_users";
						$receiver['where'] = "user_id = ".$owner_id;
						$receiver_result = jp_get($receiver);
						$receiver_row = mysqli_fetch_assoc($receiver_result);
						$list['receiver_row'][] = $receiver_row;
						//fetch commenter data to construct push data
						$user['select'] = "username, profile_pic";
						$user['table'] = "veeds_users";
						$user['where'] = "user_id = ".$_POST['user_id'];
						$user_result = jp_get($user);
						$user_row = mysqli_fetch_assoc($user_result);

						if($receiver_row['notifications'] == 1){
							//get all device ids of video owner
							$device_ids = array();
							$devices['select'] = "google_device_id";
							$devices['table'] = "veeds_user_devices";
							$devices['where'] = "user_id = ".$owner_id;

							$device_result = jp_get($devices);

							while($device_row = mysqli_fetch_array($device_result)){
								$device_ids[] = $device_row['google_device_id'];
							}
							//push data
							$push = array();
							$push['reciever_id'] = $notif_data['user_id']; //owner that will receive notification
							$push['video_id'] = $_POST['video_id'];
							$push['body'] = $user_row['username']." commented on your post.";
							$push['type'] = "comment";
							$push['image'] = 'http://ec2-52-40-31-134.us-west-2.compute.amazonaws.com/veeds/profile_pics/'.$user_row['profile_pic'];
							$list['push'][] = $push;
							sendGoogleCloudMessage($push, $device_ids);
						}
					}
					//Users who comment to the video will receive the notification except the one who comment and owner of the video
					$search['select'] = "DISTINCT user_id";
					$search['table'] = "veeds_comments";
					$search['where'] = "video_id = '".$_POST['video_id']."' AND user_id != '".$_POST['user_id']."' AND user_id != '".$owner_id."'";

					if (jp_count($search) > 0) {
						$result = jp_get($search);

						while ($row = mysqli_fetch_assoc($result)) {
							$list['commenters'][] = $row;
							$notif_data['user_id'] = $row['user_id'];//users fetch
							$notif_data['type'] = "comment";
							$notif_data['activity_user'] = $_POST['user_id'];//commenter of the video
							$notif_data['notif_datetime'] = date('Y-m-d H:i:s');
							$notif_data['video_id'] = $_POST['video_id'];
							$notif['data'] = $notif_data;
							$notif['table'] = "notifications";

							if(jp_add($notif)){
								$receiver['select'] = "user_id, notifications";
								$receiver['table'] = "veeds_users";
								$receiver['where'] = "user_id = ".$row['user_id'];
								$receiver_result = jp_get($receiver);
								$receiver_row = mysqli_fetch_assoc($receiver_result);
								$list['receiver_row'][] = $receiver_row;
								//fetch commenter data to construct push data
								$user['select'] = "username, profile_pic";
								$user['table'] = "veeds_users";
								$user['where'] = "user_id = ".$_POST['user_id'];
								$user_result = jp_get($user);
								$user_row = mysqli_fetch_assoc($user_result);

								if($receiver_row['notifications'] == 1){
									//get all device ids of users commented on the video
									$device_ids = array();
									$devices['select'] = "google_device_id";
									$devices['table'] = "veeds_user_devices";
									$devices['where'] = "user_id = ".$row['user_id'];

									$device_result = jp_get($devices);

									while($device_row = mysqli_fetch_array($device_result)){
										$device_ids[] = $device_row['google_device_id'];
									}
									//push data
									$push = array();
									$push['reciever_id'] = $notif_data['user_id']; //users that will receive notification
									$push['video_id'] = $_POST['video_id'];
									$push['body'] = $user_row['username']." also commented on the post you are following.";
									// $push['body'] = $user_row['username']." also commented on the live photo you are following.";
									$push['type'] = "comment";
									$push['image'] = 'http://ec2-52-40-31-134.us-west-2.compute.amazonaws.com/veeds/profile_pics/'.$user_row['profile_pic'];
									//$list['push'][] = $notif['data'];
									$list['push'][] = $push;
									sendGoogleCloudMessage($push, $device_ids);
								}
							}
						}
					} else {
						$list['commenters'] = "no ids";
					}
				} else {
					//Users who comment to the video will receive the notification except the owner of the video
					$search['select'] = "DISTINCT user_id";
					$search['table'] = "veeds_comments";
					$search['where'] = "video_id = '".$_POST['video_id']."' AND user_id != ".$owner_id;

					$result = jp_get($search);

					while ($row = mysqli_fetch_assoc($result)){
						$list['commenters'][] = $row;
						$notif_data['user_id'] = $row['user_id'];
						$notif_data['type'] = "comment";
						$notif_data['activity_user'] = $owner_id;
						$notif_data['notif_datetime'] = date('Y-m-d H:i:s');
						$notif_data['video_id'] = $_POST['video_id'];
						$notif['data'] = $notif_data;
						$notif['table'] = "notifications";

						if(jp_add($notif)){
							$receiver['select'] = "user_id, notifications";
							$receiver['table'] = "veeds_users";
							$receiver['where'] = "user_id = ".$row['user_id'];
							$receiver_result = jp_get($receiver);
							$receiver_row = mysqli_fetch_assoc($receiver_result);
							$list['receiver_row'][] = $receiver_row;
							//fetch commenter data to construct push data
							$user['select'] = "username, profile_pic";
							$user['table'] = "veeds_users";
							$user['where'] = "user_id = ".$owner_id;
							$user_result = jp_get($user);
							$user_row = mysqli_fetch_assoc($user_result);

							if($receiver_row['notifications'] == 1){
								//get all device ids of commenters
								$device_ids = array();
								$devices['select'] = "google_device_id";
								$devices['table'] = "veeds_user_devices";
								$devices['where'] = "user_id = ".$row['user_id'];

								$device_result = jp_get($devices);

								while($device_row = mysqli_fetch_array($device_result)){
								$device_ids[] = $device_row['google_device_id'];
								}
								//push data
								$push = array();
								$push['reciever_id'] = $notif_data['user_id'];
								$push['video_id'] = $_POST['video_id'];
								$push['body'] = $user_row['username']." also commented on the post you are following.";
								// $push['body'] = $user_row['username']." also commented on the live photo you are following.";
								$push['type'] = "comment";
								$push['image'] = 'http://ec2-52-40-31-134.us-west-2.compute.amazonaws.com/veeds/profile_pics/'.$user_row['profile_pic'];
								$list['push'][] = $push;
								sendGoogleCloudMessage($push, $device_ids);
							}
						}
					}
				}

				$search['select'] = "video_id";
				$search['table'] = "veeds_comments";
				$search['where'] = "video_id = ".$_POST['video_id'];

				$count = jp_count($search);

				$reply = array('reply' => '1', 'data' => $data, 'commenters' => $list, 'comments_count' => $count);
			} else {
				$reply = array('reply' => '0');
			}
		}
		echo json_encode($reply);
	}

?>
