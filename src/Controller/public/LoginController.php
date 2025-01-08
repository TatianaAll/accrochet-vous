<?php

namespace App\Controller\public;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginController extends AbstractController
{
    #[Route(path: '/user/logout', name: 'user_logout')]
    public function logout(): void
    {
        //route use by Symfony to logout, it's magic
        //redirect to the security.yaml file
    }

    #[Route(path: '/user/login', name: 'login')]
    public function loginUser(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();
        $errorMessage = "Adresse mail ou mot de passe incorrect";

        return $this->render('public/login.html.twig', [
            'error' => $error,
            'errorMessage' => $errorMessage,
            'lastUsername' => $lastUsername
        ]);
    }

    #[Route(path: "/inscription", name: "user_inscription")]
    public function userInscription(UserRepository              $userRepository,
                                    Request                     $request,
                                    EntityManagerInterface      $entityManager,
                                    UserPasswordHasherInterface $passwordHasher, MailerInterface $mailer): Response
    {
        $user = new User();

        $user->setCreationDate(new \DateTime());
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plaintextPassword = $form->get('password')->getData();
            $confirmPassword = $form->get('passwordConfirmation')->getData();

            if ($plaintextPassword === $confirmPassword) {
                $hashedPassword = $passwordHasher->hashPassword(
                    $user,
                    $plaintextPassword
                );
                $user->setPassword($hashedPassword);

                //vérifier si le nom est dispo
                //vérifier si l'adresse mail est déjà utilisé

                $user->setRoles(["ROLE_USER"]);

                $entityManager->persist($user);
                $entityManager->flush();

                $email = new Email();

                //je fais un template avec mon mail
                //je lui fais passer ma vue et je lui donne les valeur de contact récup dans le form
                $emailTemplate = $this->renderView('mails/inscription.html.twig', ['user' => $user]);

                //j'envoie avec mailer
                $mailer->send(
                    $email->from('noreply@accrochetvous.com')
                        ->to($user->getEmail())
                        ->subject('Inscription')
                        ->html($emailTemplate)
                );

                $this->addFlash('success', 'Votre compte a bien été créé');
                return $this->redirectToRoute('home');
            } else {
                $form->addError(new FormError('Les mots de passe ne sont pas identiques'));
            }
        }

        return $this->render('public/inscription.html.twig', [
            "formView" => $form->createView()
        ]);
    }
}