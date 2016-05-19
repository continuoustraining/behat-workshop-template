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
     * @BeforeScenario
     */
    public function purgeDatabase()
    {
        $purger = new \Doctrine\Common\DataFixtures\Purger\ORMPurger($this->getEntityManager());
        $purger->purge();
    }

    /**
     * @Given a user:
     */
    public function aUser(TableNode $table)
    {
        /** @var \Ecommerce\V1\Rest\Users\UsersEntity $user */
        $user = $this->getServiceManager()->get('entity.user');

        /** @var \Ecommerce\V1\Rest\Users\UsersMapper $mapperUsers */
        $mapperUsers = $this->getServiceManager()->get('mapper.user');

        foreach ($table->getRows() as $property) {
            $setter = 'set' . ucfirst($property[0]);
            $user->$setter($property[1]);
        }

        $mapperUsers->store($user)
                    ->flush($user);
    }

    /**
     * @Then a :arg1 with the following data should have been created:
     */
    public function aWithTheFollowingDataShouldHaveBeenCreated($entityName, TableNode $table)
    {
        /** @var \Ecommerce\V1\Rest\Users\UsersMapper $mapperUsers */
        $mapperUsers = $this->getServiceManager()->get('mapper.' . $entityName);
        $users = $mapperUsers->findAll();

        if (count($users) != 1) {
            throw new \Exception('Exactly 1 user should have been created. Found: ' . count($users));
        }
        
        foreach ($table->getRows() as $property) {
            $getter = 'get' . ucfirst($property[0]);
            if ($property[1] != $users[0]->$getter()) {
                throw new \Exception('Resource not found.');
            }
        }
    }
}
