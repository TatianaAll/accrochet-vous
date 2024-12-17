<?php

namespace App\Controller\admin;

use App\Entity\Article;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminArticlesController extends AbstractController
{
    #[Route(path: '/admin/article/create', name: 'admin_article_create', methods:['POST', 'GET'])]
    public function createArticle(Request $request, EntityManagerInterface $entityManager) : Response
    {
        //new instance of article
        $article = new Article();

        //calling the form
        $formAdminCreateArticle = $this->createForm(ArticleType::class, $article);
        $formAdminView = $formAdminCreateArticle->createView();

        $formAdminCreateArticle->handleRequest($request);

        if($formAdminCreateArticle->isSubmitted() && $formAdminCreateArticle->isValid())
        {
            $article->setCreatedAt(new \DateTime());
            //$article->setAdminId($this->getUser()->getId());
            dd($article);
            $entityManager->persist($article);
            $entityManager->flush();

            $this->addFlash("success", "Modèle ajouté ;)");
            $this->redirectToRoute('admin_article_list');
        }
        return $this->render('admin/articles/create.html.twig', ['formView'=>$formAdminView]);
    }

    #[Route(path:'/admin/articles/list', name: 'admin_articles_list', methods:['GET'])]
    public function adminListArticles(ArticleRepository $articleRepository)
    {
        $articles = $articleRepository->findAll();

        return $this->render('admin/articles/list.html.twig', ['articles'=>$articles]);
    }
}