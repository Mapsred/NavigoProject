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
 * @property bool order_by
 */
class Api
{
    private $container;
    private $data;
    private $order;
    private $limit;
    private $user;
    private $method;
    private $apikey;


    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param null $api_key
     * @return Api
     */
    public function setApiKey($api_key = null)
    {
        $this->method = strtolower($_SERVER['REQUEST_METHOD']);
        switch ($this->method) {
            case 'get':
                $this->data = $_GET;
                break;
            case 'post':
                $this->data = $_POST;
                break;
            case 'put':
            case 'delete':
                parse_str(file_get_contents('php://input'), $put_vars);
                $this->data = $put_vars;
                break;
            default:
//                $this->throwError(12, $this->method);
        }
        $this->apikey = $api_key;
        // Lowercase the key data, ignores the case of the arguments.
        $this->data = array_change_key_case($this->data);

        // Check if order by argument is set.
        if ($this->hasRequest('order_by')) {
            if (!$this->getRequest('order_by')) {
                // Throw error if the 'order_by' argument is set but empty.
                $this->throwError(1, 'order_by');
            }
            $this->order_by = $this->getRequest('order_by');
        }
        // Check if order argument is set.
        if ($this->hasRequest('order')) {
            if (!$this->getRequest('order')) {
                // Throw error if the 'order' argument is set but empty.
                $this->throwError(1, 'order');
            }
            $this->order = strtolower($this->getRequest('order'));
            if (in_array($this->order, ['d', 'desc', 'descending'])) {
                // Order is descending (10-0).
                $this->order = 'desc';
            } else {

                if (in_array($this->order, ['a', 'asc', 'ascending'])) {
                    // Order is ascending (0-10).
                    $this->order = 'asc';
                } else {
                    // Order is unknown, default to descending (10-0).
                    $this->order = 'desc';
                }
            }
        } else {
            // Order is not set, default to descending (10-0).
            $this->order = 'desc';
        }
        // Set the limit.
        $this->setLimit(100);

        return $this;
    }

    /**
     * Returns TRUE if the '$key' is set a argument, returns FALSE if not.
     * @param $key
     * @return bool
     */
    public function hasRequest($key)
    {
        return isset($this->data[strtolower($key)]);
    }

    /**
     * Gets the data of the '$key', returns FALSE if the '$key' argument was not set.
     * @param $key
     * @return bool
     */
    public function getRequest($key)
    {
        return $this->hasRequest($key) ? $this->data[strtolower($key)] : false;
    }

    /**
     * @return bool
     */
    public function verifyApiKey()
    {

        return true;
    }

    /**
     * @param $int
     * @param $string
     * @throws \Exception
     */
    private function throwError($int, $string)
    {
        if ($int == 1) {
            throw new \Exception($string." is defined empty");
        } elseif ($int == 21) {
            throw new \Exception($string." is defined but is not a number");
        }
    }

    /**
     * @param $default
     */
    public function setLimit($default)
    {
        // Check if limit argument is set.
        if ($this->hasRequest('limit')) {
            if (!$this->getRequest('limit') && (is_numeric($this->getRequest('limit')) && $this->getRequest(
                        'limit'
                    ) != 0)
            ) {
                // Throw error if the 'limit' argument is set but empty.
                $this->throwError(1, 'limit');
            } else {
                if (!is_numeric($this->getRequest('limit'))) {
                    // Throw error if the 'limit' argument is set but not a number.
                    $this->throwError(21, 'limit');
                }
            }
            $this->limit = $this->getRequest('limit');
        } else {
            // Limit is not set, default to $default variable.
            $this->limit = $default;
        }
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