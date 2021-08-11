<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Form\CommentType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CommentController extends AbstractController
{
    /**
     * @Route("/comment", name="comment")
     */
    public function index(): Response
    {
        return $this->render('comment/index.html.twig', [
            'controller_name' => 'CommentController',
        ]);
    }

    /**
     * @Route("/comments/create", name="app_comment_create")
     */
    public function create(EntityManagerInterface $em): Response
    {
        $form = $this->createForm(CommentType::class);

        if ($form->isSubmitted() && $form->isValid()) {
            $comment = new Comment;
            $comment = $form->getData();
            $em->persist($comment);
            $em->flush();
            return $this->redirectToRoute('app_question_show', [
                'id' => $comment->getQuestion->getId()
            ]);
        }
        return $this->render('partials/forms/_comment_create_form.html.twig', [
            'form' => $form
        ]);
    }
}
