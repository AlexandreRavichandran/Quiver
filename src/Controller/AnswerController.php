<?php

namespace App\Controller;

use App\Entity\Answer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AnswerController extends AbstractController
{
    /**
     * @Route("/answer", name="app_answer_index")
     */
    public function index(): Response
    {
        return $this->render(
            'answer/index.html.twig',
            ['page' => 'answer']
        );
    }

    /**
     * 
     * @Route("/answers/create",name="app_answer_create")
     * @return Response
     */
    public function create(EntityManagerInterface $em): Response
    {
        $form = $this->createForm(Answer::class);
        if ($form->isSubmitted() && $form->isValid()) {
            $answer = new Answer;
            $answer = $form->getData();
            $em->persist($answer);
            $em->flush();
            return $this->redirectToRoute('app_question_show', [
                'id' => $answer->getQuestion->getId()
            ]);
        }

        return $this->render('partials/forms/_answer_create_form.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
