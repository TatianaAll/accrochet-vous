<?php

namespace App\Controller\admin;

use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class AdminCommentModeration extends AbstractController
{
    #[Route(path:"/admin/comments/list", name: "admin_comments_list", methods: ["GET"])]
    #[IsGranted(new Expression('is_granted("ROLE_SUPER_ADMIN") or is_granted("ROLE_ADMIN")'))]
    public function listAllComments(CommentRepository $commentRepository) : Response
    {
        $comments = $commentRepository->findAll();

        return $this->render('admin/comments/list.html.twig', ['comments'=>$comments]);
    }

    #[Route(path:"/admin/comments/to_moderate", name: "admin_comments_moderate", methods: ["GET"])]
    #[IsGranted(new Expression('is_granted("ROLE_SUPER_ADMIN") or is_granted("ROLE_ADMIN")'))]
    public function listCommentsToModerate(CommentRepository $commentRepository) : Response
    {
        $comments = $commentRepository->findBy(['status' => "to moderate"]);
        return $this->render('admin/comments/list_to_moderate.html.twig', ['comments'=>$comments]);
    }

    #[Route(path:"/admin/comment/{id}/to_moderate", name:"admin_comment_moderate_show")]
    #[IsGranted(new Expression('is_granted("ROLE_SUPER_ADMIN") or is_granted("ROLE_ADMIN")'))]
    public function showCommentToModerate (CommentRepository $commentRepository, int $id) : Response
    {
        $comment = $commentRepository->find($id);
        if(!$comment){
            $this->addFlash("error", "Commentaire non trouvé");
            return $this->redirectToRoute("admin_comments_moderate");
        }
        return $this->render('admin/comments/show_comment_to_moderate.html.twig', ['comment'=>$comment]);
    }

    #[Route(path:"/admin/comment/{id}/to_moderate/published", name:"admin_comment_moderate_published")]
    #[IsGranted(new Expression('is_granted("ROLE_SUPER_ADMIN") or is_granted("ROLE_ADMIN")'))]
    public function publishedComment(int $id, CommentRepository $commentRepository, EntityManagerInterface $entityManager) : Response
    {
        $comment = $commentRepository->find($id);
        if(!$comment){
            $this->addFlash("error", "Commentaire non trouvé");
            return $this->redirectToRoute("admin_comments_moderate");
        }

        $comment->setStatus("published");
        $entityManager->persist($comment);
        $entityManager->flush();

        $this->addFlash("success", "Commentaire de" . $comment->getUser()->getUsername() . " accepté et publié !");
        return $this->redirectToRoute('admin_comments_moderate');
    }

    #[Route(path:"/admin/comment/{id}/to_moderate/blocked", name:"admin_comment_moderate_blocked")]
    #[IsGranted(new Expression('is_granted("ROLE_SUPER_ADMIN") or is_granted("ROLE_ADMIN")'))]
    public function blockedComment(int $id, CommentRepository $commentRepository, EntityManagerInterface $entityManager) : Response
    {
        $comment = $commentRepository->find($id);
        if(!$comment){
            $this->addFlash("error", "Commentaire non trouvé");
            return $this->redirectToRoute("admin_comments_moderate");
        }

        $comment->setStatus("blocked");

        $entityManager->persist($comment);
        $entityManager->flush();

        $this->addFlash("success", "Commentaire de " . $comment->getUser()->getUsername() . "bloqué !");
        return $this->redirectToRoute('admin_comments_moderate');
    }
}