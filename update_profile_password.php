<?php
/*

	Change Password

*/

	include("jp_library/jp_lib.php");

	if(isset($_POST['user_id']) && isset($_POST['old_password'])){

		$reply = array();

		$search['select'] = "password";
		$search['table'] = "veeds_users";
		$search['where'] = "user_id = '".$_POST['user_id']."'";

		if(jp_count($search) > 0){
			$result = jp_get($search);
			$row = mysqli_fetch_assoc($result);

			// if(crypt($_POST['old_password'], $row['password']) == $row['password']){
			if(crypt('st',$_POST['old_password']) == $row['password']){

				$_POST['password'] = crypt('st',$_POST['password']);
				$data['data'] = $_POST;
				$data['table'] = "veeds_users";
				$data['where'] = "user_id = '".$_POST['user_id']."'";

				if(jp_update($data))
					$reply = array('reply' => 'Password updated');
				else
					$reply = array('reply' => 'Update failed');
			}else{
				$reply = array('reply' => 'Incorrect Password');
			}
		}
		echo json_encode($reply);
	}

?>