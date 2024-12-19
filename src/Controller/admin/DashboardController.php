<?php

namespace App\Controller\admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class DashboardController extends AbstractController
{
    #[Route(path: '/admin/logout', name: 'admin_logout')]
    public function logout(): void
    {
        //route utilisée par symfony pour se décnnecter, c'est magique un peu
        //en vrai ça renvoi vers le fichier security.yalm et est utilisé dans le logout
    }

    #[Route(path:'/admin', name:'admin_dashboard', methods: ['GET'])]
    #[IsGranted(new Expression('is_granted("ROLE_SUPER_ADMIN, ROLE_ADMIN")'))]
    public function adminDashboard(): Response
    {
        return $this->render('admin/dashboard.html.twig');
    }


}