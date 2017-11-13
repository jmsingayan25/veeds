<?php
	include("jp_library/jp_lib.php");
	
	if(isset($_POST['video_id'])){
		$search['select'] = "video_id, video_file, video_thumb";
		$search['table'] = "veeds_videos";
		$search['where'] = "video_id = '".$_POST['video_id']."' AND user_id = '".$_POST['user_id']."'"; 
		$count = jp_count($search);
		
		if($count > 0){
			$result = jp_get($search);
			$row = mysqli_fetch_array($result);
			
			if(jp_delete($search)){
				$search2['table'] = "veeds_videos_likes";
				$search2['where'] = "video_id = '".$_POST['video_id']."'"; 
				
				jp_delete($search2);
				
				$search2['table'] = "veeds_video_tags";
				jp_delete($search2);
			
				unlink('videos/'.$row['video_file']);
				unlink('thumbnails/'.$row['video_thumb']);
				$reply = array('reply' => '1');
			}else{
				$reply = array('reply' => '0');
			}
		}else{
			$search['select'] = "video_id";
			$search['table'] = "veeds_video_tags";
			
			$count = jp_count($search);
			if($count > 0){
				jp_delete($search);
				$reply = array('reply' => '1');
			}else
				$reply = array('reply' => '0');
		}
		
		
		
		echo json_encode($reply);
		
	}
	
?>