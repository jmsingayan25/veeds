<?php


	include("jp_library/jp_lib.php");

	if (isset($_POST['user_id']) && isset($_POST['facebook_id'])) {
		
		$list = array();

		// $search['select'] = "DISTINCT user_id, username, firstname, lastname, personal_information, profile_pic";
		// $search['table'] = "veeds_users";
		// $search['where'] = "user_id = '".$_POST['user_id']."'";

		// if(jp_count($search) > 0){
		// 	$result = jp_get($search);
		// 	while($row = mysqli_fetch_assoc($result)){
		// 		$list['user'][] = $row;
		// 	}
		// }
		$facebook_id = rtrim($_POST['facebook_id'],',');
		$search['select'] = "DISTINCT user_id, username, firstname, lastname, personal_information, profile_pic";
		$search['table'] = "veeds_users";
		$search['where'] = "fbid IN (".$facebook_id.")";

		if(jp_count($search) > 0){
			$result = jp_get($search);
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
				$list['users'][] = $row;
			}
		}
		echo json_encode($list);

	}

?>