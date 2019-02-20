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

    private function getAllPokemonBasicData($begin)
    {
        $response = file_get_contents('https://pokeapi-215911.firebaseapp.com/api/v2/pokemon?offset=0&limit=100000');
        $pokemons = array();
        $pokemonSprites = array();
        $returnedData = array();
        $json = json_decode(json_encode($response), true);
        $data = json_decode($json);
        $results = $data->results;
        foreach($results as $result){
            $pokemonData = array();
            $response = file_get_contents($pokemon["url"]);
            $json = json_decode(json_encode($response), true);
            $data = json_decode($json);

             $pokemon = json_decode(json_encode($result), true);
             array_push($pokemons,$pokemon["name"]);

             $response = file_get_contents($pokemon["url"]);
            $json = json_decode(json_encode($response), true);
            $data = json_decode($json);
            $sprite = json_decode(json_encode($data->sprites), true);
            array_push($pokemonSprites,$sprite["front_default"]);

            $response = file_get_contents($pokemon["url"]);
            $json = json_decode(json_encode($response), true);
            $data = json_decode($json);
            $sprite = json_decode(json_encode($data->sprites), true);
            //var_dump($sprite["front_default"]);
            $pokemonData['name'] = $pokemon['name'];
            $pokemonData['sprite'] = $pokemon['front_default'];
            array_push($returnedData, $pokemonData);
        }
        return $returnedData;
    }

    private function generateJSONArray($array){
        return $this->render('index.html.php', array(
            'jsonArray' => $array
        ));
    }
    
    public function renderPokemonBasicInformations($id)
    {
        $jsonData = $this->loadJSONData("https://pokeapi.co/api/v2/pokemon/",$id);
        generateJSONArray($jsonData);
    }

    public function renderPokemonBasicList($begin){
        $jsonData = $this->getAllPokemonBasicData($begin);
        generateJSONArray($jsonData);
    }
}
?>