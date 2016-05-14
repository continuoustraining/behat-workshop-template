<?php

namespace Ecommerce\V1\Rest\Users;

use Zend\ServiceManager\ServiceLocatorAwareTrait;
use ZF\ApiProblem\ApiProblem;
use ZF\Rest\AbstractResourceListener;
use \DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use \Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;

class UsersResource extends AbstractResourceListener
{
    use ServiceLocatorAwareTrait;

    /**
     * Create a resource
     *
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function create($data)
    {
        /** @var \Ecommerce\V1\Rest\Users\UsersMapper $mapperUsers */
        $mapperUsers = $this->getServiceLocator()->get('mapper.user');

        /** @var \Ecommerce\V1\Rest\Users\UsersEntity $user */
        $user = $this->getServiceLocator()->get('entity.user');
        $user->setUsername($data->username)
             ->setFirstname($data->firstname)
             ->setLastname($data->lastname);

        try {
            $mapperUsers->store($user)
                        ->flush($user);
        } catch (\Exception $e) {
            return new ApiProblem(400, "Couldn't create user.", null, 'Bad Request');
        }

        return $user;
    }

    /**
     * Delete a resource
     *
     * @param  mixed $id
     * @return ApiProblem|mixed
     */
    public function delete($id)
    {
        /** @var \Ecommerce\V1\Rest\Users\UsersMapper $mapperUsers */
        $mapperUsers = $this->getServiceLocator()->get('mapper.user');

        if (!$user = $mapperUsers->find($id)) {
            return new ApiProblem(404, "Couldn't find user with id '$id'.", null, 'Not Found');
        }

        $mapperUsers->remove($user)
                    ->flush($user);

        return true;
    }

    /**
     * Delete a collection, or members of a collection
     *
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function deleteList($data)
    {
        return new ApiProblem(405, 'The DELETE method has not been defined for collections');
    }

    /**
     * Fetch a resource
     *
     * @param  mixed $id
     * @return ApiProblem|mixed
     */
    public function fetch($id)
    {
        /** @var \Ecommerce\V1\Rest\Users\UsersMapper $mapperUsers */
        $mapperUsers = $this->getServiceLocator()->get('mapper.user');

        if (!$user = $mapperUsers->find($id)) {
            return new ApiProblem(404, "Couldn't find user with id '$id'.", null, 'Not Found');
        }

        return $user;
    }

    /**
     * Fetch all or a subset of resources
     *
     * @param  array $params
     * @return ApiProblem|mixed
     */
    public function fetchAll($params = array())
    {
        /** @var \Ecommerce\V1\Rest\Users\UsersMapper $mapperUsers */
        $mapperUsers = $this->getServiceLocator()->get('mapper.user');

        $adapter    = new DoctrineAdapter(new DoctrinePaginator($mapperUsers->findAllUsers()));
        $collection = new \Ecommerce\V1\Rest\Users\UsersCollection($adapter);

        return $collection;
    }

    /**
     * Patch (partial in-place update) a resource
     *
     * @param  mixed $id
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function patch($id, $data)
    {
        return new ApiProblem(405, 'The PATCH method has not been defined for individual resources');
    }

    /**
     * Replace a collection or members of a collection
     *
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function replaceList($data)
    {
        return new ApiProblem(405, 'The PUT method has not been defined for collections');
    }

    /**
     * Update a resource
     *
     * @param  mixed $id
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function update($id, $data)
    {
        return new ApiProblem(405, 'The PUT method has not been defined for individual resources');
    }
}
