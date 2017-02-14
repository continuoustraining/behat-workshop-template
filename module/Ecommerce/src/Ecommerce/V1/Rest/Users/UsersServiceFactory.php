<?php

namespace Ecommerce\V1\Rest\Users;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class UsersServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $serviceUsers = new \Ecommerce\V1\Rest\Users\UsersService();
        $serviceUsers->setServiceLocator($serviceLocator);

        return $serviceUsers;
    }
}