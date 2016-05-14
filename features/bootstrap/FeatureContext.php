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

    /**
     * @BeforeScenario
     */
    public function purgeDatabase()
    {
        $purger = new \Doctrine\Common\DataFixtures\Purger\ORMPurger($this->getEntityManager());
        $purger->purge();
    }

    public function getServiceManager()
    {
        return $this->zf2MvcApplication->getServiceManager();
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
     * @Given a CPL:
     */
    public function aCpl(TableNode $table)
    {
        /** @var \Application\Entity\CompositionPlaylist $cpl */
        $cpl = $this->getServiceManager()->get('entity.composition-playlist');
        $cpl->setLastUpdate(new \DateTime());

        /** @var \Application\Service\CompositionPlaylist $serviceCpl */
        $serviceCpl = $this->getServiceManager()->get('service.composition-playlist');

        foreach ($table->getRows() as $property) {
            $setter = 'set' . ucfirst($property[0]);
            $cpl->$setter($property[1]);
        }

        $serviceCpl->store($cpl);
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
     * @Given response collection :arg1 should contain the resource:
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
     * @Given a cinema:
     */
    public function aCinema(TableNode $table)
    {
        /** @var \Application\Entity\Cinema $cinema */
        $cinema = $this->getServiceManager()->get('entity.cinema');

        /** @var \Application\Service\Cinema $serviceCinema */
        $serviceCinema = $this->getServiceManager()->get('service.cinema');

        foreach ($table->getRows() as $property) {
            $setter = 'set' . ucfirst($property[0]);
            $cinema->$setter($property[1]);
        }

        $serviceCinema->store($cinema);
    }

    /**
     * @Given a chain:
     */
    public function aChain(TableNode $table)
    {
        /** @var \Application\Entity\Chain $chain */
        $chain = $this->getServiceManager()->get('entity.chain');

        /** @var \Application\Service\Chain $serviceChain */
        $serviceChain = $this->getServiceManager()->get('service.chain');

        foreach ($table->getRows() as $property) {
            $setter = 'set' . ucfirst($property[0]);
            $chain->$setter($property[1]);
        }

        $serviceChain->store($chain);
    }

    /**
     * @Given cinema :arg1 is linked to chain :arg2
     */
    public function cinemaIsLinkedToChain($cinemaId, $chainId)
    {
        $mapperCinema = $this->getServiceManager()->get('mapper.cinema');
        $cinema       = $mapperCinema->find($cinemaId);

        $mapperChain = $this->getServiceManager()->get('mapper.chain');
        $chain       = $mapperChain->find($chainId);

        $cinema->setChain($chain);
        $mapperCinema->flush($cinema);
    }

    /**
     * @Given a player of type :arg1:
     */
    public function aPlayer($playerType, TableNode $table)
    {
        /** @var \Application\Entity\Player $player */
        $player = $this->getServiceManager()->get('entity.player.' . $playerType);

        /** @var \Application\Service\Player $servicePlayer */
        $servicePlayer = $this->getServiceManager()->get('service.player');

        foreach ($table->getRows() as $property) {
            $setter = 'set' . ucfirst($property[0]);
            $player->$setter($property[1]);
        }

        $servicePlayer->store($player);
    }

    /**
     * @Given player :arg1 is linked to to cinema :arg2
     */
    public function playerIsLinkedToToCinema($playerId, $cinemaId)
    {
        $mapperCinema = $this->getServiceManager()->get('mapper.cinema');
        $cinema       = $mapperCinema->find($cinemaId);

        $mapperPlayer = $this->getServiceManager()->get('mapper.player');
        $player       = $mapperPlayer->find($playerId);

        $player->setCinema($cinema);
        $mapperPlayer->flush($player);
    }

    /**
     * @Given the Playout Log records:
     */
    public function thePlayoutLogRecords(TableNode $table)
    {
        $mapperCpl         = $this->getServiceManager()->get('mapper.composition-playlist');
        $mapperPlayer      = $this->getServiceManager()->get('mapper.player');
        $servicePlayoutLog = $this->getServiceManager()->get('service.playout-log');

        foreach ($table->getRows() as $row) {
            $cpl = $mapperCpl->find($row[1]);
            $player = $mapperPlayer->find($row[2]);

            /** @var \Application\Entity\PlayoutLog $record */
            $record = $this->getServiceManager()->get('entity.playout-log');
            $record->setId($row[0])
                   ->setCompositionPlaylist($cpl)
                   ->setPlayer($player)
                   ->setCreated(new \DateTime())
                   ->setRecordTimestamp(date_create()->createFromFormat('Y-m-d\TH:i:sP', $row[3]))
                   ->setRecordType($row[4])
                   ->setRecordSubType($row[5]);
            $servicePlayoutLog->store($record);
        }
    }

    /**
     * @Given response report should contain exactly :arg1 entries
     */
    public function responseReportShouldContainExactlyEntries($nbEntries)
    {
        $response = $this->getLastResponseJsonData();

        if (count($response->playoutReport) != $nbEntries) {
            throw new \Exception("The entry count doesn't match: " . count($response->playoutReport));
        }
    }

    /**
     * @Given response report should contain the entry:
     */
    public function responseReportShouldContainTheEntry(TableNode $table)
    {
        $params = $table->getRowsHash();
        $response = $this->getLastResponseJsonData();

        $entryFound = false;

        foreach ($response->playoutReport as $entry) {
            $paramsOk = true;

            foreach ($params as $key => $val) {
                if ($val != $entry->$key) {
                    $paramsOk = false;
                    break;
                }
            }

            if ($paramsOk) {
                $entryFound = true;
                break;
            }
        }

        if (!$entryFound) {
            throw new \Exception('Entry not found.');
        }
    }

    /**
     * @Given a room :arg1 with number :arg2 and name :arg3 having :arg4 seats
     */
    public function aRoomWithNumber($roomId, $roomNumber, $roomName, $nbSeats)
    {
        /** @var \Application\Entity\Room $room */
        $room = $this->getServiceManager()->get('entity.room');
        $room->setId($roomId)
             ->setNum($roomNumber)
             ->setName($roomName)
             ->setSeats($nbSeats)
             ->setSupport35mm(false)
             ->setSupport3D(false);

        /** @var \Application\Service\Room $serviceRoom */
        $serviceRoom = $this->getServiceManager()->get('service.room');

        $serviceRoom->store($room);
    }

    /**
     * @Given room :arg1 is is linked to cinema :arg2
     */
    public function roomIsIsLinkedToCinema($roomId, $cinemaId)
    {
        $mapperCinema = $this->getServiceManager()->get('mapper.cinema');
        $cinema       = $mapperCinema->find($cinemaId);

        $mapperRoom = $this->getServiceManager()->get('mapper.room');
        $room       = $mapperRoom->find($roomId);

        $room->setCinema($cinema);
        $mapperRoom->flush($room);
    }

    /**
     * @Given player :arg1 is linked to to room :arg2
     */
    public function playerIsLinkedToToRoom($playerId, $roomId)
    {
        $mapperRoom = $this->getServiceManager()->get('mapper.room');
        $room       = $mapperRoom->find($roomId);

        $mapperPlayer = $this->getServiceManager()->get('mapper.player');
        $player       = $mapperPlayer->find($playerId);

        $player->setRoom($room);
        $mapperPlayer->flush($player);
    }
}
