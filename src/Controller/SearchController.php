<?php

namespace App\Controller;

use App\Repository\AnswerRepository;
use App\Repository\QuestionRepository;
use App\Repository\SpaceRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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
        $queryResults = array_merge($spacesByQuery, $usersByQuery, $questionsByQuery, $answersByquery);
        $queryResults = array_slice($queryResults, 0, 10);
        return $this->render('search/index.html.twig', [
            'type' => 'all',
            'query_results' => $queryResults,
            'query' => $query,
        ]);
    }

    /**
     * @Route("/search/question", name="search_by_question")
     */
    public function byQuestion(Request $request): Response
    {
        $query = $request->query->get('q');
        $queryResults = $this->questionRepository->findQuestionsByQuery($query);
        $queryResults = array_slice($queryResults, 0, 10);
        return $this->render('search/index.html.twig', [
            'type' => 'question',
            'query' => $query,
            'query_results' => $queryResults,
        ]);
    }

    /**
     * @Route("/search/answer", name="search_by_answer")
     */
    public function byAnswer(Request $request): Response
    {
        $query = $request->query->get('q');
        $queryResults = $this->answerRepository->findAnswersByQuery($query);
        $queryResults = array_slice($queryResults, 0, 10);
        return $this->render('search/index.html.twig', [
            'type' => 'answer',
            'query' => $query,
            'query_results' => $queryResults,
        ]);
    }

    /**
     * @Route("/search/profile", name="search_by_profile")
     */
    public function byProfile(Request $request): Response
    {
        $query = $request->query->get('q');
        $queryResults = $this->userRepository->findUsersByQuery($query);
        $queryResults = array_slice($queryResults, 0, 10);
        return $this->render('search/index.html.twig', [
            'type' => 'profile',
            'query' => $query,
            'query_results' => $queryResults,
        ]);
    }
    /**
     * @Route("/search/space", name="search_by_space")
     */
    public function bySpace(Request $request): Response
    {
        $query = $request->query->get('q');
        $queryResults = $this->spaceRepository->findSpacesByQuery($query);
        $queryResults = array_slice($queryResults, 0, 10);
        return $this->render('search/index.html.twig', [
            'type' => 'space',
            'query' => $query,
            'query_results' => $queryResults,
        ]);
    }

    /*****************  API REQUEST METHODS *****************/

    /**
     * 
     * @Route("/search/all/generate/{q}/{id}", name="api_search_generate_all",methods="GET")
     * @return JsonResponse
     */
    public function generateAll(string $q, int $id): JsonResponse
    {

        $questionsByQuery = $this->questionRepository->findQuestionsByQuery($q);
        $answersByquery = $this->answerRepository->findAnswersByQuery($q);
        $spacesByQuery = $this->spaceRepository->findSpacesByQuery($q);
        $usersByQuery = $this->userRepository->findUsersByQuery($q);
        $total = array_merge($spacesByQuery, $usersByQuery, $questionsByQuery, $answersByquery);
        $total = array_slice($total, $id, $id + 10);

        $jsonData = [
            'content' => $this->renderView('search/partials/_all.html.twig', ['query_results' => $total])
        ];
        return new JsonResponse($jsonData);
    }

    /**
     * @Route("/search/question/generate/{q}/{id}", name="api_search_generate_by_question",methods="GET")
     * @return JsonResponse
     */
    public function generateByQuestion(string $q, int $id): JsonResponse
    {
        $questionsByQuery = $this->questionRepository->findQuestionsByQuery($q);
        $total = array_slice($questionsByQuery, $id, $id + 10);
        return new JsonResponse($total);
    }

    /**
     * @Route("/search/answer/generate/{q}/{id}", name="api_search_generate_by_answer",methods="GET")
     * @return JsonResponse
     */
    public function generateByAnswer(string $q, int $id): JsonResponse
    {

        $answersByquery = $this->answerRepository->findAnswersByQuery($q);
        $total = array_slice($answersByquery, $id, $id + 10);
        return new JsonResponse($total);
    }

    /**
     * @Route("/search/profile/generate/{q}/{id}", name="api_search_generate_by_profile",methods="GET")
     * @return JsonResponse
     */
    public function generateByProfile(string $q, int $id): JsonResponse
    {

        $usersByQuery = $this->userRepository->findUsersByQuery($q);
        $total = array_slice($usersByQuery, $id, $id + 10);
        return new JsonResponse($total);
    }

    /**
     * @Route("/search/space/generate/{q}/{id}", name="api_search_generate_by_space",methods="GET")
     * @return JsonResponse
     */
    public function generateBySpace(string $q, int $id): JsonResponse
    {

        $spacesByQuery = $this->spaceRepository->findSpacesByQuery($q);
        $total = array_slice($spacesByQuery, $id, $id + 10);
        return new JsonResponse($total);
    }
}
