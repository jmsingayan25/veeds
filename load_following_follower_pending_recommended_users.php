<?php

	include("jp_library/jp_lib.php");
	include("functions.php");
	include("class_place.php");
	
	if(empty($_POST['count']) || !isset($_POST['count'])){
		$_POST['count'] = 0;
	}

	$_POST['user_id'] = "251";

	if(isset($_POST)){

		$list = array();
		$suggested_user = array();
		$array = array();
		$u_blocks = array();

		$block['select'] = "DISTINCT user_id_block";
		$block['table'] = "veeds_users_block";
		$block['where'] = "user_id = ".$_POST['user_id'];
		$result_block = jp_get($block);
		while($row = mysqli_fetch_assoc($result_block)){
			$u_blocks[] = $row['user_id_block'];
		}

		// Get users blocked by the user
		$block['select'] = "DISTINCT user_id";
		$block['table'] = "veeds_users_block";
		$block['where'] = "user_id_block = ".$_POST['user_id'];
		$result_block = jp_get($block);
		while($row = mysqli_fetch_assoc($result_block)){
			if(!in_array($row, $u_blocks))
				$u_blocks[] = $row['user_id'];
		}

		if(count($u_blocks) > 0){
			$u_extend_followed = " AND user_id_follow NOT IN (".implode(",", $u_blocks).")";
			$u_extend_follower = " AND user_id NOT IN (".implode(",", $u_blocks).")";
		}else{
			$u_extend_followed = "";
			$u_extend_follower = "";
		}

		$search['select'] = "firstname, lastname, username, personal_information, u.user_id, profile_pic, gender, private";
		$search['table'] = "veeds_users u, veeds_users_follow f";
		$search['where'] = "f.user_id_follow = u.user_id
							AND f.user_id = '".$_POST['user_id']."' 
							AND approved = 1".$u_extend_followed;

		if(jp_count($search) > 0){

			$result = jp_get($search);
			while ($row = mysqli_fetch_assoc($result)) {

				$search1['select'] = "user_id";
				$search1['table'] = "veeds_users_follow";
				$search1['where'] = "user_id = '".$_POST['user_id']."' AND user_id_follow = '".$row['user_id']."' AND approved = 1";
				
				$count = jp_count($search1);
				if($count > 0){
					$row['followed'] = 1;
				}else{
					$search2['table'] = "veeds_users_follow";
					$search2['where'] = "user_id = '".$_POST['user_id']."' AND user_id_follow = '".$row['user_id']."' AND approved = 0";
					$count2 = jp_count($search2);
					if($count2 > 0)
						$row['followed'] = 2;
					else
						$row['followed'] = 0;
				}

				$search3['select'] = "user_id";
				$search3['table'] = "veeds_users_block";
				$search3['where'] = "user_id = '".$_POST['user_id']."' AND user_id_block = '".$row['user_id']."'"; 

				$count3 = (int)jp_count($search3);
				if($count3 > 0){
					$row['blocked'] = true;
				}else{
					$row['blocked'] = false;
				}

				$row['gender'] = (int)$row['gender'];
				$row['private'] = (int)$row['private'];
				$row['logged_id'] = $_POST['user_id'];

				if(!in_array($row['user_id'], $array)){
					$array[] = $row['user_id'];
				}

				$list['followed_user'][] = $row;
			}
		}

		$search4['select'] = "firstname, lastname, username, personal_information, u.user_id, profile_pic, gender, private";
		$search4['table'] = "veeds_users u, veeds_users_follow f";
		$search4['where'] = "f.user_id = u.user_id
							AND f.user_id_follow = '".$_POST['user_id']."' 
							AND approved = 1".$u_extend_follower;

		if(jp_count($search4) > 0){

			$result = jp_get($search4);
			while ($row = mysqli_fetch_assoc($result)) {

				$search5['select'] = "user_id";
				$search5['table'] = "veeds_users_follow";
				$search5['where'] = "user_id = '".$_POST['user_id']."' AND user_id_follow = '".$row['user_id']."' AND approved = 1";
				
				$count = jp_count($search5);
				if($count > 0){
					$row['followed'] = 1;
				}else{
					$search6['table'] = "veeds_users_follow";
					$search6['where'] = "user_id = '".$_POST['user_id']."' AND user_id_follow = '".$row['user_id']."' AND approved = 0";
					$count2 = jp_count($search6);
					if($count2 > 0)
						$row['followed'] = 2;
					else
						$row['followed'] = 0;
				}

				$search7['select'] = "user_id";
				$search7['table'] = "veeds_users_block";
				$search7['where'] = "user_id = '".$_POST['user_id']."' AND user_id_block = '".$row['user_id']."'"; 

				$count3 = (int)jp_count($search7);
				if($count3 > 0){
					$row['blocked'] = true;
				}else{
					$row['blocked'] = false;
				}

				$row['gender'] = (int)$row['gender'];
				$row['private'] = (int)$row['private'];
				$row['logged_id'] = $_POST['user_id'];

				if(!in_array($row['user_id'], $array)){
					$array[] = $row['user_id'];
				}

				$list['follower_user'][] = $row;
			}
		}

		$search8['select'] = "firstname, lastname, username, personal_information, u.user_id, profile_pic, gender, private";
		$search8['table'] = "veeds_users u, veeds_users_follow f";
		$search8['where'] = "f.user_id_follow = u.user_id
							AND f.user_id = '".$_POST['user_id']."' AND approved = 0";
							// echo implode(" ", $search8);
		if(jp_count($search8) > 0){

			$result = jp_get($search8);
			while ($row = mysqli_fetch_assoc($result)) {

				$search9['select'] = "user_id";
				$search9['table'] = "veeds_users_follow";
				$search9['where'] = "user_id_follow = '".$_POST['user_id']."' AND user_id = '".$row['user_id']."' AND approved = 1";
				
				$count = jp_count($search9);
				if($count > 0){
					$row['followed'] = 1;
				}else{
					$search10['table'] = "veeds_users_follow";
					$search10['where'] = "user_id = '".$_POST['user_id']."' AND user_id_follow = '".$row['user_id']."' AND approved = 0";
					$count2 = jp_count($search10);
					if($count2 > 0)
						$row['followed'] = 2;
					else
						$row['followed'] = 0;
				}

				$search11['select'] = "user_id";
				$search11['table'] = "veeds_users_block";
				$search11['where'] = "user_id = '".$_POST['user_id']."' AND user_id_block = '".$row['user_id']."'"; 

				$count3 = (int)jp_count($search11);
				if($count3 > 0){
					$row['blocked'] = true;
				}else{
					$row['blocked'] = false;
				}

				$row['gender'] = (int)$row['gender'];
				$row['private'] = (int)$row['private'];
				$row['logged_id'] = $_POST['user_id'];

				if(!in_array($row['user_id'], $array)){
					$array[] = $row['user_id'];
				}

				$list['pending_user'][] = $row;
			}
		}

		//get popular user having many posts
		$search12['user_id'] = "user_id";
		$search12['table'] = "veeds_videos";
		$search12['filters'] = "GROUP BY user_id HAVING COUNT(user_id) >= 10";

		if(jp_count($search12) > 0){
			$result = jp_get($search12);
			while($row = mysqli_fetch_assoc($result)){
	       		$suggested_user[] = $row['user_id'];
			}
		}else{
			$suggested_user[] = "''";
		}

		//get popular user having more likes on photos
	    $search13['select'] = "DISTINCT user_id";
	    $search13['table'] = "veeds_videos_likes";
	    $search13['filters'] = "GROUP BY user_id HAVING COUNT(user_id) >= 10";

	    if(jp_count($search13) > 0){
			$result = jp_get($search13);
			while($row = mysqli_fetch_assoc($result)){
				if(!in_array($row['user_id'], $suggested_user))
			    	$suggested_user[] = $row['user_id'];
			}
		}else{
			$suggested_user[] = "''";
		}

		//get popular user having many followers
		$search14['select'] = "DISTINCT user_id_follow";
		$search14['table'] = "veeds_users_follow";
		$search14['where'] = "approved = 1";
		$search14['filters'] = "GROUP BY user_id_follow HAVING COUNT(user_id_follow) >= 10";
		// echo implode(" ",$search14);
		if(jp_count($search14) > 0){
			$result = jp_get($search14);
			while($row = mysqli_fetch_assoc($result)){
				if(!in_array($row['user_id_follow'], $suggested_user))
					$suggested_user[] = $row['user_id_follow'];
			}
		}else{
			$suggested_user[] = "''";
		}

		//displays info of the users
		$start = $_POST['count'] * 15;
	    $search15['select'] = "firstname, lastname, username, personal_information, user_id, profile_pic, gender, private";
	    $search15['table'] = "veeds_users";
	    $search15['where'] = "user_id IN (".implode(",",$suggested_user).")
	    						AND user_id NOT IN (".implode(",",$array).")";
		$search15['filters'] = "LIMIT ".$start.", 15";
		// echo implode(" ",$search15);
		if(jp_count($search15) > 0){
			$result = jp_get($search15);
			while($row = mysqli_fetch_assoc($result)){

				$search16['select'] = "user_id";
				$search16['table'] = "veeds_users_follow";
				$search16['where'] = "user_id = '".$_POST['user_id']."' AND user_id_follow = '".$row['user_id']."' AND approved = 1";
				
				$count = jp_count($search16);
				if($count > 0){
					$row['followed'] = 1;
				}else{
					$search17['table'] = "veeds_users_follow";
					$search17['where'] = "user_id = '".$_POST['user_id']."' AND user_id_follow = '".$row['user_id']."' AND approved = 0";
					$count2 = jp_count($search17);
					if($count2 > 0)
						$row['followed'] = 2;
					else
						$row['followed'] = 0;
				}

				$search18['select'] = "user_id";
				$search18['table'] = "veeds_users_block";
				$search18['where'] = "user_id = '".$_POST['user_id']."' AND user_id_block = '".$row['user_id']."'"; 

				$count3 = (int)jp_count($search18);
				if($count3 > 0){
					$row['blocked'] = true;
				}else{
					$row['blocked'] = false;
				}

				$row['gender'] = (int)$row['gender'];
				$row['private'] = (int)$row['private'];
				$row['logged_id'] = $_POST['user_id'];
			 	$list['suggested_user'][] = $row;
			}
		}

		echo json_encode($list);
	}

?>