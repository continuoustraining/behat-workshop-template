<?php

namespace Ecommerce\V1\Rest\Users;

use Zend\ServiceManager\ServiceLocatorAwareTrait;
use ZF\ApiProblem\ApiProblem;
use ZF\Rest\AbstractResourceListener;

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
            return new ApiProblem(400, 'Bad Request', "Couldn't create user.");
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
        return new ApiProblem(405, 'The DELETE method has not been defined for individual resources');
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
        return new ApiProblem(405, 'The GET method has not been defined for individual resources');
    }

    /**
     * Fetch all or a subset of resources
     *
     * @param  array $params
     * @return ApiProblem|mixed
     */
    public function fetchAll($params = array())
    {
        return new ApiProblem(405, 'The GET method has not been defined for collections');
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
