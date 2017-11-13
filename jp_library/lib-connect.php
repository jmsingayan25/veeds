<?php 
	
	date_default_timezone_set('Asia/Manila');

	// $con = new mysqli('localhost','veeds_user','!O6y8q38zx>~kJL','veeds');
	$con = new jp_controller('localhost','root','','veeds2');
	if (mysqli_connect_error()) {
   		echo "Database connection failed: " . mysqli_connect_error();
	}else
		echo '1';
 
	// $con = new jp_controller('localhost','veeds_user','!O6y8q38zx>~kJL','veeds');
		$con = new jp_controller('localhost','root','','veeds2');
	echo "yooooo";
?>