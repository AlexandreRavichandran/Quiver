<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SpaceController extends AbstractController
{
    /**
     * @Route("/spaces", name="app_space_index")
     */
    public function index(): Response
    {
        return $this->render('space/index.html.twig');
    }
}
