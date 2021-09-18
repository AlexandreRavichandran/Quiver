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
     * @Route("/spaces/create", name="app_space_create",methods="POST")
     * @return Response
     */
    public function create(Request $request, ValidatorInterface $validator, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            $spaceName = $request->request->get('space_name');
            $spaceDescription = $request->request->get('space_description');
            $space = new Space;
            $space->setName($spaceName);
            $space->setDescription($spaceDescription);

            //Check if created question objet is valid following the entity's contraint
            $errors = $validator->validate($space);

            if (count($errors) === 0) {
                $em->persist($space);
                $em->flush();

                $this->addFlash('successMessage', 'Votre espace a bien été crée. Vous pouvez maintenant lier une question à cet espace.');
                $redirectRoute = $this->redirectToRoute('app_space_show', [
                    'id' => $space->getId()
                ]);
            } else {
                //Display error messages
                foreach ($errors as $error) {
                    $this->addFlash('errorMessage', $error->getMessage());
                }
                $redirectRoute = $this->redirectToRoute('app_home_index');
            }

            return $redirectRoute;
        }
    }

    /**
     * Create & discover spaces
     * @Route("/spaces", name="app_space_index",methods="GET")
     * @return Response
     */
    public function index(SpaceRepository $spaceRepository): Response
    {
        $userFollowingSpaces = $this->getUser()->getSubscribedSpaces();
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
     * @Route("/following",name="app_space_following",methods="GET")
     * @return Response
     */
    public function following(SpaceRepository $spaceRepository, QuestionRepository $questionRepository): Response
    {
        $date = new DateTime();
        $date = $date->format('Y-m-d');
        $date = new DateTimeImmutable($date);
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
     * Show one space and its related questions
     * @Route("/spaces/{id}",name="app_space_show",methods="GET",requirements={"id"="\d+"})
     * @return Response
     */
    public function show(Space $space, SpaceRepository $spaceRepository, EntityManagerInterface $em): Response
    {
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
     * @Route("/spaces/{id}/top_questions",name="app_space_show_top_question",methods="GET",requirements={"id"="\d+"})
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


    /*****************  API REQUEST METHODS *****************/

    /**
     *
     * @Route("/spaces/{id}/subscribers/{action}", name="api_space_subscribe",methods="GET",requirements={"id"="\d+","action"="\b(add)\b|\b(remove)\b"})
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

        return new JsonResponse();
    }

    /**
     * 
     * @Route("/following/generate/{date}", name="api_space_generate_more_following_question", methods="GET", requirements={"date"="\d{4}-\d{2}-\d{2}"})
     * @return JsonResponse
     */
    public function addMoreFollowingQuestions(string $date, QuestionRepository $questionRepository): JsonResponse
    {
        $date = new DateTimeImmutable($date);
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
     * @Route("/spaces/generate/{id}", name="api_space_generate_space",methods="GET",requirements={"id":"\d+"})
     * @return JsonResponse
     */
    public function generateSpaces(int $id, SpaceRepository $spaceRepository): JsonResponse
    {
        $spaces = $spaceRepository->findSpaces($id, 6);

        $jsonData = [
            'content' => $this->renderView('space/partials/_spaceList.html.twig', ['spaces' => $spaces])
        ];

        return new JsonResponse($jsonData);
    }

    /**
     * @Route("/spaces/questions/{id}",name="api_space_get_all_spaces",methods="GET",requirements={"id"="\d+"})
     * @return JsonResponse
     */
    public function getRemainingSpaces(int $id, SpaceRepository $spaceRepository): JsonResponse
    {
        $allSpaces = $spaceRepository->findAll();
        $questionSpace = $spaceRepository->findSpaceByQuestionId($id);
        $jsonData = [];

        foreach ($allSpaces as $space) {

            if (!in_array($space, $questionSpace)) {
                $spaceArray = [
                    'id' => $space->getId(),
                    'name' => $space->getName()
                ];
                $jsonData[] = $spaceArray;
            }
        }

        return $this->json($jsonData, 200);
    }
}
