<?php

namespace App\Controller;

use App\Entity\Space;
use App\Repository\SpaceRepository;
use App\Repository\QuestionRepository;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class SpaceController extends AbstractController
{

    /**
     * 
     * @Route("/spaces/create", name="app_space_create")
     * @return Response
     */
    public function create(Request $request, ValidatorInterface $validator, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            $spaceName = $request->request->get('space_name');
            $spaceDescription = $request->request->get('space_description');
            $space = new Space;
            $space->setName(htmlspecialchars($spaceName));
            $space->setDescription(htmlspecialchars($spaceDescription));

            //Check if created question objet is valid following the entity's contraint
            $errors = $validator->validate($space);
            if (count($errors) === 0) {
                $em->persist($space);
                $em->flush();
                return $this->redirectToRoute('app_space_show', [
                    'id' => $space->getId()
                ]);
            }

            //Display error messages
            foreach ($errors as $error) {
                $this->addFlash('yellow', $error->getMessage());
                return $this->redirectToRoute('app_home');
            }
        }
    }

    /**
     * Create & discover spaces
     * @Route("/spaces", name="app_space_index")
     */
    public function index(SpaceRepository $spaceRepository): Response
    {
        $userFollowingSpaces = $spaceRepository->orderUserSpace($this->getUser()->getId(), 'lastVisited');
        $spaces = $spaceRepository->findBy([], null, 6);

        return $this->render(
            'space/index.html.twig',
            [
                'page' => 'space',
                'spaces' => $spaces,
                'user_following_spaces' => $userFollowingSpaces
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
        $date = new DateTime();
        $date = $date->format('Y-m-d');
        $userFollowingSpaces = $this->getUser()->getSubscribedSpaces()->toArray();
        $userFollowingSpaceNames = [];
        foreach ($userFollowingSpaces as $space) {
            $userFollowingSpaceNames[] = $space->getId();
        }
        $questions = $questionRepository->findAllQuestionsBySpaceNames($userFollowingSpaceNames, $date, 3);
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
     * Undocumented function
     * @Route("/spaces/user/generate/{order}", name="app_space_order")
     * @return JsonResponse
     */
    public function setUserOrderSpace($order, SpaceRepository $spaceRepository): JsonResponse
    {
        if ($order === 'name' || $order === 'lastVisited') {
            $userFollowingSpaces = $spaceRepository->orderUserSpace($this->getUser()->getId(), $order);
            $jsonData = [
                'content' => $this->renderView('space/partials/_user_space_list.html.twig', ['userFollowingSpaces' => $userFollowingSpaces])
            ];
            return new JsonResponse($jsonData);
        }

        return new JsonResponse(null, 401);
    }

    /**
     * Show one space and its related questions
     * @Route("/spaces/{id}",name="app_space_show")
     * @return Response
     */
    public function show(Space $space, SpaceRepository $spaceRepository, EntityManagerInterface $em): Response
    {
        $space->setLastVisited(new DateTimeImmutable());
        $em->flush();
        $spaces = $spaceRepository->findBy([], null, 8);
        $questions = $space->getQuestions();

        return $this->render(
            'space/show.html.twig',
            [
                'partial' => 'index',
                'spaces' => $spaces,
                'space' => $space,
                'questions' => $questions,
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
                'partial' => 'top_questions',
                'spaces' => $spaces,
                'space' => $space,
                'questions' => $questions,
            ]
        );
    }

    /**
     *
     * @Route("/spaces/{id}/subscribers/{action}")
     * @return JsonResponse
     */
    public function subscribe(Space $space, string $action, EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();
        $isSubscribedTo = $space->hasSubscriber($user);
        if ($action === 'add') {
            if ($isSubscribedTo) {
                $space->removeSubscriber($user);
            } else {
                $space->addSubscriber($user);
            }
        } else {
            $space->removeSubscriber($user);
        }
        $em->flush();
        return new JsonResponse;
    }

    /**
     * 
     * @Route("/following/generate/{date}", name="app_user_following_AJAX")
     * @return JsonResponse
     */
    public function addMoreFollowingQuestions(string $date, QuestionRepository $questionRepository): JsonResponse
    {
        $userFollowingSpaces = $this->getUser()->getSubscribedSpaces()->toArray();
        $userFollowingSpaceNames = [];
        foreach ($userFollowingSpaces as $space) {
            $userFollowingSpaceNames[] = $space->getId();
        }
        $questions = $questionRepository->findAllQuestionsBySpaceNames($userFollowingSpaceNames, $date, 3);
        $jsonData = [
            'content' => $this->renderView('partials/question_headers/question_header_follow.html.twig', [
                'questions' => $questions
            ])
        ];
        return new JsonResponse($jsonData);
    }

    /**
     * 
     * @Route("/spaces/generate/{id}")
     * @return void
     */
    public function generateSpaces(int $id, SpaceRepository $spaceRepository)
    {
        $spaces = $spaceRepository->findSpaces($id, 6);

        $jsonData = [
            'content' => $this->renderView('space/partials/_spaceList.html.twig', ['spaces' => $spaces])
        ];

        return new JsonResponse($jsonData);
    }
}
