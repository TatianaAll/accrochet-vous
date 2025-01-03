<?php

namespace App\Controller\public;

use App\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route(path:"/", name:"home", methods:"GET")]
    public function homePage(ArticleRepository $articleRepository) : Response
    {
        $lastArticle = $articleRepository->getLastArticlePublished();
        return $this->render('public/home/homepage.html.twig', ['lastArticle'=>$lastArticle]);
    }
}