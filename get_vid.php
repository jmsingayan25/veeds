<?php
	include("jp_library/jp_lib.php");
	
	if(isset($_POST['video_id'])){
		$search['select'] = "video_name, description, video_file, view_count, video_thumb";
		$search['table'] = $_POST['table'];
		$search['where'] = "video_id = '".$_POST['video_id']."'"; 
		$commentBlock = array();
		if (jp_count($search) > 0) {
				$result = jp_get($search);
			
			$search2['table'] = "veeds_videos_likes";
			$search2['where'] = "video_id = '".$_POST['video_id']."' AND user_id = '".$_POST['user_id']."'"; 
				
			$count = jp_count($search2);
			
			$row = mysqli_fetch_array($result);
			$row['video_name'] = str_replace(';quote;',"'", $row['video_name']);
			$row['description'] = str_replace(';quote;',"'", $row['description']);
			if($count > 0){
				$row['like_status'] = 1;
			}else{
				$row['like_status'] = 0;
			}
			
			
			// $view_count = array();
			// $view_count['view_count'] = $row['view_count'] + 1;
			// $update['table'] = "veeds_videos";
			// $update['data'] = $view_count;
			// $update['where'] = "video_id = '".$_POST['video_id']."'"; 
			// jp_update($update);
			
			// $row['view_count'] = $view_count['view_count'];
			$row['like_count'] = $count;
			
			$count = jp_count($search2);
			$commentBlock = $row;
			//
			
			if(isset($_POST['count'])){
				
				$blocks = array();
				$block['table'] = "veeds_users_block";
				$block['where'] = "user_id_block = ".$_POST['user_id'];
				$result3 = jp_get($block);
				while($row3 = mysqli_fetch_array($result3)){
					$blocks[] = $row3['user_id'];
				}
			
				$block['where'] = "user_id = ".$_POST['user_id'];
				$result3 = jp_get($block);
				while($row3 = mysqli_fetch_array($result3)){
					if(!in_array($row3['user_id_block'], $blocks))
						$blocks[] = $row3['user_id_block'];
				}
				
				if(count($blocks) > 0)
					$extend = " AND b.user_id NOT IN (".implode(",", $blocks).")";
				else
					$extend = "";
				
				$search['select'] = "comment_id, comment, comment_date, firstname, lastname, profile_pic, b.user_id, b.username";
				$search['table'] = "veeds_comments a, veeds_users b";
				$search['where'] = "a.user_id = b.user_id AND a.video_id = '".$_POST['video_id']."' AND b.disabled = 0".$extend; 
				$start = $_POST['count'] * 5;
				$search['filters'] = "ORDER BY a.comment_date DESC LIMIT ".$start.", 5"; 
				
				$result2 = jp_get($search);
				$list = array();
				
				$search2['select'] = "video_name";
				$search2['table'] = "veeds_videos";
				$search2['where'] = "video_id = '".$_POST['video_id']."'"; 
					
				$result22 = jp_get($search2);
				
				$row22 = mysqli_fetch_array($result22);
				$list['video_title'] = $row22['video_name'];
				
				$search2['select'] = "comment_id";
				$search2['table'] = "veeds_comments";
				$search2['where'] = "video_id = '".$_POST['video_id']."'"; 
						
				$count = jp_count($search2);
						
				if($count > 0){
					$row['comment_count'] = $count;
				}else{
					$row['comment_count'] = 0;
				}
				$list['comments'] = array();
				// $list['comments'] = array();
				while($row2 = mysqli_fetch_array($result2)){
						$row2['name'] = $row2['firstname']." ".$row2['lastname'];
					$row2['username'] = $row2['username'];
					$list['comments'][] = $row2;
				}
				$commentBlock['comments'] = $list['comments'];
				$row['comments'] = $list['comments'];

				$u_blocks = array();
			
				$block['table'] = "veeds_users_block";
				$block['where'] = "user_id_block = ".$_POST['user_id'];
				$result3 = jp_get($block);
				while($row3 = mysqli_fetch_array($result3)){
					$u_blocks[] = $row3['user_id'];
				}
				
				if(count($u_blocks) > 0){
					$followed_extend = " AND a.user_id_follow NOT IN (".implode(",", $u_blocks).")";
				}else{
					$followed_extend = "";
				}
				
				$follow['select'] = "b.username, b.user_id, b.profile_pic";
				$follow['table'] = "veeds_users_follow a, veeds_users b";
				$follow['where'] = "a.user_id = ".$_POST['user_id']." AND a.user_id_follow = b.user_id AND a.approved = 1 AND b.disabled = 0".$followed_extend;
				$result = jp_get($follow);
				$friends['users'] = array();
				while($row = mysqli_fetch_array($result)){
					$search10['select'] = "user_id_follow, user_id";
					$search10['table'] = "veeds_users_follow";
					$search10['where'] = "(user_id = '".$_POST['user_id']."' AND user_id_follow = '".$row['user_id']."') OR (user_id = '".$row['user_id']."' AND user_id_follow = '".$_POST['user_id']."')";
					if (jp_count($search10) == 2) {
						$friends['users'][] = $row;
					}
					
				}
				$commentBlock['friends'] = $friends['users'];

				$search2['select'] = "video_id, like_type, user_id_liker";
				$search2['table'] = "veeds_videos_likes";
				$search2['where'] = "video_id = ".$_POST['video_id'].""; 
				$row['user_id_liker'] = array();
				$liker = jp_get($search2);
				if($count > 0){
					
					while ($row10 = mysqli_fetch_assoc($liker)) {
						$commentBlock['userId'][] = $row10;
						if ($_POST['user_id'] != !is_null($row10['user_id_liker'])) {
							$commentBlock['userId'] = $row10;
							$search20['select'] = "firstname, lastname, username, personal_information, user_id, profile_pic, private";
							$search20['table'] = "veeds_users";
							$search20['where'] = "disabled = 0 AND user_id = ".$row10['user_id_liker']; 
							$result11 = jp_get($search20);
							$row10 = mysqli_fetch_assoc($result11);

							$search3['select'] = "user_id";
							$search3['table'] = "veeds_users_follow";
							$search3['where'] = "user_id = '".$_POST['user_id']."' AND user_id_follow = '".$row10['user_id']."' AND approved = 1"; 
							$count2 = jp_count($search3);
							if($count2 > 0){
								$row10['followed'] = 1;
							}else{
								$search3['where'] = "user_id = '".$_POST['user_id']."' AND user_id_follow = '".$row10['user_id']."' AND approved = 0"; 
								$count2 = jp_count($search3);
								if($count2 > 0)
									$row10['followed'] = 2;
								else
									$row10['followed'] = 0;
								}

							$search3['select'] = "user_id";
							$search3['table'] = "veeds_users_block";
							$search3['where'] = "user_id = '".$_POST['user_id']."' AND user_id_block = '".$row10['user_id']."'"; 
							$count2 = jp_count($search3);
							if($count2 > 0){
								$row10['blocked'] = true;
							}else{
								$row10['blocked'] = false;
							}
							$row['user_id_liker'][] = $row10;
						}
					}
				}
				$commentBlock['likers'] = $row['user_id_liker'];
				// $row['comments'] = $list['comments'];
				// $row['friends'] = $friends['users'];
				// $sda = array('test' => true);
				// $row['friends'] = $friends;
			}
			echo json_encode($commentBlock);
		} else {
			$value = array('reply' => 'This comment thread is longer available.');
			echo json_encode($value);
		}
		
		
		
		
	}
	
?>