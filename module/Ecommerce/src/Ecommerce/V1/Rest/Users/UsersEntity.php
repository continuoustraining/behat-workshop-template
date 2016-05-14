<?php

namespace Ecommerce\V1\Rest\Users;

use Ecommerce\EntityAbstract;
use Doctrine\ORM\Mapping as ORM;
use Zend\Stdlib\ArraySerializableInterface;

/**
 * Class UsersEntity
 * @package Ecommerce\V1\Rest\Users
 *
 * @ORM\Entity
 * @ORM\Table(name="users")
 */
class UsersEntity extends EntityAbstract implements ArraySerializableInterface, \JsonSerializable
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

    public function getArrayCopy()
    {
        return [
            'id'        => $this->getUsername(),
            'username'  => $this->getUsername(),
            'firstname' => $this->getFirstname(),
            'lastname'  => $this->getLastname()
        ];
    }

    public function jsonSerialize()
    {
        return $this->getArrayCopy();
    }
}
