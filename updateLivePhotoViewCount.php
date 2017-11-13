<?php
	include("jp_library/jp_lib.php");
	
	if (isset($_POST['user_id'])) {
		$search['select'] = "view_count";
		$search['table'] = $_POST['table'];
		$search['where'] = "video_id = '".$_POST['video_id']."'"; 

		if (jp_count($search) > 0) {
			$result = jp_get($search);

			$row = mysqli_fetch_array($result);

			$view_count = array();
			$view_count['view_count'] = $row['view_count'] + 1;
			$update['table'] = $_POST['table'];
			$update['data'] = $view_count;
			$update['where'] = "video_id = '".$_POST['video_id']."'"; 
			jp_update($update);
			$reply = array();
			$data['data'] = $_POST;
			$data['table'] = "veeds_live_photo_viewer"; 
			$data['viewed_at'] = date("Y-m-d H:i:s");
			if (jp_add($data)) {
				$reply = array('view' => 'true', 'post' => $_POST);
			} else {
				$reply = array('view' => 'false', 'post' => $_POST);
			}
			
			echo json_encode($reply);

		}
		
	}
?>