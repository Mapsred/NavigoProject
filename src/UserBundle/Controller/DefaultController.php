<?php

namespace UserBundle\Controller;

require __DIR__.'/../../../vendor/paypal/rest-api-sdk-php/sample/common.php';


use AppBundle\Entity\Order;
use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Exception\PayPalConnectionException;
use ResultPrinter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
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
        $form = $this->createForm(UserFileForm::class, $this->getUser());

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->persist($this->getUser());
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
        $payer = new Payer();
        $payer->setPaymentMethod("paypal");

        $item = new Item();
        $sku = $this->getUser()->getCard()->getUuid();
        $item->setName("Renouvellement Navigo")->setDescription("Renouvellement Navigo 2 mois")->setQuantity(1)
            ->setPrice(20)->setSku($sku)->setCurrency("EUR");
        $itemList = new ItemList();
        $itemList->setItems([$item]);

        $details = new Details();
        $details->setSubtotal(doubleval($item->getPrice()));

        $amount = new Amount();
        $amount->setCurrency($item->getCurrency())->setTotal($details->getSubtotal());

        $transaction = new Transaction();
        $transaction->setAmount($amount)->setItemList($itemList)->setDescription("Payment description")
            ->setInvoiceNumber(uniqid());

        $baseUrl = getBaseUrl()."/renouveller/completing?success";
        $redirectUrls = new RedirectUrls();
        $redirectUrls->setReturnUrl("$baseUrl=true")->setCancelUrl("$baseUrl=false");

        $payment = new Payment();
        $payment->setIntent("sale")->setPayer($payer)->setRedirectUrls($redirectUrls)->setTransactions([$transaction]);

        #May launch an Exception on failure
        $payment->create($this->get("paypal")->getApiContext());
        $approvalUrl = $payment->getApprovalLink();

        return $this->redirect($approvalUrl);
    }

    /**
     * @Route("/renouveller/completing", name="payment_completing")
     * @param Request $request
     * @return RedirectResponse
     */
    public function paymentCompletingAction(Request $request)
    {
        if ($request->query->has("success") && $request->query->get("success") == 'true') {
            $paymentId = $request->query->get("paymentId");
            $payment = Payment::get($paymentId, $this->get("paypal")->getApiContext());
            $transaction = $payment->getTransactions()[0];

            $order = new Order();
            $order->setUser($this->getUser())->setAmount($transaction->getAmount())->setDone(false)
                ->setUuid($paymentId)->setDone(true);

            $execution = new PaymentExecution();
            $execution->setPayerId($request->query->get("PayerID"))->addTransaction($transaction);
            $card = $this->getUser()->getCard();
            $card->setExpiratedAt($card->getExpiratedAt()->add(new \DateInterval("P2M")));

            $this->getDoctrine()->getManager()->persist($card);
            $this->getDoctrine()->getManager()->persist($order);
            $this->getDoctrine()->getManager()->flush();

            try {
                $this->addFlash("success", "Paiement réussi");
            } catch (\Exception $ex) {
                $this->addFlash("danger", "Paiement échoué. Veuillez retenter plus tard");
            }

        } else {
            $this->addFlash("warning", "Paiement annulé");
        }

        return $this->redirectToRoute("profile");
    }

}
