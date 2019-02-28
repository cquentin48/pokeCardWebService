<?php
namespace App\Controller;

use App\Entity\Post;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class PokemonController extends AbstractController
{
    private $basicPokemonURL = "https://pokeapi.co/api/v2/pokemon/?offset=";
    private $limit = 20;

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

    function getAllPokemonBasicData($pageId)
    {
        $response = file_get_contents($this->basicPokemonURL.($pageId*20)."&limit=$this->limit");
        $pokemons = array();
        $pokemonSprites = array();
        $returnedData = array();
        $json = json_decode($response, true);
        $results = $json['results'];
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

    function fetchPokedexPage($pageId){
        if($pageId<0){
            $errorData = [];
            $errorData['title'] = "Pokemon list fetching error";
            $errorData['message'] = "Error while fetching pokemon list from pokeapi. Please try again with a number positive or null.";
            return $this->render('index.html.php', array(
                'jsonArray' => $errorData
            ));
        }else{
            return $this->render('index.html.php', array(
                'jsonArray' => $this->getAllPokemonBasicData($pageId)
            ));
        }
    }
    
    public function renderPokemonBasicInformations($id)
    {
        $jsonData = $this->loadJSONData("https://pokeapi.co/api/v2/pokemon/",$id);
        return $this->render('index.html.php', array(
            'jsonArray' => $jsonData
        ));
    }
}
?>