<?php
/**
 * Created by PhpStorm.
 * User: maps_red
 * Date: 29/11/16
 * Time: 10:31
 */

namespace AppBundle\Utils;

use Symfony\Component\DependencyInjection\ContainerInterface;
use UserBundle\Entity\User;

/**
 * Class Api
 * @package AppBundle\Utils
 */
class Api
{
    /** @var ContainerInterface $container */
    private $container;
    /** @var User $user */
    private $user;
    /** @var string $apikey */
    private $apikey;

    /**
     * Api constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param $api_key
     * @return Api
     */
    public function setApiKey($api_key)
    {
        $this->apikey = $api_key;

        return $this;
    }
    /**
     * @return null|object|User
     */
    public function getUser()
    {
        return !empty($this->user) ? $this->user :
            $this->container->get("doctrine")->getRepository("UserBundle:User")->findOneBy(['apiToken' => $this->apikey]);
    }
}