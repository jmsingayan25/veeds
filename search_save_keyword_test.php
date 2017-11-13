<?php
/*
	
	Save searched keyword to the veeds_users_history

*/
	include("jp_library/jp_lib.php");

	$_POST['search_date'] = date('Y-m-d H:i:s');
	$_POST['user_id'] = "183";
	$_POST['keyword'] = "Mcdo";
	$_POST['category'] = "Places";
	if(isset($_POST['user_id']) && isset($_POST['keyword']) && isset($_POST['category'])){

		$reply = array();

		$search['select'] = "user_id, keyword";
		$search['table'] = "veeds_users_history";
		$search['where'] = "user_id = '".$_POST['user_id']."' 
							AND keyword = '".$_POST['keyword']."' 
							AND category = '".$_POST['category']."'";

		if(jp_count($search) > 0){

			$data['data'] = array('search_date' => $_POST['search_date']);
			$data['table'] = "veeds_users_history";
			$data['where'] = "user_id = '".$_POST['user_id']."' 
								AND keyword = '".$_POST['keyword']."'
								AND category = '".$_POST['category']."'";

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
		    					'category' => $_POST['category'],
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