<?php

namespace Emercoin\OAuthBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="users")
 *
 * @ORM\AttributeOverrides({
 *  @ORM\AttributeOverride(name="usernameCanonical",
 *      column=@ORM\Column(
 *          name = "username_canonical",
 *          type = "string",
 *          length = 180,
 *          nullable = false,
 *          unique = false
 *      )
 *  ),
 *  @ORM\AttributeOverride(name="emailCanonical",
 *      column=@ORM\Column(
 *          name = "email_canonical",
 *          type = "string",
 *          length = 180,
 *          nullable = false,
 *          unique = false
 *      )
 *  )
 * })
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="serial", type="string", length=255, nullable=false, unique=true)
     */
    protected $serial;

    /**
     * @var string
     *
     * @ORM\Column(name="infocard", type="text", nullable=true)
     */
    protected $infocard;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Emercoin\OAuthBundle\Entity\Client", mappedBy="user")
     */
    protected $clients;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Set serial
     *
     * @param string $serial
     * @return User
     */
    public function setSerial($serial)
    {
        $this->serial = $serial;

        return $this;
    }

    /**
     * Get serial
     *
     * @return string
     */
    public function getSerial()
    {
        return $this->serial;
    }

    /**
     * Add clients
     *
     * @param Client $clients
     * @return User
     */
    public function addClient(Client $clients)
    {
        $this->clients[] = $clients;

        return $this;
    }

    /**
     * Remove clients
     *
     * @param Client $clients
     */
    public function removeClient(Client $clients)
    {
        $this->clients->removeElement($clients);
    }

    /**
     * Get clients
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getClients()
    {
        return $this->clients;
    }

    /**
     * Set infocard
     *
     * @param string $infocard
     * @return User
     */
    public function setInfocard($infocard)
    {
        $this->infocard = $infocard;

        return $this;
    }

    /**
     * Get infocard
     *
     * @return string
     */
    public function getInfocard()
    {
        return $this->infocard;
    }
}
