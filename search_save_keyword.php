<?php
/*
	
	Save searched keyword to the veeds_users_history

*/
	include("jp_library/jp_lib.php");

	$_POST['search_date'] = date('Y-m-d H:i:s');
	if(isset($_POST['user_id']) && isset($_POST['keyword'])){

		$reply = array();

	    $search['select'] = "user_id, keyword";
		$search['table'] = "veeds_users_history";
		$search['where'] = "user_id = '".$_POST['user_id']."' AND keyword = '".$_POST['keyword']."'";

		if(jp_count($search) > 0){

			$data['data'] = array('search_date' => $_POST['search_date']);
			$data['table'] = "veeds_users_history";
			$data['where'] = "user_id = '".$_POST['user_id']."' AND keyword = '".$_POST['keyword']."'";

			// $reply = array('data' => $data);
			if(jp_update($data)){
				$reply = array('reply' => 'Update Success');
			}else{
				$reply = array('reply' => 'Update Failed');
			}
		}else{

			$data['data'] = array(
		    					'user_id' => $_POST['user_id'], 
		    					'keyword' => $_POST['keyword'], 
		    					'search_date' => $_POST['search_date']
		    				);
			$data['table'] = "veeds_users_history";
		    
			// $reply = array('data' => $data);
			if(jp_add($data)){
				$reply = array('reply' => 'Add Success');
			}else{
				$reply = array('reply' => 'Add Failed');
			}
		}

		echo json_encode($reply);
	}

?>