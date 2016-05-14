<?php

namespace Ecommerce;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

interface MapperDoctrineInterface extends MapperInterface
{
    /**
     * Get entity manager
     *
     * @return EntityManager
     */
    public function getEntityManager();

    /**
     * Set entity Manager
     *
     * @param EntityManager $entityManager
     *
     * @return $this
     */
    public function setEntityManager(EntityManager $entityManager);

    /**
     * Set entityRepository
     *
     * @param EntityRepository $entityRepository
     */
    public function setEntityRepository(EntityRepository $entityRepository);

    /**
     * Get entityRepository
     *
     * @return EntityRepository
     */
    public function getEntityRepository();

    /**
     * set entity class name
     *
     * @param $entityClassName
     *
     * @return $this
     */
    public function setEntityClassName($entityClassName);

    /**
     * get entity class Name
     *
     * @return string
     */
    public function getEntityClassName();
}