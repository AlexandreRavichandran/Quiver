<?php

namespace App\Controller;

use App\Entity\Question;
use App\Form\QuestionType;
use App\Repository\QuestionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class QuestionController extends AbstractController
{
    /**
     * 
     * @Route("/questions/create",name="app_question_create")
     * @return Response
     */
    public function create(EntityManagerInterface $em): Response
    {
        $form = $this->createForm(QuestionType::class);

        if ($form->isSubmitted() && $form->isValid()) {
            $question = new Question;
            $question = $form->getData();
            $em->persist($question);
            $em->flush();
            return $this->redirectToRoute('app_home');
        }

        return $this->render('partials/form/_question_create_form.html.twig', [
            'form' => $form->createView()
        ]);
    }


    /**
     * @Route("/questions/{id}", name="app_question_show")
     */
    public function index(Question $question, QuestionRepository $questionRepository): Response
    {
        $alternativeQuestions = $questionRepository->findBy([], null, 5);
        return $this->render('question/show.html.twig', [
            'question' => $question,
            'alternativeQuestions' => $alternativeQuestions
        ]);
    }
}
