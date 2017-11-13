<?php

	// include("jp_library/jp_lib.php");
	include("jp_library/con-define.php");

	// $data = "LOAD DATA LOCAL INFILE 'words_alpha.txt' INTO TABLE 'veeds_dictionary' LINES TERMINATED BY '\n'";

	// if(mysqli_query($db, $data)){
	// 	echo "success ".$data;
	// }else{
	// 	echo "failed ".$data;
	// }
	ini_set('max_execution_time', 7200);
	$fhandle=fopen("txt/tl.wl", "r");
	fgets($fhandle); //First fgets to read over header line.

	while($line=fgets($fhandle)){
	    //Explode your line by space delimeter
	    $words=explode(" ",$line);
	    /*
	        Do additional checks and data sanitizing here.
	    */
	    //If every line follows the format in your example, and is not empty, insert into table here
	    // $words = str_replace(" ", "", $words);
		if(strlen($words[0]) > 3){
		// 	echo str_replace(" ", "", $words[0])."<br>";
		// echo $words[0]." = ".strlen($words[0])."<br>";
			$sql = "INSERT INTO veeds_dictionary(word) VALUES ('".str_replace(" ", "", $words[0])."')";

		    if(mysqli_query($db, $sql)){
				echo "success ";
			}else{
				echo "failed ";
			}	
		}
	}
?>