<?php 
	include("jp_library/jp_lib.php");

	$device['select'] = "user_id";
		$device['table'] = "veeds_user_devices";
		$device['where'] = "user_id = ".$_POST['user_id']." AND google_device_id ='".$_POST['device_id']."'";
		
		if($GLOBALS['con']->jp_count($device) >= 0){
			$device['data'] = array('user_id' => $_POST['user_id'], 'google_device_id' => $_POST['device_id']);
			 $GLOBALS['con']->jp_update($device);
		}
		echo json_encode($device['data']);
?>