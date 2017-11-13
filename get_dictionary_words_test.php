<?php

	include("jp_library/jp_lib.php");
	include("functions.php");

	$_POST['keyword'] = "#goodmorning #RainyMorning #nagsasanay #lbhq";
	$_POST['source'] = "#RainyDays #RainRainRain";
	$searchstring = $_POST['keyword'];
	$sourcestring = $_POST['source'];

	if($_POST){
		// $list = array();
		$list_of_words = array();
		$new_word = array();

		$search['select'] = "word";
		$search['table'] = "veeds_dictionary";

		$result = jp_get($search);
		// echo jp_count($search);
		while ($row = mysqli_fetch_assoc($result)) {

			// echo implode(" ", $row);
			$row['word'] = str_replace("\n", "", $row['word']);
			if(strlen($row['word']) > 2){
				$list_of_words[] = $row['word'];
			}
			// $list['words'][] = $row;
			
		}

		foreach ($list_of_words as $word) {
			// var_dump(stristr($searchstring,$word) !== false); // this will help you to understand, what is happening here.
			if (stristr($searchstring,$word) !== false) {
				// echo $word." - ".$searchstring."<br>";
				// $new_word = str_replace($word, "", $searchstring);
				$new_word['words'][] = $word;
				// echo $word." - ".$new_word."<br>";
				// echo $word." - YES<br>";
			}
		}

		
		// $list = array("good", "morning", "#");
		array_push($new_word['words'], "#");
		$lower_list = array_map('strtolower', $new_word['words']);
		$very_new_word = str_replace($lower_list, "", $searchstring);

		echo $very_new_word;

		// Provides: You should eat pizza, beer, and ice cream every day
		$phrase  = "You should eat fruits, vegetables, and fiber every day.";
		$healthy = array("fruits", "vegetables", "fiber");
		$yummy   = array("pizza", "beer", "ice cream");

		$newphrase = str_replace($healthy, $yummy, $phrase);

	}
	
?>