<?php
namespace App\Controller;

use App\Entity\Post;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class PokemonController extends AbstractController
{
    private $basicPokemonURL = "https://pokeapi.co/api/v2/pokemon/";
    private $offsetAttribute = "?offset=";
    private $limitAttribute = "&limit=";
    private $pokemonLocalizationURL = "https://pokeapi.co/api/v2/pokemon-species/";
    private $pokemonTypesURL = "https://pokeapi.co/api/v2/type/";
    private $limit = 20;

    private $jsonRenderer;

    function __construct(){
        $this->jsonRenderer = new JSONController();
    }

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

    /**
     * Load all pokemon ids from a chosen type
     */
    private function loadAllPokemonFromType($typeId){
        $pokemonIdArray = [];
        if($typeId>=1){
            $data = json_decode(file_get_contents($this->pokemonTypesURL.$typeId),true);
            foreach($data['pokemon'] as $key => $singlePokemon){
				$id = str_replace("/","",str_replace($this->basicPokemonURL,"",$singlePokemon['pokemon']['url']));
				if($id<900){
					array_push($pokemonIdArray,$id);
				}
            }  
        }
        return $pokemonIdArray;
    }

    /**
     * Load all pokemon id based on the types chosen
     */
    private function loadTypeList($firstTypeId, $secondTypeId){
        $pokemonIdArray = [];
        $pokemonIdArray['firstType'] = $this->loadAllPokemonFromType($firstTypeId);
        $pokemonIdArray['secondType'] = $this->loadAllPokemonFromType($secondTypeId);
        return $pokemonIdArray;
    }

    /**
     * Generate a pokemon id list for the two types chosen
     */
    private function generateCommonList($pokemonIdArray){
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

    /**
     * Generate a random pokemon for the crafting fragment
     */
    public function craftPokemon($firstTypeId, $secondTypeId){
        if($firstTypeId<0 || $secondTypeId<0){
            return $this->jsonRenderer->renderErrorMessage("Error","A type id can't be null");
        }else{
            $pokemonList = $this->generateCommonList($this->loadTypeList($firstTypeId, $secondTypeId));
            $returnData = $this->loadSpriteAndName($pokemonList[rand(0,sizeof($pokemonList)-1)]);
            return $this->renderJSONPage($returnData);
        }
    }

    /**
     * Load the sprite url and the pokemon name
     * @param pokemonId id of the pokemon
     */
    private function loadSpriteAndName($pokemonId){
        $pokemonArray = [];
        $spriteData = json_decode(file_get_contents($this->basicPokemonURL.$pokemonId),true);
        $pokemonArray['sprite'] = $spriteData['sprites']['front_default'];
        $nameData = json_decode(file_get_contents($this->pokemonLocalizationURL.$pokemonId),true);
        $pokemonArray['name'] = $nameData['names'][6]['name'];
        $pokemonArray['id'] = $pokemonId;
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
        return $this->renderJSONPage($arrayType);
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

    /**
     * Fetch all pokemon data
     */
    private function getAllPokemonBasicData($pageId)
    {
        $response = file_get_contents($this->basicPokemonURL.
                                      $this->limitAttribute.
                                      ($pageId*20).
                                      $this->limitAttribute.
                                      $this->limit);
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

    /**
     * Fetch the pokedex page and render the data to the application
     */
    public function fetchPokedexPage($pageId){
        if($pageId<0){
            return $this->renderJSONPage(renderErrorMessage("Pokemon list fetching error","Error while fetching pokemon list from pokeapi. Please try again with a number positive or null."));
        }else{
            return $this->renderJSONPage($this->getAllPokemonBasicData($pageId));
        }
    }
    
    /**
     * Fetch the data from the pokemon and render it for the android application
     */
    public function renderPokemonBasicInformations($id)
    {
        if($id<=0){
            return $this->renderJSONPage($this->renderErrorMessage("Pokemon fetching error","Error while fetching pokemon list from pokeapi. Please try again with a number positive."));
        }else{
            return $this->renderJSONPage($this->loadPokemonInfos("https://pokeapi.co/api/v2/pokemon/",$id));
        }
    }

    /**
     * Render a json page into the browser with a json format
     */
    private function renderJSONPage($jsonArray){
        return $this->render('index.html.php',array(
            'jsonArray' => $jsonArray
        ));
    }

    
    /**
     * Render a json page into the browser with a json format while containing the error message with title
     */
    private function renderErrorMessage($title, $message){
        $errorData = $this->jsonRenderer->generateErrorMessage($title,$message);
        return $this->renderJSONPage($errorData);
    }
}
?>