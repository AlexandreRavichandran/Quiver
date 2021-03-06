<?php

namespace App\Controller;

use App\Entity\Space;
use App\Form\SpaceType;
use App\Repository\SpaceRepository;
use App\Repository\QuestionRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class HomeController extends AbstractController
{
    /**
     * 
     * @Route("/", name="app_home_index",methods="GET")
     * @param SpaceRepository $spaceRepository Repository of space entity
     * @param QuestionRepository $questionRepository Repository of question entity
     * @return Response
     */
    public function index(SpaceRepository $spaceRepository, QuestionRepository $questionRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER', null, 'Veuillez vous connecter');
        $date = new DateTime();
        $questions = $questionRepository->findAllQuestionsWithAnswers($date->format('Y-m-d'), 3);
        $spaces = $spaceRepository->findBy([], null, 8);

        return $this->render(
            'home/index.html.twig',
            [
                'page' => 'home',
                'questions' => $questions,
                'spaces' => $spaces,
            ]
        );
    }

    /**
     * @Route("/about", name="app_about",methods="GET")
     * @return Response
     */
    public function about(): Response
    {
        return $this->render('home/about.html.twig');
    }

    /**
     * @Route("/impressum", name="app_impressum",methods="GET")
     * @return Response
     */
    public function impressum(): Response
    {
        return $this->render('home/impressum.html.twig');
    }
}
