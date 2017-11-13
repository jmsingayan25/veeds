<?php 
	include("jp_library/jp_lib.php");

	$con = new mysqli("localhost","veeds_user","!O6y8q38zx>~kJL","veeds");
	$data = array();
	$data['video_id'] = "1204";
	$data['user_id'] = "49";
	$data['comment'] = "test cron job";
	$data['table'] = "veeds_comments";
	$data['comment_date'] = date('Y-m-d H:i:s');

	//$sql_query = "CREATE TABLE veeds_users_likes (user_id INT(11) PRIMARY KEY, user_total_likes INT(11))";
	
	$sql_query = "INSERT INTO `veeds_comments` (`video_id`, `user_id`, `comment`, `comment_date`) VALUES ('".$data['video_id']."', '".$data['user_id']."', '".$data['comment']."', '".$data['comment_date']."')";
	if (mysqli_query($con, $sql_query)) {
		$reply = array('reply' => 'record added');
	} else {
		$reply = array('reply' => 'record not recorded', 'query' => $sql_query);
	}
	echo json_encode($reply);
	
	// jp_add($data);

	// $reply = array('reply' => '1', 'auto post data' => $data);
	
	// echo json_encode($reply);
?>