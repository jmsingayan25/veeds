<?php
	include("jp_library/jp_lib.php");
	
	if(empty($_POST['count']) || !isset($_POST['count'])){
		$_POST['count'] = 0;
	}

	$_POST['user_id'] = "183";
	if(isset($_POST['user_id'])){
		
		$search['select'] = "a.type, a.notif_datetime, b.username, b.firstname, b.lastname, b.profile_pic, a.activity_user, a.video_id, a.notif_id";
		$search['table'] = "notifications a, veeds_users b";
		$search['where'] = "a.activity_user = b.user_id AND a.user_id = ".$_POST['user_id']."" /* AND a.notif_datetime > DATE_SUB(NOW(), INTERVAL 24 HOUR) 
				    AND a.notif_datetime <= NOW()"*/; 
		$start = $_POST['count'] * 10;
		$search['filters'] = "ORDER BY a.notif_datetime DESC LIMIT ".$start.", 10"; 
		// echo implode(" ", $search);
		$result = jp_get($search);
		
		$list['notifications'] = array();
		
		$types = array(
			'like' => ' liked your post.',
			'started_following' => ' started following you.',
			'accepted' => ' has accept your follow request.',
			'comment' => ' commented on your post.',
			'tag' => ' tagged you in a post.',
			'request' => ' has requested to follow you.'
		);
		
		while($row = mysqli_fetch_assoc($result)){
			$notif = array();
			
			//$notif['body'] = $row['firstname']." ".$row['lastname'].$types[$row['type']];
			$notif['body'] = $row['username'].$types[$row['type']];
			$notif['time'] = $row['notif_datetime'];
			$notif['type'] = $row['type'];
			$notif['id']   = $row['notif_id'];
			$notif['profile_pic'] = 'http://ec2-52-40-31-134.us-west-2.compute.amazonaws.com/veeds/profile_pics/'.$row['profile_pic'];
			$notif['actor_id'] = $row['activity_user'];

			if($row['type'] == "started_following" || $row['type']== "accepted" || $row['type']== "request"){
				// $notif['user_id'] = $row['activity_user'];
				$notif['user_id'] = $_POST['user_id'];
			}else{
				$notif['video_id'] = $row['video_id'];
			}

			if (($row['type'] == "like") || ($row['type'] == "comment")) {
				$search1['select'] = "video_thumb";
				$search1['table'] = "veeds_videos";
				$search1['where'] = "video_id = '".$row['video_id']."'";

				$result1 = jp_get($search1);

				$row1 = mysqli_fetch_array($result1);

				$notif['video_thumb'] = $row1['video_thumb'];
			}
			$list['user_id'] = $_POST['user_id'];
			$notif['user_id'] = $_POST['user_id'];
			$list['notifications'][] = $notif;
		}
		
		echo json_encode($list);
		
	}
	
?>