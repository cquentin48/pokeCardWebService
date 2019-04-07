<?php
namespace App\Controller;

use App\Entity\Post;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class JSONController extends AbstractController
{
    /**
     * Render a json page into the browser with a json format
     */
    public function renderJSONPage($jsonArray){
        return $this->render('index.html.php',array(
            'jsonArray' => $jsonArray
        ));
    }

    /**
     * Return an array which contains the error message
     */
    private function generateErrorMessage($title, $errorMessage){
        $errorMessage = [];
        $errorMessage['title'] = $title;
        $errorMessage['message'] = $errorMessage;
        return $errorMessage;
    }
}
?>