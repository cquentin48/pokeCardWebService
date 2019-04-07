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
    public $firebaseInstance;
    private $pokemonSpriteURL = "https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/";
    private $pokemonSpriteExtension = ".png";

    function __construct(){
        $this->firebaseInstance = new FirebaseController();
    }

    public function loadFriendList($userId){
        $returnedArray = [];
        if($userId == "No user Id"){
            $returnedArray['title'] = "Error";
            $returnedArray['message'] = "No user chosen";
            return $this->render('index.html.php',array(
                'jsonArray' => $returnedArray
            ));
        }else if($this->firebaseInstance->isChildEmpty($this->firebaseInstance->returnReference("users/$userId/friendsList"))){
            $returnedArray['title'] = "Information";
            $returnedArray['message'] = "No user friend";
            return $this->render('index.html.php',array(
                'jsonArray' => $returnedArray
            ));
        }else{
            $returnedArray['returnedData'] = $this->loadUserFriendsId($userId);
            return $this->render('index.html.php', array(
                'jsonArray' => $returnedArray
            ));
        }
    }

    public function loadPokemonCollection($userId){
        $pokemonCollection = $this->firebaseInstance->returnValueOfReference("collections/$userId")
        $pokemonList = [];
        foreach($pokemonCollection as $pokemonId => $singlePokemon){
            $pokemonList[$pokemonId] = [];
            foreach($singlePokemon as $singleCreatedPokemon){
                $pokemonList[$pokemonId] = $singleCreatedPokemon;
                $pokemonList[$pokemonId]['sprite'] = $this->pokemonSpriteURL.$pokemonId.$this->pokemonSpriteExtension;
            }
        }
        return $this->render('index.html.php', array(
            'jsonArray' => $pokemonList
        ));
    }

    private function loadUserFriendsId($userId){
        $friendListArray = [];
        $friendList = $this->firebaseInstance->returnValueOfReference("users/$userId/friendsList");
        foreach($friendList as $singleFriend){
            array_push($friendListArray,$this->loadUserNameAndSprite($singleFriend));
        }
        return $friendListArray;
    }

    private function loadUserNameAndSprite($userId){
        $userData = [];
        $rawData = $this->firebaseInstance->returnValueOfReference("users/$userId");
        $userData['username'] = $rawData['username'];
        $userData['sprite'] = $rawData['avatarImage'];
        return $userData;
    }
}
?>