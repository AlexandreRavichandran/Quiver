<?php

namespace App\Controller;

use App\Entity\Space;
use App\Form\SpacePictureType;
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
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\JsonResponse;

class SpaceController extends AbstractController
{
    private $em;
    private $spaceRepository;

    public function __construct(EntityManagerInterface $em, SpaceRepository $spaceRepository)
    {
        $this->em = $em;
        $this->spaceRepository = $spaceRepository;
    }

    /**
     * 
     * @Route("/spaces/create", name="app_space_create",methods="POST")
     * 
     * @param Request $request Request sent to this method
     * @param ValidatorInterface $validator Validator to check if entity is correctly filled
     * 
     * @return Response
     */
    public function create(Request $request, ValidatorInterface $validator): Response
    {
        //Check if request method is correct
        if (!$request->isMethod('POST')) {

            $this->addFlash('errorMessage', "Une erreur s'est produite.");
            return $this->redirectToRoute('app_home_index');
        }

        $csrfToken = $request->request->get('_csrf_token');

        //check csrf
        if (!$this->isCsrfTokenValid('create_space', $csrfToken)) {
            $this->addFlash('errorMessage', 'le serveur a détecté une attaque CSRF et l\'operation a été abandonnée.');
            return $this->redirectToRoute('app_home_index');
        }

        $spaceName = $request->request->get('space_name');
        $spaceDescription = $request->request->get('space_description');
        $space = new Space();
        $space->setName($spaceName);
        $space->setDescription($spaceDescription);

        $errors = $validator->validate($space);

        //Check if created question objet is valid following the entity's contraint
        if (0 !== count($errors)) {
            //Display error messages
            foreach ($errors as $error) {
                $this->addFlash('errorMessage', $error->getMessage());
            }

            return $this->redirectToRoute('app_home_index');
        }

        $this->em->persist($space);
        $this->em->flush();

        $this->addFlash('successMessage', 'Votre espace a bien été crée. Vous pouvez maintenant lier une question à cet espace.');

        return $this->redirectToRoute('app_space_show', [
            'id' => $space->getId()
        ]);
    }

