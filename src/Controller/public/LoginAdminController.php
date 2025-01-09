<?php

namespace App\Controller\public;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginAdminController extends AbstractController
{
  //I do the path to the login in the public part of my website because i need to connect before having access
  #[Route(path:'/admin/login', name:'login_admin')]
  public function loginAdmin(AuthenticationUtils $authenticationUtils): Response {
    $error = $authenticationUtils->getLastAuthenticationError();
    $lastUsername = $authenticationUtils->getLastUsername();

    return $this->render('public/login_admin.html.twig', [
      'error' => $error,
      'lastUsername' => $lastUsername
    ]);
  }

  #[Route(path: '/admin/logout', name: 'admin_logout')]
  public function logout(): void {
    //route use by Symfony to logout, it's magic
    //redirect to the security.yaml file
    }

}