<?php

namespace App\Controller\users;

use App\Entity\Comment;
use App\Form\CommentType;
use App\Repository\ArticleRepository;
use App\Services\ImageImporter;
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
                               ArticleRepository $articleRepository,
                               Request $request, EntityManagerInterface $entityManager,
                               ImageImporter $imageImporter) :Response
    {
        $article = $articleRepository->find($articleId);
        $comment = new Comment();

        $comment->setUser($this->getUser());
        $comment->setArticle($article);
        $comment->setStatus("to moderate");
        $comment->setCreatedAt(new \DateTime());
        $comment->setLinkedRate(false);

        $formNewComment = $this->createForm(CommentType::class, $comment);

        $formNewComment->handleRequest($request);

        if($formNewComment->isSubmitted() && $formNewComment->isValid()) {
            $imageImported = $formNewComment->get('image')->getData();
            if ($imageImported) {
                $newImageName = $imageImporter->importImage($imageImported);
                $comment->setImage($newImageName);
            }
            $entityManager->persist($comment);
            $entityManager->flush();

            $this->addFlash("success", "Commentaire ajouté, il sera lu par nos administrateurs avant d'apparaitre !");
            return $this->redirectToRoute('article_show', ['id' => $articleId]);
        }
        $formView = $formNewComment->createView();
        return $this->render("users/comments/create.html.twig", ["article" => $article, "formView"=>$formView]);
    }


}