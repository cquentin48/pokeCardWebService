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
class FirebaseController extends AbstractController
{
    private $jsonFileContent;
    private $factory;
    private $database;
    private $references;

    private function initFactory(){
        $this->jsonFileContent = ServiceAccount::fromJsonFile(dirname(__DIR__).'/secretJSONData/pokeapi-1541497105412-186297ab70cc.json');
        $this->factory = (new Factory)
        ->withServiceAccount($this->jsonFileContent)
        // The following line is optional if the project id in your credentials file
        // is identical to the subdomain of your Firebase project. If you need it,
        // make sure to replace the URL with the URL of your project.
        ->withDatabaseUri('https://pokeapi-1541497105412.firebaseio.com/')
        ->create();
    }

    public function getReference($refId){
        return $this->references[$refId];
    }

    private function initDatabase(){
        $this->database = $this->factory->getDatabase();
    }

    public function isChildEmpty($ref){
        return $ref->getValue() == null;
    }

    function __construct(){
        $this->initFactory();
        $this->initDatabase();
        $this->initFirebaseReferences();
    }

    private function initFirebaseReferences(){
        $references = [];
        $references['users'] = $this->database->getReference('users')->getSnapshot();
    }

    public function insertIntoDatabase($ref, $data){
        $ref->set($data);
    }
}
?>