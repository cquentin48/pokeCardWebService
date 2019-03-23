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
    
    public function renderPokemonBasicInformations($id)
    {
        if($id == 0){
            
        }
        $jsonData = $this->loadJSONData("https://pokeapi.co/api/v2/pokemon/",$id);
        return $this->render('index.html.php', array(
            'jsonArray' => $jsonData
        ));
    }
}
?>