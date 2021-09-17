<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="app_login",methods={"GET","POST"})
     */
    public function login(AuthenticationUtils $authenticationUtils, UserRepository $userRepository): Response
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
        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error, 'user' => $randomUser]);
    }

    /**
     * @Route("/logout", name="app_logout",methods="GET")
     */
    public function logout()
    {
        return $this->redirectToRoute('app_home_index');
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
