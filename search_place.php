<?php

	include("jp_library/jp_lib.php");

	$_POST['place_name'] = "rain";
	if(isset($_POST['place_name'])){

		$list = array();
		
		$search['select'] = "DISTINCT place_name, location";
		$search['table'] = "veeds_establishment";
		$search['where'] = "place_name LIKE '%".$_POST['place_name']."%' OR location LIKE '%".$_POST['place_name']."%'";

		$result = jp_get($search);
		while ($row = mysqli_fetch_assoc($result)) {
			
			$list['places'][] = $row;
		}

		echo json_encode($list);
	}

?>