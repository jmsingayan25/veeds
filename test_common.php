<?php


	$arr1 = array('Rain');
	$arr2 = array('RainRainRainRain');

	$common = array_intersect($arr1, $arr2);
	var_dump($common);

?>