<?php

namespace CanalTP\TyrComponent\Guzzle3;

use Guzzle\Common\Event;
use Guzzle\Http\Message\Response;
use Guzzle\Service\Client;
use CanalTP\TyrComponent\VersionChecker;
use CanalTP\TyrComponent\AbstractTyrService;

class TyrService extends AbstractTyrService
{
    /**
     * {@InheritDoc}
     */
    protected function checkGuzzleVersion()
    {
        VersionChecker::supportsGuzzleVersion(3, get_class($this));
    }

    /**
     * {@InheritDoc}
     */
    protected function createDefaultClient()
    {
        $client = new Client(
            $this->wsUrl,
            [
                'request.options' => [
                    'exceptions' => false,
                    'stream' => false
                ]
            ]
        );

        return $client;
    }

    /**
     * {@InheritDoc}
     */
    protected function listenResponses($client)
    {
        $client->getEventDispatcher()->addListener('request.sent', function (Event $event) {
            $this->lastResponse = $event['response'];
        });

        return $this;
    }

    /**
     * {@InheritDoc}
     */
    public function createUser($email, $login, array $parameters = array())
    {
        $this->checkEndPointId();

        $params = array_merge($parameters, array(
            'email' => urlencode($email),
            'login' => $login,
        ));

        if (array_key_exists('billing_plan_default', $params) && $params['billing_plan_default'] != "") {
            $billingPlan = $this->getBillingPlanFilterByName($params['billing_plan_default']);
            if ($billingPlan !== null) {
                $params['billing_plan_id'] = $billingPlan->id;
            }
        }

        if (null !== $this->endPointId) {
            $params['end_point_id'] = $this->endPointId;
        }

        $response = $this->client->post('users', [], $params)->send();

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

        $response = $this->client->get('users', [], array(
            'query' => array(
                'email' => $email,
                'end_point_id' => $this->endPointId,
            ),
        ))->send();

        $user = json_decode($response->getBody());

        if (is_array($user) && count($user) > 0) {
            return $user[0];
        } else {
            return null;
        }
    }

    /**
     * {@InheritDoc}
     */
    public function getUsersByEndPointId($endPointId)
    {
        $this->setEndPointId($endPointId);

        $response = $this->client->get('users', [], [
            'query' => ['end_point_id' => $this->endPointId]
        ])->send();

        $users = json_decode($response->getBody());

        return (is_array($users) && count($users) > 0) ? $users : [];
    }

    /**
     * {@InheritDoc}
     */
    public function getUsers()
    {
        $response = $this->client->get('users')->send();
        $users = json_decode($response->getBody());

        return (is_array($users) && count($users) > 0) ? $users : [];
    }

    /**
     * {@InheritDoc}
     */
    public function updateUser($userId, array $parameters)
    {
        $this->client->put('users/'.$userId, [], $parameters)->send();

        return true;
    }

    /**
     * {@InheritDoc}
     */
    public function deleteUser($email)
    {
        $user = $this->getUserByEmail($email);

        if (null !== $user) {
            $response = $this->client->delete('users/'.$user->id)->send();

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

        $response = $this->client->post($url, [], array(
            'app_name' => $appName,
        ))->send();

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
     * {@inheritdoc}
     */
    public function addUserInstance($userId, $api, $instance)
    {
        if (null !== $userId) {
            $url = sprintf('users/%s/authorizations', $userId);
            /* @var Response $response */
            $response = $this->client->post($url, [], array(
                'api_id' => $api,
                'instance_id' => $instance,
            ))->send();

            return $response->getStatusCode() === 200;
        } else {
            return false;
        }
    }

    /**
     * {@InheritDoc}
     */
    public function getUserKeys($userId)
    {
        try {
            $response = $this->client->get('users/'.$userId.'/keys')->send();
        } catch (\Guzzle\Http\Exception\CurlException $ex) {
            return null;
        }

        return json_decode($response->getBody());
    }

    /**
     * {@InheritDoc}
     */
    public function getUserById($id)
    {
        $response = $this->client->get('users/'.$id)->send();

        return json_decode($response->getBody());
    }

    /**
     * {@InheritDoc}
     */
    public function deleteUserKey($userId, $keyId)
    {
        $response = $this->client->delete(sprintf('users/%s/keys/%s', $userId, $keyId))->send();

        return json_decode($response->getBody());
    }

    /**
     * {@InheritDoc}
     */
    public function createBillingPlan($name, $maxRequestCount, $maxObjectCount, $default)
    {
        $params = [
            'name' => $name,
            'max_request_count' => $maxRequestCount,
            'max_object_count' => $maxObjectCount,
            'default' => $default ? 1 : ''
        ];

        $response = $this->client->post('billing_plans', [], $params)->send();

        return json_decode($response->getBody());
    }

    /**
     * {@InheritDoc}
     */
    public function getBillingPlans()
    {
        $this->checkEndPointId();

        $response = $this->client->get('billing_plans')->send();

        return array_filter(json_decode($response->getBody()), function (\stdClass $billingPlan) {
            return $this->endPointId === $billingPlan->end_point->id;
        });
    }

    /**
     * {@InheritDoc}
     */
    public function getBillingPlan($id)
    {
        $response = $this->client->get('billing_plans/'.$id)->send();

        return json_decode($response->getBody());
    }

    /**
     * {@InheritDoc}
     */
    public function updateBillingPlan($id, $name, $maxRequestCount, $maxObjectCount, $default)
    {
        $params = [
            'name' => $name,
            'max_request_count' => $maxRequestCount,
            'max_object_count' => $maxObjectCount,
            'default' => $default ? 1 : ''
        ];

        $response = $this->client->put('billing_plans/'.$id, [], $params)->send();

        return json_decode($response->getBody());
    }

    /**
     * {@InheritDoc}
     */
    public function deleteBillingPlan($id)
    {
        $response = $this->client->delete(sprintf('billing_plans/%s', $id))->send();

        return json_decode($response->getBody());
    }

    /**
     * {@InheritDoc}
     */
    public function getInstances()
    {
        $response = $this->client->get('instances')->send();
        $instances = json_decode($response->getBody());

        return (is_array($instances) && count($instances) > 0) ? $instances : [];
    }
}
