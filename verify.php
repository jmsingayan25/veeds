<?php

	include("jp_library/jp_lib.php");

	if(isset($_GET['email']) && !empty($_GET['email']) AND isset($_GET['code']) && !empty($_GET['code'])){
	    // Verify data
	    $email = mysql_escape_string($_GET['email']); // Set email variable
	    $code = mysql_escape_string($_GET['code']); // Set code variable

	    $search['select'] = "email, signup_code";
	    $search['table'] = "veeds_users";
	    $search['where'] = "email = '".$email."' AND signup_code = '".$code."'";

	    
	}



?>