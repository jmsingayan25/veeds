<?php
	include("jp_library/jp_lib.php");


	if(empty($_POST['count']) || !isset($_POST['count'])){
		$_POST['count'] = 0;
	}
	if(isset($_POST['user_id'])){

		$list = array();
		$array = array();
		//get user following
		$search0['select'] = "DISTINCT user_id_follow";
		$search0['table'] = "veeds_users_follow";
    		$search0['where'] = "user_id = ".$_POST['user_id']." AND approved = 1";


		if(jp_count($search0) > 0){

			$search['select'] = "DISTINCT user_id_follow";
			$search['table'] = "veeds_users_follow";
	    		$search['where'] = "user_id = ".$_POST['user_id']." AND approved = 1";

			if(jp_count($search) > 0){
				$result = jp_get($search);
				while($row = mysqli_fetch_assoc($result)){
					$array['users_follow'][] = $row['user_id_follow'];
				}
			}else{
				$array['users_follow'][] = "''";
			}
			//get users followed following except the itself
			$search1['select'] = "DISTINCT user_id_follow";
			$search1['table'] = "veeds_users_follow";
			$search1['where'] = "user_id IN (".implode(",",$array['users_follow']).") AND user_id_follow != ".$_POST['user_id']."
					     AND user_id_follow NOT IN (".implode(",",$array['users_follow']).") AND approved = 1";
			
			if(jp_count($search1) > 0){
				$result1 = jp_get($search1);
				while($row1 = mysqli_fetch_assoc($result1)){
					$array['users_followed_following'][] = $row1['user_id_follow'];
				}
			}else{
				$array['users_followed_following'][] = "''";
			}
			//get users who blocked by the users followed following
			$block['table'] = "veeds_users_block";
			$block['where'] = "user_id IN (".implode(",",$array['users_followed_following']).")";

			if(jp_count($block) > 0){
				$result = jp_get($block);
				while($row = mysqli_fetch_array($result)){
					$array['block'][] = $row['user_id_block'];
				}
			}else{
				$array['block'][] = "''";
			}
			//get user who blocked by current user
			$block['where'] = "user_id = ".$_POST['user_id'];

			if(jp_count($block) > 0){
				$result = jp_get($block);
				while($row = mysqli_fetch_array($result)){
					$array['user_block'][] = $row['user_id_block'];
				}
			}else{
				$array['user_block'][] = "''";
			}			
			//displays info of the users followed following
			$start2 = $_POST['count'] * 10;
			$search2['select'] = "DISTINCT user_id, username, firstname, lastname, personal_information, profile_pic";
			$search2['table'] = "veeds_users";
			$search2['where'] = "user_id IN (".implode(",",$array['users_followed_following']).")
					     AND user_id NOT IN (".implode(",",$array['block']).")";			
			$search2['filters'] = "ORDER BY find_in_set(user_id,'".implode(",",$array['users_followed_following'])."') LIMIT ".$start2.", 10";
			
			if(jp_count($search2) > 0){
				$result2 = jp_get($search2);
				while($row2 = mysqli_fetch_assoc($result2)){
					$list['users'][] = $row2;
				}
			}
			//get liked user except itself, its following and the followed following
			$array_data3 = array_merge($array['users_follow'],$array['users_followed_following']);
			$search3['select'] = "DISTINCT user_id";
			$search3['table'] = "veeds_videos_likes";
			$search3['where'] = "user_id_liker = ".$_POST['user_id']." AND user_id != ".$_POST['user_id']."
					     AND user_id NOT IN (".implode(",",$array_data3).")";
		
			if(jp_count($search3) > 0){
				$result3 = jp_get($search3);
				while($row3 = mysqli_fetch_assoc($result3)){
					$array['user_liked'][] = $row3['user_id'];
				}
			}else{
				$array['user_liked'][] = "''";
			}
			//displays latest posts of the liked user and users followed following
			$array_data4 = array_merge($array['users_followed_following'],$array['user_liked']);
			$start4 = $_POST['count'] * 15;
			$search4['select'] = "DISTINCT v.video_id, v.video_name, v.description, v.video_file, v.video_thumb, v.date_upload, v.video_length,
					      v.view_count, v.location, v.landscape_file, v.timestamp, v.user_id as uploader_user_id, u.username, u.firstname, 
					      u.lastname, u.profile_pic";
			$search4['table'] = "veeds_videos v, veeds_users u";
			$search4['where'] = "v.user_id = u.user_id AND v.user_id IN (".implode(",",$array_data4).") 
					     AND v.user_id NOT IN (".implode(",",$array['block']).")
					     AND v.date_upload IN (SELECT MAX(date_upload) FROM veeds_videos WHERE user_id IN (".implode(",",$array_data4).")
								   GROUP BY user_id)";
	  		$search4['filters'] = "ORDER BY v.date_upload DESC LIMIT ".$start4.", 15";
		
			if(jp_count($search4) > 0){
				$result4 = jp_get($search4);
				while($row = mysqli_fetch_assoc($result4)){
					include('video_checks.php');
					$row['view_count'] = (int)$row['view_count'];
					$row['user_id'] = $_POST['user_id'];
			 		$list['videos'][] = $row;
		 		}
			}

			$search5['select'] = "hashtag, COUNT(LOWER(hashtag)) AS count";
			$search5['table'] = "veeds_hashtag";
			$search5['where'] = "user_id = ".$_POST['user_id'];
			$search5['filters'] = "GROUP BY hashtag ORDER BY count DESC";
			
			if(jp_count($search5) > 0){
				$result5 = jp_get($search5);
				$row5 = mysqli_fetch_assoc($result5);
				$row5['hashtag'] = str_replace(array("%","_","'",'"',"/","\\","$"),array("\%","\_","\'",'\"',"\/","\\","\$"),$row5['hashtag']);
				$hashtag_extend = " AND (v.description LIKE '%".$row5['hashtag']."%' OR v.description LIKE '".$row5['hashtag']."%')";
				//$row5['hashtag'] = str_replace(array("%","_","+","-","/","'"),array("|%","|_","|+","|-","|/","|'"),$row5['hashtag']);
				//$hashtag_extend = " AND (v.description LIKE '%".$row5['hashtag']."%' OR v.description LIKE '".$row5['hashtag']."%' ESCAPE '|')";
			}else{
				$hashtag_extend = " AND v.description LIKE '#%'";
			}
			
			//displays suggested posts according to the most used hashtag by the user
			$start6 = $_POST['count'] * 30;
			$search6['select'] = "DISTINCT v.video_id, v.video_name, v.description, v.video_file, v.video_thumb, v.date_upload, v.video_length,
					      v.view_count, v.location, v.landscape_file, v.timestamp, v.user_id as uploader_user_id, u.username, u.firstname, 
					      u.lastname, u.profile_pic";
			$search6['table'] = "veeds_videos v, veeds_users u";
			$search6['where'] = "v.user_id = u.user_id AND v.user_id != ".$_POST['user_id']." 
					     AND v.user_id NOT IN (".implode(",",$array['user_block']).") ".$hashtag_extend;		
			$search6['filters'] = "ORDER BY v.date_upload LIMIT ".$start6.", 30";
			
			if(jp_count($search6) > 0){
				$result6 = jp_get($search6);
				while($row = mysqli_fetch_assoc($result6)){
					include('video_checks.php');
					$row['view_count'] = (int)$row['view_count'];
					$row['user_id'] = $_POST['user_id'];
				 	$list['videos'][] = $row;
				}
			}
		}else{

			$search10['select'] = "DISTINCT user_id";
			$search10['table'] = "veeds_videos";
			$search10['filters'] = "GROUP BY user_id HAVING COUNT(user_id) >= 10";

			if(jp_count($search10) > 0){
				$result = jp_get($search10);
				while($row = mysqli_fetch_assoc($result)){
					$array[] = $row['user_id'];
				}
			}else{
				$array[] = "''";
			}
			//get popular user having more likes on photos
			$search11['select'] = "DISTINCT user_id";
			$search11['table'] = "veeds_videos_likes";
			$search11['filters'] = "GROUP BY user_id HAVING COUNT(user_id) >= 10";

			if(jp_count($search11) > 0){
				$result1 = jp_get($search11);
				while($row1 = mysqli_fetch_assoc($result1)){
					if(!in_array($row1['user_id'], $array))
						$array[] = $row1['user_id'];
				}
			}else{
				$array[] = "''";
			}
			//get popular user having many followers
			$search12['select'] = "DISTINCT user_id_follow";
			$search12['table'] = "veeds_users_follow";
			$search12['where'] = "approved = 1";
			$search12['filters'] = "GROUP BY user_id_follow HAVING COUNT(user_id_follow) >= 10";

			if(jp_count($search12) > 0){
				$result2 = jp_get($search12);
				while($row2 = mysqli_fetch_assoc($result2)){
					if(!in_array($row2['user_id_follow'], $array))
						$array[] = $row2['user_id_follow'];
				}
			}else{
				$array[] = "''";
			}
			//displays info of the users
			$start = $_POST['count'] * 15;
			$search13['select'] = "DISTINCT user_id, username, firstname, lastname, personal_information, profile_pic";
			$search13['table'] = "veeds_users";
			$search13['where'] = "user_id IN (".implode(",",$array).")";
			$search13['filters'] = "LIMIT ".$start.", 15";
			
			if(jp_count($search13) > 0){
				$result3 = jp_get($search13);
				while($row3 = mysqli_fetch_assoc($result3)){
					$list['users'][] = $row3;
				}
			}

			$search15['select'] = "hashtag, COUNT(LOWER(hashtag)) AS count";
			$search15['table'] = "veeds_hashtag";
			$search15['where'] = "user_id = ".$_POST['user_id'];
			$search15['filters'] = "GROUP BY hashtag ORDER BY count DESC";
			
			if(jp_count($search15) > 0){
				$result5 = jp_get($search15);
				$row5 = mysqli_fetch_assoc($result5);
				$row5['hashtag'] = str_replace(array("%","_","'",'"',"/","\\","$"),array("\%","\_","\'",'\"',"\/","\\","\$"),$row5['hashtag']);
				$hashtag_extend = " AND (v.description LIKE '%".$row5['hashtag']."%' OR v.description LIKE '".$row5['hashtag']."%')";
				//$row5['hashtag'] = str_replace(array("%","_","+","-","/","'"),array("|%","|_","|+","|-","|/","|'"),$row5['hashtag']);
				//$hashtag_extend = " AND (v.description LIKE '%".$row5['hashtag']."%' OR v.description LIKE '".$row5['hashtag']."%' ESCAPE '|')";
			}else{
				$hashtag_extend = " AND v.description LIKE '#%'";
			}
			
			//displays suggested posts according to the most used hashtag by the user
			$start6 = $_POST['count'] * 30;
			$search6['select'] = "DISTINCT v.video_id, v.video_name, v.description, v.video_file, v.video_thumb, v.date_upload, v.video_length,
					      v.view_count, v.location, v.landscape_file, v.timestamp, v.user_id as uploader_user_id, u.username, u.firstname, 
					      u.lastname, u.profile_pic";
			$search6['table'] = "veeds_videos v, veeds_users u";
			$search6['where'] = "v.user_id = u.user_id AND v.user_id != ".$_POST['user_id']." ".$hashtag_extend;		
			$search6['filters'] = "ORDER BY v.date_upload LIMIT ".$start6.", 30";
			
			if(jp_count($search6) > 0){
				$result6 = jp_get($search6);
				while($row = mysqli_fetch_assoc($result6)){
					include('video_checks.php');
					$row['view_count'] = (int)$row['view_count'];
					$row['user_id'] = $_POST['user_id'];
				 	$list['videos'][] = $row;
				}
			}
		}
		//get hashtag acording to users most used hashtag
		// $search5['select'] = "hashtag, COUNT(LOWER(hashtag)) AS count";
		// $search5['table'] = "veeds_hashtag";
		// $search5['where'] = "user_id = ".$_POST['user_id'];
		// $search5['filters'] = "GROUP BY hashtag ORDER BY count DESC";
		
		// if(jp_count($search5) > 0){
		// 	$result5 = jp_get($search5);
		// 	$row5 = mysqli_fetch_assoc($result5);
		// 	$row5['hashtag'] = str_replace(array("%","_","'",'"',"/","\\","$"),array("\%","\_","\'",'\"',"\/","\\","\$"),$row5['hashtag']);
		// 	$hashtag_extend = " AND (v.description LIKE '%".$row5['hashtag']."%' OR v.description LIKE '".$row5['hashtag']."%')";
		// 	//$row5['hashtag'] = str_replace(array("%","_","+","-","/","'"),array("|%","|_","|+","|-","|/","|'"),$row5['hashtag']);
		// 	//$hashtag_extend = " AND (v.description LIKE '%".$row5['hashtag']."%' OR v.description LIKE '".$row5['hashtag']."%' ESCAPE '|')";
		// }else{
		// 	$hashtag_extend = " AND v.description LIKE '#%'";
		// }
		
		// //displays suggested posts according to the most used hashtag by the user
		// $start6 = $_POST['count'] * 30;
		// $search6['select'] = "DISTINCT v.video_id, v.video_name, v.description, v.video_file, v.video_thumb, v.date_upload, v.video_length,
		// 		      v.view_count, v.location, v.landscape_file, v.timestamp, v.user_id as uploader_user_id, u.username, u.firstname, 
		// 		      u.lastname, u.profile_pic";
		// $search6['table'] = "veeds_videos v, veeds_users u";
		// $search6['where'] = "v.user_id = u.user_id AND v.user_id != ".$_POST['user_id']." 
		// 		     AND v.user_id NOT IN (".implode(",",$array['user_block']).") ".$hashtag_extend;		
		// $search6['filters'] = "ORDER BY v.date_upload LIMIT ".$start6.", 30";
		
		// if(jp_count($search6) > 0){
		// 	$result6 = jp_get($search6);
		// 	while($row = mysqli_fetch_assoc($result6)){
		// 		include('video_checks.php');
		// 		$row['view_count'] = (int)$row['view_count'];
		// 		$row['user_id'] = $_POST['user_id'];
		// 	 	$list['videos'][] = $row;
		// 	}
		// }
		echo json_encode($list);
	}
?>
