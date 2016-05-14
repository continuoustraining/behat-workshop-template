<?php
namespace Ecommerce\V1\Rest\Users;

use Ecommerce\EntityAbstract;

/**
 * Class UsersEntity
 * @package Ecommerce\V1\Rest\Users
 *
 * @ORM\Entity
 * @ORM\Table(name="user")
 */
class UsersEntity extends EntityAbstract
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string")
     * 
     * @var string
     */
    protected $username;

    /**
     * @ORM\Column(type="string")
     * 
     * @var string
     */
    protected $firstname;

    /**
     * @ORM\Column(type="string")
     * 
     * @var string
     */
    protected $lastname;

    /**
     * @ORM\Column(type="string")
     * 
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param string $username
     * @return UsersEntity
     */
    public function setUsername($username)
    {
        $this->username = $username;
        return $this;
    }

    /**
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * @param string $firstname
     * @return UsersEntity
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;
        return $this;
    }

    /**
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * @param string $lastname
     * @return UsersEntity
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;
        return $this;
    }
}
