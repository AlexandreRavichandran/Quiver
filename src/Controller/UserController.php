<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    /**
     * @Route("/profile", name="app_user_profile")
     */
    public function index(): Response
    {
        return $this->render('user/index.html.twig', [
            'partial' => 'profile'
        ]);
    }

    /**
     * @Route("/profile/answers", name="app_user_profile_answer")
     */
    public function answers(): Response
    {
        return $this->render('user/index.html.twig', [
            'partial' => 'answer'
        ]);
    }

    /**
     * @Route("/profile/questions", name="app_user_profile_question")
     */
    public function questions(): Response
    {
        return $this->render('user/index.html.twig', [
            'partial' => 'question'
        ]);
    }

    /**
     * @Route("/profile/subscribers", name="app_user_profile_subscriber")
     */
    public function subscribers(): Response
    {
        return $this->render('user/index.html.twig', [
            'partial' => 'subscriber'
        ]);
    }

    /**
     * @Route("/profile/subscriptions", name="app_user_profile_subscription")
     */
    public function subscriptions(): Response
    {
        return $this->render('user/index.html.twig', [
            'partial' => 'subscription'
        ]);
    }
}
