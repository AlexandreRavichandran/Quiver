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
     * @Route("/answers/{id}/{action}", name="app_user_manage_likes")
     * @return JsonResponse
     */
    public function handleLikes(Answer $answer, $action, Request $request, AnswerRepository $answerRepository, UserRepository $userRepository, EntityManagerInterface $em): JsonResponse
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
        return new JsonResponse($jsonData);
    }

    /**
     * @Route("/answers", name="app_answer_index")
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
                    'content' => $this->renderView('partials/question_headers/question_header_singleQuestion.html.twig', ['answers' => [$answer]])
                ];
                return new JsonResponse($jsonData, 200);

                foreach ($errors as $error) {
                    $this->addFlash('yellow', $error->getMessage());
                    return $this->redirectToRoute('app_question_show', ['question' => $question]);
                }
            }
        }
    }
}
