<?php 
	include("jp_library/jp_lib.php");

	if (isset($_POST['user_id'])) {
		$search['select'] = "*";
			$search['table'] = "veeds_videos";
			$search['where'] = "user_id = ".$_POST['user_id'];
			$count = array();
			$list['user'] = array();
			if (jp_count($search) >= 0) {

				$result = jp_get($search);
				while($row = mysqli_fetch_assoc($result)) {
					include('video_checks.php');
					$list['user'][] = $row;
				}
				

				//$reply = array('userId' => $row['user_id'], 'video_id' => $row['video_id'], 'like_count' => $row['like_count'], 'row' => $row);

			} else {
				$list['user'][] = array('error' => 'no data found!');
			}
	}
	
	echo json_encode($list);	
?>