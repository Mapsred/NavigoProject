<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     * @return Response
     */
    public function indexAction()
    {
        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.root_dir').'/..').DIRECTORY_SEPARATOR,
        ]);
    }


    /**
     * @Route("/card", name="card_route")
     * @param Request $request
     * @return Response
     */
    public function checkValidAction(Request $request)
    {
        if ($request->query->has("uuid")) {
            $uuid = $request->query->get("uuid");
            $card = $this->getDoctrine()->getRepository("UserBundle:Card")->findOneBy(['uuid' => $uuid]);
        }


        return $this->render("AppBundle:Default:card.html.twig", ['uuid' => isset($card) ? $card : null]);
    }
}
