<?php
  include("jp_library/jp_lib.php");

  	if(isset($_POST)){
    		//get username of the user
    		$user['select'] = "username";
    		$user['table'] = "veeds_users";
    		$user['where'] = "user_id = ".$_POST['user_id'];
    		$user_res = jp_get($user);
    		$user_row = mysqli_fetch_assoc($user_res);
    		$user_row1 = "@".$user_row['username'];
    		//get video name
    		$video['select'] = "video_name";
    		$video['table'] = "veeds_videos";
    		$video['where'] = "video_id = ".$_POST['video_id'];
    		$video_res = jp_get($video);
		$video_row = mysqli_fetch_assoc($video_res);

    		$tags = implode(" ", $video_row); //implode to string
    		$tags_explode = explode(" ",$tags); //explode to array
    		for($i = 0; $i < count($tags_explode); $i++){
        		if(substr($tags_explode[$i],0,1) == "@" && $tags_explode[$i] == $user_row1){
          			$tags_explode = str_replace($tags_explode[$i],'', $tags_explode);
          			$tags = implode(" ",$tags_explode);

                   		$data['data'] = array("video_name" => $tags);
      				$data['table'] = 'veeds_videos';
      				$data['where'] = "video_id = ".$_POST['video_id'];

          			$data1['table'] = "veeds_video_tags";
          			$data1['where'] = "video_id = ".$_POST['video_id']." AND user_id = ".$_POST['user_id'];

          			if(jp_count($data1) > 0){
          				jp_update($data);
    					jp_delete($data1);
          				$result = array('reply' => '1', 'data' => $data);
        			}else{
          				$result = array('reply' => '2');
        			}
      			}else{
       				 $result = array('reply' => '0');
      			}
    		}
    	echo json_encode($result);
  	} 
?>
