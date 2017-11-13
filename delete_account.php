<?php
	include("jp_library/jp_lib.php");
	
	if(isset($_POST['email'])){
		$search['select'] = "email, user_id";
		$search['table'] = "veeds_users";
		$search['where'] = "email = '".$_POST['email']."'";
 

		$search['select'] = "video_id, video_file, video_thumb";
		$search['table'] = "veeds_videos";
		$search['where'] = "user_id = '".$_POST['user_id']."'"; 
		$count = jp_count($search);
		
		if($count > 0){
			$result = jp_get($search);
			while($row = mysqli_fetch_array($result)){
				
				$search2['table'] = "veeds_videos_likes";
				$search2['where'] = "video_id = '".$row['video_id']."'"; 
				
				jp_delete($search2);
				
				$search2['table'] = "veeds_video_tags";
				jp_delete($search2);
			
				$search2['table'] = "veeds_comments";
				jp_delete($search2);
			
				unlink('videos/'.$row['video_file']);
				unlink('thumbnails/'.$row['video_thumb']);
				
				$search2['table'] = "veeds_videos";
				jp_delete($search2);
				
			}	
		}
		
		$search2['table'] = "veeds_videos_likes";
		$search2['where'] = "user_id = '".$_POST['user_id']."'"; 
				
		jp_delete($search2);
				
		$search2['table'] = "veeds_video_tags";
		jp_delete($search2);
			
		$search2['table'] = "veeds_users_follow";				
		jp_delete($search2);
				
		$search2['table'] = "veeds_users_block";
		jp_delete($search2);
		
		$search2['table'] = "veeds_comments";
		jp_delete($search2);
		
		$search2['table'] = "veeds_users_follow";
		$search2['where'] = "user_id_follow = '".$_POST['user_id']."'"; 		
		jp_delete($search2);
				
		$search2['table'] = "veeds_users_block";
		$search2['where'] = "user_id_block = '".$_POST['user_id']."'"; 
		jp_delete($search2);
		
		$search['select'] = "user_id, profile_pic";
		$search['table'] = "veeds_users";
		$search['where'] = "user_id = '".$_POST['user_id']."'"; 
		
		$result = jp_get($search);
		$row = mysqli_fetch_array($result);
		
		if(!empty($row['profile_pic']))
			unlink('profile_pics/'.$row['profile_pic']);
			
		jp_delete($search);
		
		$reply = array('deleted' => 1);
		
		echo json_encode($reply);
		
	}
	
?>