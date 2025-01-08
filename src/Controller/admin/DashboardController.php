<?php

namespace App\Controller\admin;

use App\Repository\ArticleRepository;
use App\Repository\CommentRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class DashboardController extends AbstractController
{
    #[Route(path:'/admin', name:'admin_dashboard', methods: ['GET'])]
    #[IsGranted(new Expression('is_granted("ROLE_SUPER_ADMIN") or is_granted("ROLE_ADMIN")'))]
    public function adminDashboard(UserRepository $userRepository, ArticleRepository $articleRepository,
                                   CommentRepository $commentRepository): Response
    {
        $allUsers = $userRepository->findAll();
        $numberOfUsers = count($allUsers);

        $articlesPublished = $articleRepository->findBy(['status'=>'published']);
        $numberOfArticlesPublished = count($articlesPublished);

        $articlesToModerate = $articleRepository->findBy(['status' => "to moderate"]);
        $numberOfArticlesToModerate = count($articlesToModerate);

        $commentsToModerate = $commentRepository->findBy(['status' => "to moderate"]);
        $numberOfCommentsToModerate = count($commentsToModerate);

        //Think... what information are pertinent here
        return $this->render('admin/dashboard.html.twig', ['numberOfUsers' => $numberOfUsers,
            'numberOfArticlesPublished' => $numberOfArticlesPublished,
            'numberOfArticlesToModerate' => $numberOfArticlesToModerate,
            'numberOfCommentsToModerate' => $numberOfCommentsToModerate]);
    }


}