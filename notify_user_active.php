<?php 
	include("jp_library/jp_lib.php");
	include("GCM.php");


	$u_blocks = array();
			
	$block['table'] = "veeds_users_block";
	$block['where'] = "user_id_block = ".$_POST['user_id'];
	$result3 = jp_get($block);
	while($row3 = mysqli_fetch_array($result3)){
		$u_blocks[] = $row3['user_id'];
	}
				
	if(count($u_blocks) > 0){
		$followed_extend = " AND a.user_id_follow NOT IN (".implode(",", $u_blocks).")";
	}else{
		$followed_extend = "";
	}
				
	$follow['select'] = "b.username, b.user_id, b.profile_pic";
	$follow['table'] = "veeds_users_follow a, veeds_users b";
	$follow['where'] = "a.user_id = ".$_POST['user_id']." AND a.user_id_follow = b.user_id AND a.approved = 1 AND b.disabled = 0".$followed_extend;
	$result = jp_get($follow);
	$friends['users'] = array();
	while($row = mysqli_fetch_array($result)){
		$search10['select'] = "user_id_follow, user_id";
		$search10['table'] = "veeds_users_follow";
		$search10['where'] = "(user_id = '".$_POST['user_id']."' AND user_id_follow = '".$row['user_id']."') OR (user_id = '".$row['user_id']."' AND user_id_follow = '".$_POST['user_id']."')";
		if (jp_count($search10) == 2) {
			
					$receiver['select'] = "notifications";
					$receiver['table'] = "veeds_users";
					$receiver['where'] = "user_id = ".$row['user_id'];
					$receiver_result = jp_get($receiver);
					$receiver_row = mysqli_fetch_assoc($receiver_result);
					$friends['receiver_row'][] = $receiver_row;
					
					//fetch liker data to construct push data
					$user['select'] = "CONCAT(firstname,' ', lastname) AS name, profile_pic";
					$user['table'] = "veeds_users";
					$user['where'] = "user_id = ".$_POST['user_id'];
					$user_result = jp_get($user);
					$user_row = mysqli_fetch_assoc($user_result);
					$friends['user_row'][] = $user_row;
					if($receiver_row['notifications'] == 1){
						//get all device ids of video owner
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
						$push['reciever_id'] = $row['user_id'];
						$push['body'] = $user_row['name']." is currently active right now!";
						$push['type'] = "active";
						$push['image'] = 'http://ec2-52-40-31-134.us-west-2.compute.amazonaws.com/veeds/profile_pics/'.$user_row['profile_pic'];
						// sendGoogleCloudMessage($push, $device_ids);
						$friends['push'][] = $push; 
					}
		}
		$friends['users'][] = $row;
					
	}
	echo json_encode($friends);
?>