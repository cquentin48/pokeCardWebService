<?php
namespace App\Controller;

use App\Entity\Post;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class JSONController extends AbstractController
{
    public function renderJSONPage($jsonArray){
        return $this->render('index.html.php',array(
            'jsonArray' => $jsonArray
        ));
    }

    private function generateErrorMessage($title, $errorMessage){
        $errorMessage = [];
        $errorMessage['title'] = $title;
        $errorMessage['message'] = $errorMessage;
        return $errorMessage;
    }

    public function renderErrorMessage($title, $errorMessage){
        return $this->render('index.html.php',array(
            'jsonArray' => $this->generateErrorMessage($title,$errorMessage);
        ));
    }
}
?>