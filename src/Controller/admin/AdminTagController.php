<?php

namespace App\Controller\admin;

use App\Entity\Tag;
use App\Form\TagType;
use App\Repository\TagRepository;
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
            return $this->redirectToRoute('admin_tags_list');
        }
        $formAdminView = $formAdminCreateTag->createView();

        return $this->render('admin/tags/create.html.twig', ['formView' => $formAdminView]);
    }

    #[Route(path: '/admin/tags/list', name: 'admin_tags_list', methods: ['POST', 'GET'])]
    #[IsGranted(new Expression('is_granted("ROLE_SUPER_ADMIN") or is_granted("ROLE_ADMIN")'))]
    public function adminTagsList(Request $request, EntityManagerInterface $entityManager, TagRepository $tagRepository): Response
    {
        $tags = $tagRepository->findAll();

        return $this->render('admin/tags/list.html.twig', ['tags' => $tags]);
    }

    #[Route(path: "/admin/tags/{id}/update", name: "admin_tag_update", requirements: ["id" => "\d+"], methods: ["GET", "POST"])]
    #[IsGranted(new Expression('is_granted("ROLE_SUPER_ADMIN") or is_granted("ROLE_ADMIN")'))]
    public function updateTag(int $id, TagRepository $tagRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        $tagToUpdate = $tagRepository->find($id);

        if (!$tagToUpdate) {
            $this->addFlash("error", "Cette étiquette n'existe pas :(");
            return $this->redirectToRoute("admin_tags_list");
        }

        $formTagToUpdate = $this->createForm(TagType::class, $tagToUpdate);

        $formTagToUpdate->handleRequest($request);

        if ($formTagToUpdate->isSubmitted() && $formTagToUpdate->isValid()) {

            $entityManager->persist($tagToUpdate);
            $entityManager->flush();

            $this->addFlash("success", "Modification effectuée avec succès");
            return $this->redirectToRoute("admin_tags_list");
        }
        $formView = $formTagToUpdate->createView();
        return $this->render("admin/tags/update.html.twig", ["formView" => $formView, "tagToUpdate" => $tagToUpdate]);
    }

    #[Route(path: "/admin/tag/{id}/delete", name: "admin_tag_delete", requirements: ['id' => '\d+'], methods: ["GET"])]
    #[IsGranted(new Expression('is_granted("ROLE_SUPER_ADMIN")'))]
    public function deleteArticle(int $id, TagRepository $tagRepository,
                                  EntityManagerInterface $entityManager): Response
    {
        $tagToDelete = $tagRepository->find($id);

        if (!$tagToDelete) {
            $this->addFlash('error', "Cette étiquette n'existe pas :(");
            return $this->redirectToRoute("admin_tags_list");
        }
        $entityManager->remove($tagToDelete);
        $entityManager->flush();

        $this->addFlash("success", "Etiquette supprimée !");
        return $this->redirectToRoute("admin_tags_list");
    }
}