    /**
     * 
     * @Route("/spaces", name="app_space_index",methods="GET")
     * 
     * @param SpaceRepository $spaceRepository Repository of space entity
     * 
     * @return Response
     */
    public function index(): Response
    {
        $userFollowingSpaces = $this->getUser()->getSubscribedSpaces();
        $spaces = $this->spaceRepository->findBy([], null, 6);

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
     * 
     * @param SpaceRepository $spaceRepository Repository of space entity
     * @param QuestionRepository $questionRepository Repository of Question entity
     * 
     * @return Response
     */
    public function following(QuestionRepository $questionRepository): Response
    {
        $date = new DateTime();
        $date = $date->format('Y-m-d');
        $date = new DateTimeImmutable($date);


        //Get all user following spaces and get questions related to this spaces
        $userFollowingSpaces = $this->getUser()->getSubscribedSpaces()->toArray();
        $userFollowingSpaceNames = [];
        foreach ($userFollowingSpaces as $space) {
            $userFollowingSpaceNames[] = $space->getId();
        }
        $questions = $questionRepository->findAllQuestionsBySpaceNames($userFollowingSpaceNames, $date, 3);

        $spaces = $this->spaceRepository->findBy([], null, 8);

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
     * 
     * @Route("/spaces/{id}",name="app_space_show",methods="GET",requirements={"id"="\d+"})
     * 
     * @param Space $space Space objet which has the url given id
     * 
     * @return Response
     */
    public function show(Space $space): Response
    {
        $form = $this->createForm(SpacePictureType::class);
        $spaces = $this->spaceRepository->findBy([], null, 8);
        $questions = $space->getQuestions();

        return $this->render(
            'space/show.html.twig',
            [
                'partial' => 'index',
                'spaces' => $spaces,
                'space' => $space,
                'questions' => $questions,
                'form' => $form->createView()
            ]
        );
    }

    /**
     * Show one space and its related questions
     * 
     * @Route("/spaces/{id}/top_questions",name="app_space_show_top_question",methods="GET",requirements={"id"="\d+"})
     * 
     * @param Space $space Space object which has the url given id
     * 
     * @return Response
     */
    public function showTopQuestions(Space $space): Response
    {
        $form = $this->createForm(SpacePictureType::class);
        $spaces = $this->spaceRepository->findBy([], null, 8);
        $questions = $space->getQuestions();

        return $this->render(
            'space/show.html.twig',
            [
                'partial' => 'top_questions',
                'spaces' => $spaces,
                'space' => $space,
                'questions' => $questions,
                'form' => $form->createView()
            ]
        );
    }

    /**
     * Show one space and its related questions
     * 
     * @Route("/space/picture",name="app_space_update_picture",methods="POST")
     * 
     * @param Request $request Request sent to this method
     * @param Filesystem $filesystem Component to manage files
     * 
     * @return Response
     */
    public function updatePicture(Request $request, Filesystem $filesystem): Response
    {
        $file = $request->files->get('space_picture')['imageFile']['file'];
        $space = $this->spaceRepository->find($request->request->get('spaceId'));


        //Check if new picture extension is correct
        $extension = $file->guessExtension();
        if ('jpg' !== $extension && 'png' !== $extension) {
            $this->addFlash('errorMessage', 'L\'extension de votre fichier est incorrecte. Veuillez réessayer avec une extension valide (JPG, PNG, JPEG)');

            return $this->redirecttoRoute('app_space_show', ['id' => $space->getId()]);
        }


        $fileName = uniqid() . 'image-' . $space->getId() . '.' . $file->guessExtension();

        $file->move($this->getParameter('space_pictures_directory'), $fileName);


        //If image already exists, remove the previous image
        $previousPicture = $space->getImageName();
        $filesystem->remove($this->getParameter('space_pictures_directory') . '/' . $previousPicture);

        $space->setImageName($fileName);
        $this->em->persist($space);
        $this->em->flush();

        $this->addFlash('successMessage', 'Votre photo de profil a bien été mis à jour');

        return $this->redirecttoRoute('app_space_show', ['id' => $space->getId()]);
    }


    /*****************  API REQUEST METHODS *****************/

    /**
     * Handle the space subscription
     * 
     * @Route("/api/spaces/{id}/subscribers/{action}", name="api_space_subscribe",methods="GET",requirements={"id"="\d+","action"="\b(add)\b|\b(remove)\b"})
     * 
     * @param Space $space Space object which has the url given id
     * @param string $action Action to do with this space (Only 2 values : 'add' and 'remove')
     * 
     * @return JsonResponse
     */
    public function subscribe(Space $space, string $action): JsonResponse
    {
        $actions = ['add', 'remove'];

        if (!in_array($action, $actions)) {

            $responseCode = 401;
            $label = 'errorMessage';
            $messageText = "Une erreur s'est produite.";
            $jsonData = [
                'content' => $this->renderView('partials/_alert_message.html.twig', ['message' => $messageText, 'label' => $label])
            ];

            return new JsonResponse($jsonData, $responseCode);
        }

        $user = $this->getUser();
        $isSubscribedTo = $space->hasSubscriber($user);

        /**
         * Following if the user has already subscribed the space, an action will be made
         */
        if ('add' === $action) {
            if ($isSubscribedTo) {
                $space->removeSubscriber($user);
            } else {
                $space->addSubscriber($user);
            }
        } else {
            $space->removeSubscriber($user);
        }

        $this->em->flush();

        return new JsonResponse();
    }

    /**
     * 
     * @Route("/api/following/generate/{date}", name="api_space_generate_more_following_question", methods="GET", requirements={"date"="\d{4}-\d{2}-\d{2}"})

     * @param string|null $date Date of the last showed answer. date format must be YYYY-MM-DD
     * @param QuestionRepository $questionRepository Repository of the question entity
     * 
     * @return JsonResponse
     */
    public function addMoreFollowingQuestions(string $date = null, QuestionRepository $questionRepository): JsonResponse
    {
        if (null === $date) {
            $date = new DateTimeImmutable();
        } else {
            $date = new DateTimeImmutable($date);
        }

        //Get all user following spaces and get questions related to this spaces
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
     * @Route("/api/spaces/generate/{id}", name="api_space_generate_space",methods="GET",requirements={"id":"\d+"})
     * 
     * @param integer $id Id of the last diplayed space
     * 
     * @return JsonResponse
     */
    public function generateSpaces(int $id): JsonResponse
    {
        $spaces = $this->spaceRepository->findSpaces($id, 6);

        $jsonData = [
            'content' => $this->renderView('space/partials/_spaceList.html.twig', ['spaces' => $spaces])
        ];

        return new JsonResponse($jsonData);
    }

    /**
     * Show spaces that are not related with an question
     * 
     * @Route("/api/spaces/questions/{id}",name="api_space_get_all_spaces",methods="GET",requirements={"id"="\d+"})
     * 
     * @param integer $id Id of the question
     * 
     * @return JsonResponse
     */
    public function getRemainingSpaces(int $id): JsonResponse
    {
        $allSpaces = $this->spaceRepository->findAll();
        $questionSpace = $this->spaceRepository->findSpaceByQuestionId($id);
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

        return new JsonResponse($jsonData);
    }
}
