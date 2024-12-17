<?php

namespace App\Controller\admin;

use App\Entity\Admin;
use App\Form\AdminType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\ExpressionLanguage\Expression;

class AdminRightController extends AbstractController
{
    #[Route(path:'/superAdmin/rights/create_admin', name: 'superAdmin_admin_create', methods: ['GET', 'POST'])]
    //add restriction access to this method only for SUPER_ADMIN
    #[IsGranted(new Expression('is_granted("ROLE_SUPER_ADMIN")'))]
    public function adminCreateAdmin(Request $request,
                                     UserPasswordHasherInterface $passwordHasher,
                                     EntityManagerInterface $entityManager) : Response
    {
        $admin = new Admin();

        $form = $this->createForm(AdminType::class, $admin);
        $formView = $form->createView();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            //il faut que je récupère le mdp rentré et que je le hache
            $plaintextPassword = $form->get('password')->getData();

            if (!$plaintextPassword) {
                $this->addFlash('error', 'Veuillez rentrer un mot de passe');
                return $this->redirectToRoute('superAdmin_admin_create');
            }
            //je hash le tout
            $hashedPassword = $passwordHasher->hashPassword(
                $admin,
                $plaintextPassword
            );
            $admin->setPassword($hashedPassword);
            //dd($hashedPassword);

            $entityManager->persist($admin);
            $entityManager->flush();

            $this->addFlash('success', 'Un nouvel admin a été créé');
            //return $this->redirectToRoute('admin_dashboard');
        }
        return $this->render('superAdmin/rights/create_admin.html.twig', ['formView' => $formView]);

    }
}