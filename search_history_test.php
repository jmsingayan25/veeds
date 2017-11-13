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

		$search['select'] = "DISTINCT u.user_id, u.username, u.firstname, u.lastname, u.personal_information, u.profile_pic, h.search_date";
		$search['table'] = "veeds_users_history h, veeds_users u";
		$search['where'] = "h.user_id = '".$_POST['user_id']."'
							AND u.user_id = h.user_id_search
							AND h.search_date IN (SELECT MAX(search_date) FROM veeds_users_history
													WHERE user_id = ".$_POST['user_id']."
													AND user_id_search IN (SELECT MAX(user_id_search) FROM veeds_users_history
																		 	 WHERE user_id = ".$_POST['user_id']."
																			 GROUP BY user_id_search)
							 						GROUP BY user_id_search)
							AND h.search_date BETWEEN (NOW() - INTERVAL 1 MONTH) AND NOW()";
		$start = $_POST['count'] * 5;				  
    	$search['filters'] = "ORDER BY h.search_date DESC LIMIT ".$start.", 5";
		// echo implode(" ", $search);
		$result = jp_get($search);

    		// while($row = mysqli_fetch_assoc($result)){
      // 			$list['user'][] = $row;
    		// }
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
			
			$row['keyword'] = " ";
			$row['logged_id'] = $_POST['user_id'];

			$list['history'][] = $row;
		}

		$search2['select'] = "keyword, search_date";
		$search2['table'] = "veeds_users_history";
		$search2['where'] = "user_id = '".$_POST['user_id']."' 
								AND (keyword != '' OR keyword != NULL)
								AND search_date BETWEEN (NOW() - INTERVAL 1 MONTH) AND NOW()";
		$start = $_POST['count'] * 5;				  
    	$search2['filters'] = "ORDER BY search_date DESC LIMIT ".$start.", 5";

		if(jp_count($search2) > 0){
			$result = jp_get($search2);
			while($row = mysqli_fetch_assoc($result)){

				$row = array(
						'user_id' => " ", 
						'username' => " ", 
						'firstname' => " ", 
						'lastname' => " ", 
						'personal_information' => " ", 
						'profile_pic' => " ", 
						'search_date' => $row['search_date'], 
						'followed' => 0,
						'blocked' => false,
						'keyword' => $row['keyword'], 
						'logged_id' => $_POST['user_id']
					);

				$list['history'][] = $row;
			}
		}
		

		echo json_encode($list);
	}
?>
