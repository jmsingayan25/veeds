<?php
	include("jp_library/jp_lib.php");
	
	if(isset($_FILES)){
		print_r($_FILES);
		$filename = "veeds_".time();
		
		$filename = jp_upload($_FILES['userfile'],$filename,"thumbnails");
		
		if(!empty($filename)){
			
			echo $filename;
		}
		
		
	}
	
?>