<?php

namespace App\Controller;

use App\Repository\QuestionRepository;
use App\Repository\SpaceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="app_home")
     * @return Response
     */
    public function index(SpaceRepository $spaceRepository, QuestionRepository $questionRepository): Response
    {
        $questions = $questionRepository->findBy([], null, 4);
        $spaces = $spaceRepository->findBy([], null, 8);
        return $this->render(
            'home/index.html.twig',
            [
                'page' => 'home',
                'questions' => $questions,
                'spaces' => $spaces
            ]
        );
    }

    /**
     * @Route("/about", name="app_about")
     * @return Response
     */
    public function about(): Response
    {
        return $this->render('home/about.html.twig');
    }

    /**
     * @Route("/impressum", name="app_impressum")
     * @return Response
     */
    public function impressum(): Response
    {
        return $this->render('home/impressum.html.twig');
    }
}
