<?php

namespace UserBundle\Controller;

require __DIR__.'/../../../vendor/paypal/rest-api-sdk-php/sample/common.php';

use AppBundle\Utils\Paypal;
use PayPal\Api\RedirectUrls;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use UserBundle\Entity\User;
use UserBundle\Form\UserFileForm;
use UserBundle\Form\UserType;

/**
 * Class DefaultController
 * @package UserBundle\Controller
 * @method User getUser()
 */
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
     * @Route("/register", name="security_register")
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function registerAction(Request $request)
    {
        $form = $this->createForm(UserType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User $user */
            $user = $form->getData();
            $card = $this->getDoctrine()->getRepository("UserBundle:Card")
                ->findOneBy(['uuid' => $user->getCard()->getUuid()]);
            if (!empty($card->getUser())) {
                $this->addFlash('warning', 'Card already linked to a user');

                return $this->redirectToRoute('homepage');
            }

            $userE = $this->getDoctrine()->getRepository("UserBundle:User")->findOneByCardUUID($card->getUuid());
            if ($userE) {
                $this->addFlash('warning', 'User already created');

                return $this->redirectToRoute('homepage');
            }

            $user->setPassword($this->get("security.password_encoder")->encodePassword($user, $user->getPassword()))
                ->setRoles(['ROLE_USER'])->setApiToken($this->get("generator")->generateAPIKey())->setCard($card);

            $card->setUser($user);
            $this->getDoctrine()->getManager()->persist($user);
            $this->getDoctrine()->getManager()->flush();


            $this->addFlash('success', 'User created');

            return $this->redirectToRoute('homepage');
        }

        return $this->render("UserBundle:Security:register.html.twig", ['form' => $form->createView()]);
    }

    /**
     * @Route("/profile", name="profile")
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function profileAction(Request $request)
    {
        $user = $this->getUser();
        $form = $this->createForm(UserFileForm::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setUpdatedAt(new \DateTime());
            $this->getDoctrine()->getManager()->persist($user);
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'Image de profil ajoutée');

            return $this->redirectToRoute('profile');
        }

        $twigArray = [
            'form' => $form->createView(),
            "expired" => new \DateTime() > $this->getUser()->getCard()->getExpiratedAt(),
        ];

        return $this->render("UserBundle:Default:profile.html.twig", $twigArray);
    }

    /**
     * @Route("/renouveller", name="card_renew")
     * @return RedirectResponse|Response
     */
    public function renewCardAction()
    {
        $paypal = new Paypal($this->getDoctrine()->getManager(), $this->getUser());
        $baseUrl = getBaseUrl()."/renouveller/completing?success";
        $redirectUrls = new RedirectUrls();
        $redirectUrls->setReturnUrl("$baseUrl=true")->setCancelUrl("$baseUrl=false");
        $approvalUrl = $paypal->renew($this->get("paypal")->getApiContext(), $redirectUrls);

        return $this->redirect($approvalUrl);
    }

    /**
     * @Route("/renouveller/completing", name="payment_completing")
     * @param Request $request
     * @return RedirectResponse
     */
    public function paymentCompletingAction(Request $request)
    {
        $paypal = new Paypal($this->getDoctrine()->getManager(), $this->getUser());
        $msg = $paypal->completing($request, $this->get("paypal")->getApiContext());
        $this->addFlash($msg[0], $msg[1]);

        return $this->redirectToRoute("profile");
    }
}
