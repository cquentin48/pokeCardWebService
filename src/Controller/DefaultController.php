<?php
    namespace App\Controller\DefaultController;
    use Symfony\Bundle\FrameWorkBundle\Controller\Controller;

    class DefaultController extends Controller{
        public function indexAction(){
            return $this->render("index.php");
        }
    }
?>