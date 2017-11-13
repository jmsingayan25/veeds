<?php
	include("jp_library/jp_lib.php");
	
	$list = array();
	if(isset($_POST['user_id'])){
		$u_blocks = array();
		
		$block['table'] = "veeds_users_block";
		$block['where'] = "user_id_block = ".$_POST['logged_id']; //pass logged id required.
		$result3 = jp_get($block);
		while($row3 = mysqli_fetch_array($result3)){
			$u_blocks[] = $row3['user_id'];
		}
		
		if(count($u_blocks) > 0){
			$follower_extend = " AND user_id NOT IN (".implode(",", $u_blocks).")";
			$followed_extend = " AND user_id_follow NOT IN (".implode(",", $u_blocks).")";
		}else{
			$follower_extend = "";
			$followed_extend = "";
		}
		
		$follow['table'] = "veeds_users_follow";
		
		$follow['where'] = "user_id = ".$_POST['user_id']." AND approved = 1".$followed_extend;
		$list['count_followed'] = jp_count($follow);
		
		$follow['where'] = "user_id_follow = ".$_POST['user_id']." AND approved = 1".$follower_extend;
		$list['count_follower'] = jp_count($follow);
		
		$follow['where'] = "user_id_follow = ".$_POST['user_id']." AND user_id = ".$_POST['logged_id']." AND approved = 1";
		if(jp_count($follow) > 0){
			$list['followed'] = 1;
		}else{
			$follow['where'] = "user_id_follow = ".$_POST['user_id']." AND user_id = ".$_POST['logged_id']." AND approved = 0"; 
			
			if(jp_count($follow) > 0)
				$list['followed'] = 2;
			else
				$list['followed'] = 0;
		}
		
		echo json_encode($list);
	}
	
?>