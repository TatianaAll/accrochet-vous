<?php

namespace App\Controller\public;

use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class SearchResultController extends AbstractController
{
    #[Route(path:"/search", name: "user_search", methods: ["POST", "GET"])]
    public function showResultSearch(string $search, Request $request, ArticleRepository $articleRepository, CategoryRepository $categoryRepository )
    {
        $search = $request->query->get('search');

        //on définies les catégories trouvées avec la méthode searchCategories de CategoryRepo
        $categoriesFound = $categoryRepository->searchCategories($search);
        $articlesFound = $articleRepository->searchArticles($search);

        return $this->render('public/search_result.html.twig', ["categoriesFound" => $categoriesFound, "articlesFound" => $articlesFound]);
    }
}