<?php
	include("jp_library/jp_lib.php");
	
	if($_POST['user_id']){
		$ids = explode(',',$_POST['user_id']);
		foreach($ids as $id){
			$data['select'] = "user_id";
			$data['table'] = "veeds_users_follow";
			$data['where'] = "user_id = '".$_POST['logged_id']."' AND user_id_follow = '".$id."'";
			
			if(jp_count($data) > 0){
			}else{
				$_POST['user_id_follow'] = $id;
				$_POST['user_id'] = $_POST['logged_id'];
				$data2['select'] = "private";
				$data2['table'] = "veeds_users";
				$data2['where'] = "user_id = '".$_POST['user_id_follow']."'";
				$result = jp_get($data2);
				$row = mysqli_fetch_assoc($result);
				if($row['private'] == 1){
					$_POST['approved'] = 0;
					$data['data'] = $_POST;
					jp_add($data);
					$result = array('follow' => 2);
				}else{
					$data['data'] = $_POST;
					jp_add($data);
					$result = array('follow' => 1);
				}
			}
		}
		
		echo json_encode($result);
		
	}
	
?>