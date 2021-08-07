<?php

namespace App\Controller;

use App\Entity\Question;
use App\Repository\QuestionRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class QuestionController extends AbstractController
{
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
