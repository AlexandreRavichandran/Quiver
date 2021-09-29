<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserPictureType;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserController extends AbstractController
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {

        $this->em = $em;
    }
    /**
     * 
     * @Route("/profile/{pseudonym}", name="app_user_profile",methods="GET")
     * 
     * @param User $user User objet which has the url given pseudonym
     * 
     * @return Response
     */
    public function index(User $user): Response
    {
        if ($this->getUser()->getPassword() === "") {
            $this->addFlash('errorMessage', 'Vous devez fournir un mot de passe afin de vous connecter prochainement.');
        }

        $form = $this->createForm(UserPictureType::class);
        $userForm = $this->createForm(UserType::class, $this->getUser());

        return $this->render('user/index.html.twig', [
            'partial' => 'profile',
            'user' => $user,
            'form' => $form->createView(),
            'user_form' => $userForm->createView(),
        ]);
    }

    /**
     * @Route("/profile/{pseudonym}/answers", name="app_user_answer",methods="GET")
     * 
     * @param User $user User objet which has the url given pseudonym
     * 
     * @return Response
     */
    public function answers(User $user): Response
    {

        $form = $this->createForm(UserPictureType::class);
        $userForm = $this->createForm(UserType::class, $this->getUser());

        return $this->render('user/index.html.twig', [
            'partial' => 'answer',
            'user' => $user,
            'form' => $form->createView(),
            'user_form' => $userForm->createView(),
        ]);
    }

    /**
     * @Route("/profile/{pseudonym}/questions", name="app_user_question",methods="GET")
     * 
     * @param User $user User objet which has the url given pseudonym
     * 
     * @return Response
     */
    public function questions(User $user): Response
    {

        $form = $this->createForm(UserPictureType::class);
        $userForm = $this->createForm(UserType::class, $this->getUser());

        return $this->render('user/index.html.twig', [
            'partial' => 'question',
            'user' => $user,
            'form' => $form->createView(),
            'user_form' => $userForm->createView(),
        ]);
    }

    /**
     * @Route("/profile/{pseudonym}/subscribers", name="app_user_subscriber",methods="GET")
     * 
     * @param User $user User objet which has the url given pseudonym
     * 
     * @return Response
     */
    public function subscribers(User $user): Response
    {

        $form = $this->createForm(UserPictureType::class);
        $userForm = $this->createForm(UserType::class, $this->getUser());

        return $this->render('user/index.html.twig', [
            'partial' => 'subscriber',
            'user' => $user,
            'form' => $form->createView(),
            'user_form' => $userForm->createView(),
        ]);
    }

    /**
     * @Route("/profile/{pseudonym}/subscriptions", name="app_user_subscription",methods="GET")
     * 
     * @param User $user User objet which has the url given pseudonym
     * 
     * @return Response
     */
    public function subscriptions(User $user): Response
    {

        $form = $this->createForm(UserPictureType::class);
        $userForm = $this->createForm(UserType::class, $this->getUser());

        return $this->render('user/index.html.twig', [
            'partial' => 'subscription',
            'user' => $user,
            'form' => $form->createView(),
            'user_form' => $userForm->createView(),
        ]);
    }

    /**

     * @Route("/profile/picture",name="app_user_update_profile_picture",methods="POST")
     * 
     * @param Request $request Request sent to this method
     * @param Filesystem $filesystem
     * 
     * @return Response
     */
    public function updateProfilePicture(Request $request, Filesystem $filesystem): Response
    {

        $user = $this->getUser();

        $file = $request->files->get('user_picture')['imageFile']['file'];

        $extension = $file->guessExtension();

        if ('jpg' !== $extension && 'png' !== $extension) {
            $this->addFlash('errorMessage', 'L\'extension de votre fichier est incorrecte. Veuillez réessayer avec une extension valide (JPG, PNG, JPEG)');

            return $this->redirectToRoute('app_user_profile', ['pseudonym' => $user->getPseudonym()]);
        }

        $fileName = uniqid() . 'image-' . $user->getId() . '.' . $file->guessExtension();
        $file->move($this->getParameter('profile_pictures_directory'), $fileName);

        //If user already had a picture (except basic picture) remove the previous picture
        $previousPicture = $user->getImageName();
        if ('image_base.png' !== $previousPicture) {
            $filesystem->remove($this->getParameter('profile_pictures_directory') . '/' . $previousPicture);
        }

        $user->setImageName($fileName);
        $this->em->persist($user);
        $this->em->flush();

        $this->addFlash('successMessage', 'Votre photo de profil a bien été mis à jour');

        return $this->redirectToRoute('app_user_profile', ['pseudonym' => $user->getPseudonym()]);
    }

    /**
     * @Route("/profile/update",name="app_user_update_profile_data",methods="POST")
     * @return Response
     */
    public function updateProfileData(Request $request, UserPasswordHasherInterface $passwordEncoder, ValidatorInterface $validator): Response
    {

        if (!$request->isMethod('POST')) {
            $this->addFlash("errorMessage", "Une erreur s'est produite");
            return $this->redirectToRoute('app_home_index');
        }
        $datas = $request->request->all()['user'];

        if (!$this->isCsrfTokenValid('user', $datas['_token'])) {
            $this->addFlash("errorMessage", 'le serveur a détecté une attaque CSRF et l\'operation a été abandonnée.');
            $this->redirectToRoute('app_home_index');
        }

        $user = $this->getUser();
        $user
            ->setLastName($datas['lastName'])
            ->setFirstName($datas['firstName'])
            ->setPseudonym($datas['pseudonym'])
            ->setEmail($datas['email'])
            ->setPassword($datas['password'])
            ->setQualification($datas['qualification'])
            ->setDescription($datas['description']);

        $errors = $validator->validate($user);

        //Check if created question objet is valid following the entity's contraint
        if (0 !== count($errors)) {

            foreach ($errors as $error) {
                $this->addFlash('errorMessage', $error->getMessage());
            }

            return $this->redirectToRoute('app_home_index');
        }
        
        $user->setPassword($passwordEncoder->hashPassword($user, $datas['password']));
        $this->em->persist($user);
        $this->em->flush();

        $this->addFlash('successMessage', 'Votre profil a bien été mis à jour.');

        return $this->redirectToRoute('app_user_profile', ['pseudonym' => $user->getPseudonym()]);
    }
    /*****************  API REQUEST METHODS *****************/

    /**
     * 
     * @Route("/api/profile/{id}/subscribers/{action}",name="api_user_handle_subscriber",methods="GET",requirements={"id"="\d+","action"="\b(add)\b|\b(remove)\b"})
     * 
     * @param User $user User objet which has the url given id
     * @param string $action Action to do with this user (Only 2 values : 'add' and 'remove')
     * @param UserRepository $userRepository
     * 
     * @return JsonResponse
     */
    public function handleSubsriber(User $user, string $action): JsonResponse
    {

        //Check if action is correct
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

        $userToSubcribeWith = $this->getUser();

        $isSubscribed = in_array($userToSubcribeWith, $user->getSubscribers()->toArray());

        /**
         * Following if the user has already subscibed to another user, an action will be made
         */
        if ('add' === $action) {
            if ($isSubscribed) {
                $user->removeSubscriber($userToSubcribeWith);
            } else {
                $user->addSubscriber($userToSubcribeWith);
            }
        } else {
            $user->removeSubscriber($userToSubcribeWith);
        }

        $this->em->flush();

        $jsonData = [
            'subscriberNumber' => count($user->getSubscribers()),
            'subscriptionNumber' => count($userToSubcribeWith->getSubscriptions()),
        ];

        return new JsonResponse($jsonData);
    }

    /**
     * 
     * @Route("/api/login/user/generate",name="api_user_generate",methods="GET")
     * 
     * @param UserRepository $userRepository Repository of the user entity
     * 
     * @return JsonResponse
     */
    public function generateUser(UserRepository $userRepository): JsonResponse
    {
        $allUsers = $userRepository->findAll();
        $randomUser = $allUsers[mt_rand(0, count($allUsers) - 1)];

        $jsonData = [
            'email' => $randomUser->getEmail()
        ];

        return new JsonResponse($jsonData);
    }

    /**
     * 
     * @Route("/api/profile/qualification/update",name="api_user_update_qualification",methods="POST")
     * 
     * @param Request $request Request sent to the method
     * @param ValidatorInterface $validatorInterface Validator to check if entity is correctly filled
     * 
     * @return JsonResponse
     */
    public function updateQualification(Request $request, ValidatorInterface $validatorInterface): JsonResponse
    {
        //Check if request method is correct
        if (!$request->isMethod('POST')) {

            $label = 'errorMessage';
            $responseCode = 405;
            $messageText = "Une erreur s'est produite";
            $message = $this->renderView('partials/_alert_message.html.twig', ['message' => $messageText, 'label' => $label]);
            $jsonData['message'] = $message;

            return new JsonResponse($jsonData, $responseCode);
        }

        $datas = json_decode($request->getContent());
        $user = $this->getUser();

        $newQualification = $datas->newQualification;
        $user->setQualification($newQualification);

        $errors = $validatorInterface->validate($user);

        //Check if created question objet is valid following the entity's contraint
        if (0 !== count($errors)) {
            $label = 'errorMessage';
            $responseCode = 400;
            foreach ($errors as $key => $error) {
                $message[$key] = $this->renderView('partials/_alert_message.html.twig', ['message' => $error->getMessage(), 'label' => $label]);
            }

            $jsonData['message'] = $message;

            return new JsonResponse($jsonData, $responseCode);
        }

        $this->em->persist($user);
        $this->em->flush();
        $responseCode = 201;

        //Prepare datas for success alert message
        $messageText = 'Votre commentaire a été postée avec succès.';
        $label = 'successMessage';
        $message =  $this->renderView('partials/_alert_message.html.twig', ['message' => $messageText, 'label' => $label]);
        $jsonData = ['newQualification' => $newQualification];
        $jsonData['message'] = $message;

        return new JsonResponse($jsonData, $responseCode);
    }

    /**
     *
     * @Route("/api/profile/description/update",name="api_user_update_description",methods="POST")
     * @param Request $request
     * @return JsonResponse
     */
    public function updateDescription(Request $request, ValidatorInterface $validatorInterface): JsonResponse
    {

        //Check if request method is correct
        if (!$request->isMethod('POST')) {

            $label = 'errorMessage';
            $responseCode = 405;
            $messageText = "Une erreur s'est produite";
            $message = $this->renderView('partials/_alert_message.html.twig', ['message' => $messageText, 'label' => $label]);
            $jsonData['message'] = $message;

            return new JsonResponse($jsonData, $responseCode);
        }

        $datas = json_decode($request->getContent());
        $user = $this->getUser();
        $newDescription = $datas->newDescription;
        $user->setDescription($newDescription);
        $errors = $validatorInterface->validate($user);

        //Check if created question objet is valid following the entity's contraint
        if (0 !== count($errors)) {


            $label = 'errorMessage';
            $responseCode = 400;
            foreach ($errors as $key => $error) {
                $message[$key] = $this->renderView('partials/_alert_message.html.twig', ['message' => $error->getMessage(), 'label' => $label]);
            }

            $jsonData['message'] = $message;

            return new JsonResponse($jsonData, $responseCode);
        }

        $this->em->persist($user);
        $this->em->flush();
        $responseCode = 201;

        //Prepare datas for success alert message
        $messageText = 'Votre commentaire a été postée avec succès.';
        $label = 'successMessage';
        $message = $this->renderView('partials/_alert_message.html.twig', ['message' => $messageText, 'label' => $label]);
        $jsonData = ['newQualification' => $newDescription];


        $jsonData['message'] = $message;

        return new JsonResponse($jsonData, $responseCode);
    }
}
