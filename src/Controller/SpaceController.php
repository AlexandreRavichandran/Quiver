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
        return $this->render('space/index.html.twig');
    }

    /**
     * Show questions with user's following spaces
     * @Route("/following",name="app_space_following")
     * @return Response
     */
    public function following(): Response
    {
        return $this->render('space/following.html.twig');
    }

    /**
     * Show one space and its related questions
     * @Route("/spaces/3")
     * @return Response
     */
    public function show(): Response
    {
        return $this->render('space/show.html.twig');
    }
}
