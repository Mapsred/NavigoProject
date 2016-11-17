<?php

namespace UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * @Route("/login", name="security_login")
     * @return Response
     */
    public function loginAction()
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('homepage');
        }
        $helper = $this->get('security.authentication_utils');
        $exception = $this->get('security.authentication_utils')->getLastAuthenticationError();
        $twigArray['last_username'] = $helper->getLastUsername();
        $twigArray['error'] = $exception ? $exception : null;

        return $this->render('UserBundle:Security:login.html.twig', $twigArray);
    }

    /**
     * @Route("/login_check", name="security_login_check")
     */
    public function loginCheckAction()
    {
        // will never be executed
    }

    /**
     * @Route("/register", name="security_register")
     */
    public function RegisterAction()
    {
    }

    /**
     * @Route("/profile", name="profile")
     */
    public function profileAction()
    {

    }
}
