<?php

namespace App\Controller\admin;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CategoryController extends AbstractController
{
    #[Route(path: '/admin/category/create', name: 'admin_category_create', methods:['POST', 'GET'])]
    #[IsGranted(new Expression('is_granted("ROLE_SUPER_ADMIN") or is_granted("ROLE_ADMIN")'))]
    public function createCategory(Request $request, EntityManagerInterface $entityManager): Response
    {
        $category = new Category();

        $categoryForm = $this->createForm(CategoryType::class, $category);
        $formView = $categoryForm->createView();

        $categoryForm->handleRequest($request);

        if ($categoryForm->isSubmitted() && $categoryForm->isValid()) {
            //dd($category);
            $entityManager->persist($category);
            $entityManager->flush();

            $this->addFlash('success', "Catégorie créée");

            return $this->redirectToRoute('admin_category_list');
        }

        return $this->render('admin/categories/create.html.twig', ["formView"=>$formView]);
    }

    #[Route(path: '/admin/categories/list', name: 'admin_category_list', methods:['GET'])]
    #[IsGranted(new Expression('is_granted("ROLE_SUPER_ADMIN") or is_granted("ROLE_ADMIN")'))]
    public function listCategories(CategoryRepository $categoryRepository): Response
    {
        $categories = $categoryRepository->findAll();

        return $this->render('admin/categories/list.html.twig', ["categories"=>$categories]);

    }

    #[Route(path: '/admin/category/{id}/show', name: 'admin_category_show', requirements: ['id'=>'\d+'] ,methods:['GET'])]
    #[IsGranted(new Expression('is_granted("ROLE_SUPER_ADMIN") or is_granted("ROLE_ADMIN")'))]
    public function showCategory(int $id, CategoryRepository $categoryRepository): Response
    {
        $category = $categoryRepository->find($id);

        return $this->render('admin/categories/show.html.twig', ["category"=>$category]);
    }

    #[Route(path:'/admin/category/{id}/update', name: 'admin_category_update', requirements: ['id'=>'\d+'], methods:['POST', 'GET'])]
    #[IsGranted(new Expression('is_granted("ROLE_SUPER_ADMIN") or is_granted("ROLE_ADMIN")'))]
    public function updateCategory(int $id,Request $request,
                                   CategoryRepository $categoryRepository,
                                   EntityManagerInterface $entityManager): Response
    {
        //dd('test');
        $categoryToUpdate = $categoryRepository->find($id);

        $categoryForm = $this->createForm(CategoryType::class, $categoryToUpdate);
        $formView = $categoryForm->createView();

        $categoryForm->handleRequest($request);

        if($categoryForm->isSubmitted() && $categoryForm->isValid()){
            $entityManager->persist($categoryToUpdate);
            $entityManager->flush();

            $this->addFlash('success', 'Modification enregistrée');
            return $this->redirectToRoute('admin_category_list');
        }
        return $this->render('admin/categories/update.html.twig',
            ["formView"=>$formView, "categoryToUpdate"=>$categoryToUpdate]);
    }

    #[Route(path: '/admin/category/{id}/delete', name: 'admin_category_delete', requirements: ['id'=>'\d+'])]
    #[IsGranted(new Expression('is_granted("ROLE_SUPER_ADMIN")'))]
    public function deleteCategory(int $id, CategoryRepository $categoryRepository, EntityManagerInterface $entityManager) : Response
    {
        $categoryToDelete = $categoryRepository->find($id);
        //dd($categoryToDelete);

        $entityManager->remove($categoryToDelete);
        $entityManager->flush();

        $this->addFlash("success", "Catégorie supprimée");

        return $this->redirectToRoute("admin_category_list");

    }
}
