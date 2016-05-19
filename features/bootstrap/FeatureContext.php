<?php

use Behat\Behat\Tester\Exception\PendingException;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;

/**
 * Behat context class.
 */
class FeatureContext implements SnippetAcceptingContext
{
    /**
     * Initializes context.
     *
     * Every scenario gets it's own context object.
     * You can also pass arbitrary arguments to the context constructor through behat.yml.
     */
    public function __construct()
    {
        require_once(__DIR__ . '/../../vendor/autoload.php');

        ini_set('memory_limit', '-1');

        $this->zf2MvcApplication = \Zend\Mvc\Application::init(require __DIR__ . '/../../config/application.config.php');
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEntityManager()
    {
        return $this->getServiceManager()->get('entity_manager');
    }

    public function getServiceManager()
    {
        return $this->zf2MvcApplication->getServiceManager();
    }

    /**
     * @Given a user:
     */
    public function aUser(TableNode $table)
    {
        throw new PendingException();
    }
}
