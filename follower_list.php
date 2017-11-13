<?php
	include("jp_library/jp_lib.php");
	
	if(isset($_POST['user_id'])){
		$in = "";	
		
		$list = array();
		
		$follower_extend = "";
		$followed_extend = "";
		
		if(isset($_POST['logged_id'])){
			$u_blocks = array();
		
			$block['table'] = "veeds_users_block";
			$block['where'] = "user_id_block = ".$_POST['logged_id'];
			$result3 = jp_get($block);
			while($row3 = mysqli_fetch_array($result3)){
				$u_blocks[] = $row3['user_id'];
			}
		
			if(count($u_blocks) > 0){
				$follower_extend = " AND user_id NOT IN (".implode(",", $u_blocks).")";
				$followed_extend = " AND user_id_follow NOT IN (".implode(",", $u_blocks).")";
			}
		}
		
		$follow2['table'] = "veeds_users_follow";
		
		$follow2['where'] = "user_id = ".$_POST['user_id']." AND approved = 1".$followed_extend;
			$list['count_followed'] = jp_count($follow2);
		
		$follow2['where'] = "user_id_follow = ".$_POST['user_id']." AND approved = 1".$follower_extend;
			$list['count_follower'] = jp_count($follow2);
		
		$follow['table'] = "veeds_users_follow";
		if(isset($_POST['followed'])){
			
			$follow['where'] = "user_id = ".$_POST['user_id']." AND approved = 1".$followed_extend;
			$count_followed = jp_count($follow);
			$result2 = jp_get($follow);
			while($row2 = mysqli_fetch_array($result2)){
				if(empty($in))
					$in = $row2['user_id_follow'];
				else	
					$in .= ', '.$row2['user_id_follow'];
			}
		}elseif(isset($_POST['request'])){
			
			$follow['where'] = "user_id_follow = ".$_POST['user_id']." AND approved = 0".$follower_extend;
			$result2 = jp_get($follow);
			while($row2 = mysqli_fetch_array($result2)){
				if(empty($in))
					$in = $row2['user_id'];
				else	
					$in .= ', '.$row2['user_id'];
			}
		}else{
			
			$follow['where'] = "user_id_follow = ".$_POST['user_id']." AND approved = 1".$follower_extend;
			$count_follower = jp_count($follow);
			$result2 = jp_get($follow);
			while($row2 = mysqli_fetch_array($result2)){
				if(empty($in))
					$in = $row2['user_id'];
				else	
					$in .= ', '.$row2['user_id'];
			}
		}
		
		$search['select'] = "firstname, lastname, username, personal_information, user_id, profile_pic";
		$search['table'] = "veeds_users";
		$search['where'] = "user_id IN (".$in.") AND disabled = 0"; 
		$start = $_POST['count'] * 10;
		$search['filters'] = "LIMIT ".$start.", 10";
		
		
		$result = jp_get($search);
			
		$list['users'] = array();
		//if(isset($count_followed))
		//	$list['followed_count'] = $count_followed;
		
		//if(isset($count_follower))
		//	$list['follower_count'] = $count_follower;
		
		if(isset($_POST['logged_id']))
			$user_check = $_POST['logged_id'];
		else
			$user_check = $_POST['user_id'];
		if($in != ""){
			while($row = mysqli_fetch_assoc($result)){
				$search2['select'] = "user_id";
				$search2['table'] = "veeds_users_follow";
				$search2['where'] = "user_id = '".$user_check."' AND user_id_follow = '".$row['user_id']."' AND approved = 1"; 
				$count2 = jp_count($search2);
				if($count2 > 0){
					$row['followed'] = 1;
				}else{
					$search2['where'] = "user_id = '".$user_check."' AND user_id_follow = '".$row['user_id']."' AND approved = 0"; 
					$count2 = jp_count($search2);
					if($count2 > 0)
						$row['followed'] = 2;
					else
						$row['followed'] = 0;
				}
				$row['logged_id'] = $_POST['logged_id'];
				$list['users'][] = $row;
			}
		}
		
		echo json_encode($list);
	}
?>