<?php

namespace UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Card
 *
 * @ORM\Table(name="card")
 * @ORM\Entity(repositoryClass="UserBundle\Repository\CardRepository")
 */
class Card
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="UserBundle\Entity\User", mappedBy="card", cascade={"persist"})
     */
    private $user;

    /**
     * @var string
     *
     * @ORM\Column(name="uuid", type="string", length=255)
     */
    private $uuid;

    /**
     * @var string
     *
     * @ORM\Column(name="firstname", type="string", length=255, nullable=true)
     */
    private $firstname;

    /**
     * @var string
     *
     * @ORM\Column(name="lastname", type="string", length=255, nullable=true)
     */
    private $lastname;


    /**
     * @var \DateTime $expiratedAt
     *
     * @ORM\Column(type="datetime", name="expirated_at")
     */
    private $expiratedAt;

    /**
     * Card constructor.
     */
    public function __construct()
    {
        $this->expiratedAt = (new \DateTime())->add(new \DateInterval("P2M"));
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set user
     *
     * @param string $user
     *
     * @return Card
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return string
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set uuid
     *
     * @param string $uuid
     *
     * @return Card
     */
    public function setUuid($uuid)
    {
        $this->uuid = $uuid;

        return $this;
    }

    /**
     * Get uuid
     *
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    public function __toString()
    {
        return (string)$this->uuid;
    }

    /**
     * Set firstname
     *
     * @param string $firstname
     *
     * @return Card
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * Get firstname
     *
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * Set lastname
     *
     * @param string $lastname
     *
     * @return Card
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * Get lastname
     *
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * Set expiratedAt
     *
     * @param \DateTime $expiratedAt
     *
     * @return Card
     */
    public function setExpiratedAt($expiratedAt)
    {
        $this->expiratedAt = $expiratedAt;

        return $this;
    }

    /**
     * Get expiratedAt
     *
     * @return \DateTime
     */
    public function getExpiratedAt()
    {
        return $this->expiratedAt;
    }

    /**
     * @return bool
     */
    public function isExpired()
    {
        return new \DateTime() > $this->getExpiratedAt();
    }
}
