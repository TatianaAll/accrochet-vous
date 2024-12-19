<?php

namespace App\Controller\public;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginAdminController extends AbstractController
{
    //je fais ma route avec le login dans ma partie public par ce que sinon je ne peux jamais ya voir accès
    #[Route(path:'/admin/login', name:'login_admin')]
    public function loginAdmin(AuthenticationUtils $authenticationUtils) : Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('public/login_admin.html.twig', [
            'error' => $error,
            'lastUsername' => $lastUsername
        ]);
    }

    #[Route(path: '/admin/logout', name: 'logout_admin')]
    public function logout(): void
    {
        //route utilisée par symfony pour se décnnecter, c'est magique un peu
        //en vrai ça renvoi vers le fichier security.yalm et est utilisé dans le logout
    }
}