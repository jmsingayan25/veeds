<?php

	include("jp_library/jp_lib.php");

	$array = array();
	$reply = array();
	
	$search['select'] = "video_id, video_file, video_thumb";
	$search['table'] = "veeds_videos";
	$search['where'] = "DATE_FORMAT(date_expiry,'%Y-%m-%d %H:%i %s') < NOW()";

	// echo implode(" ", $search);

	if(jp_count($search) > 0){
		$result = jp_get($search);
		while($row = mysqli_fetch_assoc($result)){
			// $array[] = $row['video_id'];

			if((unlink('videos/'.$row['video_file']) && unlink('videos/'.$row['landscape_file']) && unlink('thumbnails/'.$row['video_thumb'])) || unlink('thumbnails/'.$row['video_thumb'])){
				$reply = array('reply' => 1, 'message' => 'item deleted');
			}

			// if(unlink('videos/'.$row['video_file']) && unlink('thumbnails/'.$row['video_thumb'])){
			// 	$reply = array('reply' => 1, 'message' => 'file deleted');
			// }
		}

		// $search1['table'] = "veeds_videos_likes";
		// $search1['where'] = "video_id IN (".implode(",",$array).")";
		// if(jp_count($search1) > 0){
		// 	jp_delete($search1);
		// }

		// $search2['table'] = "veeds_video_tags";
		// $search2['where'] = "video_id IN (".implode(",",$array).")";
		// if(jp_count($search2) > 0){
		// 	jp_delete($search2);
		// }

		// $search3['table'] = "notifications";
		// $search3['where'] = "video_id IN (".implode(",",$array).")";
		// if(jp_count($search3) > 0){
		// 	jp_delete($search3);
		// }

		// $search4['table'] = "veeds_comments";
		// $search4['where'] = "video_id IN (".implode(",",$array).")";
		// if(jp_count($search4) > 0){
		// 	jp_delete($search4);
		// }

		// unlink('videos/'.$row['video_file']);
		// unlink('thumbnails/'.$row['video_thumb']);

		// if(jp_delete($search1)){
		// 	jp_delete($search);
		// 	$reply = array('reply' => 'deleted');
		// }
		
		// jp_delete($search);
		// $reply = array('reply' => 1, 'message' => 'item deleted');

		// echo json_encode($reply);

	}else{
		$reply = array('reply' => 0, 'message' => 'no item to be deleted!');
		// echo json_encode($reply);
	}
	echo json_encode($reply);

?>