<?php
/**
 * Created by PhpStorm.
 * User: maps_red
 * Date: 29/11/16
 * Time: 11:09
 */

namespace AppBundle\Utils;


use AppBundle\Entity\Order;
use Doctrine\Common\Persistence\ObjectManager;
use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Rest\ApiContext;
use Symfony\Component\HttpFoundation\Request;
use UserBundle\Entity\User;

class Paypal
{
    /** @var User $user */
    private $user;

    private $em;

    /**
     * @param ObjectManager $manager
     * @param User $user
     */
    public function __construct(ObjectManager $manager, User $user)
    {
        $this->em = $manager;
        $this->user = $user;
    }

    /**
     * @param User $user
     * @param ApiContext $apiContext
     * @param RedirectUrls $urls
     * @return null|string
     */
    public function renew(ApiContext $apiContext, RedirectUrls $urls)
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

        $payment = new Payment();
        $payment->setIntent("sale")->setPayer($payer)->setRedirectUrls($urls)->setTransactions([$transaction]);

        #May launch an Exception on failure
        $payment->create($apiContext);

        return $payment->getApprovalLink();
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param Request $request
     * @param ApiContext $apiContext
     * @return array
     */
    public function completing(Request $request, ApiContext $apiContext)
    {
        if ($request->query->has("success") && $request->query->get("success") == 'true') {
            $paymentId = $request->query->get("paymentId");
            $payment = Payment::get($paymentId, $apiContext);
            $transaction = $payment->getTransactions()[0];

            $order = $this->getManager()->getRepository("AppBundle:Order")->findOneBy(['uuid' => $paymentId]);
            if ($order) {
                return ["danger", "Paiement déjà effectué"];
            }

            $order = new Order();
            $order->setUser($this->getUser())->setAmount($transaction->getAmount()->getTotal())
                ->setDone(false)->setUuid($paymentId)->setDone(true);

            $execution = new PaymentExecution();
            $execution->setPayerId($request->query->get("PayerID"))->addTransaction($transaction);
            $card = $this->getUser()->getCard();
            $card->setExpiratedAt($card->getExpiratedAt()->add(new \DateInterval("P2M")));

            $this->getManager()->persist($card);
            $this->getManager()->persist($order);
            $this->getManager()->flush();

            return ["success", "Paiement réussi"];
        } else {
            return ["warning", "Paiement annulé"];
        }

    }

    /**
     * @return ObjectManager
     */
    public function getManager()
    {
        return $this->em;
    }
}