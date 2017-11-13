<?php 
	include("jp_library/jp_lib.php");
	$search['select'] = "video_id, video_file, video_thumb";
	$search['table'] = "veeds_videos";
	$search['where'] = "date_upload < NOW() - INTERVAL 16 HOUR";
  	
  	$array = array();
	if(jp_count($search) > 0){
  	  $result = jp_get($search);
		while($row = mysqli_fetch_assoc($result)){
  	    $array[] = $row['video_id'];
  	  }
  	
  	  $search1['table'] = "veeds_videos_likes";
  	  $search1['where'] = "video_id IN (".implode(",",$array).")";
  	  if(jp_count($search1) > 0){
  	   	jp_delete($search1);
  	  }
  	
  	  $search1['table'] = "veeds_video_tags";
  	  $search1['where'] = "video_id IN (".implode(",",$array).")";
  	  if(jp_count($search1) > 0){
  	   	jp_delete($search1);
  	  }
  	
  	  $search1['table'] = "notifications";
  	  $search1['where'] = "video_id IN (".implode(",",$array).")";
  	  if(jp_count($search1) > 0){
  	   	jp_delete($search1);
  	  }
  	
  	  $search1['table'] = "veeds_comments";
  	  $search1['where'] = "video_id IN (".implode(",",$array).")";
  	  if(jp_count($search1) > 0){
  	   	jp_delete($search1);
  	  }
  	  unlink('videos/'.$row['video_file']);
  	  unlink('thumbnails/'.$row['video_thumb']);
  	  if(jp_delete($search1)){
  	    jp_delete($search);
  	    $reply = array('reply' => 'deleted');
  	  }
		echo json_encode($reply);
	} else {
  	  $result = "";
		$reply = array('reply' => 'no item to be deleted!', 'result' => $result, 'search' => $search);
		echo json_encode($reply);
	}
		
		//delete live photos

	//  $con = new mysqli("localhost","veeds_user","!O6y8q38zx>~kJL","veeds");
	// //$sql_query = "SELECT video_id, video_file, video_thumb FROM `veeds_videos` WHERE date_upload < NOW() - INTERVAL 24 HOUR";
	// $sql_query = "DELETE FROM `veeds_videos` WHERE date_upload < NOW() - INTERVAL 16 HOUR";
	// $video = "";
	// $thumbnails = "";
	// $list = array();
	// if (mysqli_query($con, $sql_query)) {
	// $filesVideo = glob('/var/www/veeds/videos/*.mov');
	// $filesThumb = glob('/var/www/veeds/thumbnails/*.png');
	// foreach($filesVideo as $file) {
	// 	$lastModifiedTime = filemtime($file);
 //    	$currentTime = time();
 //    	$timeDiff = abs($currentTime - $lastModifiedTime)/(60*60);
	// 	if(is_file($file) && $timeDiff > 24 ) {
	// 		unlink($file);
	// 		$list['videos'][] = $file;
	// 	}
	// }
	// foreach($filesThumb as $file) {
	// 	$lastModifiedTime = filemtime($file);
 //    	$currentTime = time();
 //    	$timeDiff = abs($currentTime - $lastModifiedTime)/(60*60);
	// 	if(is_file($file) && $timeDiff > 24 ) {
	// 		unlink($file);
	// 		$list['thumb'][] = $file;
	// 	}
	// }
	// 	$reply = array('reply' => 'Live Photo Deleted');
	// } else {
	// 	$reply = array('reply' => 'Live Photo not deleted', 'sql query' => $sql_query);
	// } 
	// $date = new DateTime('2013-10-01', new DateTimeZone('UTC'));
	// echo $date->format('Y-m-d H:i:sP') . "\n";

	// $date->setTimezone(new DateTimeZone('Asia/Manila'));
	// echo $date->format('Y-m-d H:i:sP') . "\nManila";
	 // echo json_encode($reply);
?>
