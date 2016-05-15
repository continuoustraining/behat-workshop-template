<?php

use Behat\Behat\Tester\Exception\PendingException;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;

/**
 * Behat context class.
 */
class RestContext implements SnippetAcceptingContext
{
    protected $client;

    /**
     * @var Request
     */
    protected $lastRequest;

    /**
     * @var Response
     */
    protected $lastResponse;

    protected $custormHeaders =
        [
        ];

    protected $customHeadersEnabled = true;

    /** @var array The query string to add (in URI Template format) */
    protected $queryString = [];

    /**
     * Initializes context.
     *
     * Every scenario gets it's own context object.
     * You can also pass arbitrary arguments to the context constructor through behat.yml.
     */
    public function __construct()
    {
        $this->client = new \GuzzleHttp\Client(
            [
                'base_uri' => 'http://172.17.0.2',
                'verify'   => false
            ]
        );
    }

    public function setCustomHeader($key, $value)
    {
        $this->custormHeaders[$key] = $value;
        return $this;
    }

    public function setCustomHeadersEnabled($customHeadersEnabled)
    {
        $this->customHeadersEnabled = $customHeadersEnabled;
        return $this;
    }

    public function getQueryString()
    {
        return $this->queryString;
    }

    /**
     * @When /^I send ([A-Z]+) request to "([^"]*)"$/
     * @When /^I send ([A-Z]+) request to "([^"]*)" with values:$/
     */
    public function iSendRequest($method, $url, TableNode $table = null)
    {
        $values = $table ? $table->getRowsHash() : [];

        $matches = [];
        preg_match_all('({[A-Z_0-9\ \.]+})', $url, $matches);

        foreach ($matches[0] as $match) {
            $url = str_replace($match, $this->getReplacements()[$match], $url);
        }

        $headers =
            [
                'Accept' => 'application/hal+json'
            ];

        if ($this->customHeadersEnabled) {
            $headers = array_merge($headers, $this->custormHeaders);
        }

        $this->lastResponse = $this->client->request(
            strtoupper($method),
            $url,
            [
                'headers'     => $headers,
                'form_params' => $values,
                'verify'      => false,
                'http_errors' => false,
                'query'       => $this->getQueryString()
            ]
        );
    }

    /**
     * @When /^I send ([A-Z]+) request to "([^"]*)" with payload from "([^"]*)"$/
     */
    public function iSendRequestWithPayloadFrom($method, $url, $fileName)
    {
        $matches = [];
        preg_match_all('({[A-Z_0-9\ \.]+})', $url, $matches);

        foreach ($matches[0] as $match) {
            $url = str_replace($match, $this->getReplacements()[$match], $url);
        }

        $headers =
            [
                'Accept' => 'application/hal+json',
                'Content-Type' => 'application/json'
            ];

        if ($this->customHeadersEnabled) {
            $headers = array_merge($headers, $this->custormHeaders);
        }

        $queryParams         = $this->getQueryString();
        $queryParamsFiltered = [];

        foreach ($queryParams as $key => $val) {
            if (preg_match('({[A-Z_0-9\ \.]+})', $val)) {
                $queryParamsFiltered[$key] = $this->getReplacements()[$val];
            } else {
                $queryParamsFiltered[$key] = $val;
            }
        }

        $this->lastResponse = $this->client->request(strtoupper($method), $url, [
            'http_errors' => false,
            'headers' => $headers,
            'body' => file_get_contents(__DIR__ . '/../_files/' . $fileName),
            'verify' => false,
            'query' => $queryParamsFiltered
        ]);
    }

    /**
     * @Given /^query string parameter "([^"]*)" with value "([^"]*)"$/
     */
    public function queryStringParameterWithValue($name, $value)
    {
        if (preg_match('/\[\]$/', $name)) {
            $this->queryString[str_replace('[]', '', $name)][] = $value;
        } else {
            $this->queryString[$name] = $value;
        }
    }

