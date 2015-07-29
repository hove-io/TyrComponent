<?php

namespace CanalTP\TyrComponent;

use GuzzleHttp\Client;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Event\CompleteEvent;

class TyrService
{
    /**
     * @var string
     */
    private $wsUrl;

    /**
     * @var string
     */
    private $endPointId;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var Response
     */
    private $lastResponse;

    /**
     * Constructor.
     *
     * @param string $wsUrl
     * @param string $endPointId
     */
    public function __construct($wsUrl, $endPointId)
    {
        $this->wsUrl = $wsUrl;
        $this->endPointId = $endPointId;

        $this->setClient($this->createDefaultClient());
    }

    /**
     * @return Response
     */
    public function getLastResponse()
    {
        return $this->lastResponse;
    }

    /**
     * Create a default Guzzle client.
     *
     * @return Client
     */
    private function createDefaultClient()
    {
        $client = new Client(array(
            'base_url' => $this->wsUrl,
            'stream' => false,
            'http_errors' => false,
        ));

        $client->setDefaultOption('exceptions', false);

        return $client;
    }

    /**
     * Set Guzzle client.
     *
     * @param Client $client
     *
     * @return TyrService
     */
    public function setClient(Client $client)
    {
        $this->client = $client;

        $this->client->getEmitter()->removeListener('complete', array($this, 'onResponse'));
        $this->client->getEmitter()->on('complete', array($this, 'onResponse'));

        return $this;
    }

    /**
     * Callback called after a response has been received
     *
     * @param CompleteEvent $event
     */
    public function onResponse(CompleteEvent $event)
    {
        $this->lastResponse = $event->getResponse();
    }

    /**
     * @param string $email
     * @param string $login
     * @param array $parameters extra parameters
     *
     * @return \stdClass
     */
    public function createUser($email, $login, array $parameters = array())
    {
        $params = array_merge($parameters, array(
            'email' => $email,
            'login' => $login,
        ));

        if (null !== $this->endPointId) {
            $params['end_point_id'] = $this->endPointId;
        }

        $response = $this->client->post('users', array(
            'json' => $params,
        ));

        return json_decode($response->getBody());
    }

    /**
     * @param string $email
     *
     * @return bool
     */
    public function hasUserByEmail($email)
    {
        return null !== $this->getUserByEmail($email);
    }

    /**
     * @param string $email
     *
     * @return \stdClass|null
     */
    public function getUserByEmail($email)
    {
        $response = $this->client->get('users', array(
            'query' => array(
                'email' => $email,
                'end_point_id' => $this->endPointId,
            ),
        ));

        $users = json_decode($response->getBody());

        if (is_array($users) && count($users) > 0) {
            return $users[0];
        } else {
            return null;
        }
    }

    /**
     * @param string $email
     *
     * @return bool success
     */
    public function deleteUser($email)
    {
        $user = $this->getUserByEmail($email);

        if (null !== $user) {
            $response = $this->client->delete('users/'.$user->id);

            return null === json_decode($response->getBody());
        } else {
            return false;
        }
    }

    /**
     * @param int $userId
     * @param string $appName
     *
     * @return string|null created key or null on failure
     */
    public function createUserKey($userId, $appName = 'default')
    {
        $url = sprintf('users/%s/keys', $userId);

        $response = $this->client->post($url, array(
            'json' => array(
                'app_name' => $appName,
            ),
        ));

        $result = json_decode($response->getBody());
        $key = null;

        if(is_object($result) && property_exists($result, 'keys') && is_array($result->keys)) {
            $lastKey = end($result->keys);
            if(is_object($lastKey) && property_exists($lastKey, 'token')) {
                $key = $lastKey->token;
            }
        }

        return $key;
    }

    /**
     * @param int $userId
     *
     * @return array|\stdClass array of keys
     *                         or \stdClass with attribute 'status' if $userId not found
     */
    public function getUserKeys($userId)
    {
        $response = $this->client->get('users/'.$userId.'/keys');

        return json_decode($response->getBody());
    }

    /**
     * @param int $userId
     * @param int $keyId
     *
     * @return array|\stdClass
     */
    public function deleteUserKey($userId, $keyId)
    {
        $response = $this->client->delete(sprintf('users/%s/keys/%s', $userId, $keyId));

        return json_decode($response->getBody());
    }
}
