<?php
/**
 * Created by PhpStorm.
 * User: maps_red
 * Date: 29/11/16
 * Time: 10:30
 */

namespace AppBundle\Controller;


use AppBundle\Utils\Api;
use AppBundle\Utils\Paypal;
use PayPal\Api\RedirectUrls;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class ApiController
 * @package AppBundle\Controller
 * @Route("/api")
 */
class ApiController extends Controller
{
    /**
     * @Route("/card/{apiKey}", name="api_get_card")
     * @param $apiKey
     * @return JsonResponse
     */
    public function getCardAction($apiKey)
    {
        $cardEncoded = $this->get('jms_serializer')->serialize($this->getApi($apiKey)->getUser()->getCard(), 'json');
        $card = json_decode($cardEncoded, true);
        unset($card['user']);

        return new JsonResponse($card);
    }

    /**
     * @Route("/user/{apiKey}", name="api_get_user")
     * @param $apiKey
     * @return JsonResponse
     */
    public function getUserAction($apiKey)
    {
        $userEncoded = $this->get('jms_serializer')->serialize($this->getApi($apiKey)->getUser(), 'json');
        $user = json_decode($userEncoded, true);
        unset($user['card'], $user['roles'], $user['password'], $user['file']);

        return new JsonResponse($user);
    }

    /**
     * Return the paypal paiement link
     * @Route("/update/card/{apiKey}", name="api_update_card")
     * @param $apiKey
     * @return JsonResponse
     */
    public function updateCardDateAction($apiKey)
    {
        $paypal = new Paypal($this->getDoctrine()->getManager(), $this->getApi($apiKey)->getUser());
        $baseUrl = $this->generateUrl('api_update_card_confirm', ['apiKey' => $apiKey], UrlGeneratorInterface::ABSOLUTE_URL)."?success";
        $redirectUrls = new RedirectUrls();
        $redirectUrls->setReturnUrl("$baseUrl=true")->setCancelUrl("$baseUrl=false");
        $approvalUrl = $paypal->renew($this->get("paypal")->getApiContext(), $redirectUrls);

        return new JsonResponse(['payment link' => $approvalUrl]);
    }

    /**
     * Return the paypal confirmation
     * @Route("/update/card/{apiKey}/confirm", name="api_update_card_confirm")
     * @param Request $request
     * @param $apiKey
     * @return JsonResponse
     */
    public function updateCardDateConfirmAction(Request $request, $apiKey)
    {
        $paypal = new Paypal($this->getDoctrine()->getManager(), $this->getApi($apiKey)->getUser());
        $result = $paypal->completing($request, $this->get("paypal")->getApiContext());

        return new JsonResponse($result);
    }


    /**
     * @param $apiKey
     * @return Api
     */
    private function getApi($apiKey)
    {
        return $this->get("api")->setApiKey($apiKey);
    }

}