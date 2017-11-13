<?php
/*

	Display search history of the user

*/	
	include("jp_library/jp_lib.php");

	if(empty($_POST['count']) || !isset($_POST['count'])){
		$_POST['count'] = 0;
	}

	$_POST['user_id'] = "183";
	if(isset($_POST['user_id'])){
   		
		$list = array();

		$search['select'] = "DISTINCT category";
		$search['table'] = "veeds_users_history";
		$search['where'] = "user_id = '".$_POST['user_id']."'";

		$result = jp_get($search);
		while ($row = mysqli_fetch_assoc($result)) {
			
			if($row['category'] == "Users"){
				
				$start = $_POST['count'] * 10;
				$search1['select'] = "user_id, user_id_search, category, search_date";
				$search1['table'] = "veeds_users_history";
				$search1['where'] = "user_id = '".$_POST['user_id']."' AND category = 'Users'";
				$search1['filters'] = "ORDER BY search_date DESC LIMIT ".$start.", 10";
				// echo implode(" ", $search1);
				if(jp_count($search1) > 0){

					$result1 = jp_get($search1);
					while ($row1 = mysqli_fetch_assoc($result1)) {

						$search2['select'] = "user_id, username, firstname, lastname, personal_information, profile_pic";
						$search2['table'] = "veeds_users";
						$search2['where'] = "username = '".$row1['user_id_search']."'";

						if(jp_count($search2) > 0){

							$result2 = jp_get($search2);
							while ($row2 = mysqli_fetch_assoc($result2)) {

								$search3['select'] = "user_id";
								$search3['table'] = "veeds_users_follow";
								$search3['where'] = "user_id = '".$_POST['user_id']."' AND user_id_follow = '".$row1['user_id']."' AND approved = 1";

								$count3 = jp_count($search3);
								if($count3 > 0){
									$row1['followed'] = 1;
								}else{
									$search3['where'] = "user_id = '".$_POST['user_id']."' AND user_id_follow = '".$row1['user_id']."' AND approved = 0";
									$count4 = jp_count($search3);
									if($count4 > 0)
										$row1['followed'] = 2;
									else
										$row1['followed'] = 0;
								}

								$search4['select'] = "user_id";
								$search4['table'] = "veeds_users_block";
								$search4['where'] = "user_id = '".$_POST['user_id']."' AND user_id_block = '".$row1['user_id']."'";

								$count5 = (int)jp_count($search4);
								if($count5 > 0){
									$row1['blocked'] = true;
								}else{
									$row1['blocked'] = false;
								}

								$row1 = array(
											'category' => 'Users',
											'user_id' => $row2['user_id'],
											'username' => $row2['username'],
											'firstname' => $row2['firstname'],
											'lastname' => $row2['lastname'],
											'personal_information' => $row2['personal_information'],
											'profile_pic' => $row2['profile_pic'],
											'search_date' => $row1['search_date'],
											'followed' => $row1['followed'],
											'blocked' => $row1['blocked'],
											'logged_id' => $_POST['user_id']
											);

								$list['history'][] = $row1;
							}
						}
					}
				}
			}

			if($row['category'] == "Places"){
				
				$start = $_POST['count'] * 10;
				$search5['select'] = "user_id, user_id_search, category, search_date";
				$search5['table'] = "veeds_users_history";
				$search5['where'] = "user_id = '".$_POST['user_id']."' AND category = 'Places'";
				$search5['filters'] = "ORDER BY search_date DESC LIMIT ".$start.", 10";
				// echo implode(" ", $search5);
				if(jp_count($search5)){
					$result5 = jp_get($search5);
					while ($row5 = mysqli_fetch_assoc($result5)) {

						$row5 = array(
									'category' => "Places",
									'user_id' => "",
									'username' => $row5['user_id_search'],
									'firstname' => "",
									'lastname' => "",
									'personal_information' => "",
									'profile_pic' => "",
									'search_date' => $row5['search_date'],
									'followed' => 0,
									'blocked' => false,
									'logged_id' => $_POST['user_id']
									);

						$list['history'][] = $row5;
					}
				}
			}

			if($row['category'] == "Hashtags"){
				// echo "hashtag <br>";
				$start = $_POST['count'] * 10;
				$search7['select'] = "DISTINCT user_id, user_id_search, category, search_date";
				$search7['table'] = "veeds_users_history";
				$search7['where'] = "user_id = '".$_POST['user_id']."' AND category = 'Hashtags'";
				$search7['filters'] = "ORDER BY search_date DESC LIMIT ".$start.", 10";
				// echo implode(" ", $search7);
				if(jp_count($search7) > 0){

					$result7 = jp_get($search7);
					while ($row7 = mysqli_fetch_assoc($result7)) {
								
						$row7 = array(
									'category' => "Hashtags",
									'user_id' => "",
									'username' => $row7['user_id_search'],
									'firstname' => "",
									'lastname' => "",
									'personal_information' => "",
									'profile_pic' => "",
									'search_date' => $row7['search_date'],
									'followed' => 0,
									'blocked' => false,
									'logged_id' => $_POST['user_id']
								);

						$list['history'][] = $row7;
					}
				}
			}
		}
		echo json_encode($list);
	}

	// if(isset($_POST['user_id'])){
   		
	// 	$list = array();

	// 	$search['select'] = "DISTINCT u.user_id, u.username, u.firstname, u.lastname, u.personal_information, u.profile_pic, h.search_date";
	// 	$search['table'] = "veeds_users_history h, veeds_users u";
	// 	$search['where'] = "h.user_id = '".$_POST['user_id']."'
	// 					    AND u.user_id = h.user_id_search
	// 					    AND h.search_date IN (SELECT MAX(search_date) FROM veeds_users_history
	// 												WHERE user_id = ".$_POST['user_id']."
	// 												AND user_id_search IN (SELECT MAX(user_id_search) 
	// 																		FROM veeds_users_history
	// 												 	 					WHERE user_id = ".$_POST['user_id']."
	// 															 			GROUP BY user_id_search)
	// 												GROUP BY user_id_search)
	// 						AND h.search_date BETWEEN (NOW() - INTERVAL 1 MONTH) AND NOW()";
	// 	$start = $_POST['count'] * 5;				  
 //    	$search['filters'] = "ORDER BY h.search_date DESC LIMIT ".$start.", 5";

	// 	$result = jp_get($search);

	// 	while($row = mysqli_fetch_assoc($result)){
	// 		$search2['select'] = "user_id";
	// 		$search2['table'] = "veeds_users_follow";
	// 		$search2['where'] = "user_id = '".$_POST['user_id']."' AND user_id_follow = '".$row['user_id']."' AND approved = 1";

	// 		$count2 = jp_count($search2);
	// 		if($count2 > 0){
	// 			$row['followed'] = 1;
	// 		}else{
	// 			$search2['where'] = "user_id = '".$_POST['user_id']."' AND user_id_follow = '".$row['user_id']."' AND approved = 0";
	// 			$count2 = jp_count($search2);
	// 			if($count2 > 0)
	// 				$row['followed'] = 2;
	// 			else
	// 				$row['followed'] = 0;
	// 		}

	// 		$search3['select'] = "user_id";
	// 		$search3['table'] = "veeds_users_block";
	// 		$search3['where'] = "user_id = '".$_POST['user_id']."' AND user_id_block = '".$row['user_id']."'";

	// 		$count2 = (int)jp_count($search3);
	// 		if($count2 > 0){
	// 			$row['blocked'] = true;
	// 		}else{
	// 			$row['blocked'] = false;
	// 		}

	// 		$row['keyword'] = " ";
	// 		$row['logged_id'] = $_POST['user_id'];

	// 		$list['history'][] = $row;
	// 	}

	// 	$search2['select'] = "keyword, search_date";
	// 	$search2['table'] = "veeds_users_history";
	// 	$search2['where'] = "user_id = '".$_POST['user_id']."' 
	// 							AND (keyword != '' OR keyword != NULL)
	// 							AND search_date BETWEEN (NOW() - INTERVAL 1 MONTH) AND NOW()";
	// 	$start = $_POST['count'] * 5;				  
 //    	$search2['filters'] = "ORDER BY search_date DESC LIMIT ".$start.", 5";

	// 	if(jp_count($search2) > 0){
	// 		$result2 = jp_get($search2);
	// 		while($row2 = mysqli_fetch_assoc($result2)){

	// 			$row2 = array(
	// 					'user_id' => " ", 
	// 					'username' => " ", 
	// 					'firstname' => " ", 
	// 					'lastname' => " ", 
	// 					'personal_information' => " ", 
	// 					'profile_pic' => " ", 
	// 					'search_date' => $row2['search_date'], 
	// 					'followed' => 0,
	// 					'blocked' => false,
	// 					'keyword' => $row2['keyword'], 
	// 					'logged_id' => $_POST['user_id']
	// 					);

	// 			$list['history'][] = $row2;
	// 		}
	// 	}
	
	// 	echo json_encode($list);
	// }
?>
