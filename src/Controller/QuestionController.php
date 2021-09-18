<?php

namespace App\Controller;

use App\Entity\Question;
use App\Form\QuestionType;
use App\Repository\AnswerRepository;
use App\Repository\QuestionRepository;
use App\Repository\SpaceRepository;
use App\Repository\UserRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Validator\Constraints\Json;

class QuestionController extends AbstractController
{

    /**
     * 
     * @Route("/questions/create",name="app_question_create",methods="POST")
     * @return Response
     */
    public function create(EntityManagerInterface $em, Request $request, ValidatorInterface $validator, UserRepository $user): Response
    {
        if ($request->isMethod('POST')) {
            $questionSentence = $request->request->get('question');
            $question = new Question;
            $question->setQuestion(htmlspecialchars($questionSentence));
            $question->setAuthor($this->getUser())
                ->setCreatedAt(new DateTimeImmutable());

            //Check if created question objet is valid following the entity's contraint
            $errors = $validator->validate($question);

            if (count($errors) === 0) {

                $em->persist($question);
                $em->flush();
                $this->addFlash('successMessage', 'Votre question a bien été postée. Veuillez lier votre question à des espaces.');
                $redirectRoute =  $this->redirectToRoute('app_question_show', [
                    'id' => $question->getId()
                ]);
            } else {
                //Display error messages
                foreach ($errors as $error) {
                    $this->addFlash('errorMessage', $error->getMessage());
                    $redirectRoute = $this->redirectToRoute('app_home_index');
                }
            }

            return $redirectRoute;
        }
    }

    /**
     * @Route("/questions/{id}", name="app_question_show",methods="GET",requirements={"id"="\d+"})
     */
    public function show(Question $question, QuestionRepository $questionRepository, AnswerRepository $answerRepository, SpaceRepository $spaceRepository): Response
    {
        $date = new DateTimeImmutable();
        $alternativeQuestions = $questionRepository->findBy([], null, 5);
        $answers = $answerRepository->findAnswersByQuestionId($question->getId(), $date, 3);

        return $this->render('question/show.html.twig', [
            'question' => $question,
            'answers' => $answers,
            'alternative_questions' => $alternativeQuestions
        ]);
    }

    /**
     * 
     * @Route("/question/spaces/add",name="app_question_add_space",methods="POST")
     * @return Response
     */
    public function addSpace(Request $request, SpaceRepository $spaceRepository, QuestionRepository $questionRepository, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            $questionId = $request->request->get('questionId');
            $newSpacesIds = $request->request->all('spaces');

            $question = $questionRepository->find($questionId);

            $question->removeallSpace();
            foreach ($newSpacesIds as $spaceId) {
                $space = $spaceRepository->find($spaceId);
                $question->addSpace($space);
                $em->persist($question);
            }

            $em->flush();
            $this->addFlash('successMessage', 'Votre modification a été pris en compte.');

            return $this->redirectToRoute('app_question_show', ['id' => $questionId]);
        }
    }

    /*****************  API REQUEST METHODS *****************/


    /**
     * @Route("/questions/{id}/generate/{date}", name="api_question_generate_more_answer",methods="GET",requirements={"id"="\d+","date"="\d{4}-\d{2}-\d{2}"})
     */
    public function generateMoreAnswer(Question $question, string $date, AnswerRepository $answerRepository): Response
    {
        $date = new DateTimeImmutable($date);
        $answers = $answerRepository->findAnswersByQuestionId($question->getId(), $date, 3);
        $jsonData = [
            'content' => $this->renderView('partials/question_headers/question_header_single_question.html.twig', ['answers' => $answers])
        ];

        return new JsonResponse($jsonData);
    }

    /**
     * 
     * @Route("/questions/generate/{date}", name="api_generate_more_question_and_answer",methods="GET",requirements={"date"="\d{4}-\d{2}-\d{2}"})
     * @return JsonResponse
     */
    public function generateMoreQuestionsAndAnswers(string $date, QuestionRepository $questionRepository): JsonResponse
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