    /**
     * @Then /^response should be in JSON$/
     */
    public function responseShouldBeInJson()
    {
        $contentType = $this->getLastResponse()->getHeader('Content-Type');

        if ('application/hal+json' !== $contentType[0]) {
            throw new \Exception(sprintf('Expected json content type, but got %s.', $contentType[0]));
        }

        $this->getLastResponseJsonData();
    }

    /**
     * @Then /^response should be an ApiProblem$/
     */
    public function responseShouldBeAnApiProblem()
    {
        $contentType = $this->getLastResponse()->getHeader('Content-Type');

        if ('application/problem+json' !== $contentType[0]) {
            throw new \Exception(sprintf('Expected ApiProblem content type, but got %s.', $contentType[0]));
        }
    }

    /**
     * @Given /^the response has a "([^"]*)" property$/
     */
    public function theResponseHasAProperty($propertyName)
    {
        $this->getLastResponseJsonProperty($propertyName);
    }

    /**
     * @Then /^the "([^"]*)" property equals "([^"]*)"$/
     */
    public function thePropertyEquals($propertyName, $expectedValue)
    {
        $actualValue = $this->getLastResponseJsonProperty($propertyName);

        if ($expectedValue !== $actualValue) {
            throw new \Exception(sprintf(
                'Property "%s" was expected to equal "%s", but got "%s".',
                $propertyName,
                $expectedValue,
                $actualValue
            ));
        }
    }


    /**
     * @Then /^response status code should be (\d+)$/
     */
    public function responseStatusCodeShouldBe($httpStatus)
    {
        if ((string)$this->getLastResponse()->getStatusCode() !== $httpStatus) {

            throw new \Exception('HTTP code does not match '.$httpStatus.
                ' (actual: '.$this->getLastResponse()->getStatusCode().')' . PHP_EOL
                . $this->getLastResponse()->getBody());
        }
    }

    /**
     * @Then dump last request
     */
    public function dumpLastRequest()
    {
        $this->printDebug($this->getLastRequest().PHP_EOL.$this->getLastResponse());
    }

    /**
     * Prints beautified debug string.
     *
     * @param string $string debug string
     */
    public function printDebug($string)
    {
        echo "\n\033[36m|  " . strtr($string, array("\n" => "\n|  ")) . "\033[0m\n\n";
    }

    /**
     * Returns the last sent request
     *
     * @return Request
     */
    public function getLastRequest()
    {
        if (null === $this->lastRequest) {
            throw new \LogicException('No request sent yet.');
        }

        return $this->lastRequest;
    }

    /**
     * Returns the response of the last request
     *
     * @return \GuzzleHttp\Psr7\Response
     */
    public function getLastResponse()
    {
        if (null === $this->lastResponse) {
            throw new \LogicException('No request sent yet.');
        }

        return $this->lastResponse;
    }

    public function setLastResponse($lastResponse)
    {
        $this->lastResponse = $lastResponse;
    }

    public function getLastResponseJsonData()
    {
        $responseBody = $this->getLastResponse()->getBody(true);

        $data = json_decode($responseBody);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new \Exception(sprintf('Invalid json body: %s', $responseBody));
        }

