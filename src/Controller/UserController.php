<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class UserController extends AbstractController
{
    /**
     * @Route("/profile/{pseudonym}", name="app_user_profile")
     */
    public function index(User $user): Response
    {

        return $this->render('user/index.html.twig', [
            'partial' => 'profile',
            'user' => $user
        ]);
    }

    /**
     * @Route("/profile/{pseudonym}/answers", name="app_user_profile_answer")
     */
    public function answers(User $user): Response
    {
        return $this->render('user/index.html.twig', [
            'partial' => 'answer',
            'user' => $user
        ]);
    }

    /**
     * @Route("/profile/{pseudonym}/questions", name="app_user_profile_question")
     */
    public function questions(User $user): Response
    {
        return $this->render('user/index.html.twig', [
            'partial' => 'question',
            'user' => $user
        ]);
    }

    /**
     * @Route("/profile/{pseudonym}/subscribers", name="app_user_profile_subscriber")
     */
    public function subscribers(User $user): Response
    {
        return $this->render('user/index.html.twig', [
            'partial' => 'subscriber',
            'user' => $user
        ]);
    }

    /**
     * @Route("/profile/{pseudonym}/subscriptions", name="app_user_profile_subscription")
     */
    public function subscriptions(User $user): Response
    {
        
        return $this->render('user/index.html.twig', [
            'partial' => 'subscription',
            'user' => $user
        ]);
    }

    /**
     * 
     * @Route("/profile/{id}/subscribers/{action}")
     * @return JsonResponse
     */
    public function addSubscriber(User $user, string $action, UserRepository $userRepository, EntityManagerInterface $em): JsonResponse
    {
        $userToSubcribeWith = $this->getUser();
        $userToSubscribe = $userRepository->findOneBy(['id' => $user->getId()]);

        $isSubscribed = in_array($userToSubcribeWith, $userToSubscribe->getSubscribers()->toArray());
        if ($action === 'add') {
            if ($isSubscribed) {
                $userToSubscribe->removeSubscriber($userToSubcribeWith);
            } else {
                $userToSubscribe->addSubscriber($userToSubcribeWith);
            }
        } else {
            $userToSubscribe->removeSubscriber($userToSubcribeWith);
        }

        $em->flush();

        $jsonData = [
            'subscriberNumber' => count($userToSubscribe->getSubscribers()),
            'subscriptionNumber' => count($userToSubcribeWith->getSubscriptions()),
        ];
        return new JsonResponse($jsonData);
    }
}