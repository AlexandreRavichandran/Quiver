<?php

namespace App\Controller;

use App\Entity\Question;
use App\Form\QuestionType;
use App\Repository\QuestionRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

class QuestionController extends AbstractController
{
    /**
     * 
     * @Route("/questions/create",name="app_question_create")
     * @return Response
     */
    public function create(EntityManagerInterface $em, Request $request, ValidatorInterface $validator, UserRepository $user): Response
    {
        if ($request->isMethod('POST')) {
            $questionSentence = $request->request->get('question');
            $question = new Question;
            $question->setQuestion(htmlspecialchars($questionSentence));
            $question->setAuthor($this->getUser());

            //Check if created question objet is valid following the entity's contraint
            $errors = $validator->validate($question);
            if (count($errors) === 0) {
                $em->persist($question);
                $em->flush();
                return $this->redirectToRoute('app_question_show', [
                    'id' => $question->getId()
                ]);
            }

            //Check if the referer route is valid
            try {
                $redirectRoute =  $this->generateUrl($request->request->get('referer'));
                $redirectRoute = $request->request->get('referer');
            } catch (RouteNotFoundException $e) {
                $redirectRoute = 'app_home';
            }

            //Display error messages
            foreach ($errors as $error) {
                $this->addFlash('yellow', $error->getMessage());
            }
        }
        return $this->redirectToRoute($redirectRoute);
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

    /**
     * 
     * @Route("/questions/generate/{date}", name="question_generate_AJAX")
     * @return JsonResponse
     */
    public function getMoreQuestionsAndAnswers(string $date, QuestionRepository $questionRepository): JsonResponse
    {
        $questions = $questionRepository->findAllQuestionsWithAnswers($date, 3);

        $jsonData = [
            'content' => $this->renderView(
                'partials/question_headers/question_header_full.html.twig',
                ['questions' => $questions]
            )
        ];

        return new JsonResponse($jsonData);
    }
}
