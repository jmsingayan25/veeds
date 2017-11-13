<?php
		include("jp_library/jp_lib.php");
		// $_POST['user_id'] = "49";	
		$reply = array();
	//	$con = new mysqli("localhost","veeds_user","!O6y8q38zx>~kJL","veeds");
		// $con = new mysqli("localhost","root","","veeds2");
    	// 	$sql_query = "CREATE TABLE `veeds_active_videos` (user_id INT(11) NOT NULL, video_id INT(11) NOT NULL, viewed_at datetime NOT NULL)";
	//	$sql_query = "CREATE TABLE `veeds_hashtag` (user_id INT(11) NULL, hashtag varchar(100) NULL, hashtag_date datetime NULL)";

   		// $sql_query = "CREATE TABLE `veeds_users_location` (
   		// 				`user_id` int(11) NOT NULL,
					//   	`location` text NOT NULL)";
	
	
	// 	$sql_query = "ALTER TABLE veeds_users ADD post int(11) NULL AFTER video_file";	
	//	$sql_query = "ALTER TABLE veeds_users DROP COLUMN post";
	//	$sql_query = "ALTER TABLE `veeds_users` ADD `post` INT(11) NOT NULL DEFAULT '0' AFTER `video_file`";
	// 	$sql_query = "DELETE FROM `veeds_user_devices`";
		// if (mysqli_query($con, $sql_query)) {
		// 	$reply = array('reply' => 'column created');
		// } else {
		// 	$reply = array('reply' => 'column not created');
		// }
	
	//	$result = mysqli_query($con, "SHOW tables");
	//	$result = mysqli_query($con, "SHOW COLUMNS FROM veeds_active_videos");
	//	if (!$result) {
	//	    echo 'Could not run query: ' . mysql_error();
	//	    exit;
	//	}
	//	if (mysqli_num_rows($result) > 0) {
	//	    while ($row = mysqli_fetch_assoc($result)) {
	//	        $reply[] = $row;
	//	    }
	//	}
	// 	$search['table'] = "veeds_active_videos";
	// 	$search['where'] = "user_id = 183";
	// 	if (jp_count($search) > 0) {
	// 		$result = jp_get($search);
	// 		while ($row = mysqli_fetch_assoc($result)) {
	// 			$reply['ids'][] = $row;
	// 		}
	// 	}
	//	$search['select'] = "user_id, video_id";
	//	$search['table'] = "veeds_videos";
	// 	if (jp_count($search) > 0) {
	// 		$result = jp_get($search);
	// 		while ($row = mysqli_fetch_assoc($result)) {
	// 			$reply['ids'][] = $row;
	// 		}
	// 	}
	//  	$search['select'] = "user_id, hashtag";
	//  	$search['table'] = "veeds_hashtag";
	//  	if (jp_count($search) > 0) {
	//		$result = jp_get($search);
	//		while ($row = mysqli_fetch_assoc($result)) {
	//			$reply['ids'][] = $row;
	//		}
	//	}


	// $search['select'] = " HOUR(date_upload), COUNT(*) as TOTAL";
	// $search['table'] = "veeds_videos";
	// $search['where'] = "user_id = 183";
	// $search['filters'] = "GROUP BY HOUR(date_upload)";

	// $result = jp_get($search);
	// while ($row = mysqli_fetch_assoc($result)) {
	// 	$reply['hour'][] = $row;
	// }

	// $search['select'] = " HOUR(date_upload), video_id";
	// $search['table'] = "veeds_videos";
	// $search['where'] = "user_id = 183";
	// // $search['filters'] = "GROUP BY HOUR(date_upload)";

	// $result = jp_get($search);

	// while ($row = mysqli_fetch_assoc($result)) {
	// 	$reply[$row['HOUR(date_upload)']][] = $row;
	// }

	//	get hashtag acording to users most used hashtag
	//	$search5['select'] = "hashtag, COUNT(LOWER(hashtag)) AS count";
	//	$search5['table'] = "veeds_hashtag";
	//	$search5['where'] = "user_id = ".$_POST['user_id'];
	//	$search5['filters'] = "GROUP BY hashtag ORDER BY count DESC";
	
	//	if(jp_count($search5) > 0){
	//		$result5 = jp_get($search5);
	//		$row5 = mysqli_fetch_assoc($result5);
	//		$row5['hashtag'] = str_replace(array("%","_","+","-","/","'"),array("|%","|_","|+","|-","|/","|'"),$row5['hashtag']);
	//		$hashtag_extend = " AND (v.description LIKE '%".$row5['hashtag']."%' OR v.description LIKE '".$row5['hashtag']."%' ESCAPE '|')";
	//		//echo $hashtag_extend;
	//	}else{
	//		$hashtag_extend = " AND v.description LIKE '#%'";
	//	}

	//	//displays suggested posts according to the most used hashtag by the user
	//	//$start6 = $_POST['count'] * 30;
	//	$search6['select'] = "DISTINCT v.video_id, v.video_name, v.description, v.video_file, v.video_thumb, v.date_upload, v.video_length,
	//			      v.view_count, v.location, v.landscape_file, v.timestamp, v.user_id, u.username, u.firstname, u.lastname, u.profile_pic";
	//	$search6['table'] = "veeds_videos v, veeds_users u";
	//	$search6['where'] = "v.user_id = u.user_id AND v.user_id != ".$_POST['user_id']." ".$hashtag_extend;
	//	//$search6['filters'] = "LIMIT ".$start6.", 30";

	//	if(jp_count($search6) > 0){
	//		$result6 = jp_get($search6);
	//		while($row6 = mysqli_fetch_assoc($result6)){
	//		 	$reply['hashtag'][] = $row6;
	//		}
	//	}
		echo json_encode($reply);

	// 	if(empty($_POST['count']) || !isset($_POST['count'])){
	// 		$_POST['count'] = 0;
	// 	}
	// 	if(isset($_POST['user_id'])){

	// 	$list = array();
	// 	$u_blocks = array();
	// 	$array = array();
	// 	//get user who block current user
	// 	$block['table'] = "veeds_users_block";
	// 	$block['where'] = "user_id_block = ".$_POST['user_id'];

	// 	if(jp_count($block) > 0){
	// 		$result = jp_get($block);
	// 		while($row = mysqli_fetch_array($result)){
	// 			$u_blocks['user'][] = $row['user_id'];
	// 		}
	// 	}else{
	// 		$u_blocks['user'][] = "''";
	// 	}
	// 	//get user who blocked by current user
	// 	$block['where'] = "user_id = ".$_POST['user_id'];

	// 	if(jp_count($block) > 0){
	// 		$result = jp_get($block);
	// 		while($row = mysqli_fetch_array($result)){
	// 			$u_blocks['block'][] = $row['user_id_block'];
	// 		}
	// 	}else{
	// 		$u_blocks['block'][] = "''";
	// 	}

	// 	$u_blocks_data = array_merge($u_blocks['user'],$u_blocks['block']);

	// 	if(count($u_blocks_data) > 0){
	// 		$follower_extend = " AND user_id NOT IN (".implode(",",$u_blocks['block']).")";
	// 		$follower_extend2 = " AND v.user_id NOT IN (".implode(",", $u_blocks_data).")";
	// 		$followed_extend = " AND user_id_follow NOT IN (".implode(",", $u_blocks['user']).")";
	// 	}else{
	// 		$follower_extend = "";
	// 		$follower_extend2 = "";
	// 		$followed_extend = "";
	// 	}

	// 	$follow2['table'] = "veeds_users_follow";

	// 	$follow2['where'] = "user_id = ".$_POST['user_id']." AND approved = 1".$followed_extend;
	// 	$count = (int)jp_count($follow2);
	// 		$list['count_followed'] = $count;

	// 	$follow2['where'] = "user_id_follow = ".$_POST['user_id']." AND approved = 1".$follower_extend;
	// 	$count = (int)jp_count($follow2);
	// 		$list['count_follower'] = $count;
	// 	//get user videos
	// 	$search['select'] = "video_id";
	// 	$search['table'] = "veeds_videos";
	// 	$search['where'] = "user_id = '".$_POST['user_id']."'";
	// 	$search['filters'] = "ORDER BY video_id DESC";

	// 	$count = (int)jp_count($search);

	// 	if($count > 0){
	// 		$result = jp_get($search);
	// 		while ($row1 = mysqli_fetch_assoc($result)) {
	// 			$array['own'][] = $row1['video_id'];
	// 		}
	// 		$list['video_count'] = $count;
	// 	}else{
	// 		$array['own'][] = "''";
	// 		$list['video_count'] = 0;
	// 	}
	// 	//get videos where user is tagged
	// 	$search['select'] = "DISTINCT t.video_id";
	// 	$search['table'] = "veeds_video_tags t, veeds_videos v";
	// 	$search['where'] = "t.video_id = v.video_id AND t.user_id = ".$_POST['user_id']."
	// 			    AND v.video_id NOT IN (".implode(",",$array['own']).")".$follower_extend2;
	// 	$search['filters'] = "ORDER BY t.video_id DESC";

	// 	$count = (int)jp_count($search);

	// 	if($count > 0){
	// 		$result = jp_get($search);
	// 		while ($row2 = mysqli_fetch_assoc($result)) {
	// 			$array['tag'][] = $row2['video_id'];
	// 		}
	// 		$list['tag_video_count'] = $count;
	// 	}else{
	// 		$array['tag'][] = "''";
	// 		$list['tag_video_count'] = 0;
	// 	}
	// 	//displays info of the user and the video
	// 	$array_data = array_merge($array['own'],$array['tag']);
	// 	$start = $_POST['count'] * 15;
	// 	$search['select'] = "DISTINCT v.video_id, v.video_name, v.video_file, v.description, v.video_thumb, v.date_upload, v.video_length,
	// 			     v.view_count, v.location, v.landscape_file, v.timestamp, v.user_id as uploader_user_id, u.username, u.firstname,
	// 			     u.lastname, u.profile_pic, u.personal_information";
	// 	$search['table'] = "veeds_videos v, veeds_users u";
	// 	$search['where'] = "v.user_id = u.user_id AND v.video_id IN (".implode(",",$array_data).")";
	// 	$search['filters'] = "ORDER BY find_in_set(v.video_id,'".implode(",",$array_data)."') LIMIT ".$start.", 15";

	// 	$result = jp_get($search);

	// 	while($row = mysqli_fetch_assoc($result)){
	// 		include('video_checks.php');
	// 		$row['video_name'] = str_replace(';quote;',"'",$row['video_name']);
	// 		$row['description'] = str_replace(';quote;',"'", $row['description']);
	// 		$row['view_count'] = (int)$row['view_count'];


	// 		if(!empty($row['timestamp'])){

	// 			$search1['select'] = "video_thumb, time_stamp_key";
	// 			$search1['table'] = "veeds_active_videos";
	// 			//$search1['where'] = "time_stamp_key IN (".implode(",",array_filter($array['timestamp'])).")";
	// 			$search1['where'] = "time_stamp_key = ".$row['timestamp'];

	// 			//echo implode(" ",$search1);
	// 			$thumb = array();
	// 			if(jp_count($search1) > 0){
	// 				$result1 = jp_get($search1);
	// 				while($row1 = mysqli_fetch_assoc($result1)){
	// 					$thumb['video_thumb'][] = $row1['video_thumb'];
	// 					//$list['thumb'][] = $row1;
	// 				}
	// 			}

	// 			$search5['select'] = "location, time_stamp_key";
	// 			$search5['table'] = "veeds_active_videos";
	// 			//$search2['where'] = "time_stamp_key IN (".implode(",",array_filter($array['timestamp'])).")";
	// 			$search5['where'] = "time_stamp_key = ".$row['timestamp'];
	// 			$search5['filters'] = "GROUP BY time_stamp_key";

	// 			if(jp_count($search5) > 0){
	// 				$result2 = jp_get($search5);
	// 				while($row2 = mysqli_fetch_assoc($result2)){

	// 					if(!isset($thumb['video_thumb'][0])){
	// 						$thumb['video_thumb'][0] = null;
	// 					}else{
	// 						$row2["thumb1"] = $thumb['video_thumb'][0];
	// 					}
	// 					if(!isset($thumb['video_thumb'][1])){
	// 						$thumb['video_thumb'][1] = null;
	// 					}else {
	// 						$row2["thumb2"] = $thumb['video_thumb'][1];
	// 					}
	// 					if(!isset($thumb['video_thumb'][2])){
	// 						$thumb['video_thumb'][2] = null;
	// 					}else {
	// 						$row2["thumb3"] = $thumb['video_thumb'][2];
	// 					}
	// 					$list['active'][] = $row2;
	// 					unset($thumb);
	// 				}
	// 			}
	// 		} else {
	// 			$list['videos'][] = $row;
	// 		}

	// 	}

	// 	echo json_encode($list);
	// }	


?>
