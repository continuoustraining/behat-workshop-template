<?php

use Behat\Behat\Tester\Exception\PendingException;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;

/**
 * Behat context class.
 */
class InterfaceContext extends \Behat\MinkExtension\Context\RawMinkContext
    implements SnippetAcceptingContext, \Behat\MinkExtension\Context\MinkAwareContext
{
    /** @var \Behat\MinkExtension\Context\MinkContext */
    private $minkContext;

    /** @BeforeScenario */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $environment = $scope->getEnvironment();

        $this->minkContext = $environment->getContext('Behat\MinkExtension\Context\MinkContext');
    }

    /**
     * @Then after some time I should be on :arg1 and see :arg2
     */
    public function afterSomeTimeIShouldSee($arg1, $arg2)
    {
        $minkContext = $this->minkContext;
        $this->spin(function($context) use ($minkContext, $arg1) {
            return $context->getSession()->getCurrentUrl() === $arg1;
        }, 10);
        $this->assertSession()->pageTextContains(str_replace('\\"', '"', $arg2));
    }
    protected function spin($lambda, $wait = 60)
    {
        for ($i = 0; $i < $wait; $i++) {
            try {
                if ($lambda($this)) {
                    return true;
                }
            } catch (Exception $e) {
                // do nothing
            }
            sleep(1);
        }
        throw new Exception("Timeout after $wait seconds.");
    }
}
