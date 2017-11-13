<?php
	include("jp_library/jp_lib.php");

	if(empty($_POST['count']) || !isset($_POST['count'])){
		$_POST['count'] = 0;
	}

	$_POST['search_date'] = date('Y-m-d H:i:s');
	if(isset($_POST['user_id'])){
		$u_blocks = array();
		$blocks = array();
		$private_followed = array($_POST['user_id']);

		$block['table'] = "veeds_users_block";
		$block['where'] = "user_id_block = ".$_POST['user_id'];
		$result3 = jp_get($block);
		while($row3 = mysqli_fetch_array($result3)){
			$blocks[] = $row3['user_id'];
			$u_blocks[] = $row3['user_id'];
		}

		$block['where'] = "user_id = ".$_POST['user_id'];
		$result3 = jp_get($block);
		while($row3 = mysqli_fetch_array($result3)){
			if(!in_array($row3['user_id_block'], $blocks))
				$blocks[] = $row3['user_id_block'];

		}

		if(count($u_blocks) > 0){
			$u_extend = " AND user_id NOT IN (".implode(",", $u_blocks).")";
		}else{
			$u_extend = "";
		}

		if(count($blocks) > 0){
			$extend = " AND b.user_id NOT IN (".implode(",", $blocks).")";
		}else{
			$extend = "";
		}

		$follow['select'] = "DISTINCT(a.user_id_follow)";
		$follow['table'] = "veeds_users_follow a, veeds_users b";
		$follow['where'] = "a.user_id_follow = b.user_id AND b.private = 1 AND a.user_id = ".$_POST['user_id']." AND a.approved = 1";
		$result4 = jp_get($follow);
		while($row4 = mysqli_fetch_array($result4)){
			if(!in_array($row4['user_id_follow'], $blocks))
				$private_followed[] = $row4['user_id_follow'];
		}

		if(count($private_followed) > 0){
			$p_extend = " AND (b.private = 0 OR (b.private = 1 AND b.user_id IN (".implode(",", $private_followed).")))";
		}else{
			$p_extend = " AND b.private = 0";
		}
		//add user search to veeds_users_history
		// if(isset($_POST['user_id']) && isset($_POST['keyword'])){
		// 	$search['select'] = "firstname, lastname, username, personal_information, user_id, profile_pic";
		//   $search['table'] = "veeds_users";
		//   $search['where'] = "(firstname LIKE '%".$_POST['keyword']."%' OR lastname LIKE '%".$_POST['keyword']."%' OR username LIKE '%".$_POST['keyword']."%') AND disabled = 0 ".$u_extend;

		 

		// 	if(jp_count($search) > 0){
		//     $result = jp_get($search);
		//     $row = mysqli_fetch_array($result);

		//     $data['table'] = "veeds_users_history";
		//     $data['data'] = array('user_id' => $_POST['user_id'], 'user_id_search' => $row['user_id'], 'search_date' => $_POST['search_date']);
		// 		jp_add($data);
		// 	}
		// if(isset($_POST['user_id']) && isset($_POST['search_id'])){
		// 	$search['select'] = "firstname, lastname, username, personal_information, user_id, profile_pic";
		//   	$search['table'] = "veeds_users";
		//  	$search['where'] = "user_id = '".$_POST['search_id']."'";

		// 	if(jp_count($search) > 0){
		//     $result = jp_get($search);
		//     $row = mysqli_fetch_array($result);

		//     $data['table'] = "veeds_users_history";
		//     $data['data'] = array('user_id' => $_POST['user_id'], 'user_id_search' => $row['user_id'], 'search_date' => $_POST['search_date']);
		// 		jp_add($data);
		// 	}
		// }else

		if(isset($_POST['user'])){
			$search['select'] = "firstname, lastname, username, personal_information, user_id, profile_pic";
			$search['table'] = "veeds_users";
			$search['where'] = "(firstname LIKE '%".$_POST['keyword']."%' OR lastname LIKE '%".$_POST['keyword']."%' OR username LIKE '%".$_POST['keyword']."%') AND disabled = 0 ".$u_extend;
			$start = $_POST['count'] * 5;
			$search['filters'] = "LIMIT ".$start.", 5";

			$result = jp_get($search);

			$list = array();
			$list['users'] = array();

			// $data['table'] = "veeds_users_history";
		 //    $data['data'] = array('user_id' => $_POST['user_id'], 'keyword' => $_POST['keyword'], 'search_date' => $_POST['search_date']);
			// jp_add($data);

			while($row = mysqli_fetch_assoc($result)){
				$search2['select'] = "user_id";
				$search2['table'] = "veeds_users_follow";
				$search2['where'] = "user_id = '".$_POST['user_id']."' AND user_id_follow = '".$row['user_id']."' AND approved = 1";
				$count2 = jp_count($search2);
				if($count2 > 0){
					$row['followed'] = 1;
				}else{
					$search2['where'] = "user_id = '".$_POST['user_id']."' AND user_id_follow = '".$row['user_id']."' AND approved = 0";
					$count2 = jp_count($search2);
					if($count2 > 0)
						$row['followed'] = 2;
					else
						$row['followed'] = 0;
				}

				$search3['select'] = "user_id";
				$search3['table'] = "veeds_users_block";
				// $search3['where'] = "user_id = '".$_POST['user_id']."' AND user_id_block = '".$row10['user_id']."'"; 
				$search3['where'] = "user_id = '".$_POST['user_id']."' AND user_id_block = '".$row['user_id']."'"; 
				$count2 = (int)jp_count($search3);
				if($count2 > 0){
					$row['blocked'] = true;
				}else{
					$row['blocked'] = false;
				}
				$row['category'] = "Users"; // new line
				$list['users'][] = $row;
			}
			
		}else if(isset($_POST['hashtag'])){
			$search['select'] = "a.video_id, a.video_name, a.description, a.video_id, b.firstname, a.video_thumb, b.lastname, b.username, a.date_upload, a.view_count, a.location, b.profile_pic, a.video_length, b.username, a.user_id";
			$search['table'] = "veeds_users b, veeds_videos a";
			$search['where'] = "a.user_id = b.user_id AND a.description LIKE '%#".$_POST['keyword']." %' AND b.disabled = 0 ".$extend.$p_extend;
			$start = $_POST['count'] * 5;
			$search['filters'] = "ORDER BY a.date_upload DESC LIMIT ".$start.", 5";

			$result = jp_get($search);

			$list = array();
			$list['videos'] = array();
			while($row = mysqli_fetch_assoc($result)){
				include('video_checks.php');
				$row['video_name'] = str_replace(';quote;',"'",$row['video_name']);
				$row['description'] = str_replace(';quote;',"'", $row['description']);
				$list['videos'][] = $row;
			}
		}elseif(isset($_POST['user_onstart'])){
			//echo '1';
			// $search['select'] = "firstname, lastname, username, personal_information, user_id, profile_pic";
			// $search['table'] = "veeds_users";
			// $search['where'] = "disabled = 0 ".$u_extend;
			// $start = $_POST['count'] * 5;
			// $search['filters'] = "LIMIT ".$start.", 5";
			// //$search['debug'] = 1;
			// $result = jp_get($search);

			// $list = array();
			// $list['users'] = array();
			// while($row = mysqli_fetch_assoc($result)){
			// 	$search2['select'] = "user_id";
			// 	$search2['table'] = "veeds_users_follow";
			// 	$search2['where'] = "user_id = '".$_POST['user_id']."' AND user_id_follow = '".$row['user_id']."' AND approved = 1";
			// 	$count2 = jp_count($search2);
			// 	if($count2 > 0){
			// 		$row['followed'] = 1;
			// 	}else{
			// 		$search2['where'] = "user_id = '".$_POST['user_id']."' AND user_id_follow = '".$row['user_id']."' AND approved = 0";
			// 		$count2 = jp_count($search2);
			// 		if($count2 > 0)
			// 			$row['followed'] = 2;
			// 		else
			// 			$row['followed'] = 0;
			// 	}
			// 	$list['users'][] = $row;
			// }
			// $search9['select'] = "DISTINCT u.user_id, u.username, u.firstname, u.lastname, u.personal_information, u.profile_pic, h.search_date";
			// $search9['table'] = "veeds_users_history h, veeds_users u";
			// $search9['where'] = "h.user_id = '".$_POST['user_id']."'
			// 		    AND u.user_id = h.user_id_search
			// 		    AND h.search_date IN (SELECT MAX(search_date) FROM veeds_users_history
			// 					  WHERE user_id = ".$_POST['user_id']."
			// 					  AND user_id_search IN (SELECT MAX(user_id_search) FROM veeds_users_history
			// 							 	 WHERE user_id = ".$_POST['user_id']."
			// 								 GROUP BY user_id_search)
			// 					  GROUP BY user_id_search)";
	  //   	$search9['filters'] = "ORDER BY h.search_date DESC";
   //  		$result9 = jp_get($search9);

   //  		while($row9 = mysqli_fetch_assoc($result9)){
			// 	$search2['select'] = "user_id";
			// 	$search2['table'] = "veeds_users_follow";
			// 	$search2['where'] = "user_id = '".$_POST['user_id']."' AND user_id_follow = '".$row9['user_id']."' AND approved = 1";
			// 	$count2 = jp_count($search2);
			// 	if($count2 > 0){
			// 		$row9['followed'] = 1;
			// 	}else{
			// 		$search2['where'] = "user_id = '".$_POST['user_id']."' AND user_id_follow = '".$row9['user_id']."' AND approved = 0";
			// 		$count2 = jp_count($search2);
			// 		if($count2 > 0)
			// 			$row9['followed'] = 2;
			// 		else
			// 			$row9['followed'] = 0;
			// 	}

			// 	$search3['select'] = "user_id";
			// 	$search3['table'] = "veeds_users_block";
			// 	// $search3['where'] = "user_id = '".$_POST['user_id']."' AND user_id_block = '".$row10['user_id']."'"; 
			// 	$search3['where'] = "user_id = '".$_POST['user_id']."' AND user_id_block = '".$row9['user_id']."'"; 
			// 	$count2 = (int)jp_count($search3);
			// 	if($count2 > 0){
			// 		$row9['blocked'] = true;
			// 	}else{
			// 		$row9['blocked'] = false;
			// 	}
			// 	$list['users_history'][] = $row9;
			// }
/*
	new line
*/
			$search9['select'] = "DISTINCT u.user_id, u.username, u.firstname, u.lastname, u.personal_information, u.profile_pic, h.search_date";
			$search9['table'] = "veeds_users_history h, veeds_users u";
			$search9['where'] = "h.user_id = '".$_POST['user_id']."'
					    			AND u.user_id = h.user_id_search
					    			AND h.category = 'Users'
				   					AND h.search_date IN (SELECT MAX(search_date) FROM veeds_users_history
								  							WHERE user_id = ".$_POST['user_id']."
								  							AND user_id_search IN (SELECT MAX(user_id_search) 
								  													FROM veeds_users_history
										 	 										WHERE user_id = ".$_POST['user_id']."
																		 			GROUP BY user_id_search)
							  								GROUP BY user_id_search)";
			$start = $_POST['count'] * 5;
	    	$search9['filters'] = "ORDER BY h.search_date DESC LIMIT ".$start.", 5";
    		$result9 = jp_get($search9);

    		while($row9 = mysqli_fetch_assoc($result9)){
				$search2['select'] = "user_id";
				$search2['table'] = "veeds_users_follow";
				$search2['where'] = "user_id = '".$_POST['user_id']."' AND user_id_follow = '".$row9['user_id']."' AND approved = 1";
				$count2 = jp_count($search2);
				if($count2 > 0){
					$row9['followed'] = 1;
				}else{
					$search2['where'] = "user_id = '".$_POST['user_id']."' AND user_id_follow = '".$row9['user_id']."' AND approved = 0";
					$count2 = jp_count($search2);
					if($count2 > 0)
						$row9['followed'] = 2;
					else
						$row9['followed'] = 0;
				}

				$search3['select'] = "user_id";
				$search3['table'] = "veeds_users_block";
				// $search3['where'] = "user_id = '".$_POST['user_id']."' AND user_id_block = '".$row10['user_id']."'"; 
				$search3['where'] = "user_id = '".$_POST['user_id']."' AND user_id_block = '".$row9['user_id']."'"; 
				$count2 = (int)jp_count($search3);
				if($count2 > 0){
					$row9['blocked'] = true;
				}else{
					$row9['blocked'] = false;
				}
				$row9['category'] = "Users";
				$row9['logged_id'] = $_POST['user_id'];

				$row9 = array(
							'category' => $row9['category'],
							'user_id' => $row9['user_id'],
							'username' => $row9['username'],
							'firstname' => $row9['firstname'],
							'lastname' => $row9['lastname'],
							'personal_information' => $row9['personal_information'],
							'profile_pic' => $row9['profile_pic'],
							'search_date' => $row9['search_date'],
							'followed' => $row9['followed'],
							'blocked' => $row9['blocked'],
							'logged_id' => $row9['logged_id']
							);
				$list['users'][] = $row9;
				// $list['users_history'][] = $row9;
			}
/*
	end of new line
*/
		}elseif(isset($_POST['hashtag_onstart'])){
			$search['select'] = "a.video_id, a.video_name, a.description, a.video_id, b.firstname, a.video_thumb, b.lastname, b.username, a.date_upload, a.view_count, a.location, b.profile_pic, a.video_length, b.username, a.user_id";
			$search['table'] = "veeds_users b, veeds_videos a";
			$search['where'] = "a.user_id = b.user_id AND a.description LIKE '%#%' AND b.disabled = 0 ".$extend.$p_extend;
			$start = $_POST['count'] * 5;
			$search['filters'] = "ORDER BY a.date_upload DESC LIMIT ".$start.", 5";

			$result = jp_get($search);

			$list = array();
			$list['videos'] = array();
			while($row = mysqli_fetch_assoc($result)){
				include('video_checks.php');
				$row['video_name'] = str_replace(';quote;',"'",$row['video_name']);
				$row['description'] = str_replace(';quote;',"'", $row['description']);
				$list['videos'][] = $row;
			}
		}elseif(isset($_POST['video_onstart'])){
			$search['select'] = "a.video_id, a.video_name, a.description, a.video_id, b.firstname, a.video_thumb, b.lastname, b.username, a.date_upload, a.view_count, a.location, b.profile_pic, a.video_length, b.username, a.user_id";
			$search['table'] = "veeds_users b, veeds_videos a";
			$search['where'] = "a.user_id = b.user_id AND b.disabled = 0 ".$extend.$p_extend;
			$start = $_POST['count'] * 5;
			$search['filters'] = "ORDER BY a.date_upload DESC LIMIT ".$start.", 5";

			$result = jp_get($search);

			$list = array();
			$list['videos'] = array();
			while($row = mysqli_fetch_assoc($result)){
				include('video_checks.php');
				$row['video_name'] = str_replace(';quote;',"'",$row['video_name']);
				$row['description'] = str_replace(';quote;',"'", $row['description']);
				$list['videos'][] = $row;
			}
		}else{
			$search['select'] = "a.video_id, a.video_name, a.description, a.video_id, b.firstname, a.video_thumb, b.lastname, b.username, a.date_upload, a.view_count, a.location, b.profile_pic, a.video_length, b.username, a.user_id";
			$search['table'] = "veeds_users b, veeds_videos a";
			$search['where'] = "a.user_id = b.user_id AND (a.video_name LIKE '%".$_POST['keyword']."%' OR a.description LIKE '%".$_POST['keyword']."%') AND b.disabled = 0 ".$extend.$p_extend;
			$start = $_POST['count'] * 5;
			$search['filters'] = "ORDER BY a.date_upload DESC LIMIT ".$start.", 5";

			$result = jp_get($search);

			$list = array();
			$list['videos'] = array();
			while($row = mysqli_fetch_assoc($result)){
				include('video_checks.php');
				$row['video_name'] = str_replace(';quote;',"'",$row['video_name']);
				$row['description'] = str_replace(';quote;',"'", $row['description']);
				$list['videos'][] = $row;
			}
		}


		echo json_encode($list);
	}

?>
