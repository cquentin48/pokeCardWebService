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
    private $jsonRender;
    
    function __construct(){
        $this->firebaseInstance = new FirebaseController();
        $this->jsonRender = new JSONController();
    }

    public function addPokemonToExchangeMarket($pokemonId, $craftedPokemonId, $userId){
        if($pokemonId<=0){
            $this->renderErrorMessage("Error","Please choose a pokemon with an id strictly positive.");
        }else if($craftedPokemonId == ""){
            $this->renderErrorMessage("Error","Please choose a crafted with an existing id");
        }else if($this->firebaseInstance->userExist($userId)){
            $this->renderErrorMessage("Error","User not found.");
        }else{
            insertIntoMarketExchange($pokemonId, $craftedPokemonId, $userId);
            renderErrorMessage("Success","Pokemon n°$pokemonId added to the market.");
        }
    }

    private function insertIntoMarketExchange($pokemonId, $craftedPokemonId, $userId){
        $data = $this->firebaseInstance->returnValueOfReference("users/$userId/pokemonCollection/$pokemonId/$craftedPokemonId");
        $this->firebaseInstance->returnReference("users/$userId/pokemonCollection/$pokemonId/$craftedPokemonId")->remove();
        $this->firebaseInstance->returnReference("exchanges/gts/$userId/pokemonCollection/$pokemonId/$craftedPokemonId")->set($data);
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