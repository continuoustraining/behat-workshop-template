<?php

namespace Ecommerce\V1\Rest\Users;

class UsersResourceFactory
{
    public function __invoke($services)
    {
        $resource = new UsersResource();
        $resource->setServiceLocator($services);

        return $resource;
    }
}
