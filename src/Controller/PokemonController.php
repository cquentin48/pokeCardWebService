<?php
namespace App\Controller;

use App\Entity\Post;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class PokemonController extends AbstractController
{
    private static $basicPokemonURL = "https://pokeapi.co/api/v2/pokemon/";
    private function loadJSONData($adress, $pokemonId){
        $rawJSONPage = $adress;
        $rawJSONPage = "https://pokeapi.co/api/v2/pokemon/1/";
        $jsonRawData = file_get_contents($rawJSONPage);
        $json = json_decode($jsonRawData, true);
        $jsonOutput = array();
        $jsonOutput['id'] = $json['id'];
        $jsonOutput['species'] = $json['species'];
        $jsonOutput['sprites'] = $json['sprites'];
        $jsonOutput['types'] = $json['types'];
        $jsonEncoded = json_encode($jsonOutput);
        return $jsonEncoded;
    }
    /**
     * @Route("/", name="homepage")
     */
    public function renderPokemonBasicInformations($pokemonId)
    {
        return new JsonResponse(array('name' => loadJSONData($basicPokemonURL.$pokemonId.'/')));
    }
}
?>