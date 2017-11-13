<?php
	include("jp_library/jp_lib.php");
	
	//if(isset($_POST['user_id'])){
		$search['select'] = "a.video_name, a.description, a.video_id, b.firstname, b.lastname, a.date_upload, a.like_count";
		$search['table'] = "veeds_users b, veeds_videos a";
		$search['where'] = "a.user_id = 1 AND a.user_id = b.user_id"; 
		$start = 0 * 3;
		//$end = $start + 2;
		$search['filters'] = "LIMIT ".$start.", 3";
			
		$result = jp_get($search);
		
		$list = array();
		$list['videos'] = array();
		$i = 0;
		while($row = mysqli_fetch_array($result)){
			$row['name'] = $row['firstname']." ".$row['lastname'];
			$search2['table'] = "veeds_videos_likes";
			$search2['where'] = "video_id = ".$row['video_id'].""; 
				
			$count = jp_count($search2);
			
			if($count > 0){
				$row['like_status'] = 1;
			}else{
				$row['like_status'] = 0;
			}	

			$list['videos'][] = $row;
			//$list['videos']['like_count'] = $row['like_count'];
			$i++;
		}
		
		//if($i != 0){
			echo json_encode($list);
		//}
		
	//}
	
?>