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

class SpaceController extends AbstractController
{
    /**
     * Create & discover spaces
     * @Route("/spaces", name="app_space_index")
     */
    public function index(SpaceRepository $spaceRepository, Request $request, EntityManagerInterface $em): Response
    {
        $spaces = $spaceRepository->findAll();

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
            'space/index.html.twig',
            [
                'page' => 'space',
                'spaces' => $spaces,
                'spaceForm' => $spaceForm->createView()
            ]
        );
    }

    /**
     * Show questions with user's following spaces
     * @Route("/following",name="app_space_following")
     * @return Response
     */
    public function following(SpaceRepository $spaceRepository, QuestionRepository $questionRepository, Request $request, EntityManagerInterface $em): Response
    {
        $questions = $questionRepository->findAll();
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
            'space/following.html.twig',
            [
                'page' => 'following',
                'spaces' => $spaces,
                'questions' => $questions,
                'spaceForm' => $spaceForm->createView()
            ]
        );
    }

    /**
     * Show one space and its related questions
     * @Route("/spaces/{id}",name="app_space_show")
     * @return Response
     */
    public function show(Space $space, SpaceRepository $spaceRepository, Request $request, EntityManagerInterface $em): Response
    {
        $spaces = $spaceRepository->findBy([], null, 8);
        $questions = $space->getQuestions();

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
            'space/show.html.twig',
            [
                'partial' => 'index',
                'spaces' => $spaces,
                'space' => $space,
                'questions' => $questions,
                'spaceForm' => $spaceForm->createView()
            ]
        );
    }

    /**
     * Show one space and its related questions
     * @Route("/spaces/{id}/top_questions",name="app_space_show_top_question")
     * @return Response
     */
    public function showTopQuestions(Space $space, SpaceRepository $spaceRepository, Request $request, EntityManagerInterface $em): Response
    {
        $spaces = $spaceRepository->findBy([], null, 8);
        $questions = $space->getQuestions();

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
            'space/show.html.twig',
            [
                'partial' => 'topQuestions',
                'spaces' => $spaces,
                'space' => $space,
                'questions' => $questions,
                'spaceForm' => $spaceForm->createView()
            ]
        );
    }
}
