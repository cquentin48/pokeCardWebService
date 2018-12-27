<?php
namespace App\Controller;

use App\Entity\Post;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="homepage")
     */
    public function index()
    {
        $posts = $this->getDoctrine()
            ->getRepository(Post::class)
            ->findLatest();

        return $this->render('default/index.html.twig', [
            'posts' => $posts,
        ]);
    }
}
?>