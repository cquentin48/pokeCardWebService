<?php
namespace App\Controller;

use App\Entity\Post;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class PokemonController extends AbstractController
{
    private static $basicPokemonURL = "https://pokeapi.co/api/v2/pokemon/";
    private static $offSet = 20;

    private function loadJSONData($adress, $pokemonId){
        $rawJSONPage = $adress.$pokemonId."/";
        $jsonRawData = file_get_contents($rawJSONPage);
        $json = json_decode($jsonRawData, true);
        $jsonOutput = array();
        $jsonOutput['id'] = $json['id'];
        $jsonOutput['species'] = $json['species'];
        $jsonOutput['sprites'] = $json['sprites'];
        $jsonOutput['types'] = $json['types'];
        return $jsonOutput;
    }

    function getAllPokemonBasicData($begin)
    {
        $response = file_get_contents("https://pokeapi-215911.firebaseapp.com/api/v2/pokemon?offset=$begin&limit=$offSet");
        $pokemons = array();
        $pokemonSprites = array();
        $returnedData = array();
        $json = json_decode($response, true);
        $results = $json['results'];
        $returnedData['count'] = $json['count'];
        if($begin+$offSet+1<$returnedData['count']){
            $newBegin = $begin + $offSet+1;
            $newOffSet = ($returnedData['count']<($newBegin+$offSet))?$returnedData['count']-$newBegin:$offSet;
            $returnedData['next'] = "https://pokeapi-215911.firebaseapp.com/api/v2/pokemon?offset=$begin&limit=$newOffSet";
        }
		$returnedData['pokemonList'] = array();
        foreach($results as $result){
            $returnedPokemonData = array();
            $response = file_get_contents($result['url']);
            $returnedJSONData = json_decode($response,true);
            $returnedPokemonData['name'] = $result['name'];
            $returnedPokemonData['sprite'] = $returnedJSONData['sprites']['front_default'];
            array_push($returnedData['pokemonList'], $returnedPokemonData);
        }
        return $returnedData;
    }
    
    public function renderPokemonBasicInformations($id)
    {
        if($id == 0){
			$returnedData = $this->getAllPokemonBasicData();
            return $this->render('index.html.php', array(
                'jsonArray' => $returnedData
            ));
        }else{
            $jsonData = $this->loadJSONData("https://pokeapi.co/api/v2/pokemon/",$id);
            return $this->render('index.html.php', array(
                'jsonArray' => $jsonData
            ));
        }

    }
}
?>