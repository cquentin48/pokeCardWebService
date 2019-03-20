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
        }else if($this->firebaseInstance->isChildEmpty($this->firebaseInstance->getReference('users'))){
            $returnedArray['title'] = "Information";
            $returnedArray['message'] = "No user friend";
            return $this->render('index.html.php',array(
                'jsonArray' => $returnedArray
            ));
        }else{
            return $this->render('index.html.php', array(
                'jsonArray' => loadUserFriendsId($userId)
            ));
        }
    }

    private function loadUserFriendsId($userId){
        $friendListArray = [];
        $friendList = $this->firebaseInstance->getReference('user')->getChild($userId)->getChild('friendsList')->getValue();
        foreach($friendList as $singleFriend){
            array_push($friendListArray,loadUserNameAndSprite($singleFriend['userId']));
        }
    }

    private function loadUserNameAndSprite($userId){
        $userData = [];
        $rawData = $this->firebaseInstance->getReference('users')->getChild($userId)->getValue();
        $userData['userName'] = $rawData['username'];
        $userData['sprite'] = $rawData['avatarImage'];
    }
}
$user = new UserDatabaseController();
print_r($user->firebaseInstance->getReference('users')->getSnapshot()->hasChild());
?>