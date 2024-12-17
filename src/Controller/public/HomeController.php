<?php

namespace App\Controller\public;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route(path:"/", name:"home", methods:"GET")]
    public function homePage() : Response
    {
        return $this->render('public/home/homepage.html.twig');
    }
}