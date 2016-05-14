<?php

namespace Ecommerce;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

class MapperDoctrineAbstract implements MapperDoctrineInterface
{
    /** @var \Zend\ServiceManager\ServiceManager */
    protected $serviceManager;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var EntityRepository
     */
    protected $entityRepository;

    /**
     * @var string
     */
    protected $entityClassName;

    /**
     * @return \Zend\ServiceManager\ServiceManager
     */
    public function getServiceManager()
    {
        return $this->serviceManager;
    }

    /**
     * @param \Zend\ServiceManager\ServiceManager $serviceManager
     * @return ContentManagementController
     */
    public function setServiceManager(\Zend\ServiceManager\ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
        return $this;
    }

    /**
     * Get entity manager
     *
     * @return EntityManager
     * @throws Exception
     */
    public function getEntityManager()
    {
        if (!$this->entityManager) {
            if ($this->getServiceManager()->has('entity_manager')) {
                $this->setEntityManager($this->getServiceManager()->get('entity_manager'));
            } else {
                throw new Exception('No entity manager set.');
            }
        }

        return $this->entityManager;
    }

    /**
     * Set entity Manager
     *
     * @param EntityManager $entityManager
     *
     * @return $this
     */
    public function setEntityManager(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;

        return $this;
    }

    /**
     * Set entityRepository
     *
     * @param EntityRepository $entityRepository
     */
    public function setEntityRepository(EntityRepository $entityRepository)
    {
        $this->entityRepository = $entityRepository;
    }

    /**
     * Get entityRepository
     *
     * @return EntityRepository
     * @throws Exception
     */
    public function getEntityRepository()
    {
        if (!$this->entityRepository) {
            if ($this->getEntityClassName()) {
                $this->entityRepository = $this->getEntityManager()->getRepository($this->getEntityClassName());
            } else {
                throw new Exception('No Entity Class defined.');
            }
        }

        return $this->entityRepository;
    }

    /**
     * set entity class name
     *
     * @param string $entityClassName
     *
     * @return $this
     */
    public function setEntityClassName($entityClassName)
    {
        $this->entityClassName = (string)$entityClassName;

        return $this;
    }

    /**
     * get entity class Name
     *
     * @return string
     */
    public function getEntityClassName()
    {
        return $this->entityClassName;
    }

    /**
     * Persists the passed entity
     *
     * @param EntityAbstract $entity
     *
     * @return $this
     */
    public function store(EntityAbstract $entity)
    {
        $this->getEntityManager()->persist($entity);

        return $this;
    }

    public function remove(EntityAbstract $entity)
    {
        $this->getEntityManager()->remove($entity);

        return $this;
    }

    /**
     * Flush queries
     *
     * @param \Application\Entity\EntityAbstract The entity to flush
     * @return $this
     */
    public function flush(EntityAbstract $entity = null)
    {
        $this->getEntityManager()->flush($entity);

        return $this;
    }

    /**
     * Begin a transaction
     *
     * @return $this
     */
    public function transactionBegin()
    {
        $this->getEntityManager()->beginTransaction();

        return $this;
    }

    /**
     * Commit a transaction
     *
     * @return $this
     */
    public function transactionCommit()
    {
        $this->getEntityManager()->commit();

        return $this;
    }

    /**
     * Rollback change
     *
     * @return $this
     */
    public function transactionRollback()
    {
        $this->getEntityManager()->rollback();

        return $this;
    }

    public function find($id)
    {
        return $this->getEntityRepository()->find($id);
    }

    public function findOneBy(array $criteria, array $orderBy = null)
    {
        return $this->getEntityRepository()->findOneBy($criteria, $orderBy);
    }

    public function findAll()
    {
        return $this->getEntityRepository()->findAll();
    }

    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        return $this->getEntityRepository()->findBy($criteria, $orderBy, $limit, $offset);
    }
}