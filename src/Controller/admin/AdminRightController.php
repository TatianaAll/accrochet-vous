<?php

namespace App\Controller\admin;

use App\Entity\Admin;
use App\Form\AdminType;
use App\Repository\AdminRepository;
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
    #[Route(path: '/admin/rights/create_admin', name: 'admin_admin_create', methods: ['GET', 'POST'])]
    //add restriction access to this method only for SUPER_ADMIN
    #[IsGranted(new Expression('is_granted("ROLE_SUPER_ADMIN")'))]
    public function adminCreateAdmin(Request                     $request,
                                     UserPasswordHasherInterface $passwordHasher,
                                     EntityManagerInterface      $entityManager): Response
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
                return $this->redirectToRoute('admin_admin_create');
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
        return $this->render('admin/rights/create_admin.html.twig', ['formView' => $formView]);

    }

    #[Route(path: '/admin/rights/list_admin', name: 'admin_admin_list', methods: ['GET'])]
    #[IsGranted(new Expression('is_granted("ROLE_SUPER_ADMIN, ROLE_ADMIN")'))]
    public function listAdmin(AdminRepository $adminRepository): Response
    {
        $admins = $adminRepository->findAll();
        return $this->render('admin/rights/list_admin.html.twig', ['admins' => $admins]);
    }

    #[Route(path: "/admin/rights/{id}/update", name: "admin_admin_update", requirements: ["id"=>"\d+"], methods: ["GET","POST"])]
    #[IsGranted(new Expression('is_granted("ROLE_SUPER_ADMIN")'))]
    public function updateAdminRights(int $id, AdminRepository $adminRepository,
                                      EntityManagerInterface $entityManager,
                                      UserPasswordHasherInterface $passwordHasher): Response
    {
        $adminToUpdate = $adminRepository->find($id);

        if(!$adminToUpdate){
            $this->addFlash("error", "Cet administrateur n'existe pas");
            return $this->redirectToRoute("admin_admin_list");
        }

        $form = $this->createForm(AdminType::class, $adminToUpdate);
        $formView = $form->createView();

        if($form->isSubmitted())
        {
            //je récupère le nouveau mdp et je le hash à la hache
            $plaintextPassword = $form->get('password')->getData();
            //si rien = on garde le même
            if ($plaintextPassword) {
                //je hache le tout
                $hashedPassword = $passwordHasher->hashPassword(
                    $adminToUpdate,
                    $plaintextPassword
                );
                $adminToUpdate->setPassword($hashedPassword);
                //dd($hashedPassword);
            }

            $entityManager->persist($adminToUpdate);
            $entityManager->flush();

            $this->addFlash("succes", "Droit de l'admin modifiés");
            return $this->redirectToRoute("admin_admin_list");
        }

        return $this->render("admin/rights/update.html.twig", ["admin"=>$adminToUpdate, "formView"=>$formView]);
    }

    #[Route(path: "/admin/rights/{id}/delete", name: "admin_admin_delete", requirements: ["id"=>"\d+"], methods: ['GET'])]
    //add restriction access to this method only for SUPER_ADMIN
    #[IsGranted(new Expression('is_granted("ROLE_SUPER_ADMIN")'))]
    public function deleteAdmin(int $id, EntityManagerInterface $entityManager, AdminRepository $adminRepository) : Response
    {
        $adminToDelete = $adminRepository->find($id);
        if(!$adminToDelete){
            $this->addFlash("error", "Cet administrateur n'existe pas");
            return $this->redirectToRoute("admin_admin_list");
        }
        if ($adminToDelete->getId() === $this->getUser()->getId()) {
            $this->addFlash("error", "Cette personne est vous, vous ne pouvez pas vous supprimer");
            return $this->redirectToRoute("admin_admin_list");
        }
        $entityManager->remove($adminToDelete);
        $entityManager->flush();

        $this->addFlash("success", "Administrateur supprimé avec succès");
        return $this->redirectToRoute("admin_admin_list");
    }

}