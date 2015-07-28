<?php

namespace CanalTP\TyrComponent;

use GuzzleHttp\Client;

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
     * @var string|null
     */
    private $appName;

    /**
     * @var Client
     */
    private $client;

    /**
     * Constructor.
     *
     * @param string $wsUrl
     * @param string $endPointId
     * @param string|null $appName
     */
    public function __construct($wsUrl, $endPointId, $appName = null)
    {
        $this->wsUrl = $wsUrl;
        $this->endPointId = $endPointId;
        $this->appName = $appName;

        $this->setClient($this->createDefaultClient());
    }

    /**
     * Create a default Guzzle client.
     *
     * @return Client
     */
    private function createDefaultClient()
    {
        return new Client(array(
            'base_url' => $this->wsUrl,
            'stream' => false,
            'http_errors' => false,
        ));
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

        return $this;
    }

    /**
     * @param string $email
     * @param string $login
     * @param string $type
     *
     * @return \stdClass
     */
    public function createUser($email, $login, $type)
    {
        $parameters = array(
            'email' => $email,
            'login' => $login,
            'type' => $type,
        );

        if (null !== $this->endPointId) {
            $parameters['end_point_id'] = $this->endPointId;
        }

        $response = $this->client->post('users', array(
            'json' => $parameters,
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
            return null === $this->client->delete('users/'.$user->id);
        } else {
            return false;
        }
    }

    /**
     * @param int $userId
     *
     * @return string|null created key or null on failure
     */
    public function createUserKey($userId)
    {
        $url = sprintf('users/%s/keys', $userId);

        $response = $this->client->post($url, array(
            'json' => array(
                'app_name' => $this->appName,
            ),
        ));

        if(is_object($response) && property_exists($response, 'keys') && is_array($response->keys)) {
            $lastKey = end($response->keys);
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
