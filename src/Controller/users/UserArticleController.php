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
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserArticleController extends AbstractController
{
    #[Route(path: '/user/article/create', name: 'user_article_create', methods: ['POST', 'GET'])]
    #[IsGranted(new Expression('is_granted("ROLE_REDACTOR")'))]
    public function userSubmitArticle(Request $request, ImageImporter $imageImporter,
                                      EntityManagerInterface $entityManager, MailerInterface $mailer) :Response
    {
        //new instance of article
        $userArticle = new Article();

        $userArticle->setCreatedAt(new \DateTime());
        $userArticle->setStatus('to moderate');
        $userArticle->setUser($this->getUser());

        //calling the form
        $formUserCreateArticle = $this->createForm(ArticleType::class, $userArticle, [
            'is_admin' => $this->isGranted('ROLE_ADMIN'),
        ]);
        $formUserCreateArticle->handleRequest($request);

        if($formUserCreateArticle->isSubmitted())
        {
            $imageImported = $formUserCreateArticle->get('image')->getData();
            if ($imageImported) {
                $newImageName = $imageImporter->importImage($imageImported);
                $userArticle->setImage($newImageName);
            }

            if ($formUserCreateArticle->isValid()) {

                $entityManager->persist($userArticle);
                $entityManager->flush();

                $email = new Email();

                //mail template
                //give the template the view associate and the values
                $emailTemplate = $this->renderView('mails/article_submission.html.twig', ['article' => $userArticle]);

                //j'envoie avec mailer
                $mailer->send(
                    $email->from('noreply@accrochetvous.com')
                        ->to('contact@accrochetvous.com')
                        ->subject('Nouvel article soumis')
                        ->html($emailTemplate)
                );

                $this->addFlash("success", "Modèle envoyé aux administrateurs pour relecture ;)");
                return $this->redirectToRoute('user_current_profile');
            }
        }
        $formUserView = $formUserCreateArticle->createView();
        return $this->render('users/users_articles/submit.html.twig', ['formView'=>$formUserView]);
    }
}