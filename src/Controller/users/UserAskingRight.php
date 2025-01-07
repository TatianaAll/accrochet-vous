<?php

namespace App\Controller\users;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserAskingRight extends AbstractController
{
    #[Route(path: '/user/asking_right', name: 'user_asking_redactor', methods: ['GET'])]
    #[IsGranted(new Expression('is_granted("ROLE_USER")'))]
    public function askRedactorRight(MailerInterface $mailer)
    {
        $user = $this->getUser();
        $email = new Email();

        $emailTemplate = $this->renderView('mails/asking_right.html.twig', ['user' => $user]);

        $mailer->send(
            $email->from('noreply@accrochetvous.com')
                ->to('contact@accrochetvous.com')
                ->subject('Demande de droit rédacteur')
                ->html($emailTemplate)
        );
        $this->addFlash("success", "Demande envoyée !");
        return $this->redirectToRoute('home');
    }
}