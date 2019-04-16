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
    public function initUser($username, $userId, $avatarImage, $email){
        $data = [];
        $data['username'] = $username;
        $data['email'] = $email;
        $data['registrationDate'] = "";
        $data['lastUserConnection'] = "";
        $data['avatarImage'] = $avatarImage;
        $data['distance'] = 0;
        $data['loggedIn'] = true;

        $this->firebaseInstance->getDatabase()->getReference("users/$userId")->set($data);
        return $this->renderJSONPage($data);
    }

    public function getUserData($userId){
        $dataRef = $this->firebaseInstance->getDatabase()->getReference("users/$userId")->getValue();
        $returnedData = [];
        $returnedData['lastUserConnection'] = $dataRef['lastUserConnection'];
        $returnedData['registrationDate'] = $dataRef['registrationDate'];
        if($dataRef['pokemonCollection'] == 0){
            $returnedData['pokemonCount'] = 0;
        }else{
            $returnedData['pokemonCount'] = sizeof($dataRef['pokemonCollection']);
        }
        return $this->renderJSONPage($returnedData);
    }

    /**
     * Load pokemon firebase collections from a chosen user
     */
    public function loadPokemonCollection($userId){
        return $this->renderJSONPage($this->importPokemonCollection($this->firebaseInstance->returnValueOfReference("users/$userId/pokemonCollection/")));
    }

    /**
     * Import the pokemon collection from a user
     */
    private function importPokemonCollection($pokemonCollection){
        $pokemonList = [];
        $pokemonList['collection'] = [];
            foreach($pokemonCollection as $key=>$singleCreatedPokemon){
                $data = $singleCreatedPokemon;
                $data['sprite'] = $this->pokemonSpriteURL.$key.$this->pokemonSpriteExtension;
                array_push($pokemonList['collection'],$data);
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
    private function renderJSONPage($jsonArray, $statusCode=200){
        $response = new JsonResponse();
        $response->setData($jsonArray);
        $response->setStatusCode($statusCode);
        print_r($response);
        //return $response;
    }

    
    /**
     * Render a json page into the browser with a json format while containing the error message with title
     */
    private function renderErrorMessage($title, $message){
        $errorData = $this->jsonRenderer->generateErrorMessage($title,$message);
        return $errorData;
    }
}
?>