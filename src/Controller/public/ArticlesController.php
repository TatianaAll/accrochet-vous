<?php

namespace App\Controller\public;

use App\Repository\ArticleRepository;
use App\Repository\StatusRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ArticlesController extends AbstractController
{
    #[Route(path: '/articles', name: 'articles_list', methods: ['GET'])]
    public function articlesPublishedList(ArticleRepository $articleRepository, StatusRepository $statusRepository) : Response
    {
        //dd('test');
        $statusPublished = $statusRepository->findBy(["name"=>"publié"]);
        $articles = $articleRepository->findBy(['status' => $statusPublished]);

        return $this->render('public/articles/list.html.twig', ['articles'=>$articles]);
    }

    #[Route(path: '/article/{id}/show', name: 'article_show', requirements: ['id'=>'\d+'], methods: ['GET'])]
    public function articlePublishedShow(int $id, ArticleRepository $articleRepository) : Response
    {
        $article = $articleRepository->find($id);
        $articleStatus = $article->getStatus();
        $articleStatus = $articleStatus->getName();
        if($articleStatus !== "publié" || !$article)
        {
            $this->addFlash('error', "Cet article n'existe pas.");

            return $this->redirectToRoute('articles_list');
        }

        return $this->render('public/articles/show.html.twig', ['article'=>$article]);
    }
}