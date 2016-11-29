<?php
/**
 * Created by PhpStorm.
 * User: maps_red
 * Date: 29/11/16
 * Time: 11:09
 */

namespace AppBundle\Utils;


use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Rest\ApiContext;
use UserBundle\Entity\User;

class Paypal
{
    /** @var User $user */
    private $user;

    public function renew(User $user, ApiContext $apiContext, RedirectUrls $urls)
    {
        $this->user = $user;
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

    public function getUser()
    {
        return $this->user;
    }
}