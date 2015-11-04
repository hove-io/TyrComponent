<?php

namespace CanalTP\TyrComponent;

use GuzzleHttp\Client;
use GuzzleHttp\Event\CompleteEvent;
use CanalTP\TyrComponent\VersionChecker;
use CanalTP\TyrComponent\AbstractTyrService;

class TyrService extends AbstractTyrService
{
    /**
     * {@InheritDoc}
     */
    protected function checkGuzzleVersion()
    {
        VersionChecker::supportsGuzzleVersion(5, get_class($this));
    }

    /**
     * {@InheritDoc}
     */
    protected function createDefaultClient()
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
     * {@InheritDoc}
     */
    protected function listenResponses($client)
    {
        $client->getEmitter()->removeListener('complete', array($this, 'onResponse'));
        $client->getEmitter()->on('complete', array($this, 'onResponse'));
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
     * {@InheritDoc}
     */
    public function createUser($email, $login, array $parameters = array())
    {
        $this->checkEndPointId();

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
     * {@InheritDoc}
     */
    public function hasUserByEmail($email)
    {
        return null !== $this->getUserByEmail($email);
    }

    /**
     * {@InheritDoc}
     */
    public function getUserByEmail($email)
    {
        $this->checkEndPointId();

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
     * {@InheritDoc}
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
     * {@InheritDoc}
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

        if (is_object($result) && property_exists($result, 'keys') && is_array($result->keys)) {
            $lastKey = end($result->keys);
            if (is_object($lastKey) && property_exists($lastKey, 'token')) {
                $key = $lastKey->token;
            }
        }

        return $key;
    }

    /**
     * {@InheritDoc}
     */
    public function getUserKeys($userId)
    {
        $response = $this->client->get('users/'.$userId.'/keys');

        return json_decode($response->getBody());
    }

    /**
     * {@InheritDoc}
     */
    public function getUserById($id)
    {
        $response = $this->client->get('users/'.$id);

        return json_decode($response->getBody());
    }

    /**
     * {@InheritDoc}
     */
    public function deleteUserKey($userId, $keyId)
    {
        $response = $this->client->delete(sprintf('users/%s/keys/%s', $userId, $keyId));

        return json_decode($response->getBody());
    }

    /**
     * Creates a new plan.
     *
     * @param string $title
     * @param int $limitCalls
     *
     * @return \stdClass Created plan
     */
    public function createPlan($title, $limitCalls)
    {
        return [
            'id' => 3,
            'title' => $title,
            'limitCalls' => $limitCalls,
        ];
    }

    /**
     * Get all available plans.
     *
     * @return \stdClass[]
     */
    public function getPlans()
    {
        return [
            1 => [
                'id' => 1,
                'title' => 'Développeur',
                'limitCalls' => 3000,
            ],
            2 => [
                'id' => 2,
                'title' => 'Professional',
                'limitCalls' => 15000,
            ],
        ];
    }

    /**
     * Get all available plans.
     *
     * @return \stdClass[]
     */
    public function getPlan($id)
    {
        return $this->getPlans()[$id];
    }

    /**
     * Update a plans.
     *
     * @param int $id
     * @param string $title
     * @param int $limitCalls
     *
     * @return bool success
     */
    public function updatePlan($id, $title, $limitCalls)
    {
        return true;
    }

    /**
     * Delete a plan.
     *
     * @param int $id
     *
     * @return bool success
     */
    public function deletePlan($id)
    {
        return true;
    }
}
