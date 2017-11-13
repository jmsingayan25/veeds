<?php
	include("jp_library/jp_lib.php");
	
	if(isset($_POST['username'])){
		$extend = "";
		
		if(isset($_POST['user_id'])){
			$u_blocks = array();
		
			$block['table'] = "veeds_users_block";
			$block['where'] = "user_id_block = ".$_POST['user_id'];
			$result3 = jp_get($block);
			while($row3 = mysqli_fetch_array($result3)){
				$u_blocks[] = $row3['user_id'];
			}
		
			if(count($u_blocks) > 0){
				$extend = " AND user_id NOT IN (".implode(",", $u_blocks).")";
			}
			
		}
		
		$search['select'] = "user_id";
		$search['table'] = "veeds_users";
		$search['where'] = "username = '".$_POST['username']."' AND disabled = 0".$extend;
		
		if(jp_count($search) > 0){
			$result = jp_get($search);
		
			$row = mysqli_fetch_array($result);
			$row['ok'] = 1; 
			
		}else{
			$row = array('ok' => 0);
			
		}
		echo json_encode($row);
	}
	
?>