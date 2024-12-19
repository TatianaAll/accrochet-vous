<?php

namespace App\Controller\public;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
        //route utilisée par symfony pour se décnnecter, c'est magique un peu
        //en vrai ça renvoi vers le fichier security.yalm et est utilisé dans le logout
    }


    #[Route(path: '/user/login', name: 'login')]
    public function loginUser(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('public/login.html.twig', [
            'error' => $error,
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

        $form = $this->createForm(UserType::class, $user);
        $formView = $form->createView();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            //vérifier si le nom est dispo
            //vérifier si l'adresse mail est déjà utilisé
            $user->setCreationDate(new \DateTime());
            $user->setRoles(["ROLE_USER"]);

            //il faut que je récupère le mdp rentré et que je le hache
            $plaintextPassword = $form->get('password')->getData();

            if (!$plaintextPassword) {
                $this->addFlash('error', 'Veuillez rentrer un mot de passe');
                return $this->redirectToRoute('admin_admin_create');
            }
            //je hash le tout
            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $plaintextPassword
            );
            $user->setPassword($hashedPassword);
            //dd($hashedPassword);

            $entityManager->persist($user);
            $entityManager->flush();

            $email = new Email();

            //je fais un template avec mon mail
            //je lui fais passer ma vue et je lui donne les valeur de contact récup dans le form
            $emailTemplate = $this->renderView('mails/inscription.html.twig', ['user' => $user]);

            //j'envoie avec mailer
            $mailer->send(
                $email->from('noreply@accrochetvous.com')
                    -> to($user->getEmail())
                    -> subject('Inscription')
                    -> html($emailTemplate)
            );

            $this->addFlash('success', 'Votre compte a bien été créé');
            return $this->redirectToRoute('home');
        }

        return $this->render('public/inscription.html.twig', [
            "formView" => $formView
        ]);
    }
}