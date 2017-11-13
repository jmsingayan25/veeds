<?php
	include("jp_library/jp_lib.php");
	
	if(isset($_POST['user_id'])){
		$list = array();
		$search['select'] = "video_id";
		$search['table'] = "veeds_videos";
		$search['where'] = "user_id = '".$_POST['user_id']."'"; 
		
		$count = jp_count($search);
		
		if($count > 0){
			$list['video_count'] = $count;
		}else{
			$list['video_count'] = 0;
		}
		
		$search['table'] = "veeds_video_tags";
		$count = jp_count($search);
		
		if($count > 0){
			$list['tag_video_count'] = $count;
		}else{
			$list['tag_video_count'] = 0;
		}
	
		$search['select'] = "video_id";
		$search['table'] = "veeds_video_tags";
		$search['where'] = "user_id = '".$_POST['user_id']."'"; 
		$result = jp_get($search);
		$videos = array();
		while($row = mysqli_fetch_assoc($result)){
			$videos[] = $row['video_id'];
		}
		
		$list['videos'] = array();
		
		if(count($videos) > 0){
			$blocks = array();
			$block['table'] = "veeds_users_block";
			$block['where'] = "user_id_block = ".$_POST['user_id'];
			$result3 = jp_get($block);
			while($row3 = mysqli_fetch_array($result3)){
				$blocks[] = $row3['user_id'];
			}
		
			$block['where'] = "user_id = ".$_POST['user_id'];
			$result3 = jp_get($block);
			while($row3 = mysqli_fetch_array($result3)){
				if(!in_array($row3['user_id_block'], $blocks))
					$blocks[] = $row3['user_id_block'];
			}
			
			if(count($blocks) > 0)
				$extend = " AND b.user_id NOT IN (".implode(",", $blocks).")";
			else
				$extend = "";
		
			$search['select'] = "a.video_id, a.video_name, a.description, a.video_id, a.video_thumb, b.firstname, b.lastname, a.date_upload, a.view_count, a.location, b.profile_pic, a.video_length, b.username, a.user_id, a.landscape_file";
			$search['table'] = "veeds_users b, veeds_videos a";
			$search['where'] = "video_id IN (".implode(",", $videos).") AND a.user_id = b.user_id AND b.disabled = 0 ".$extend;
			
			if(empty($_POST['count']) || !isset($_POST['count']))
				$_POST['count'] = 0;
			
			$start = $_POST['count'] * 5;

			$search['filters'] = "ORDER BY a.date_upload DESC LIMIT ".$start.", 5";
			
			$result = jp_get($search);
		
		
			
		
			while($row = mysqli_fetch_assoc($result)){
				include('video_checks.php');	
				$row['video_name'] = str_replace(';quote;',"'",$row['video_name']);
				$row['description'] = str_replace(';quote;',"'", $row['description']);
				$list['videos'][] = $row;
			}
		}
		echo json_encode($list);
	}
	
?>