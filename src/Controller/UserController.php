<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserPictureType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UserController extends AbstractController
{
    /**
     * @Route("/profile/{pseudonym}", name="app_user_profile",methods="GET")
     */
    public function index(User $user): Response
    {
        $form = $this->createForm(UserPictureType::class);
        return $this->render('user/index.html.twig', [
            'partial' => 'profile',
            'user' => $user,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/profile/{pseudonym}/answers", name="app_user_answer",methods="GET")
     */
    public function answers(User $user): Response
    {
        $form = $this->createForm(UserPictureType::class);
        return $this->render('user/index.html.twig', [
            'partial' => 'answer',
            'user' => $user,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/profile/{pseudonym}/questions", name="app_user_question",methods="GET")
     */
    public function questions(User $user): Response
    {
        $form = $this->createForm(UserPictureType::class);
        return $this->render('user/index.html.twig', [
            'partial' => 'question',
            'user' => $user,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/profile/{pseudonym}/subscribers", name="app_user_subscriber",methods="GET")
     */
    public function subscribers(User $user): Response
    {
        $form = $this->createForm(UserPictureType::class);
        return $this->render('user/index.html.twig', [
            'partial' => 'subscriber',
            'user' => $user,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/profile/{pseudonym}/subscriptions", name="app_user_subscription",methods="GET")
     */
    public function subscriptions(User $user): Response
    {
        $form = $this->createForm(UserPictureType::class);
        return $this->render('user/index.html.twig', [
            'partial' => 'subscription',
            'user' => $user,
            'form' => $form->createView()
        ]);
    }


    /*****************  API REQUEST METHODS *****************/


    /**
     *
     * @Route("/profile/{id}/subscribers/{action}",name="api_user_handle_subscriber",methods="GET",requirements={"id"="\d+","action"="\b(add)\b|\b(remove)\b"})
     * @return JsonResponse
     */
    public function handleSubsriber(User $user, string $action, UserRepository $userRepository, EntityManagerInterface $em): JsonResponse
    {
        $userToSubcribeWith = $this->getUser();
        $userToSubscribe = $userRepository->findOneBy(['id' => $user->getId()]);

        $isSubscribed = in_array($userToSubcribeWith, $userToSubscribe->getSubscribers()->toArray());
        if ($action === 'add') {
            if ($isSubscribed) {
                $userToSubscribe->removeSubscriber($userToSubcribeWith);
            } else {
                $userToSubscribe->addSubscriber($userToSubcribeWith);
            }
        } else {
            $userToSubscribe->removeSubscriber($userToSubcribeWith);
        }

        $em->flush();

        $jsonData = [
            'subscriberNumber' => count($userToSubscribe->getSubscribers()),
            'subscriptionNumber' => count($userToSubcribeWith->getSubscriptions()),
        ];
        return new JsonResponse($jsonData);
    }

    /**
     * @Route("/login/user/generate",name="api_user_generate",methods="GET")
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
     * @Route("/profile/qualification/update",name="api_user_update_qualification",methods="POST")
     * @param Request $request
     * @return JsonResponse
     */
    public function updateQualification(Request $request, EntityManagerInterface $em, ValidatorInterface $validatorInterface): JsonResponse
    {
        $datas = json_decode($request->getContent());
        $user = $this->getUser();
        $newQualification = $datas->newQualification;
        $user->setQualification($newQualification);

        $errors = $validatorInterface->validate($user);

        if (count($errors) === 0) {
            $em->persist($user);
            $em->flush();
            $responseCode = 201;

            //Prepare datas for success alert message
            $messageText = 'Votre commentaire a été postée avec succès.';
            $label = 'successMessage';
            $message =  $this->renderView('partials/_alert_message.html.twig', ['message' => $messageText, 'label' => $label]);
            $jsonData = ['newQualification' => $newQualification];
        } else {
            $label = 'errorMessage';
            $responseCode = 400;
            foreach ($errors as $key => $error) {
                $message[$key] = $this->renderView('partials/_alert_message.html.twig', ['message' => $error->getMessage(), 'label' => $label]);
            }
            $jsonData = [];
        }

        $jsonData['message'] = $message;

        return new JsonResponse($jsonData, $responseCode);
    }

    /**
     *
     * @Route("/profile/description/update",name="api_user_update_description",methods="POST")
     * @param Request $request
     * @return JsonResponse
     */
    public function updateDescription(Request $request, EntityManagerInterface $em, ValidatorInterface $validatorInterface): JsonResponse
    {
        $datas = json_decode($request->getContent());
        $user = $this->getUser();
        $newDescription = $datas->newDescription;
        $user->setDescription($newDescription);
        $errors = $validatorInterface->validate($user);

        if (count($errors) === 0) {
            $em->persist($user);
            $em->flush();
            $responseCode = 201;

            //Prepare datas for success alert message
            $messageText = 'Votre commentaire a été postée avec succès.';
            $label = 'successMessage';
            $message = $this->renderView('partials/_alert_message.html.twig', ['message' => $messageText, 'label' => $label]);
            $jsonData = ['newQualification' => $newDescription];
        } else {

            $label = 'errorMessage';
            $responseCode = 400;
            foreach ($errors as $key => $error) {
                $message[$key] = $this->renderView('partials/_alert_message.html.twig', ['message' => $error->getMessage(), 'label' => $label]);
            }
        }

        $jsonData['message'] = $message;

        return new JsonResponse($jsonData, $responseCode);
    }

    /**
     * 
     * @Route("/profile/picture",name="app_user_update_profile_picture",methods="POST")
     * @param Request $request
     * @return Response
     */
    public function updateProfilePicture(Request $request, EntityManagerInterface $em, Filesystem $filesystem): Response
    {
        $user = $this->getUser();
        $file = $request->files->get('user_picture')['imageFile']['file'];
        $extension = $file->guessExtension();
        if ($extension === 'jpg' || $extension === 'png') {
            $fileName = uniqid() . 'image-' . $user->getId() . '.' . $file->guessExtension();
            $file->move($this->getParameter('profile_pictures_directory'), $fileName);

            $previousPicture = $user->getImageName();
            $filesystem->remove($this->getParameter('profile_pictures_directory') . '/' . $previousPicture);

            $user->setImageName($fileName);
            $em->persist($user);
            $em->flush();
            $this->addFlash('successMessage', 'Votre photo de profil a bien été mis à jour');
        } else {
            $this->addFlash('errorMessage', 'L\'extension de votre fichier est incorrecte. Veuillez réessayer avec une extension valide (JPG, PNG, JPEG)');
        }
        return $this->redirectToRoute('app_user_profile', ['pseudonym' => $user->getPseudonym()]);
    }
}
