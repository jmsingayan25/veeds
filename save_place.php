<?php

	include("jp_library/jp_lib.php");

	
	if(isset($_POST['place_name']) && isset($_POST['location']) && isset($_POST['coordinates'])){
		
		$reply = array();
		$data['data'] = array(
							'place_id' => md5(uniqid(rand(), true)),
							'place_name' => $_POST['place_name'], 
							'location' => $_POST['location'], 
							'coordinates' => $_POST['coordinates']
						);
		$data['table'] = "veeds_establishment_manual";

		if(jp_add($data)){
			$reply = array('reply' => 1);
		}else{
			$reply = array('reply' => 0);
		}

		echo json_encode($reply);
	}
?>