<?php
	include("jp_library/jp_lib.php");
	
	
	if(isset($_POST)){
	
		$data['table'] = "vender_user";
			
		$search['select'] = "user_id, password";
		$search['table'] = $data['table'];
		$search['where'] = "username = '".$_POST['username']."'"; 
			
		if(jp_count($search) > 0){
			$result = jp_get($search);
			$row = mysqli_fetch_array($result);
				
			if(crypt($_POST['password'], $row['password']) == $row['password']){
				$reply = array(
					'reply' => 'ok',
					'user_id' => $row['user_id'],
				);
				$_POST['user_id'] = $row['user_id'];
				
				$device['table'] = 'veeds_users_devices';
				$device['where'] = 'google_device_id = "'.$_POST['google_device_id'].'" AND user_id = '.$row['user_id'];
				if(jp_count($device) == 0){
					$device['data'] = $_POST;
					jp_add($device);
				}
					
			}else{
				$reply = array('reply' => '0');
			}
		}else{
			$reply = array('reply' => '0');
		}
			
		echo json_encode($reply);
	}
?>