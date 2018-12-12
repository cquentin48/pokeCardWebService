<?php
@ini_set('display_errors', 'on'); 

    function displayHomeUrls(){
        $rawJSONPage = "https://pokeapi.co/api/v2/";
        $json = file_get_contents($rawJSONPage);
        $json = json_decode(json_encode($booking), true);
        echo $json;      
    }

    displayHomeUrls();        
?>