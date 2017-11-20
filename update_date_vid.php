<?php
/*

	Update date_expiry of a post once user viewed the picture.
	Also update the view count every view of a unique user.

*/
	include("jp_library/jp_lib.php");

	// $_POST['uploader_user_id'] = "205";
	// $_POST['video_id'] = "44";
	if(isset($_POST['uploader_user_id']) && isset($_POST['video_id'])){

		$reply = array();

		$date = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s') . ' +1 day'));
		$code = array('date_expiry' => $date);

		$data['data'] = $code;
		$data['table'] = "veeds_videos";
		$data['where'] = "user_id = '".$_POST['uploader_user_id']."' AND video_id = '".$_POST['video_id']."'";

		if(jp_update($data)){
			$reply = array('reply' => 1, 'message' => 'Update success');
		}else{
			$reply = array('reply' => 0, 'message' => 'Update failed');
		}

		$search['select'] = "view_count";
		$search['table'] = "veeds_videos";
		$search['where'] = "user_id = '".$_POST['uploader_user_id']."' AND video_id = '".$_POST['video_id']."'"; 

		if (jp_count($search) > 0) {

			$result = jp_get($search);
			$row = mysqli_fetch_assoc($result);

			$view_count = array();
			$view_count['view_count'] = $row['view_count'] + 1;
			
			$update['data'] = $view_count;
			$update['table'] = "veeds_videos";
			$update['where'] = "user_id = '".$_POST['uploader_user_id']."' AND video_id = '".$_POST['video_id']."'"; 
			jp_update($update);
		}
		echo json_encode($reply);
	}
?>