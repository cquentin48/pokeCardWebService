<?php
namespace App\Controller;

use App\Entity\Post;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class PokemonController extends AbstractController
{
    private $basicPokemonURL = "https://pokeapi.co/api/v2/pokemon/";
    private $normalLimit = 20;
    private $lastLimit = 4;
    private $lastOffset = 960;

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
        $response = file_get_contents($pageId);
        $pokemons = array();
        $pokemonSprites = array();
        $returnedData = array();
        $json = json_decode($response, true);
        $results = $json['results'];
        $returnedData['count'] = $json['count'];
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
        $jsonData = $this->loadJSONData("https://pokeapi.co/api/v2/pokemon/",$id);
        return $this->render('index.html.php', array(
            'jsonArray' => $jsonData
        ));
    }

    public function renderPokemonList($pageId){
        $offset = $pageId*($this->normalLimit);
        $limit = ($offset==$this->lastOffset)?$this->lastOffset:$this->normalLimit;
        $jsonData = $this->getAllPokemonBasicData("$this->basicPokemonURL?offset=$offset&limit=$limit");
        return $this->render('index.html.php', array(
            'jsonArray' => $jsonData
        ));
    }
}
?>