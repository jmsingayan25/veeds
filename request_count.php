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
			$follower_extend = " AND user_id NOT IN (".implode(",", $u_blocks).")";
		}else{
			$follower_extend = "";
		}
		
		$search2['select'] = "user_id";
		$search2['table'] = "veeds_users_follow";
		$search2['where'] = "user_id_follow = '".$_POST['user_id']."' and approved = 0".$follower_extend;
		
		$count2 = jp_count($search2);
		if($count2 > 0){
			$list['followers'] = $count2;
		}else{
			$list['followers'] = 0;
		}
		
	
		echo json_encode($list);
	}
	
?>