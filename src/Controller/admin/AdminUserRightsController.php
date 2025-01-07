<?php

namespace App\Controller\admin;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class AdminUserRightsController extends AbstractController
{
    #[Route(path: '/admin/rights/list_users', name: 'admin_users_list', methods: ['GET'])]
    #[IsGranted(new Expression('is_granted("ROLE_SUPER_ADMIN") or is_granted("ROLE_ADMIN")'))]
    public function listAdmin(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();
        return $this->render('admin/rights/list_users.html.twig', ['users' => $users]);
    }

    #[Route(path: '/admin/rights/{id}/redactor_status', name: 'admin_users_redactor', requirements: ["id"=>"\d+"], methods: ['GET'])]
    #[IsGranted(new Expression('is_granted("ROLE_SUPER_ADMIN") or is_granted("ROLE_ADMIN")'))]
    public function giveRedactorStatus(int $id, UserRepository $userRepository,
                                       EntityManagerInterface $entityManager, MailerInterface $mailer) : Response
    {
        $user = $userRepository->find($id);
        if(!$user){
            $this->addFlash("error", "Utilisateur non trouvé");
            return $this->redirectToRoute("admin_users_list");
        }

        $user->setRoles(["ROLE_USER", "ROLE_REDACTOR"]);
        $entityManager->persist($user);
        $entityManager->flush();

        $email = new Email();

        $emailTemplate = $this->renderView('mails/giving_right.html.twig', ['user' => $user]);

        $mailer->send(
            $email->from('noreply@accrochetvous.com')
                ->to($user->getEmail())
                ->subject('Droits rédacteur attribués')
                ->html($emailTemplate)
        );

        $this->addFlash("success", "Rôle rédacteur attribué à " . $user->getUsername() . " !");
        return $this->redirectToRoute('admin_users_list');
    }

}