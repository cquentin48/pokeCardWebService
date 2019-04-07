<?php
namespace App\Controller;

use App\Entity\Post;
use App\Kernel;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;

require_once dirname(dirname(__DIR__)).'/vendor/autoload.php';
class UserDatabaseController extends AbstractController
{
    private $firebaseInstance;
    private $jsonRender;
    private $pokemonSpriteURL = "https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/";
    private $pokemonSpriteExtension = ".png";

    function __construct(){
        $this->firebaseInstance = new FirebaseController();
        $this->jsonRender = new JSONController();
    }

    /**
     * Load users friends list
     */
    public function loadFriendList($userId){
        $returnedArray = [];
        if($userId == "No user Id"){
            return $this->renderErrorMessage("Error","No user chosen");
        }else if($this->firebaseInstance->isChildEmpty($this->firebaseInstance->returnReference("users/$userId/friendsList"))){
            return $this->generateError("Information","No user friend");
        }else{
            return $this->renderJSONPage($this->loadUserFriendsId($userId));
        }
    }

    /**
     * Load pokemon firebase collections from a chosen user
     */
    public function loadPokemonCollection($userId){
        return $this->renderJSONPage($this->importPokemonCollection($this->firebaseInstance->returnValueOfReference("collections/$userId")));
    }

    /**
     * Import the pokemon collection from a user
     */
    private function importPokemonCollection($pokemonCollection){
        $pokemonList = [];
        $pokemonList['collection'] = [];
        foreach($pokemonCollection as $pokemonId => $singlePokemon){
            foreach($singlePokemon as $key=>$singleCreatedPokemon){
                $data = $singleCreatedPokemon;
                $data['sprite'] = $this->pokemonSpriteURL.$pokemonId.$this->pokemonSpriteExtension;
                array_push($pokemonList['collection'],$data);
            }
        }
        return $pokemonList;
    }

    /**
     * Load the userFriendsId
     */
    private function loadUserFriendsId($userId){
        $friendListArray = [];
        $friendList = $this->firebaseInstance->returnValueOfReference("users/$userId/friendsList");
        foreach($friendList as $singleFriend){
            array_push($friendListArray,$this->loadUserNameAndSprite($singleFriend));
        }
        return $friendListArray;
    }

    /**
     * Return the username and sprite
     */
    private function loadUserNameAndSprite($userId){
        $userData = [];
        $rawData = $this->firebaseInstance->returnValueOfReference("users/$userId");
        $userData['username'] = $rawData['username'];
        $userData['sprite'] = $rawData['avatarImage'];
        return $userData;
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
        return $this->renderJSONPage($this->jsonRenderer->generateErrorMessage($title,$message));
    }
}
?>