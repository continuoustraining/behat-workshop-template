<?php

namespace Ecommerce\V1\Rest\Users;

use Zend\ServiceManager\ServiceLocatorAwareTrait;

class UsersService
{
    use ServiceLocatorAwareTrait;

    public function createUser(array $data)
    {
        /** @var \Ecommerce\V1\Rest\Users\UsersMapper $mapperUsers */
        $mapperUsers = $this->getServiceLocator()->get('mapper.user');

        /** @var \Ecommerce\V1\Rest\Users\UsersEntity $user */
        $user = $this->getServiceLocator()->get('entity.user');
        $user
            ->setUsername($data['username'])
            ->setFirstname($data['firstname'])
            ->setLastname($data['lastname']);

        $mapperUsers
            ->store($user)
            ->flush($user);
    }
}
