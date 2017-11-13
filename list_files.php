<?php 
	include("jp_library/jp_lib.php");
	$list['files'] = array();
	$files = glob('/var/www/veeds/videos/*.mov');

	foreach($files as $file) {
		$lastModifiedTime = filemtime($file);
    	$currentTime = time();
    	$timeDiff = abs($currentTime - $lastModifiedTime)/(60*60);
		if(is_file($file) && $timeDiff > 24 ) {
			unlink($file);
		}
	}
	$list['time'] = $timeDiff;
	echo json_encode($list);
	
?>