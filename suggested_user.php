<?php
	include("jp_library/jp_lib.php");

	if(empty($_POST['count']) || !isset($_POST['count'])){
		$_POST['count'] = 0;
	}
  if(isset($_POST['user_id'])){

    	$list = array();
	$array = array();
	//get popular user having many posts
    	$search['select'] = "DISTINCT user_id";
    	$search['table'] = "veeds_videos";
    	$search['filters'] = "GROUP BY user_id HAVING COUNT(user_id) >= 10";

    	if(jp_count($search) > 0){
		$result = jp_get($search);
		while($row = mysqli_fetch_assoc($result)){
        		$array[] = $row['user_id'];
		}
	}else{
		$array[] = "''";
	}
	//get popular user having more likes on photos
    	$search1['select'] = "DISTINCT user_id";
    	$search1['table'] = "veeds_videos_likes";
    	$search1['filters'] = "GROUP BY user_id HAVING COUNT(user_id) >= 10";

	if(jp_count($search1) > 0){
      		$result1 = jp_get($search1);
      		while($row1 = mysqli_fetch_assoc($result1)){
			if(!in_array($row1['user_id'], $array))
	        		$array[] = $row1['user_id'];
    		}
    	}else{
		$array[] = "''";
	}
	//get popular user having many followers
    	$search2['select'] = "DISTINCT user_id_follow";
    	$search2['table'] = "veeds_users_follow";
	$search2['where'] = "approved = 1";
    	$search2['filters'] = "GROUP BY user_id_follow HAVING COUNT(user_id_follow) >= 10";

	if(jp_count($search2) > 0){
      		$result2 = jp_get($search2);
      		while($row2 = mysqli_fetch_assoc($result2)){
			if(!in_array($row2['user_id_follow'], $array))
        			$array[] = $row2['user_id_follow'];
      		}
	}else{
		$array[] = "''";
	}
	//displays info of the users
	$start = $_POST['count'] * 10;
    	$search3['select'] = "DISTINCT user_id, username, firstname, lastname, personal_information, profile_pic";
    	$search3['table'] = "veeds_users";
    	$search3['where'] = "user_id IN (".implode(",",$array).")";
	$search3['filters'] = "LIMIT ".$start.", 10";
	//echo implode(" ",$search3);
	if(jp_count($search3) > 0){
		$result3 = jp_get($search3);
		while($row3 = mysqli_fetch_assoc($result3)){
		 	$list['users'][] = $row3;
		}
	}
    	echo json_encode($list);
  }
 ?>
