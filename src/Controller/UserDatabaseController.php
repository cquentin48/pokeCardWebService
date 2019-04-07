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
            return $this->jsonRender->renderErrorMessage("Error","No user chosen");
        }else if($this->firebaseInstance->isChildEmpty($this->firebaseInstance->returnReference("users/$userId/friendsList"))){
            return $this->jsonRender->renderErrorMessage("Information","No user friend");
        }else{
            return $this->jsonRender->renderJSONPage($this->loadUserFriendsId($userId));
        }
    }

    /**
     * Load pokemon firebase collections from a chosen user
     */
    public function loadPokemonCollection($userId){
        return $this->jsonRender->renderJSONPage($this->importPokemonCollection($this->firebaseInstance->returnValueOfReference("collections/$userId")));
    }

    /**
     * Import the pokemon collection from a user
     */
    private function importPokemonCollection($pokemonCollection){
        $pokemonList = [];
        foreach($pokemonCollection as $pokemonId => $singlePokemon){
            $pokemonList[$pokemonId] = [];
            foreach($singlePokemon as $singleCreatedPokemon){
                $pokemonList[$pokemonId] = $singleCreatedPokemon;
                $pokemonList[$pokemonId]['sprite'] = $this->pokemonSpriteURL.$pokemonId.$this->pokemonSpriteExtension;
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
}
?>