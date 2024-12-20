<?php

namespace App\Controller\users;

use App\Entity\Comment;
use App\Entity\Status;
use App\Form\CommentType;
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

class CommentController extends AbstractController
{
    #[Route(path: "user/article/{articleId}/comment", name: "user_article_add_comment", requirements: ['articleId'=>'\d+'], methods: ['POST', 'GET'])]
    #[IsGranted(new Expression('is_granted("ROLE_USER") or is_granted("ROLE_REDACTOR")'))]
    public function addComment(int $articleId,
                               ArticleRepository $articleRepository, StatusRepository $statusRepository,
                               Request $request, EntityManagerInterface $entityManager,
                               UniqueFileNameGenerator $uniqueFileNameGenerator,
                               ParameterBagInterface $parameterBag) :Response
    {
        //dd("tesroute");
        $article = $articleRepository->find($articleId);
        $comment = new Comment();

        $form = $this->createForm(CommentType::class, $comment);
        $formView = $form->createView();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $comment->setUser($this->getUser());
            $comment->setArticle($article);

            $statusComment = $statusRepository->findOneBy(["name"=>"à modérer"]);
            //dd($statusComment);
            $comment->setStatus($statusComment);

            $comment->setCreatedAt(new \DateTime());
            $comment->setLinkedRate(false);

            $imageComment = $form->get('image')->getData();
            if($imageComment)
            {
                $nameImage = $imageComment->getClientOriginalName();
                $imageExtension = $imageComment->getClientOriginalExtension();
                $newImageName = $uniqueFileNameGenerator->generateUniqueFileName($nameImage, $imageExtension);
                $rootDir = $parameterBag->get('kernel.project_dir');
                $uploadsDir = $rootDir . '/public/assets/uploads';
                $imageComment->move($uploadsDir, $newImageName);
                // 6- stock new image in the entity instance with the new name
                $comment->setImage($newImageName);
            }
            $entityManager->persist($comment);
            $entityManager->flush();

            $this->addFlash("succes", "Commentaire ajouté !");
            return $this->redirectToRoute('article_show', ['id' => $articleId]);
        }

        return $this->render("users/comments/create.html.twig", ["article" => $article, "formView"=>$formView]);
    }
}