        return $data;
    }

    public function getLastResponseBody()
    {
        return $this->getLastResponse()->getBody(true);
    }

    /**
     * @Then /^echo last response$/
     */
    public function echoLastResponse()
    {
        $this->printDebug(
            $this->getLastResponse()->getBody()
        );
    }

    /**
     * @Then /^response should contain CORS headers$/
     */
    public function responseShouldContainCORSHeaders()
    {
        $responseHeaders = array_keys($this->getLastResponse()->getHeaders());

        $corsHeadersExpected = [
            'Access-Control-Allow-Origin',
            'Access-Control-Allow-Methods',
            'Access-Control-Allow-Headers'
        ];

        $corsHeadersFound = [];

        foreach ($responseHeaders as $responseHeader) {
            if (in_array($responseHeader, $corsHeadersExpected)) {
                $corsHeadersFound[] = $responseHeader;
            }
        }

        if ($corsHeadersFound != $corsHeadersExpected) {
            throw new \Exception('Found the following CORS headers : ' . implode(', ', $corsHeadersFound));
        }
    }

    /**
     * @Then /^response value "([^"]*)" should be "([^"]*)"$/
     */
    public function responseValueShouldBe($property, $value)
    {
        $replacements = $this->getReplacements();

        $matches = [];

        if (preg_match('/^\{(.*)\}$/', $value, $matches)) {
            $value = $replacements['{' . $matches[1] . '}'];
        }

        $data = $this->getLastResponseJsonProperty($property);
        if ($data != $value) {
            throw new \Exception(sprintf('Expected value for property %s was %s, got %s', $property, $value, $data));
        }
    }

    /**
     * @Then /^response value "([^"]*)" should contain the key "([^"]*)" with value "([^"]*)"$/
     */
    public function responseValueShouldContainTheKeyWithValue($level1Key, $level2key, $value)
    {
        $replacements = $this->getReplacements();

        $matches = [];

        if (preg_match('/^\{(.*)\}$/', $value, $matches)) {
            $value = $replacements['{' . $matches[1] . '}'];
        }

        $data = $this->getLastResponseJsonProperty($level1Key);
        if ($data->$level2key != $value) {
            throw new \Exception(sprintf('Expected value for property %s was %s, got %s', "$level1Key -> $level2key", $value, $data->$level2key));
        }
    }

    /**
     * @param $propertyName
     * @return string
     */
    public function getLastResponseJsonProperty($propertyName)
    {
        $data = $this->getLastResponseJsonData();
        if (!isset($data->$propertyName)) {
            throw new \UnexpectedValueException('Response does not contain property ' . $propertyName);
        }
        return $data->$propertyName;
    }

    /**
     * @Then /^response should be a collection of "([^"]*)"$/
     */
    public function responseShouldBeACollectionOf($resourceName)
    {
        $response = $this->getLastResponseJsonData();

        if (!property_exists($response->_embedded, $resourceName) || !is_array($response->_embedded->$resourceName)) {
            throw new \Exception('Returned ' . $resourceName . ' collection is invalid.');
        }
    }

    /**
     * @Then /^response collection should contain exactly ([^"]*) "([^"]*)"$/
     */
    public function responseCollectionShouldContainExactly($nbEntries, $typeEntries)
    {
        $response = $this->getLastResponseJsonData();

        if (count($response->_embedded->$typeEntries) != $nbEntries) {
            throw new \Exception("The entry count doesn't match: " . count($response->_embedded->$typeEntries));
        }
    }

    /**
     * @Given response collection ":arg1" should contain the resource:
     */
    public function responseCollectionShouldContainTheResource($collectionName, TableNode $table)
    {
        $params = $table->getRowsHash();
        $response = $this->getLastResponseJsonData();

        $resourceFound = false;

        foreach ($response->_embedded->$collectionName as $resource) {
            $paramsOk = true;

            foreach ($params as $key => $val) {
                if ($val === 'true') {
                    $val = true;
                } else if ($val === 'false') {
                    $val = false;
                }

                if ($val != $resource->$key) {
                    $paramsOk = false;
                    break;
                }
            }

            if ($paramsOk) {
                $resourceFound = true;
                break;
            }
        }

        if (!$resourceFound) {
            throw new \Exception('Resource not found.');
        }
    }

    /**
     * @Then response entity should contain the values:
     */
    public function responseEntityShouldContainTheValues(TableNode $table)
    {
        $params = $table->getRowsHash();
        $response = $this->getLastResponseJsonData();

        foreach ($params as $key => $val) {
            if ($val === 'true') {
                $val = true;
            } else if ($val === 'false') {
                $val = false;
            }

            if ($val != $response->$key) {
                throw new \Exception('Resource not found.');
            }
        }
    }
}
