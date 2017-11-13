<?php 
	// DEFINE("HOST", "localhost", true);
	// DEFINE("USER", "veeds_user", true);
	// DEFINE("PASS", "!O6y8q38zx>~kJL", true);
	// DEFINE("DB", "veeds", true);
	DEFINE("HOST", "localhost", true);
	DEFINE("USER", "root", true);
	DEFINE("PASS", "", true);
	DEFINE("DB", "veeds2", true);
	$db = mysqli_connect(HOST,USER,PASS,DB);

   	date_default_timezone_set("Asia/Manila");
?>