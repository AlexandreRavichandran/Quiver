<?php

namespace App\Controller;

use DateTimeImmutable;
use App\Entity\Question;
use App\Repository\AnswerRepository;
use App\Repository\QuestionRepository;
use App\Repository\SpaceRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class QuestionController extends AbstractController
{
    private $em;
    private $questionRepository;

    public function __construct(EntityManagerInterface $em, QuestionRepository $questionRepository)
    {
        $this->em = $em;
        $this->questionRepository = $questionRepository;
    }

    /**
     * 
     * @Route("/questions/create",name="app_question_create",methods="POST")
     * 
     * @param Request $request Request sent to the method
     * @param ValidatorInterface $validator Validator to check if entity is correctly filled
     * 
     * @return Response
     */
    public function create(Request $request, ValidatorInterface $validator): Response
    {
        //Check if request method is correct
        if (!$request->isMethod('POST')) {

            $this->addFlash('errorMessage', "Une erreur s'est produite");

            return $this->redirectToRoute('app_home_index');
        }

        $csrfToken = $request->request->get('_csrf_token');

        //Check csrf
        if (!$this->isCsrfTokenValid('create_question', $csrfToken)) {
            $this->addFlash('errorMessage', 'le serveur a détecté une attaque CSRF et l\'operation a été abandonnée.');

            return $this->redirectToRoute('app_home_index');
        }

        $questionSentence = $request->request->get('question');
        $question = new Question;
        $question
            ->setQuestion(htmlspecialchars($questionSentence))
            ->setAuthor($this->getUser())
            ->setCreatedAt(new DateTimeImmutable());

        $errors = $validator->validate($question);

        //Check if created question objet is valid following the entity's contraint
        if (0 !== count($errors)) {

            foreach ($errors as $error) {
                $this->addFlash('errorMessage', $error->getMessage());
            }

            return $this->redirectToRoute('app_home_index');
        }

        $this->em->persist($question);
        $this->em->flush();
        $this->addFlash('successMessage', 'Votre question a bien été postée. Veuillez lier votre question à des espaces.');

        return $this->redirectToRoute('app_question_show', [
            'id' => $question->getId()
        ]);
    }


    /**
     *
     * @Route("/questions/{id}", name="app_question_show",methods="GET",requirements={"id"="\d+"})
     * 
     * @param Question $question Question object with the url given id
     * @param AnswerRepository $answerRepository Repository of the answer entity
     * 
     * @return Response
     */
    public function show(Question $question, AnswerRepository $answerRepository): Response
    {
        $date = new DateTimeImmutable();
        $questionSpace = $question->getSpace();

        //Get questions which are related to the same spaces as the question object
        $spaceIds = [];
        foreach ($questionSpace as $space) {
            $spaceIds[] = $space->getId();
        }
        if (!empty($spaceIds)) {
            $alternativeQuestions = $this->questionRepository->findAllQuestionsBySpaceNames($spaceIds, null, 5);
        } else {
            $alternativeQuestions = $this->questionRepository->findBy([], null, 5);
        }

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
     * 
     * @return Response
     */
    public function addSpace(Request $request, SpaceRepository $spaceRepository, QuestionRepository $questionRepository, EntityManagerInterface $em): Response
    {
        //Check if request method is correct
        if (!$request->isMethod('POST')) {
            $this->addFlash('errorMessage', "Une erreur s'est produite");

            return $this->redirectToRoute('app_home_index');
        }

        //Check csrf
        $csrfToken = $request->request->get('_csrf_token');
        if (!$this->isCsrfTokenValid('add_space', $csrfToken)) {
            $this->addFlash('errorMessage', 'le serveur a détecté une attaque CSRF et l\'operation a été abandonnée.');

            return $this->redirectToRoute('app_home_index');
        }

        $questionId = $request->request->get('questionId');
        $newSpacesIds = $request->request->all('spaces');

        $question = $questionRepository->find($questionId);

        //Initialise all question related spaces and add spaces given by the user
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

    /*****************  API REQUEST METHODS *****************/


    /**
     * 
     * @Route("/api/questions/{id}/generate/{date}", name="api_question_generate_more_answer",methods="GET",requirements={"id"="\d+","date"="\d{4}-\d{2}-\d{2}"})
     * 
     * @param Question $question Question object with the url given id
     * @param string|null $date Date of the last showed answer. date format must be YYYY-MM-DD
     * @param AnswerRepository $answerRepository Repository of the answer entity
     * 
     * @return Response
     */
    public function generateMoreAnswer(Question $question, string $date = null, AnswerRepository $answerRepository): Response
    {
        if (null === $date) {
            $date = new DateTimeImmutable();
        } else {
            $date = new DateTimeImmutable($date);
        }

        $answers = $answerRepository->findAnswersByQuestionId($question->getId(), $date, 3);
        $jsonData = [
            'content' => $this->renderView('partials/question_headers/question_header_single_question.html.twig', ['answers' => $answers])
        ];

        return new JsonResponse($jsonData);
    }

    /**
     * 
     * @Route("/api/questions/generate/{date}", name="api_generate_more_question_and_answer",methods="GET",requirements={"date"="\d{4}-\d{2}-\d{2}"})
     * 
     * @param string|null $date  Date of the last showed answer. date format must be YYYY-MM-DD
     * 
     * @return JsonResponse
     */
    public function generateMoreQuestionsAndAnswers(string $date = null): JsonResponse
    {

        if (null === $date) {
            $date = new DateTimeImmutable();
        } else {
            $date = new DateTimeImmutable($date);
        }

        $questions = $this->questionRepository->findAllQuestionsWithAnswers($date, 3);

        $jsonData = [
            'content' => $this->renderView(
                'partials/question_headers/question_header_full.html.twig',
                ['questions' => $questions]
            )
        ];

        return new JsonResponse($jsonData);
    }
}
