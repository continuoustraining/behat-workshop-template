<?php

namespace Ecommerce\V1\Rest\Users;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class UsersMapperFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $mapperUsers = new \Ecommerce\V1\Rest\Users\UsersMapper();
        $mapperUsers->setEntityClassName('\Ecommerce\V1\Rest\Users\UsersEntity');
        $mapperUsers->setServiceManager($serviceLocator);

        return $mapperUsers;
    }
}