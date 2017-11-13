<?php
	include("jp_library/jp_lib.php");
	$result = array('init' => 'init');
	if(isset($_FILES)){
		$result = array('init' => $_FILES);
		$filename = "veeds_".$_GET['user_id'];
		$filename = jp_upload($_FILES['thumb'],$filename,"cover_photo");
		if (in_array('video', $_FILES)) {
			$filename2 = "veeds_".time();
		$filename2 = jp_upload($_FILES['video'],$filename2,"cover_photo");
		}
		

		if(!empty($filename)){
			$_GET['video_thumb'] = $filename;
			
			if (in_array('video', $_FILES)) {
				$_GET['video_file'] = $filename2;
				$data['data'] = array("cover_photo" => $filename, "video_file" => $_GET['video_file']);	
			} else {
				$data['data'] = array("cover_photo" => $filename);
			}
			
			$data['table'] = "veeds_users";
			$data['where'] = "user_id = '".$_GET['user_id']."'";
			if (jp_update($data)) {
				if (in_array('video', $_FILES)) { 
					$result = array('reply' => '1', 'cover_photo' => $filename,'video_file' => $filename2, 'files' => $_FILES);
				} else {
					$result = array('reply' => '1', 'cover_photo' => $filename, 'files' => $_FILES);
				}
				
			} else {
				$result = array('reply' => '2');
			}
			echo json_encode($result);
		}
	} else {
		$result = array('reply' => '0');
		echo json_encode($result);
	}
	// echo json_encode($_FILES['']);
?>
