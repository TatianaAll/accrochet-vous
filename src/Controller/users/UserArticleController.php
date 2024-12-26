<?php

namespace App\Controller\users;

use App\Entity\Article;
use App\Form\ArticleType;
use App\Services\ImageImporter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserArticleController extends AbstractController
{
    #[Route(path: '/user/article/create', name: 'user_article_create', methods: ['POST', 'GET'])]
    #[IsGranted(new Expression('is_granted("ROLE_REDACTOR")'))]
    public function userSubmitArticle(Request $request, ImageImporter $imageImporter,
                                      EntityManagerInterface $entityManager) :Response
    {
        //new instance of article
        $userArticle = new Article();

        //calling the form
        $formUserCreateArticle = $this->createForm(ArticleType::class, $userArticle, [
            'is_admin' => $this->isGranted('ROLE_ADMIN'),
        ]);
        $formUserView = $formUserCreateArticle->createView();

        $formUserCreateArticle->handleRequest($request);

        if($formUserCreateArticle->isSubmitted() && $formUserCreateArticle->isValid())
        {
            $userArticle->setCreatedAt(new \DateTime());
            $userArticle->setStatus('to moderate');
            //management of the images imported
            //get them from the form
            $imageImported = $formUserCreateArticle->get('image')->getData();

            if ($imageImported) {
                //calling the service class for importation
                $newImageName = $imageImporter->importImage($imageImported);
                // stock new image in the entity instance with the new name
                $userArticle->setImage($newImageName);
            }
            //set the author to admin
            $userArticle->setUser($this->getUser());

            $entityManager->persist($userArticle);
            $entityManager->flush();

            $this->addFlash("success", "Modèle envoyé aux administrateurs pour relecture ;)");
            return $this->redirectToRoute('user_current_profile');
        }
        return $this->render('users/users_articles/submit.html.twig', ['formView'=>$formUserView]);
    }
}