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
class ExchangesController extends AbstractController
{
    private $firebaseInstance;
    private $jsonRenderer;
    private $pokemonController;
    
    function __construct(){
        $this->firebaseInstance = new FirebaseController();
        $this->jsonRenderer = new JSONController();
        $this->pokemonController = new PokemonController();
    }

    public function listTradeExchanges(){
        $data = $this->firebaseInstance->getDatabase()->getReference("exchanges")->getValue();
        if($data == null){
            $data = [];
        }else{
            $data = $this->firebaseInstance->getDatabase()->getReference("exchanges")->getValue();
        }
        return $this->renderJsonPage($data);
    }

    public function confirmExchange($pokemonIdWanted, $originalPokemonId, $userId, $friendUserId){
        if($pokemonIdWanted<=0){
            //return $this->renderErrorMessage("Error","Please choose a pokemon with an id strictly positive.");
        }else if($originalPokemonId <= 0){
            //return $this->renderErrorMessage("Error","Please choose a pokemon with an id strictly positive.");
        }else if(!$this->firebaseInstance->userExist($userId)){
            //return $this->renderErrorMessage("Error","User not found.");
        }else if(!$this->firebaseInstance->userExist($friendUserId)){
            //return $this->renderErrorMessage("Error","User not found.");
        }else if(!$this->pokemonController->hasPokemonId($userId, $originalPokemonId)){
            //return $this->renderErrorMessage("Error","No pokemon found for pokemon id $userId");
        }else if(!$this->pokemonController->hasPokemonId($friendUserId, $pokemonIdWanted)){
            //return $this->renderErrorMessage("Error","No pokemon found for pokemon id $userId");
        }else{
            $this->confirmExchangeFirebase($originalPokemonId,
                                           $pokemonIdWanted,
                                           $userId,
                                           $friendUserId);
            $this->removeExchange($originalPokemonId, $userId);
            return $this->renderErrorMessage("Success","Pokemon wished sent to $friendUserId");
        }
    }

    /**
     * Remove exchange from firebase
     */
    public function removeExchange($pokemonId,$userId){
        return $this->firebaseInstance->getDatabase()->getReference("exchanges/$pokemonId/$userId")->remove();
    }

    /**
     * Confirm exchange in firebase
     */
    public function confirmExchangeFirebase($originalPokemonId,
                                            $pokemonIdWanted,
                                            $userId,
                                            $friendUserId){
        $pokemonWanted = $this->loadRandomPokemonById($friendUserId,$pokemonIdWanted);
        $originalPokemon = $this->loadRandomPokemonById($userId,$originalPokemonId);
        $this->movePokemonToFriend($userId, $friendUserId, $originalPokemonId, $originalPokemon);
        $this->movePokemonToFriend($friendUserId, $userId, $pokemonIdWanted, $pokemonWanted);
    }

    /**
     * Move pokemon to another place
     */
    private function movePokemonToFriend($userId, $friendId, $pokemonId, $data){
        $this->firebaseInstance->returnReference("users/$friendId/pokemonCollection/$pokemonId")->set($data);
        $this->firebaseInstance->returnReference("users/$userId/pokemonCollection/$pokemonId")->remove();
    }

    /**
     * Send pokemon to Chen
     */
    public function sendPokemonToProfChen($userId, $pokemonId, $pokemonCraftedId){
        if($pokemonId<=0){
            return $this->renderErrorMessage("Error","Please choose a pokemon with an id strictly positive.");
        }else if(!$this->firebaseInstance->userExist($userId)){
            return $this->renderErrorMessage("Error","User not found.");
        }else if(!$this->pokemonController->hasPokemonId($userId, $pokemonId)){
            return $this->renderErrorMessage("Error","No pokemon found for pokemon id $userId");
        }else{
            $this->movePokemonToFriend($userId,
                                       "Chen",
                                       $pokemonId,
                                       $pokemonCraftedId,
                                       $this->loadRandomPokemonById($userId,$pokemonId));
                                           
            return $this->renderErrorMessage("Success","Pokemon sent to prof Chen.");
        }
    }

    public function removeTradeProposition($userId, $originalPokemonId){
        $this->firebaseInstance->getDatabase()->getReference("users/$userId/exchanges/$pokemonId")->remove();
    }

    public function loadRandomPokemonById($userId, $pokemonId){
        return $this->firebaseInstance->getDatabase()->getReference("users/$userId/pokemonCollection/$pokemonId")->getValue();
    }

    public function addPokemonToExchangeMarket($pokemonIdWanted, $originalPokemonId, $userId){
        if($pokemonIdWanted<=0){
            return $this->renderErrorMessage("Error","Please choose a pokemon with an id strictly positive.");
        }else if($originalPokemonId <= 0){
            return $this->renderErrorMessage("Error","Please choose a pokemon with an id strictly positive.");
        }else if(!$this->firebaseInstance->userExist($userId)){
            return $this->renderErrorMessage("Error","User not found.");
        }else{
            $this->insertIntoMarketExchange($originalPokemonId, $pokemonIdWanted, $userId);
            return $this->renderErrorMessage("Success","Pokemon wished sent to market");
        }
    }

    public function insertIntoMarketExchange($pokemonId, $pokemonIdWanted, $userId){
        $friendUserIdRef = $this->firebaseInstance->returnReference("exchanges/$pokemonId/");
        if($friendUserIdRef->getValue() == null){
            $exchangeData = [];
        }else{
            $exchangeData = $friendUserIdRef->getValue();
        }
        $exchangeData[$userId]['pokemonWanted'] = $pokemonIdWanted;
        $friendUserIdRef->set($exchangeData);
    }

    /**
     * Render a json page into the browser with a json format
     */
    private function renderJSONPage($jsonArray, $statusCode=200){
        $response = new JsonResponse();
        $response->setData($jsonArray);
        $response->setStatusCode($statusCode);
        return $response;
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