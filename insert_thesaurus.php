<?php

	include("jp_library/jp_lib.php");

	$filename = readfile("filename.csv");
	$ext=substr($filename,strrpos($filename,"."),(strlen($filename)-strrpos($filename,".")));

	//we check,file must be have csv extention
	if($ext=="csv"){
	  $file = fopen($filename, "r");
	         while (($emapData = fgetcsv($file, 10000, ",")) !== FALSE)
	         {
	            $sql = "INSERT into veeds_dictionary(word) values('$emapData[0]')";
	            mysqli_query($sql);
	         }
	         fclose($file);
	         echo "CSV File has been successfully Imported.";
	}else{
	    echo "Error: Please Upload only CSV File";
	}
	

?>