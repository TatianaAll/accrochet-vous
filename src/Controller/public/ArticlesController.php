<?php

namespace App\Controller\public;

use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;
use App\Repository\CommentRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ArticlesController extends AbstractController
{
  #[Route(path: '/articles', name: 'articles_list', methods: ['GET'])]
  public function articlesPublishedList(ArticleRepository $articleRepository, PaginatorInterface $paginator, Request $request): Response {
    $allArticlesPublished = $articleRepository->findBy(['status' => "published"]);
    //pagination with PaginatorInterface and the methode paginate(articleToDisplay, page per default, number of article to display in 1 page)
    $articlesToDisplay = $paginator->paginate($allArticlesPublished,
      $request->query->getInt('page', 1), 5);

    return $this->render('public/articles/list.html.twig', ['articles'=>$articlesToDisplay]);
  }

  #[Route(path: '/article/{id}/show', name: 'article_show', requirements: ['id'=>'\d+'], methods: ['GET'])]
  public function articlePublishedShow(int $id, ArticleRepository $articleRepository): Response {
    $article = $articleRepository->find($id);
    if (!$article){
      $this->addFlash("error", "Article inexistant");
      return $this->redirectToRoute('articles_list');
    }
    @$allComments = $article->getComments();
    $commentsPublished = [];
    foreach ($allComments as $comment) {
      if($comment->getStatus() === "published") {
        $commentsPublished[] = $comment;
      }
    }

    $articleStatus = $article->getStatus();

    if($articleStatus !== "published" || !$article) {
      $this->addFlash('error', "Cet article n'existe pas.");
      return $this->redirectToRoute('articles_list');
    }
    return $this->render('public/articles/show.html.twig', ['article'=>$article, "comments"=>$commentsPublished]);
  }

  #[Route(path: '/{catId}/articles', name: 'articles_category', requirements: ['catId'=>'\d+'], methods: ['GET'])]
  public function articlesByCategory (int $catId, ArticleRepository $articleRepository,
                                      CategoryRepository $categoryRepository): Response {
    $articles = $articleRepository->getArticleByCategory($catId);
    $category = $categoryRepository->find($catId);
    return $this->render('public/articles/category.html.twig', ['articles'=>$articles, 'category'=>$category]);
  }
}