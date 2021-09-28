<?php

namespace App\Service;

use App\Entity\User; // your user entity
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\PassportInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class GoogleAuthenticator extends OAuth2Authenticator
{
    private $clientRegistry;
    private $entityManager;
    private $router;
    private $passwordEncoder;

    public function __construct(ClientRegistry $clientRegistry, EntityManagerInterface $entityManager, RouterInterface $router, UserPasswordHasherInterface $passwordEncoder)
    {
        $this->clientRegistry = $clientRegistry;
        $this->entityManager = $entityManager;
        $this->router = $router;
        $this->passwordEncoder = $passwordEncoder;
    }

    public function supports(Request $request): ?bool
    {
        return $request->attributes->get('_route') === 'connect_google_check';
    }

    public function authenticate(Request $request): PassportInterface
    {
        $client = $this->clientRegistry->getClient('google');
        $accessToken = $this->fetchAccessToken($client);

        return new SelfValidatingPassport(
            new UserBadge($accessToken->getToken(), function () use ($accessToken, $client) {
                /** @var GoogleClient $googleClient */
                $googleClient = $client->fetchUserFromToken($accessToken)->toArray();

                // 1) have they logged in with Facebook before? Easy!
                $existingUserById = $this->entityManager->getRepository(User::class)->findOneBy(['googleId' => $googleClient['sub']]);

                if ($existingUserById) {
                    return $existingUserById;
                }

                $email = $googleClient['email'];
                // 2) do we have a matching user by email?
                $existingUserByEmail = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

                if ($existingUserByEmail) {
                    return $existingUserByEmail;
                }

                // 3) Maybe you just want to "register" them by creating
                // a User object

                $user = new User();
                $user
                    ->setGoogleId(intval($googleClient['sub']))
                    ->setFirstName($googleClient['given_name'])
                    ->setLastName($googleClient['family_name'])
                    ->setEmail($googleClient['email'])
                    ->setPseudonym($googleClient['name'])
                    ->setImageName('image_base.png')
                    ->setPassword($this->passwordEncoder->hashPassword($user, 'demo'))
                    ->setRoles(['ROLE_USER']);

                $this->entityManager->persist($user);
                $this->entityManager->flush();

                return $user;
            })
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // change "app_homepage" to some route in your app
        $targetUrl = $this->router->generate('app_home_index');

        return new RedirectResponse($targetUrl);

        // or, on success, let the request continue to be handled by the controller
        //return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $message = strtr($exception->getMessageKey(), $exception->getMessageData());

        return new Response($message, Response::HTTP_FORBIDDEN);
    }
}
