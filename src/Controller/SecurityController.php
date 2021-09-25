<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
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
        if (!$this->isCsrfTokenValid('registration_form[_token]', $data['_token'])) {
            $this->addFlash('errorMessage', 'Jeton CSRF invalide.');
        } else {
            if ($data['firstName'] === '') {
                $firstName = null;
            } else {
                $firstName = $data['firstName'];
            }
            if ($data['lastName'] === '') {
                $lastName = null;
            } else {
                $lastName = $data['lastName'];
            }

            // encode the plain password
            $user = new User();
            $user
                ->setFirstName($firstName)
                ->setLastName($lastName)
                ->setPassword(
                    $passwordEncoder->hashPassword(
                        $user,
                        $data['plainPassword']
                    )
                )
                ->setPseudonym($data['pseudonym'])
                ->setRoles(['ROLE_USER'])
                ->setEmail($data['email'])
                ->setImageName('image_base.png');
            $errors = $validator->validate($user);
            if (count($errors) === 0) {
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($user);
                $entityManager->flush();
                $this->addFlash('successMessage', 'Vous vous êtes inscrit avec succès. Veuillez vous connecter.');
            } else {
                foreach ($errors as $error) {
                    $this->addFlash('errorMessage', $error->getMessage());
                }
            }
        }
        return $this->redirectToRoute('app_home_index');
    }
}
