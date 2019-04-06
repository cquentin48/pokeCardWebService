<?php
namespace App\Controller;

use App\Entity\Post;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class PokemonController extends AbstractController
{
    private $basicPokemonURL = "https://pokeapi.co/api/v2/pokemon/?offset=";
    private $basicPokemonURLWithoutOffset = "https://pokeapi.co/api/v2/pokemon/";
    private $pokemonLocalizationURL = "https://pokeapi.co/api/v2/pokemon-species/";
    private $pokemonTypesURL = "https://pokeapi.co/api/v2/type/";
    private $limit = 20;

    /**
     * Load main infos for the pokemon view fragment
     */
    private function loadJSONData($adress, $pokemonId){
        $rawJSONPage = $adress.$pokemonId."/";
        $jsonRawData = file_get_contents($rawJSONPage);
        $json = json_decode($jsonRawData, true);
        $jsonOutput = array();
        $jsonOutput['id'] = $json['id'];
        $jsonOutput['height'] = $json['height'];
        $jsonOutput['sprites'] = $json['sprites']['front_default'];
        $jsonOutput['types'] = $this->loadPokemonType($json['types']);
        $jsonOutput['weight'] = $json['weight'];
        return $jsonOutput;
    }

    private function loadAllPokemonFromType($typeId){
        $pokemonIdArray = [];
        if($typeId>=1){
            $data = json_decode(file_get_contents($this->pokemonTypesURL.$typeId),true);
            foreach($data['pokemon'] as $key => $singlePokemon){
				$id = str_replace("/","",str_replace($this->basicPokemonURLWithoutOffset,"",$singlePokemon['pokemon']['url']));
				if($id<900){
					array_push($pokemonIdArray,$id);
				}
            }  
        }
        return $pokemonIdArray;
    }

    private function generatePokemonList($firstTypeId, $secondTypeId){
        $pokemonIdArray = [];
        $pokemonIdArray['firstType'] = $this->loadAllPokemonFromType($firstTypeId);
        $pokemonIdArray['secondType'] = $this->loadAllPokemonFromType($secondTypeId);
        $pokemonList = [];
        if(sizeof($pokemonIdArray['secondType']) >= 1){
            foreach($pokemonIdArray['firstType'] as $singlePokemonId){
                if(in_array($singlePokemonId, $pokemonIdArray['secondType'])){
                    array_push($pokemonList,$singlePokemonId);
                }
            }
        }else{
            $pokemonList = $pokemonIdArray['firstType'];
        }
		if(sizeof($pokemonList) == 0){
			array_push($pokemonList,10);
		}
        return $pokemonList;
    }

    public function craftPokemon($firstTypeId, $secondTypeId){
        $pokemonList = $this->generatePokemonList($firstTypeId, $secondTypeId);
        $returnData = $this->loadSpriteAndName($pokemonList[rand(0,sizeof($pokemonList)-1)]);
        return $this->render('index.html.php', array(
            'jsonArray' => $returnData
        ));
    }

    private function loadSpriteAndName($pokemonId){
        $pokemonArray = [];
        $spriteData = json_decode(file_get_contents($this->basicPokemonURLWithoutOffset.$pokemonId),true);
        $pokemonArray['sprite'] = $spriteData['sprites']['front_default'];
        $nameData = json_decode(file_get_contents($this->pokemonLocalizationURL.$pokemonId),true);
        $pokemonArray['name'] = $nameData['names'][6]['name'];
        return $pokemonArray;
    }

    /**
     * Load all types in a table and display it into a table array
     */
    public function loadTypes(){
        $rawJSONPage = json_decode(file_get_contents($this->pokemonTypesURL),true);
        $arrayType = [];
        $arrayType['typeList'] = [];
        array_push($arrayType['typeList'],"Choix du type");
        foreach($rawJSONPage['results'] as $singleType){
            if($singleType["name"] != "unknown" && $singleType["name"] != "shadow"){
                array_push($arrayType["typeList"], $this->loadLocalizedType($singleType["url"]));
            }
        }
        return $this->render('index.html.php', array(
            'jsonArray' => $arrayType
        ));
    }

    /**
     * Load Pokemon type
     */
    private function loadPokemonType($typeURL){
        $pokemonTypeArray = [];
        foreach($typeURL as $singleType){
            $returnData = $this->loadLocalizedType($singleType['type']['url']);
                array_push($pokemonTypeArray,$returnData);
            }
        return $pokemonTypeArray;
    }

    /**
     * Return the french localized word for the type
     */
    private function loadLocalizedType($rawData){
        $typeContent = file_get_contents($rawData);
        $jsonDecodedData = json_decode($typeContent,true);
        return $jsonDecodedData['names'][2]['name'];
    }

    /**
     * Load pokemon infos for the pokemon view fragment
     */
    private function loadPokemonInfos($adress, $pokemonId){
        $rawData = $this->loadJSONData($adress, $pokemonId);
        $localizationData = $this->loadPokemonLocalization($rawData);
        $rawData['name'] = $localizationData['name'];
        $rawData['pokedexEntry'] = str_replace("\n", " ", $localizationData['pokedexEntry']);
        return $rawData;
    }

    /**
     * Load pokemon name in the chosen language
     */
    private function loadPokemonLocalization($rawData){
        $localizationRawData = json_decode(file_get_contents($this->pokemonLocalizationURL.$rawData['id']),true);
        $localizationData['name'] = $localizationRawData['names'][6]['name'];
        $localizationData['pokedexEntry'] = $this->loadPokemonLocalizedPokedexEntry($localizationRawData['flavor_text_entries']);
        return $localizationData;
    }

    /**
     * Load pokemon pokedex entry in french language
     */
    private function loadPokemonLocalizedPokedexEntry($nameArray){
        foreach($nameArray as $singleName){
            if($singleName['language']['name'] == "fr"){
                return $singleName['flavor_text'];
            }
        }
    }

    function getAllPokemonBasicData($pageId)
    {
        $response = file_get_contents($this->basicPokemonURL.($pageId*20)."&limit=$this->limit");
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
            $returnedPokemonData['sprite'] = $returnedJSONData['sprites']['front_default'];
            $returnedPokemonData['id'] = $returnedJSONData['id'];
            $response = file_get_contents($returnedJSONData['species']['url']);
            $secondReturnedJSONData = json_decode($response,true);
            $returnedPokemonData['name'] = $secondReturnedJSONData['names'][6]['name'];
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
        $jsonData = $this->loadPokemonInfos("https://pokeapi.co/api/v2/pokemon/",$id);
        return $this->render('index.html.php', array(
            'jsonArray' => $jsonData
        ));
    }
}
?>