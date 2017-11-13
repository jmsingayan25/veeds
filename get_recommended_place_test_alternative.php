<?php

	include("jp_library/jp_lib.php");

	$hashtags_implode = "#RainyDays #RainRainRain";
	$_POST['user_id'] = "182";
	if(isset($_POST['user_id'])){

		$words['words'] = array();

		if(isset($hashtags_implode)){
	 		$hashtags = explode(" ",$hashtags_implode);
			for($i = 0; $i < count($hashtags); $i++){
				if(substr($hashtags[$i],0,1) == "#"){
					$words['words'][] = str_replace("#", "", $hashtags[$i]);
				}
			}
			
			$hashtags_word = implode("", $words['words']);
			$case = fromCamelCase($hashtags_word);
			echo $case;
			$explode_hashtags_word = explode(" ", $case);
			for ($i=0; $i < count($explode_hashtags_word); $i++) {
				echo $explode_hashtags_word[$i]."<br>";
				
			}



		}
		// echo json_encode($words);

	}

	function fromCamelCase($camelCaseString) {
        $re = '/(?<=[a-z])(?=[A-Z])/x';
        $a = preg_split($re, $camelCaseString);
        return join($a, " " );
	}
?>