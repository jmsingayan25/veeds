<?php
	include("jp_library/jp_lib.php");
	
	if(isset($_POST['video_id'])){
		
	
		$search['select'] = "comment_id, comment, comment_date, firstname, lastname, profile_pic, b.user_id, b.username";
		$search['table'] = "veeds_comments a, veeds_users b";
		$search['where'] = "a.user_id = b.user_id AND a.video_id = '".$_POST['video_id']."' AND b.disabled = 0"; 
		$start = $_POST['count'] * 5;
		$search['filters'] = "ORDER BY a.comment_date DESC LIMIT ".$start.", 5"; 
		
		$result = jp_get($search);
		$list = array();
		
		$search2['select'] = "video_name";
		$search2['table'] = "veeds_videos";
		$search2['where'] = "video_id = '".$_POST['video_id']."'"; 
			
		$result2 = jp_get($search2);
		
		$row2 = mysqli_fetch_array($result2);
		$list['video_title'] = str_replace(';quote;',"'", $row2['video_name']);
		
		$search2['select'] = "comment_id";
		$search2['table'] = "veeds_comments";
		$search2['where'] = "video_id = '".$_POST['video_id']."'"; 
				
		$count = jp_count($search2);
				
		if($count > 0){
			$list['comment_count'] = $count;
		}else{
			$list['comment_count'] = 0;
		}
		
		$list['comments'] = array();
		while($row = mysqli_fetch_array($result)){
			
			$row['name'] = $row['firstname']." ".$row['lastname'];
			$row['username'] = $row['username'];
			$list['comments'][] = $row;
		}
		
		echo json_encode($list);
		
	}
	
?>