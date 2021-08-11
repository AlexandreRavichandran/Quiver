<?php

namespace App\Controller;

use App\Entity\Space;
use App\Form\SpaceType;
use App\Repository\SpaceRepository;
use App\Repository\QuestionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="app_home")
     * @return Response
     */
    public function index(SpaceRepository $spaceRepository, QuestionRepository $questionRepository, Request $request, EntityManagerInterface $em): Response
    {
        $questions = $questionRepository->findBy([], null, 4);
        $spaces = $spaceRepository->findBy([], null, 8);

        $spaceForm = $this->createForm(SpaceType::class);
        $spaceForm->handleRequest($request);
        if ($spaceForm->isSubmitted() && $spaceForm->isValid()) {
            $space = new Space;
            $space = $spaceForm->getData();
            $em->persist($space);
            $em->flush();
            return $this->redirectToRoute('app_space_show', [
                'id' => $space->getId()
            ]);
        }
        return $this->render(
            'home/index.html.twig',
            [
                'page' => 'home',
                'questions' => $questions,
                'spaces' => $spaces,
                'spaceForm' => $spaceForm->createView()
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
