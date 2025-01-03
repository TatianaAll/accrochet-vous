<?php

namespace App\Controller\admin;

use App\Entity\Tag;
use App\Form\TagType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\ExpressionLanguage\Expression;

class AdminTagController extends AbstractController
{
    #[Route(path: '/admin/tag/create', name: 'admin_tag_create', methods: ['POST', 'GET'])]
    #[IsGranted(new Expression('is_granted("ROLE_SUPER_ADMIN") or is_granted("ROLE_ADMIN")'))]
    public function createArticle(Request       $request, EntityManagerInterface $entityManager): Response
    {
        //new instance of tag
        $tag = new Tag();

        //calling the form
        $formAdminCreateTag = $this->createForm(TagType::class, $tag);
        $formAdminCreateTag->handleRequest($request);

        if ($formAdminCreateTag->isSubmitted() && $formAdminCreateTag->isValid()) {

            $entityManager->persist($tag);
            $entityManager->flush();

            $this->addFlash("success", "Tag crée ;)");
            return $this->redirectToRoute('admin_articles_list');
        }
        $formAdminView = $formAdminCreateTag->createView();

        return $this->render('admin/tags/create.html.twig', ['formView' => $formAdminView]);
    }
}