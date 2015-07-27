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
     * @var string|null
     */
    private $endPointId;

    /**
     * @var string|null
     */
    private $type;

    /**
     * @var string|null
     */
    private $appName;

    /**
     * @var Client
     */
    private $client;

    /**
     * @param string $wsUrl
     * @param string|null $endPointId
     * @param string|null $type
     * @param string|null $appName
     */
    public function __construct($wsUrl, $endPointId = null, $type = null, $appName = null)
    {
        $this->wsUrl = $wsUrl;
        $this->endPointId = $endPointId;
        $this->type = $type;
        $this->appName = $appName;

        $this->initClient();
    }

    /**
     * Initialize guzzle client
     */
    private function initClient()
    {
        $this->client = new Client(array(
            'base_uri' => $this->wsUrl,
            'stream' => false,
            'http_errors' => false,
        ));
    }

    /**
     * @param string $email
     * @param string $login
     *
     * @return \stdClass
     */
    public function createUser($email, $login)
    {
        $parameters = array(
            'email' => $email,
            'login' => $login,
        );

        if (null !== $this->type) {
            $parameters['type'] = $this->type;
        }

        if (null !== $this->endPointId) {
            $parameters['end_point_id'] = $this->endPointId;
        }

        $response = $this->client->post('users', array(
            'json' => $parameters,
        ));

        return json_decode($response->getBody());
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
            ),
        ));

        $users = json_decode($response->getBody());

        if (is_array($users) && count($users) > 0) {
            return $users[0];
        } else {
            return null;
        }
    }
}
