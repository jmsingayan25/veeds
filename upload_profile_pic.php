<?php
	include("jp_library/jp_lib.php");
	
	if(isset($_FILES)){
		
		$filename = "veeds_".$_GET['user_id'];
		$filename = jp_upload($_FILES['profile_pic'],$filename,"profile_pics");
		
		if(!empty($filename)){
			$data['data'] = array("profile_pic" => $filename);
			$data['table'] = "veeds_users";
			$data['where'] = "user_id = '".$_GET['user_id']."'";
			if(jp_update($data))
				$result = array('reply' => '1', 'profile_pic' => $filename, 'files' => $_FILES);
			else 
				$result = array('reply' => '2');
		}else
			$result = array('reply' => '2');
		
		echo json_encode($result);
	}else{
		$result = array('reply' => '0');
		echo json_encode($result);
	}
	
?>