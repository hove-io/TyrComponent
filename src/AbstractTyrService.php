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
     * @param int $endPointId
     */
    public function __construct($wsUrl, $endPointId = null)
    {
        $this->checkGuzzleVersion();

        $this->wsUrl = $wsUrl;
        $this->endPointId = $endPointId;

        $this->setClient($this->createDefaultClient());
    }

    /**
     * @throws Exception\NotSupportedException when Guzzle vendor version is not supported.
     * @throws Exception\VersionCheckerException when Guzzle vendor version is supported but not by this class.
     */
    abstract protected function checkGuzzleVersion();

    /**
     * Create a default Guzzle client.
     *
     * @return mixed
     */
    abstract protected function createDefaultClient();

    /**
     * @return mixed
     */
    public function getLastResponse()
    {
        return $this->lastResponse;
    }

    /**
     * @param int $endPointId
     *
     * @return AbstractTyrService
     */
    public function setEndPointId($endPointId)
    {
        $this->endPointId = $endPointId;

        return $this;
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
     * @throws \LogicException if endPointId not set
     */
    protected function checkEndPointId()
    {
        if (null === $this->endPointId) {
            throw new \LogicException('endPointId must be defined.');
        }
    }

    /**
     * Listen received responses from Guzzle client and set lastResponse.
     *
     * @param mixed $client Guzzle client to listen.
     */
    abstract protected function listenResponses($client);

    /**
     * @param string $email
     * @param string $login
     * @param array $parameters extra parameters
     *
     * @return \stdClass
     */
    abstract public function createUser($email, $login, array $parameters = array());

    /**
     * @param string $email
     *
     * @return bool
     */
    abstract public function hasUserByEmail($email);

    /**
     * @param string $email
     *
     * @return \stdClass|null
     */
    abstract public function getUserByEmail($email);

    /**
     * @param int $id
     *
     * @return \stdClass|null
     */
    abstract public function getUserById($id);

    /**
     * @param array $parameters
     *
     * @return bool
     */
    abstract public function updateUser(array $parameters);

    /**
     * @param string $email
     *
     * @return bool success
     */
    abstract public function deleteUser($email);

    /**
     * @param int $userId
     * @param string $appName
     *
     * @return string|null created key or null on failure
     */
    abstract public function createUserKey($userId, $appName = 'default');

    /**
     * @param int $userId
     *
     * @return array|\stdClass array of keys
     *                         or \stdClass with attribute 'status' if $userId not found
     */
    abstract public function getUserKeys($userId);

    /**
     * @param int $userId
     * @param int $keyId
     *
     * @return array|\stdClass
     */
    abstract public function deleteUserKey($userId, $keyId);

    /**
     * Create a new billing plan.
     *
     * @param string $name
     * @param int|null $maxRequestCount
     * @param int|null $maxObjectCount
     * @param bool $default
     *
     * @return \stdClass Created billing plan.
     */
    abstract public function createBillingPlan($name, $maxRequestCount, $maxObjectCount, $default);

    /**
     * Get all available plans.
     *
     * @return \stdClass[]
     */
    abstract public function getBillingPlans();

    /**
     * Get billing plan by id.
     *
     * @return \stdClass
     */
    abstract public function getBillingPlan($id);

    /**
     * Update a plan.
     *
     * @param int $id
     * @param string $name
     * @param int|null $maxRequestCount
     * @param int|null $maxObjectCount
     * @param bool $default
     *
     * @return bool success
     */
    abstract public function updateBillingPlan($id, $name, $maxRequestCount, $maxObjectCount, $default);

    /**
     * Delete a plan.
     *
     * @param int $id
     *
     * @return bool success
     */
    abstract public function deleteBillingPlan($id);
}
