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
        $newUser = new User();

        $newUser->setCreationDate(new \DateTime());
        $form = $this->createForm(UserType::class, $newUser);

        $form->handleRequest($request);
        $allUsers = $userRepository->findAll();
        foreach ($allUsers as $user) {
            if ($form->get('username')->getData() === $user->getUsername()) {
                $form->addError(new FormError("Ce pseudo est déjà utilisé"));
            }
            if ($form->get('email')->getData() === $user->getEmail()) {
                $form->addError(new FormError("Cette adresse mail est déjà utilisée"));
            }
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $plaintextPassword = $form->get('password')->getData();
            $confirmPassword = $form->get('passwordConfirmation')->getData();

            if ($plaintextPassword === $confirmPassword) {
                $hashedPassword = $passwordHasher->hashPassword(
                    $newUser,
                    $plaintextPassword
                );
                $newUser->setPassword($hashedPassword);

                $newUser->setRoles(["ROLE_USER"]);

                $entityManager->persist($newUser);
                $entityManager->flush();

                $email = new Email();

                //template with my mail
                //pass the view and the values
                $emailTemplate = $this->renderView('mails/inscription.html.twig', ['user' => $newUser]);

                //send with mailer
                $mailer->send(
                    $email->from('noreply@accrochetvous.com')
                        ->to($newUser->getEmail())
                        ->subject('Inscription')
                        ->html($emailTemplate)
                );

                $this->addFlash('success', 'Votre compte a bien été créé');
                return $this->redirectToRoute('login');
            } else {
                $form->addError(new FormError('Les mots de passe ne sont pas identiques'));
            }
        }

        return $this->render('public/inscription.html.twig', [
            "formView" => $form->createView()
        ]);
    }
}