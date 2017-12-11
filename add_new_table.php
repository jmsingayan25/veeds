<?php


	include("jp_library/jp_lib.php");
	$con = new mysqli("localhost","root","","veeds2");	

	$sql = "CREATE TABLE veeds_location_activities(
				activity_no int(11) AUTO_INCREMENT PRIMARY KEY NOT NULL,
				activity_place_id varchar(100) NOT NULL,
				activity varchar(100) NOT NULL
			)";

	if(mysqli_query($con, $sql)){
		$reply = array('reply' => 'success');
	}else{
		$reply = array('reply' => 'failed');
	}

	echo json_encode($reply);
?>