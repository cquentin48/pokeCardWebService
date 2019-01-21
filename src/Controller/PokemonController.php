<?php
namespace App\Controller;

use App\Entity\Post;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class PokemonController extends AbstractController
{
    private static $basicPokemonURL = "https://pokeapi.co/api/v2/pokemon/";

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

    function getAllPokemonBasicData()
    {
        $response = file_get_contents('https://pokeapi-215911.firebaseapp.com/api/v2/pokemon?offset=0&limit=100');
        $pokemons = array();
        $pokemonSprites = array();
        $returnedData = array();
        $json = json_decode($response, true);
        $results = $json['results'];
        foreach($results as $result){
            $returnedPokemonData = array();
            $response = file_get_contents($result['url']);
            $returnedJSONData = json_decode($response,true);
            $returnedPokemonData['name'] = $result['name'];
            $returnedPokemonData['sprite'] = $returnedJSONData['sprites']['back_default'];
            array_push($returnedData, $returnedPokemonData);
        }
        return $returnedData;
    }
    
    public function renderPokemonBasicInformations($id)
    {
        if($id == 0){
            return $this->render('index.html.php', array(
                'jsonArray' => getAllPokemonBasicData()
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