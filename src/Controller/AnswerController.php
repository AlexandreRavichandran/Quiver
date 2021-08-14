<?php

namespace App\Controller;

use App\Entity\Answer;
use App\Repository\AnswerRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

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
