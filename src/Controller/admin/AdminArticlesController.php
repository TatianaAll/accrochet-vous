<?php

namespace App\Controller\admin;

use App\Entity\Article;
use App\Entity\Status;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
use App\Repository\StatusRepository;
use App\Services\UniqueFileNameGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class AdminArticlesController extends AbstractController
{
    #[Route(path: '/admin/article/create', name: 'admin_article_create', methods:['POST', 'GET'])]
    #[IsGranted(new Expression('is_granted("ROLE_SUPER_ADMIN") or is_granted("ROLE_ADMIN")'))]
    public function createArticle(Request $request, EntityManagerInterface $entityManager,
                                  UniqueFileNameGenerator $uniqueFileNameGenerator,
                                  ParameterBagInterface $parameterBag) : Response
    {
        //dd("test");
        //new instance of article
        $article = new Article();

        //calling the form
        $formAdminCreateArticle = $this->createForm(ArticleType::class, $article);
        $formAdminView = $formAdminCreateArticle->createView();

        $formAdminCreateArticle->handleRequest($request);

        if($formAdminCreateArticle->isSubmitted() && $formAdminCreateArticle->isValid())
        {
            $article->setCreatedAt(new \DateTime());
            //management of the images imported
            //1- get them from the form
            $imageImported = $formAdminCreateArticle->get('image')->getData();

            if ($imageImported) {
                $nameImage = $imageImported->getClientOriginalName();
                $imageExtension = $imageImported->getClientOriginalExtension();
                //2- rename with a service class
                $newImageName = $uniqueFileNameGenerator->generateUniqueFileName($nameImage, $imageExtension);
                //dd($newImageName);
                // 3- get, with ParameterBag class, the path to the project's root directory
                $rootDir = $parameterBag->get('kernel.project_dir');
                //dd($rootDir);
                // 4- generate the path to the 'uploads' directory (in public directory)
                $uploadsDir = $rootDir . '/public/assets/uploads';
                //5- move the image to the target directory
                //rename with the new name (second argument)
                $imageImported->move($uploadsDir, $newImageName);

                // 6- stock new image in the entity instance with the new name
                $article->setImage($newImageName);
            }
            //set the author to admin
            $article->setAdminId($this->getUser());

            //dd($article);
            $entityManager->persist($article);
            $entityManager->flush();

            $this->addFlash("success", "Modèle ajouté ;)");
            return $this->redirectToRoute('admin_articles_list');
        }
        return $this->render('admin/articles/create.html.twig', ['formView'=>$formAdminView]);
    }

    #[Route(path:'/admin/articles/list', name: 'admin_articles_list', methods:['GET'])]
    #[IsGranted(new Expression('is_granted("ROLE_SUPER_ADMIN") or is_granted("ROLE_ADMIN")'))]
    public function adminListArticles(ArticleRepository $articleRepository) : Response
    {
        $articles = $articleRepository->findAll();

        return $this->render('admin/articles/list.html.twig', ['articles'=>$articles]);
    }

    #[Route(path:'/admin/article/{id}/show', name: 'admin_article_show', requirements: ['id'=>'\d+'], methods:['GET'])]
    public function adminArticleShow(int $id, ArticleRepository $articleRepository) : Response
    {
        $article = $articleRepository->find($id);

        if(!$article)
        {
            $this->addFlash("error", "Cet article n'existe pas");
            return $this->redirectToRoute("admin_articles_list");
        }

        return $this->render('admin/articles/show.html.twig', ['article'=>$article]);
    }

    #[Route(path: "/admin/article/{id}/update", name: "admin_article_update", requirements: ["id"=>"\d+"], methods: ["GET", "POST"])]
    #[IsGranted(new Expression('is_granted("ROLE_SUPER_ADMIN") or is_granted("ROLE_ADMIN")'))]
    public function updateArticle(int $id, ArticleRepository $articleRepository,
                                  Request $request, UniqueFileNameGenerator $uniqueFileNameGenerator,
                                  ParameterBagInterface $parameterBag,
                                  EntityManagerInterface $entityManager) : Response
    {
        //dd('test');
        $articleToUpdate = $articleRepository->find($id);

        if(!$articleToUpdate){
            $this->addFlash("error", "Cet article n'existe pas :(");
            return $this->redirectToRoute("admin_articles_list");
        }

        $form = $this->createForm(ArticleType::class, $articleToUpdate);
        $formView = $form->createView();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $articleToUpdate->setUpdateAt(new \DateTime());

            $imageToUpdate = $form->get('image')->getData();

            if ($imageToUpdate) {
                $nameImage = $imageToUpdate->getClientOriginalName();
                $imageExtension = $imageToUpdate->getClientOriginalExtension();
                //2- rename with a service class
                $newImageName = $uniqueFileNameGenerator->generateUniqueFileName($nameImage, $imageExtension);
                // 3- get, with ParameterBag class, the path to the project's root directory
                $rootDir = $parameterBag->get('kernel.project_dir');
                // 4- generate the path to the 'uploads' directory (in public directory)
                $uploadsDir = $rootDir . '/public/assets/uploads';
                //5- move the image to the target directory
                //rename with the new name (second argument)
                $imageToUpdate->move($uploadsDir, $newImageName);
                // 6- stock new image in the entity instance with the new name
                $articleToUpdate->setImage($newImageName);
            }
            $entityManager->persist($articleToUpdate);
            $entityManager->flush();

            $this->addFlash("success", "Modification effectuée avec succès");
            return $this->redirectToRoute("admin_articles_list");
        }

        return $this->render("admin/articles/update.html.twig", ["formView"=>$formView, "articleToUpdate"=>$articleToUpdate]);
    }

    #[Route(path:"/admin/article/{id}/delete", name:"admin_article_delete", requirements: ['id'=>'\d+'], methods: ["GET"])]
    #[IsGranted(new Expression('is_granted("ROLE_SUPER_ADMIN")'))]
    public function deleteArticle(int $id, ArticleRepository $articleRepository,
                                  EntityManagerInterface $entityManager) : Response
    {
        $articleToDelete = $articleRepository->find($id);

        if(!$articleToDelete)
        {
            $this->addFlash('error', "Cet article n'existe pas :(");
            return $this->redirectToRoute("admin_articles_list");
        }

        $entityManager->remove($articleToDelete);
        $entityManager->flush();

        $this->addFlash("success", "Article supprimé !");
        return $this->redirectToRoute("admin_articles_list");
    }

    #[Route(path:"/admin/articles/list/toModerate", name:"admin_article_delete", requirements: ['id'=>'\d+'], methods: ["GET"])]
    #[IsGranted(new Expression('is_granted("ROLE_SUPER_ADMIN") or is_granted("ROLE_ADMIN")'))]
    public function listToModerateArticle(ArticleRepository $articleRepository, StatusRepository $statusRepository) : Response
    {
        $statusToModerate = $statusRepository->findBy(["name"=>"à modérer"]);
        $articles = $articleRepository->findBy(['status' => $statusToModerate]);
        //dd($articles);
        return $this->render('admin/articles/list_toModerate.html.twig', ['articles'=>$articles]);
    }

}