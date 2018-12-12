<?php
    header('Content-Type: application/json');
    $rawJSONPage = "https://pokeapi.co/api/v2/";
    $json = file_get_contents($rawJSONPage);
    $json = json_decode($json);
    echo json_encode($json);
?>