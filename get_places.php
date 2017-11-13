<?php
	include("jp_library/jp_lib.php");
	
	if(isset($_POST['keyword'])){
		$search['select'] = "DISTINCT (location)";
		$search['table'] = "veeds_videos";
		$search['where'] = "location LIKE '%".$_POST['keyword']."%' AND location != ''"; 
		$search['filter'] = "ORDER BY location ASC"; 
			
		$result = jp_get($search);
		$list = array();
		$list['locations'] = array();
		while($row = mysqli_fetch_array($result)){
			$list['locations'][] = $row;
		}
		
		echo json_encode($list);
		
	}
	
?>