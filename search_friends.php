<?php 
	
	include("jp_library/jp_lib.php");
	
	if (isset($_POST['user_id'])) {
		$u_blocks = array();
				
		$block['table'] = "veeds_users_block";
		$block['where'] = "user_id_block = ".$_POST['user_id'];
		$result3 = jp_get($block);
		
		while($row3 = mysqli_fetch_array($result3)){
			$u_blocks[] = $row3['user_id'];
		}
					
		if(count($u_blocks) > 0) {
			$followed_extend = " AND a.user_id_follow NOT IN (".implode(",", $u_blocks).")";
		} else {
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
					$friends['users'][] = $row;
				}
						
		}
		$friends['post'] = $_POST;

		echo json_encode($friends);
	 }
	
?>