<?php

namespace App\Controller\users;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class UserController extends AbstractController
{
    #[Route(path:"/user/{id}/profile", name:"user_profile",  requirements: ["id"=>"\d+"], methods:"GET")]
    public function showProfile(int $id, UserRepository $userRepository): Response
    {
        $user = $userRepository->find($id);

        if (!$user) {
            $this->addFlash("error", "Cet utilisateur n'existe pas.");
            return $this->redirectToRoute("home");
        }

        return $this->render('users/profile.html.twig', ["user"=>$user]);
    }
}
