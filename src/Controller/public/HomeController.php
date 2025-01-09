<?php

namespace App\Controller\public;

use App\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
  #[Route(path:"/", name:"home", methods:"GET")]
  public function homePage(ArticleRepository $articleRepository): Response {
    $lastArticle = $articleRepository->getLastArticlePublished();
    $allArticles = $articleRepository->findAll();
    if(count($allArticles) >=5 ){
      $last5Articles = $articleRepository->getLast5ArticlePublished();
      return $this->render('public/home/homepage.html.twig', ['lastArticle'=>$lastArticle,
                'last5Articles'=>$last5Articles]);
    }

    return $this->render('public/home/homepage.html.twig', ['lastArticle'=>$lastArticle]);
    }
}