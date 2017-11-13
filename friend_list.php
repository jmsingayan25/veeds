<?php
	include("jp_library/jp_lib.php");
	
	if(isset($_POST['user_id'])){
		
		$u_blocks = array();
		
		$block['table'] = "veeds_users_block";
		$block['where'] = "user_id_block = ".$_POST['user_id'];
		$result3 = jp_get($block);
		while($row3 = mysqli_fetch_array($result3)){
			$u_blocks[] = $row3['user_id'];
		}
		
		if(count($u_blocks) > 0){
			$followed_extend = " AND a.user_id_follow NOT IN (".implode(",", $u_blocks).")";
		}else{
			$followed_extend = "";
		}
		
		$follow['select'] = "b.username";
		$follow['table'] = "veeds_users_follow a, veeds_users b";
		$follow['where'] = "a.user_id = ".$_POST['user_id']." AND a.user_id_follow = b.user_id AND a.approved = 1 AND b.disabled = 0".$followed_extend;
		$result = jp_get($follow);
		$list = array();
		$list['users'] = array();
		while($row = mysqli_fetch_array($result)){
			$list['users'][] = $row['username'];
		}
		
		echo json_encode($list);
		
	}
	
?>