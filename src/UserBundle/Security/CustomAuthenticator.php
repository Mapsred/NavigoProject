<?php
/**
 * Created by PhpStorm.
 * User: Maps_red
 * Date: 27/07/2016
 * Time: 23:22
 */

namespace UserBundle\Security;

use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use UserBundle\Entity\User;
use Symfony\Component\Security\Core\Security;

/**
 * Class CustomAuthenticator
 * @package UserBundle\Security
 */
class CustomAuthenticator extends AbstractGuardAuthenticator
{
    const BANNED = "Votre compte a été désactivé, veuillez contacter un administrateur afin d'obtenir plus de renseignements";
    /** @var EntityManager $em */
    private $em;
    /** @var ContainerInterface $container */
    private $container;


    /**
     * CustomAuthenticator constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->em = $container->get("doctrine")->getManager();
        $this->container = $container;
    }

    /**
     * step 1
     * Called on every request. Return whatever credentials you want,
     * or null to stop authentication.
     * @param Request $request
     * @return array
     */
    public function getCredentials(Request $request)
    {
        if ($request->getPathInfo() != '/login_check') {
            return null;
        }

        $username = ucfirst(trim($request->request->get('_username')));
        $password = $request->request->get('_password');

        return ['username' => $username, 'password' => $password];
    }

    /**
     * step 2
     * @param mixed $credentials
     * @param UserProviderInterface $userProvider
     * @return bool|UserInterface
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $user = $this->em->getRepository('UserBundle:User')->findByUsernameOrCard($credentials['username']);
        if (!$user) {
            return false;
        }

        return $user;
    }

    /**
     * step 3
     * @param mixed $credentials
     * @param UserInterface|User $user
     * @return bool
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
        $encoder = $this->container->get('security.password_encoder');
        if (!$user->getEnabled()) {
            throw new CustomUserMessageAuthenticationException(self::BANNED);
        }

        return $encoder->isPasswordValid($user, $credentials['password']);
    }

    /**
     * @param Request $request
     * @param TokenInterface $token
     * @param string $providerKey
     * @return RedirectResponse
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        return null;
    }

    /**
     * @param Request $request
     * @param AuthenticationException $exception
     * @return RedirectResponse
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $request->getSession()->set(Security::AUTHENTICATION_ERROR, $exception);

        return new RedirectResponse($this->container->get('router')->generate('security_login'));
    }

    /**
     * Called when authentication is needed, but it's not sent
     * @param Request $request
     * @param AuthenticationException|null $authException
     * @return JsonResponse
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        return new JsonResponse(['message' => 'Connexion requise'], 401);
    }

    /**
     * @return bool
     */
    public function supportsRememberMe()
    {
        return false;
    }
}