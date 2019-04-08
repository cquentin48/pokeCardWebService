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
    
    function __construct(){
        $this->firebaseInstance = new FirebaseController();
        $this->jsonRenderer = new JSONController();
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