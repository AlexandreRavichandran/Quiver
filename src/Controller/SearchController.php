<?php

namespace App\Controller;

use App\Repository\AnswerRepository;
use App\Repository\QuestionRepository;
use App\Repository\SpaceRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends AbstractController
{
    private $questionRepository;
    private $answerRepository;
    private $spaceRepository;
    private $userRepository;

    public function __construct(QuestionRepository $questionRepository, AnswerRepository $answerRepository, SpaceRepository $spaceRepository, UserRepository $userRepository)
    {
        $this->questionRepository = $questionRepository;
        $this->answerRepository = $answerRepository;
        $this->spaceRepository = $spaceRepository;
        $this->userRepository = $userRepository;
    }
    /**
     * @Route("/search", name="search_all")
     */
    public function all(Request $request): Response
    {
        $query = $request->query->get('q');
        $questionsByQuery = $this->questionRepository->findQuestionsByQuery($query);
        $answersByquery = $this->answerRepository->findAnswersByQuery($query);
        $spacesByQuery = $this->spaceRepository->findSpacesByQuery($query);
        $usersByQuery = $this->userRepository->findUsersByQuery($query);
        return $this->render('search/index.html.twig', [
            'type' => 'all',
            'query' => $query,
            'questions' => $questionsByQuery,
            'answers' => $answersByquery,
            'spaces' => $spacesByQuery,
            'users' => $usersByQuery,
        ]);
    }

    /**
     * @Route("/search/question", name="search_by_question")
     */
    public function byQuestion(Request $request): Response
    {
        $query = $request->query->get('q');
        $questionsByQuery = $this->questionRepository->findQuestionsByQuery($query);

        return $this->render('search/index.html.twig', [
            'type' => 'question',
            'query' => $query,
            'questions' => $questionsByQuery,
        ]);
    }

    /**
     * @Route("/search/answer", name="search_by_answer")
     */
    public function byAnswer(Request $request): Response
    {
        $query = $request->query->get('q');
        $answersByquery = $this->answerRepository->findAnswersByQuery($query);
        return $this->render('search/index.html.twig', [
            'type' => 'answer',
            'query' => $query,
            'answers' => $answersByquery,
        ]);
    }

    /**
     * @Route("/search/profile", name="search_by_profile")
     */
    public function byProfile(Request $request): Response
    {
        $query = $request->query->get('q');
        $usersByQuery = $this->userRepository->findUsersByQuery($query);
        return $this->render('search/index.html.twig', [
            'type' => 'profile',
            'query' => $query,
            'users' => $usersByQuery,
        ]);
    }
    /**
     * @Route("/search/space", name="search_by_space")
     */
    public function bySpace(Request $request): Response
    {
        $query = $request->query->get('q');
        $spacesByQuery = $this->spaceRepository->findSpacesByQuery($query);
        return $this->render('search/index.html.twig', [
            'type' => 'space',
            'query' => $query,
            'spaces' => $spacesByQuery,
        ]);
    }
}
