<?php
	include("jp_library/jp_lib.php");
	include("GCM.php");
	
	if(isset($_POST)){
		
		
		$data['table'] = "veeds_comments";
		
		$data['where'] = "comment_id = ".$_POST['comment_id'];
		
		if(jp_delete($data)){
			$reply = array('reply' => '1', 'data' => $data);
		}else
			$reply = array('reply' => '0', 'data' => $data);
		
		echo json_encode($reply);
	}
	
?>