<?php

namespace CanalTP\TyrComponent;

use Guzzle\Http\Message\Response;
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

        $response = $this->client->get('users', [
            'query' => ['end_point_id' => $this->endPointId]
        ]);

        $users = json_decode($response->getBody());

        return (is_array($users) && count($users) > 0) ? $users : [];
    }

    /**
     * {@InheritDoc}
     */
    public function getUsers()
    {
        $response = $this->client->get('users');
        $users = json_decode($response->getBody());

        return (is_array($users) && count($users) > 0) ? $users : [];
    }

    /**
     * {@InheritDoc}
     */
    public function updateUser($userId, array $parameters)
    {
        $this->client->put('users/'.$userId, array(
            'query' => $parameters,
        ));

        return true;
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
     * {@inheritdoc}
     */
    public function addUserInstance($userId, $api, $instance)
    {
        if (null !== $userId) {
            $url = sprintf('users/%s/authorizations', $userId);
            /* @var Response $response */
            $response = $this->client->post($url, array(
                'json' => array(
                    'api_id' => $api,
                    'instance_id' => $instance,
                ),
            ));

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
            $response = $this->client->get('users/'.$userId.'/keys');
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
     * {@InheritDoc}
     */
    public function createBillingPlan($name, $maxRequestCount, $maxObjectCount, $default)
    {
        $response = $this->client->post(sprintf(
            'billing_plans?name=%s&max_request_count=%s&max_object_count=%s&default=%s',
            $name,
            $maxRequestCount,
            $maxObjectCount,
            $default ? 1 : ''
        ));

        return json_decode($response->getBody());
    }

    /**
     * {@InheritDoc}
     */
    public function getBillingPlans()
    {
        $this->checkEndPointId();

        $response = $this->client->get('billing_plans');

        return array_filter(json_decode($response->getBody()), function (\stdClass $billingPlan) {
            return $this->endPointId === $billingPlan->end_point->id;
        });
    }

    /**
     * {@InheritDoc}
     */
    public function getBillingPlan($id)
    {
        $response = $this->client->get(sprintf('billing_plans/%s', $id));

        return json_decode($response->getBody());
    }

    /**
     * {@InheritDoc}
     */
    public function updateBillingPlan($id, $name, $maxRequestCount, $maxObjectCount, $default)
    {
        $response = $this->client->put(sprintf(
            'billing_plans/%d?name=%s&max_request_count=%s&max_object_count=%s&default=%s',
            $id,
            $name,
            $maxRequestCount,
            $maxObjectCount,
            $default ? 1 : ''
        ));

        return json_decode($response->getBody());
    }

    /**
     * {@InheritDoc}
     */
    public function deleteBillingPlan($id)
    {
        $response = $this->client->delete(sprintf('billing_plans/%s', $id));

        return json_decode($response->getBody());
    }

    /**
     * {@InheritDoc}
     */
    public function getInstances()
    {
        $response = $this->client->get('instances');
        $instances = json_decode($response->getBody());

        return (is_array($instances) && count($instances) > 0) ? $instances : [];
    }
}
