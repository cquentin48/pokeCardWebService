<?php
    $rawJSONPage = "https://pokeapi.co/api/v2/";
    $json = file_get_contents($rawJSONPage);
    $json = json_decode(json_encode($json), true);
    json_encode($json);
?>