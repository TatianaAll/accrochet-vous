<?php

namespace App\Controller\admin;

use App\Entity\Article;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
use App\Services\ImageImporter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class AdminArticlesController extends AbstractController
{
    #[Route(path: '/admin/article/create', name: 'admin_article_create', methods: ['POST', 'GET'])]
    #[IsGranted(new Expression('is_granted("ROLE_SUPER_ADMIN") or is_granted("ROLE_ADMIN")'))]
    public function createArticle(Request       $request, EntityManagerInterface $entityManager,
                                  ImageImporter $imageImporter): Response
    {
        //new instance of article
        $article = new Article();

        //set the creation date
        $article->setCreatedAt(new \DateTime());
        //set the author to admin
        $article->setAdminId($this->getUser());

        //calling the form with a cerification about admin (to manage the status of the article)
        $formAdminCreateArticle = $this->createForm(ArticleType::class, $article, [
            'is_admin' => $this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_SUPER_ADMIN'),
        ]);
        $formAdminCreateArticle->handleRequest($request);

        if ($formAdminCreateArticle->isSubmitted()) {
            //management of the images imported
            //get them from the form
            $imageImported = $formAdminCreateArticle->get('image')->getData();
            if ($imageImported) {
                //calling the service class for importation
                $newImageName = $imageImporter->importImage($imageImported);
                // stock new image in the entity instance with the new name
                $article->setImage($newImageName);
            }

            if ($formAdminCreateArticle->isValid()) {

                $entityManager->persist($article);
                $entityManager->flush();

                $this->addFlash("success", "Modèle ajouté ;)");
                return $this->redirectToRoute('admin_articles_list');
            }
        }
        $formAdminView = $formAdminCreateArticle->createView();
        return $this->render('admin/articles/create.html.twig', ['formView' => $formAdminView]);
    }

    #[Route(path: '/admin/articles/list', name: 'admin_articles_list', methods: ['GET'])]
    #[IsGranted(new Expression('is_granted("ROLE_SUPER_ADMIN") or is_granted("ROLE_ADMIN")'))]
    public function adminListArticles(ArticleRepository $articleRepository): Response
    {
        $articles = $articleRepository->findAll();

        return $this->render('admin/articles/list.html.twig', ['articles' => $articles]);
    }

    #[Route(path: '/admin/article/{id}/show', name: 'admin_article_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    #[IsGranted(new Expression('is_granted("ROLE_SUPER_ADMIN") or is_granted("ROLE_ADMIN")'))]
    public function adminArticleShow(int $id, ArticleRepository $articleRepository): Response
    {
        $article = $articleRepository->find($id);

        if (!$article) {
            $this->addFlash("error", "Cet article n'existe pas");
            return $this->redirectToRoute("admin_articles_list");
        }

        return $this->render('admin/articles/show.html.twig', ['article' => $article]);
    }

    #[Route(path: "/admin/article/{id}/update", name: "admin_article_update", requirements: ["id" => "\d+"], methods: ["GET", "POST"])]
    #[IsGranted(new Expression('is_granted("ROLE_SUPER_ADMIN") or is_granted("ROLE_ADMIN")'))]
    public function updateArticle(int                    $id, ArticleRepository $articleRepository,
                                  Request                $request, ImageImporter $imageImporter,
                                  EntityManagerInterface $entityManager, ParameterBagInterface $parameterBag): Response
    {
        $articleToUpdate = $articleRepository->find($id);

        if (!$articleToUpdate) {
            $this->addFlash("error", "Cet article n'existe pas :(");
            return $this->redirectToRoute("admin_articles_list");
        }

        $form = $this->createForm(ArticleType::class, $articleToUpdate);
        $formView = $form->createView();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $articleToUpdate->setUpdateAt(new \DateTime());

            $imageToUpdate = $form->get('image')->getData();
            if ($imageToUpdate) {
                $oldImage = $articleToUpdate->getImage();
                $rootDir = $parameterBag->get('kernel.project_dir');
                $uploadsDir = $rootDir . '/public/assets/uploads';
                @unlink($uploadsDir . '/' . $oldImage);

                $newImageName = $imageImporter->importImage($imageToUpdate);
                $articleToUpdate->setImage($newImageName);
            }
            $entityManager->persist($articleToUpdate);
            $entityManager->flush();

            $this->addFlash("success", "Modification effectuée avec succès");
            return $this->redirectToRoute("admin_articles_list");
        }

        return $this->render("admin/articles/update.html.twig", ["formView" => $formView, "articleToUpdate" => $articleToUpdate]);
    }

    #[Route(path: "/admin/article/{id}/delete", name: "admin_article_delete", requirements: ['id' => '\d+'], methods: ["GET"])]
    #[IsGranted(new Expression('is_granted("ROLE_SUPER_ADMIN")'))]
    public function deleteArticle(int                    $id, ArticleRepository $articleRepository,
                                  EntityManagerInterface $entityManager, ParameterBagInterface $parameterBag): Response
    {
        $articleToDelete = $articleRepository->find($id);

        if (!$articleToDelete) {
            $this->addFlash('error', "Cet article n'existe pas :(");
            return $this->redirectToRoute("admin_articles_list");
        }

        $imageToDelete = $articleToDelete->getImage();
        $rootDir = $parameterBag->get('kernel.project_dir');
        $uploadsDir = $rootDir . '/public/assets/uploads';
        @unlink($uploadsDir . '/' . $imageToDelete);

        $entityManager->remove($articleToDelete);
        $entityManager->flush();

        $this->addFlash("success", "Article supprimé !");
        return $this->redirectToRoute("admin_articles_list");
    }

    #[Route(path: "/admin/articles/list/to_moderate", name: "admin_articles_moderate", methods: ["GET"])]
    #[IsGranted(new Expression('is_granted("ROLE_SUPER_ADMIN") or is_granted("ROLE_ADMIN")'))]
    public function listToModerateArticle(ArticleRepository $articleRepository): Response
    {
        $articles = $articleRepository->findBy(['status' => "to moderate"]);
        return $this->render('admin/articles/list_toModerate.html.twig', ['articles' => $articles]);
    }

    #[Route(path: "/admin/article/{id}/to_moderate/published", name: "admin_article_moderate_published")]
    #[IsGranted(new Expression('is_granted("ROLE_SUPER_ADMIN") or is_granted("ROLE_ADMIN")'))]
    public function publishedComment(int $id, ArticleRepository $articleRepository, EntityManagerInterface $entityManager): Response
    {
        $articleToPublished = $articleRepository->find($id);
        if (!$articleToPublished) {
            $this->addFlash("error", "Article non trouvé");
            return $this->redirectToRoute("admin_articles_moderate");
        }

        $articleToPublished->setStatus("published");
        $entityManager->persist($articleToPublished);
        $entityManager->flush();

        $this->addFlash("success", "Article '" . $articleToPublished->getTitle() . "' accepté et publié !");
        return $this->redirectToRoute('admin_articles_moderate');
    }

    #[Route(path: "/admin/article/{id}/to_moderate/blocked", name: "admin_article_moderate_blocked")]
    #[IsGranted(new Expression('is_granted("ROLE_SUPER_ADMIN") or is_granted("ROLE_ADMIN")'))]
    public function blockedComment(int $id, ArticleRepository $articleRepository, EntityManagerInterface $entityManager): Response
    {
        $articleToBlocked = $articleRepository->find($id);
        if (!$articleToBlocked) {
            $this->addFlash("error", "Article non trouvé");
            return $this->redirectToRoute("admin_articles_moderate");
        }

        $articleToBlocked->setStatus("blocked");

        $entityManager->persist($articleToBlocked);
        $entityManager->flush();

        $this->addFlash("success", "Article '" . $articleToBlocked->getTitle() . "' bloqué !");
        return $this->redirectToRoute('admin_articles_moderate');
    }

}