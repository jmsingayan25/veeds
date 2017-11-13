<?php
	include("jp_library/jp_lib.php");
	$reply = array();
	$list = array();
	if($_POST['user_id']){
		$data['select'] = "user_id";
		$data['table'] = "veeds_users_block";
		$data['where'] = "user_id = '".$_POST['logged_id']."' AND user_id_block = '".$_POST['user_id']."'";
		$_POST['user_id_block'] = $_POST['user_id'];
		$_POST['user_id'] = $_POST['logged_id'];
		if(jp_count($data) > 0){
			
			if (jp_delete($data)) {
					if ($_POST['user_id']) {
						$id = $_POST['user_id'];
						$list['id'] = $id;
						$search['select'] = "user_id_block";
						$search['table'] = "veeds_users_block";
						$search['where'] = "user_id = '".$id."'";
						
						$count = jp_count($search);
						$list['count'] = $count;
						if (jp_count($search) > 0) {
							$result = jp_get($search);
							while ($row = mysqli_fetch_assoc($result)) {
								$search2['select'] = "user_id, username";
								$search2['table'] = "veeds_users";
								$search2['where'] = "user_id = '".$row['user_id_block']."'";
								if (jp_count($search2) > 0) {
									$result1 = jp_get($search2);
									$row1 = mysqli_fetch_array($result1);
									$list[] = $row1;
								}
							}
						}
		
						// echo json_encode($list);
					}



					$reply = array('block' => false, 'list' => $list);
				}
			
			} else {
			$data['data'] = $_POST;
			if (jp_add($data)) {
				//jp_add($data);
				$search3['select'] = "user_id";
				$search3['table'] = "veeds_users_follow";
				$search3['where'] = "user_id = '".$_POST['logged_id']."' AND user_id_follow = '".$_POST['user_id_block']."'";
				$result2 = jp_get($search3);
				$row4 = mysqli_fetch_array($result2);
				if(jp_count($search3) > 0){
					jp_delete($search3);
					$reply = array('block' => true, 'followed' => false);
				} else {
					$reply = array('block' => true, 'row' => $row4, 'search' => $search3);
				}
				
				$search3['select'] = "user_id";
				$search3['table'] = "veeds_users_follow";
				$search3['where'] = "user_id = '".$_POST['user_id_block']."' AND user_id_follow = '".$_POST['logged_id']."'";

				if (jp_count($search3) > 0) {
					jp_delete($search3);
					$reply['follower'] = array('follower' => true);
				} else {
					$reply['follower'] = array('follower' => false);
				}
			}
			
		}
			
		echo json_encode($reply);
		
	}
	
?>