<?php

namespace Ecommerce\V1\Rest\Users;

use Ecommerce\MapperDoctrineAbstract;

class UsersMapper extends MapperDoctrineAbstract
{
    public function findAllUsers()
    {
        $queryStr = 'SELECT u FROM Ecommerce\V1\Rest\Users\UsersEntity u ';
        $query    = $this->getEntityManager()->createQuery($queryStr);

        return $query;
    }
}
