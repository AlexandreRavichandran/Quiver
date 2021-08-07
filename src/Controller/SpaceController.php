<?php

namespace App\Controller;

use App\Entity\Space;
use App\Repository\QuestionRepository;
use App\Repository\SpaceRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SpaceController extends AbstractController
{
    /**
     * Create & discover spaces
     * @Route("/spaces", name="app_space_index")
     */
    public function index(SpaceRepository $spaceRepository): Response
    {
        $spaces = $spaceRepository->findAll();
        return $this->render(
            'space/index.html.twig',
            [
                'page' => 'space',
                'spaces' => $spaces
            ]
        );
    }

    /**
     * Show questions with user's following spaces
     * @Route("/following",name="app_space_following")
     * @return Response
     */
    public function following(SpaceRepository $spaceRepository, QuestionRepository $questionRepository): Response
    {
        $questions = $questionRepository->findAll();
        $spaces = $spaceRepository->findBy([], null, 8);
        return $this->render(
            'space/following.html.twig',
            [
                'page' => 'following',
                'spaces' => $spaces,
                'questions' => $questions
            ]
        );
    }

    /**
     * Show one space and its related questions
     * @Route("/spaces/{id}",name="app_space_show")
     * @return Response
     */
    public function show(Space $space, SpaceRepository $spaceRepository): Response
    {
        $spaces = $spaceRepository->findBy([], null, 8);
        $questions = $space->getQuestions();
        return $this->render(
            'space/show.html.twig',
            [
                'partial' => 'index',
                'spaces' => $spaces,
                'space' => $space,
                'questions' => $questions
            ]
        );
    }

    /**
     * Show one space and its related questions
     * @Route("/spaces/{id}/top_questions",name="app_space_show_top_question")
     * @return Response
     */
    public function showTopQuestions(Space $space, SpaceRepository $spaceRepository): Response
    {
        $spaces = $spaceRepository->findBy([], null, 8);
        $questions = $space->getQuestions();
        return $this->render(
            'space/show.html.twig',
            [
                'partial' => 'topQuestions',
                'spaces' => $spaces,
                'space' => $space,
                'questions' => $questions
            ]
        );
    }
}
