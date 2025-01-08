<?php

namespace App\Controller\public;

use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class SearchResultController extends AbstractController
{
    #[Route(path:"/search", name: "user_search", methods: ["POST", "GET"])]
    public function showResultSearch(Request $request, ArticleRepository $articleRepository,
                                     CategoryRepository $categoryRepository )
    {
        $search = $request->query->get('search');

        //defined find categories with method searchCategories de CategoryRepo
        $categoriesFound = $categoryRepository->searchCategories($search);
        $articlesFound = $articleRepository->searchArticles($search);

        return $this->render('public/search-result.html.twig', ["categoriesFound" => $categoriesFound,
            "articlesFound" => $articlesFound, "search" => $search]);
    }
}