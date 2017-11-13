<?php
  	include("jp_library/jp_lib.php");

  	if(isset($_POST)){

    		$list = array();

   	 	$search['select'] = "t.user_id, u.username, u.firstname, u.lastname, u.personal_information, u.profile_pic";
    		$search['table'] = "veeds_video_tags t, veeds_users u";
    		$search['where'] = "t.video_id = ".$_POST['video_id']." AND t.user_id = u.user_id";

    		if(jp_count($search) > 0){
			$result = jp_get($search);
      			while($row = mysqli_fetch_assoc($result)){
				$search2['select'] = "user_id";
				$search2['table'] = "veeds_users_follow";
				$search2['where'] = "user_id = '".$_POST['user_id']."' AND user_id_follow = '".$row['user_id']."' AND approved = 1";

        			$count2 = jp_count($search2);

				if($count2 > 0){
					$row['followed'] = 1;
				}else{
					$search2['where'] = "user_id = '".$_POST['user_id']."' AND user_id_follow = '".$row['user_id']."' AND approved = 0";

          				$count2 = jp_count($search2);

          				if($count2 > 0)
						$row['followed'] = 2;
					else
						$row['followed'] = 0;
				}
				$list['users'][] = $row;
			}
    		}
    		echo json_encode($list);
  	}
?>
