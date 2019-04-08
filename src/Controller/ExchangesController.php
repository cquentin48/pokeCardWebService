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

    public function confirmExchange($pokemonIdWanted, $originalPokemonId, $userId, $friendUserId, $originalCraftedPokemonId, $pokemonCraftedIdWanted){
        if($pokemonIdWanted<=0){
            return $this->renderErrorMessage("Error","Please choose a pokemon with an id strictly positive.");
        }else if($originalPokemonId <= 0){
            return $this->renderErrorMessage("Error","Please choose a pokemon with an id strictly positive.");
        }else if(!$this->firebaseInstance->userExist($userId)){
            return $this->renderErrorMessage("Error","User not found.");
        }else if(!$this->firebaseInstance->userExist($friendUserId)){
            return $this->renderErrorMessage("Error","User not found.");
        }else if(!$this->pokemonController->hasPokemonId($userId, $originalPokemonId)){
            return $this->renderErrorMessage("Error","No pokemon found for pokemon id $userId");
        }else if(!$this->pokemonController->hasPokemonId($friendUserId, $pokemonIdWanted)){
            return $this->renderErrorMessage("Error","No pokemon found for pokemon id $userId");
        }else{
            $this->confirmExchangeFirebase($originalPokemonId,
                                           $pokemonIdWanted,
                                           $pokemonCraftedIdWanted,
                                           $originalCraftedPokemonId,
                                           $userId,
                                           $friendUserId);
                                           
            return $this->renderErrorMessage("Success","Pokemon wished sent to $friendUserId");
        }
    }

    public function confirmExchangeFirebase($pokemonIdWanted,
                                            $originalPokemonId,
                                            $pokemonCraftedIdWanted,
                                            $originalCraftedPokemonId,
                                            $userId,
                                            $friendUserId){
        $pokemonWanted = $this->loadRandomPokemonById($friendUserId,$pokemonIdWanted,$pokemonCraftedIdWanted);
        $originalPokemon = $this->loadRandomPokemonById($friendUserId,$pokemonIdWanted,$pokemonCraftedIdWanted);
        $this->movePokemonToFriend($userId, $friendUserId, $originalPokemonId, $originalCraftedPokemonId, $originalPokemon);
        $this->movePokemonToFriend($friendUserId, $friendUserId, $pokemonIdWanted, $pokemonCraftedIdWanted, $pokemonWanted);
    }

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
                                       $this->loadRandomPokemonById($userId,$pokemonId, $pokemonId));
                                           
            return $this->renderErrorMessage("Success","Pokemon sent to prof Chen.");
        }
    }

    private function loadRandomPokemonById($userId, $pokemonId, $pokemonCraftedId){
        return $this->firebaseInstance->returnReference("users/$userId/pokemonCollection/$pokemonId/$pokemonCraftedId")->getSnapshot()->getValue();
    }

    private function movePokemonToFriend($userId, $friendId, $pokemonId, $pokemonCraftedId,$data){
        $this->firebaseInstance->returnReference("users/$userId/pokemonCollection/$pokemonId/$pokemonCraftedId")->remove();
        $this->firebaseInstance->returnReference("users/$friendId/pokemonCollection/$pokemonId/$pokemonCraftedId")->set($data);
    }

    public function addPokemonToExchangeMarket($pokemonIdWanted, $originalPokemonId, $userId, $friendUserId){
        if($pokemonIdWanted<=0){
            return $this->renderErrorMessage("Error","Please choose a pokemon with an id strictly positive.");
        }else if($originalPokemonId <= 0){
            return $this->renderErrorMessage("Error","Please choose a pokemon with an id strictly positive.");
        }else if(!$this->firebaseInstance->userExist($userId)){
            return $this->renderErrorMessage("Error","User not found.");
        }else if(!$this->firebaseInstance->userExist($friendUserId)){
            return $this->renderErrorMessage("Error","User not found.");
        }else{
            $this->insertIntoMarketExchange($originalPokemonId, $pokemonIdWanted, $userId, $friendUserId);
            return $this->renderErrorMessage("Success","Pokemon wished sent to $friendUserId");
        }
    }

    private function insertIntoMarketExchange($pokemonId, $pokemonIdWanted, $userId, $friendUserId){
        $friendUserIdRef = $this->firebaseInstance->returnReference("users/$friendUserId/exchanges/$pokemonIdWanted");
        if($friendUserIdRef->getSnapshot()->getValue() == null){
            $rawData = [];
        }else{
            $rawData = $friendUserIdRef->getValue();
        }
        $exchangeData = [];
        $exchangeData['userId'] = $userId;
        $exchangeData['originalPokemonId'] = $pokemonId;
        array_push($rawData,$exchangeData);
        $friendUserIdRef->set($exchangeData);
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