<?php
	include("jp_library/jp_lib.php");
	
	if(isset($_POST['fb_ids'])){
	
		$ids = explode(',', $_POST['fb_ids']);
		$list = array();
		$list['users'] = array();
		foreach($ids as $id){
			$search['select'] = "firstname, lastname, username, personal_information, user_id, profile_pic";
			$search['table'] = "veeds_users";
			$search['where'] = "fbid = '".$id."' AND disabled = 0"; 
			if(jp_count($search) > 0){
				$result = jp_get($search);
				$row = mysqli_fetch_assoc($result);
			
				$list['users'][] = $row;
			}
		}
		
		echo json_encode($list);
	}
	
?>