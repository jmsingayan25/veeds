<?php

	include("jp_library/jp_lib.php");

	if(isset($_GET['email']) && !empty($_GET['email']) AND isset($_GET['code']) && !empty($_GET['code'])){
	    // Verify data
	    $email = mysql_escape_string($_GET['email']); // Set email variable
	    $code = mysql_escape_string($_GET['code']); // Set code variable

	    $search['select'] = "email, signup_code";
	    $search['table'] = "veeds_users";
	    $search['where'] = "email = '".$email."' AND signup_code = '".$code."'";

	    if(jp_count($search) > 0){
        // We have a match, activate the account
	        // mysql_query("UPDATE users SET active='1' WHERE email='".$email."' AND hash='".$hash."' AND active='0'") or die(mysql_error());
	        $code = array('');
	        $data['data'] =
	        $data['table'] = "veeds_users";
	        $data['where'] = "email = '".$email."' AND signup_code = '".$code."'"; 
	        // echo '<div class="statusmsg">Your account has been activated, you can now login</div>';
	    }else{
	        // No match -> invalid url or account has already been activated.
	        echo '<div class="statusmsg">The url is either invalid or you already have activated your account.</div>';
	    }
	}



?>