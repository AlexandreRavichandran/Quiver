<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SpaceController extends AbstractController
{
    /**
     * Create & discover spaces
     * @Route("/spaces", name="app_space_index")
     */
    public function index(): Response
    {
        return $this->render(
            'space/index.html.twig',
            ['page' => 'space']
        );
    }

    /**
     * Show questions with user's following spaces
     * @Route("/following",name="app_space_following")
     * @return Response
     */
    public function following(): Response
    {
        return $this->render(
            'space/following.html.twig',
            ['page' => 'following']
        );
    }

    /**
     * Show one space and its related questions
     * @Route("/spaces/3",name="app_space_show")
     * @return Response
     */
    public function show(): Response
    {
        return $this->render(
            'space/show.html.twig',
            ['partial' => 'index']
        );
    }

    /**
     * Show one space and its related questions
     * @Route("/spaces/3/top_questions",name="app_space_show_top_question")
     * @return Response
     */
    public function showTopQuestions(): Response
    {
        return $this->render(
            'space/show.html.twig',
            ['partial' => 'topQuestions']
        );
    }
}
