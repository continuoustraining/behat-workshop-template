<?php

namespace Ecommerce;

interface MapperInterface
{
    /**
     * Persists the passed entity
     *
     * @param EntityAbstract $entity
     *
     * @return bool
     */
    public function store(EntityAbstract $entity);

    /**
     * Begin a transaction
     */
    public function transactionBegin();

    /**
     * Commit a transaction
     */
    public function transactionCommit();

    /**
     * Rollback change
     */
    public function transactionRollback();
}