<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

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

    /**
     * @Route("/login/user/generate",name="app_login_generate")
     * @return JsonResponse
     */
    public function generateUser(UserRepository $userRepository): JsonResponse
    {
        $allUsers = $userRepository->findAll();
        $randomUser = $allUsers[mt_rand(0, count($allUsers) - 1)];

        $jsonData = [
            'email' => $randomUser->getEmail()
        ];
        return new JsonResponse($jsonData);
    }

    /**
     *
     * @Route("/profile/qualification/update",name="app_user_qualification_update")
     * @param Request $request
     * @return JsonResponse
     */
    public function updateQualification(Request $request, EntityManagerInterface $em, ValidatorInterface $validatorInterface):JsonResponse
    {
        $datas = json_decode($request->getContent());
        $user = $this->getUser();
        $newQualification = $datas->newQualification;
        $user->setQualification($newQualification);
        $errors = $validatorInterface->validate($user);

        if (count($errors) === 0) {
            $em->persist($user);
            $em->flush();
            $responseCode = 200;
            $jsonData = ['newQualification'=>$newQualification];
        } else {
            $responseCode = 401;
            $jsonData = [];
        }

        return new JsonResponse($jsonData, $responseCode);
    }

        /**
     *
     * @Route("/profile/description/update",name="app_user_description_update")
     * @param Request $request
     * @return JsonResponse
     */
    public function updateDescription(Request $request, EntityManagerInterface $em, ValidatorInterface $validatorInterface):JsonResponse
    {
        $datas = json_decode($request->getContent());
        $user = $this->getUser();
        $newDescription = $datas->newDescription;
        $user->setDescription($newDescription);
        $errors = $validatorInterface->validate($user);

        if (count($errors) === 0) {
            $em->persist($user);
            $em->flush();
            $responseCode = 200;
            $jsonData = ['newQualification'=>$newDescription];
        } else {
            $responseCode = 401;
            $jsonData = [];
        }

        return new JsonResponse($jsonData, $responseCode);
    }
}
