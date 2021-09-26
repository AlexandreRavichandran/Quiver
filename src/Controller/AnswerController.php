<?php

namespace App\Controller;

use DateTimeImmutable;
use App\Entity\Answer;
use App\Repository\QuestionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AnswerController extends AbstractController
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @Route("/answers", name="app_answer_index",methods="GET")
     * 
     * @param QuestionRepository $questionRepository Repository of the Question entity
     *
     * @return Response
     */
    public function index(QuestionRepository $questionRepository): Response
    {
        $user = $this->getUser();
        $userSpaces = [];
        foreach ($user->getSubscribedSpaces() as $space) {
            $userSpaces[] = $space->getId();
        }
        if (count($userSpaces) > 0) {
            $userQuestions = $questionRepository->findAllQuestionsBySpaceNames($userSpaces, null, null);
        } else {
            $userQuestions = [];
        }

        return $this->render(
            'answer/index.html.twig',
            [
                'page' => 'answer',
                'questions' => $userQuestions
            ]
        );
    }



    /*****************  API REQUEST METHODS *****************/



    /**
     * 
     * @Route("/api/answers/create",name="api_answer_create",methods="POST")
     * 
     * @param Request $request Request sent to this method
     * @param QuestionRepository $questionRepository Repository of the Question entity
     * @param ValidatorInterface $validatorInterface Validator to check if entity is correctly filled
     * 
     * @return JsonResponse
     */
    public function create(Request $request, QuestionRepository $questionRepository, ValidatorInterface $validatorInterface): JsonResponse
    {
        //Check if HTTP request is correct
        if (!$request->isMethod('POST')) {
            $this->addFlash('errorMessage', 'Une erreur s\'est produite.');

            return $this->redirectToRoute('app_index_home');
        }

        $datas = json_decode($request->getContent());
        $question = $questionRepository->findOneBy(['id' => $datas->questionId]);
        $answer = new Answer;
        $answer
            ->setAnswer($datas->answer)
            ->setAuthor($this->getUser())
            ->setCreatedAt(new DateTimeImmutable())
            ->setViewsNumber(0)
            ->setQuestion($question);

        $errors = $validatorInterface->validate($answer);

        //Check if there is any error on creating answer
        if (0 !== count($errors)) {

            //Prepare datas for error alert message
            $responseCode = 400;
            $label = 'errorMessage';
            foreach ($errors as $key => $error) {
                $message[$key] = $this->renderView('partials/_alert_message.html.twig', ['message' => $error->getMessage(), 'label' => $label]);
            }
            $jsonData['message'] = $message;

            return new JsonResponse($jsonData, $responseCode);
        }
        $this->em->persist($answer);
        $this->em->flush();

        $responseCode = 201;
        $label = 'successMessage';
        $messageText = 'Votre réponse a été postée avec succès.';
        $message = $this->renderView('partials/_alert_message.html.twig', ['message' => $messageText, 'label' => $label]);
        $jsonData = [
            'content' => $this->renderView('partials/question_headers/question_header_single_question.html.twig', ['answers' => [$answer]]),
        ];

        $jsonData['message'] = $message;

        return new JsonResponse($jsonData, $responseCode);
    }

    /**
     * Add a picture for all CK editor (used to create an answer)
     * Pictures are stored in /public/images/answer_pictures
     * 
     * @Route("/api/answer/picture/add",name="api_answer_add_answer_picture",methods="POST")
     * 
     * @param Request $request Request sent to this method
     * 
     * @return JsonResponse
     */
    public function addAnswerPicture(Request $request): JsonResponse
    {
        $file = $request->files->get('upload');
        $newName = $this->getUser()->getPseudonym() . '-' . uniqId() . '-' . $file->getClientOriginalName();
        $file->move($this->getParameter('answer_pictures_directory'), $newName);

        $jsonData = [
            'uploaded' => true,
            'url' => '/images/answer_pictures/' . $newName
        ];

        return new JsonResponse($jsonData);
    }

    /**
     * Handle answer's like and dislike actions
     * 
     * @Route("/api/answers/{id}/{action}", name="api_answer_handle_likes",methods="GET",requirements={"id"="\d+","action"="\b(liked)\b|\b(disliked)\b"})
     * 
     * @param Answer $answer Current Answer object being liked or disliked
     * @param string $action Action to do with this answer (Only 2 values : 'liked' and 'disliked')
     * 
     * @return JsonResponse
     */
    public function handleLikes(Answer $answer, string $action): JsonResponse
    {

        // Check if action is correct
        $actions = ['liked', 'disliked'];
        if (!in_array($action, $actions)) {
            $responseCode = 401;
            $label = 'errorMessage';
            $messageText = "Une erreur s'est produite.";
            $jsonData = [
                'content' => $this->renderView('partials/_alert_message.html.twig', ['message' => $messageText, 'label' => $label])
            ];

            return new JsonResponse($jsonData, $responseCode);
        }

        $user = $this->getUser();
        $hasLiked = in_array($user, $answer->getLikedUsers()->toArray());
        $hasDisliked = in_array($user, $answer->getDislikedUsers()->toArray());

        /**
         * Following if the user has already liked or disliked the answer, an action will be made
         */
        if ($action === 'liked') {
            if ($hasLiked) {
                $answer->removeLikedUser($user);
            } else {
                $answer->addLikedUser($user);
                $answer->removeDislikedUser($user);
            }
        } else {
            if ($hasDisliked) {
                $answer->removeDislikedUser($user);
            } else {
                $answer->addDislikedUser($user);
                $answer->removeLikedUser($user);
            }
        }

        $this->em->flush();

        $jsonData = [
            'answerId' => $answer->getId(),
            'likeNumber' => count($answer->getLikedUsers()),
            'dislikeNumber' => count($answer->getDislikedUsers())
        ];

        return new JsonResponse($jsonData, 200);
    }
}
