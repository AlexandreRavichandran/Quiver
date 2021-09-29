<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="app_login",methods={"GET","POST"})
     */
    public function login(Request $request, AuthenticationUtils $authenticationUtils, UserRepository $userRepository, UserPasswordHasherInterface $passwordEncoder): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_user_profile');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        $allUsers = $userRepository->findAll();
        $randomUser = $allUsers[mt_rand(0, count($allUsers) - 1)];

        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error, 'user' => $randomUser, 'registration_form' => $form->createView()]);
    }

    /**
     * @Route("/logout", name="app_logout",methods="GET")
     */
    public function logout()
    {
        return $this->redirectToRoute('app_home_index');
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    /**
     * @Route("/register", name="app_register",methods="POST")
     */
    public function register(Request $request, UserPasswordHasherInterface $passwordEncoder, ValidatorInterface $validator)
    {
        $data = $request->request->all()['registration_form'];

        //Check csrf
        if (!$this->isCsrfTokenValid('register', $data['_csrf_token'])) {
            $this->addFlash('errorMessage', 'Jeton CSRF invalide.');
            return $this->redirectToRoute('app_login');
        }

        if ('' === $data['firstName']) {
            $firstName = null;
        } else {
            $firstName = $data['firstName'];
        }
        if ('' === $data['lastName']) {
            $lastName = null;
        } else {
            $lastName = $data['lastName'];
        }

        
        $user = new User();
        $user
            ->setFirstName($firstName)
            ->setLastName($lastName)
            ->setPassword(
                $data['plainPassword']
            )
            ->setPseudonym($data['pseudonym'])
            ->setRoles(['ROLE_USER'])
            ->setEmail($data['email'])
            ->setImageName('image_base.png');

        $errors = $validator->validate($user);

        //Check if created question objet is valid following the entity's contraint
        if (0 !== count($errors)) {
            foreach ($errors as $error) {
                $this->addFlash('errorMessage', $error->getMessage());
            }
            return $this->redirectToRoute('app_login');
        }

        $entityManager = $this->getDoctrine()->getManager();
        try {
            //If the password is correct (without spaces), then we encode it
            $user->setPassword(
                $passwordEncoder->hashPassword(
                    $user,
                    $data['plainPassword']
                )
            );
            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash('successMessage', 'Vous vous êtes inscrit avec succès. Veuillez vous connecter.');
        } catch (UniqueConstraintViolationException $e) {
            $this->addFlash('errorMessage', 'Un compte avec cet email existe déjà.');
        }

        return $this->redirectToRoute('app_home_index');
    }

    /**
     * Undocumented function
     * @Route("/login/google", name="app_login_with_google")
     * @param ClientRegistry $clientRegistry
     */
    public function connectWithGoogle(ClientRegistry $clientRegistry): Response
    {
        /** @var GoogleClient $client */
        $client = $clientRegistry->getClient('google');
        return $client->redirect(['https://www.googleapis.com/auth/userinfo.email', 'https://www.googleapis.com/auth/userinfo.profile']);
    }

    /**
     * Undocumented function
     * @Route("/oauth/check/google", name="connect_google_check")
     */
    public function googleRedirection()
    {
    }
}
