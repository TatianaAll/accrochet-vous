<?php

namespace App\Controller\public;

use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PublicCategoriesController extends AbstractController
{
  #[Route(path: '/categories', name: 'categories_list', methods: ['GET'])]
  public function articlesPublishedList(CategoryRepository $categoryRepository): Response {
    $categories = $categoryRepository->findAll();
    return $this->render('public/categories/list.html.twig', ['categories'=>$categories]);
  }

}