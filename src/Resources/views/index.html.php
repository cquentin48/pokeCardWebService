<?php
    require_once dirname(dirname(dirname(__DIR__))).'\vendor\autoload.php';
    $jsonEncoded = json_encode($jsonArray);
	echo $jsonEncoded;
?>