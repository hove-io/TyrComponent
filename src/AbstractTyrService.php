<?php

namespace CanalTP\TyrComponent;

abstract class AbstractTyrService
{
    /**
     * @var string
     */
    protected $wsUrl;

    /**
     * @var string
     */
    protected $endPointId;

    /**
     * @var mixed
     */
    protected $client;

    /**
     * @var mixed
     */
    protected $lastResponse;

    /**
     * Constructor.
     *
     * @param string $wsUrl
     * @param string $endPointId
     */
    public function __construct($wsUrl, $endPointId)
    {
        $this->checkGuzzleVersion();

        $this->wsUrl = $wsUrl;
        $this->endPointId = $endPointId;

        $this->setClient($this->createDefaultClient());
    }

    /**
     * @throws Exception\NotSupportedException when Guzzle vendor version is not supported.
     * @throws Exception\VersionCheckerException when version is supported is not $version.
     */
    protected abstract function checkGuzzleVersion();

    /**
     * Create a default Guzzle client.
     *
     * @return mixed
     */
    protected abstract function createDefaultClient();

    /**
     * @return mixed
     */
    public function getLastResponse()
    {
        return $this->lastResponse;
    }

    /**
     * Set Guzzle client.
     *
     * @param mixed $client
     *
     * @return AbstractTyrService
     */
    public function setClient($client)
    {
        $this->client = $client;

        $this->listenResponses($client);

        return $this;
    }

    /**
     * Listen received responses from Guzzle client and set lastResponse.
     *
     * @param mixed $client Guzzle client to listen.
     */
    protected abstract function listenResponses($client);

    /**
     * @param string $email
     * @param string $login
     * @param array $parameters extra parameters
     *
     * @return \stdClass
     */
    public abstract function createUser($email, $login, array $parameters = array());

    /**
     * @param string $email
     *
     * @return bool
     */
    public abstract function hasUserByEmail($email);

    /**
     * @param string $email
     *
     * @return \stdClass|null
     */
    public abstract function getUserByEmail($email);

    /**
     * @param string $email
     *
     * @return bool success
     */
    public abstract function deleteUser($email);

    /**
     * @param int $userId
     * @param string $appName
     *
     * @return string|null created key or null on failure
     */
    public abstract function createUserKey($userId, $appName = 'default');

    /**
     * @param int $userId
     *
     * @return array|\stdClass array of keys
     *                         or \stdClass with attribute 'status' if $userId not found
     */
    public abstract function getUserKeys($userId);

    /**
     * @param int $userId
     * @param int $keyId
     *
     * @return array|\stdClass
     */
    public abstract function deleteUserKey($userId, $keyId);
}
