<?php
    header('Content-Type: application/json');
    $rawJSONPage = "https://pokeapi.co/api/v2/";
	$jsonRawData = file_get_contents($rawJSONPage);
	$json = json_decode($jsonRawData, true);
	$jsonEncoded = json_encode($json);
	echo $jsonEncoded;
?>