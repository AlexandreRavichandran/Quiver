<?php

namespace App\Controller;

use App\Entity\SubComment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SubCommentController extends AbstractController
{
    /**
     * @Route("/sub_comment/create", name="app_sub_comment_create")
     */
    public function create(EntityManagerInterface $em): Response
    {
        $form = $this->createForm(SubCommentType::class);
        if ($form->isSubmitted() && $form->isValid()) {
            $subComment = new SubComment;
            $subComment = $form->getData();
            $em->persist($subComment);
            $em->flush();
            return $this->redirectToRoute('app_question_show', [
                'id' => $subComment->getComment()->getAnswer()->getQuestion()->getId()
            ]);
        }
        return $this->render('partials/forms/_sub_comment_create_form.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
