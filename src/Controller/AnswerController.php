<?php

namespace App\Controller;

use App\Entity\Answer;
use App\Repository\AnswerRepository;
use App\Repository\QuestionRepository;
use App\Repository\UserRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AnswerController extends AbstractController
{


    /**
     * @Route("/answers", name="app_answer_index",methods="GET")
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
     * @Route("/answers/create",name="api_answer_create",methods="POST")
     * @return JsonResponse
     */
    public function create(Request $request, EntityManagerInterface $em, QuestionRepository $questionRepository, ValidatorInterface $validatorInterface): JsonResponse
    {
        if ($request->isMethod('POST')) {
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

            if (count($errors) === 0) {
                $em->persist($answer);
                $em->flush();
                $jsonData = [
                    'content' => $this->renderView('partials/question_headers/question_header_single_question.html.twig', ['answers' => [$answer]])
                ];
                return new JsonResponse($jsonData, 200);
            }else{
                foreach ($errors as $error) {
                    $this->addFlash('yellow', $error->getMessage());
                }
                return $this->redirectToRoute('app_question_show', ['question' => $question]);
            }
        }
    }

    /**
     * @Route("/answer/picture/add",name="api_answer_add_answer_picture",methods="POST")
     */
    public function addAnswerPicture(Request $request): JsonResponse
    {
        $file = $request->files->get('upload');
        $newName = $this->getUser()->getPseudonym() . '-' . uniqId() . '-' . $file->getClientOriginalName();
        $file->move($this->getParameter('pictures_directory'), $newName);
        $jsonData = [
            'uploaded' => true,
            'url' => '/images/' . $newName
        ];

        return new JsonResponse($jsonData,200);
    }

    /**
     * @Route("/api/answers/{id}/{action}", name="api_answer_handle_likes",methods="GET",requirements={"id"="\d+","action"="\b(liked)\b|\b(disliked)\b"})
     * @return JsonResponse
     */
    public function handleLikes(Answer $answer, string $action,EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();
        $hasLiked = in_array($user, $answer->getLikedUsers()->toArray());
        $hasDisliked = in_array($user, $answer->getDislikedUsers()->toArray());

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
        $em->flush();
        $jsonData = [
            'status' => '200',
            'answerId' => $answer->getId(),
            'likeNumber' => count($answer->getLikedUsers()),
            'dislikeNumber' => count($answer->getDislikedUsers())
        ];
        return new JsonResponse($jsonData,200);
    }
}
