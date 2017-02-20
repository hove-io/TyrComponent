<?php

namespace CanalTP\TyrComponent\Tests;

use CanalTP\TyrComponent\Exception\InvalidApplicationNameException;
use CanalTP\TyrComponent\VersionChecker;

class TyrServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \CanalTP\TyrComponent\AbstractTyrService
     */
    private $tyrService;

    /**
     * Constructor
     */
    public function __construct()
    {
        $tyrServiceClass = VersionChecker::getTyrServiceClassName();

        $this->tyrService = new $tyrServiceClass('http://tyr.dev.canaltp.fr/v0/', 1);
    }

    public function testCreateUserReturnsValidStatusCode()
    {
        $user = $this->createRandomUser();

        $this->tyrService->createUser($user->email, $user->login);
        $this->assertEquals(200, $this->tyrService->getLastResponse()->getStatusCode());

        $this->tyrService->deleteUser($user->email);
    }

    public function testCreateUserAddNewUser()
    {
        $user = $this->createRandomUser();

        $resultNull = $this->tyrService->getUserByEmail($user->email);
        $this->assertNull($resultNull);

        $this->tyrService->createUser($user->email, $user->login);

        $resultNotNull = $this->tyrService->getUserByEmail($user->email);
        $this->assertNotNull($resultNotNull);

        $this->tyrService->deleteUser($user->email);
    }

    public function testCreateUserWithBillingPlanByDefault()
    {
        $user = $this->createRandomUser();
        $billingPlanName = 'nav_dev';

        $resultNull = $this->tyrService->getUserByEmail($user->email);
        $this->assertNull($resultNull);

        $user = $this->tyrService->createUser($user->email, $user->login, ['billing_plan_default' => $billingPlanName]);
        $this->assertEquals($billingPlanName, $user->billing_plan->name);

        $this->tyrService->deleteUser($user->email);
    }

    public function testUpdateUserWithNewBillingPlan()
    {
        $user = $this->createRandomUser();
        $plan = $this->createRandomPlan();
        $createdUser = $this->tyrService->createUser($user->email, $user->login);
        $createdPlan = $this->tyrService->createBillingPlan(
            $plan->name,
            $plan->max_request_count,
            $plan->max_object_count,
            $plan->default
        );

        $this->tyrService->updateUser($createdUser->id, ['billing_plan_id' => $createdPlan->id]);

        $updatedUser = $this->tyrService->getUserById($createdUser->id);

        $this->assertEquals($createdPlan->id, $updatedUser->billing_plan->id);

        $this->tyrService->deleteUser($user->email);
        $this->tyrService->deleteBillingPlan($createdPlan->id);
    }

    public function testCreatedUserHasDefaultBillingPlan()
    {
        $user = $this->createRandomUser();
        $createdUser = $this->tyrService->createUser($user->email, $user->login);

        $this->assertObjectHasAttribute('billing_plan', $createdUser);
        $this->assertTrue(true, $createdUser->billing_plan->default, 'Created user billing plan marked as default');

        $this->tyrService->deleteUser($user->email);
    }

    public function testDeleteUserReturnsValidStatusCode()
    {
        $user = $this->createRandomUser();

        $this->tyrService->createUser($user->email, $user->login);
        $success = $this->tyrService->deleteUser($user->email);

        $this->assertTrue($success);
        $this->assertEquals(204, $this->tyrService->getLastResponse()->getStatusCode());
    }

    public function testDeleteUserDesintegrateTheGuy()
    {
        $user = $this->createRandomUser();

        $this->tyrService->createUser($user->email, $user->login);

        $this->assertNotNull($this->tyrService->getUserByEmail($user->email));

        $success = $this->tyrService->deleteUser($user->email);

        $this->assertTrue($success);
        $this->assertNull($this->tyrService->getUserByEmail($user->email));
    }

    public function testCreateUserKey()
    {
        $user = $this->createRandomUser();
        $createdUser = $this->tyrService->createUser($user->email, $user->login);

        $key = $this->tyrService->createUserKey($createdUser->id);

        $this->assertEquals(200, $this->tyrService->getLastResponse()->getStatusCode());
        $this->assertRegExp('/^[0-9a-f]{8}(-[0-9a-f]{4}){3}-[0-9a-f]{12}$/', $key);

        $this->tyrService->deleteUser($user->email);
    }

    public function testGetUserKeysReturnsEmptyArrayIfNotKeys()
    {
        $user = $this->createRandomUser();
        $createdUser = $this->tyrService->createUser($user->email, $user->login);

        $keys = $this->tyrService->getUserKeys($createdUser->id);

        $this->assertCount(0, $keys);
        $this->assertEquals(array(), $keys);

        $this->tyrService->deleteUser($user->email);
    }

    public function testGetUserKeys()
    {
        $user = $this->createRandomUser();
        $createdUser = $this->tyrService->createUser($user->email, $user->login);

        $this->tyrService->createUserKey($createdUser->id);

        $this->assertCount(1, $this->tyrService->getUserKeys($createdUser->id));

        $this->tyrService->createUserKey($createdUser->id);
        $this->tyrService->createUserKey($createdUser->id);

        $this->assertCount(3, $this->tyrService->getUserKeys($createdUser->id));

        $this->tyrService->deleteUser($user->email);
    }

    public function testDeleteUserKey()
    {
        $user = $this->createRandomUser();
        $createdUser = $this->tyrService->createUser($user->email, $user->login);

        $this->tyrService->createUserKey($createdUser->id);
        $this->tyrService->createUserKey($createdUser->id);
        $this->tyrService->createUserKey($createdUser->id);

        $keys = $this->tyrService->getUserKeys($createdUser->id);

        $this->assertCount(3, $keys);

        $this->tyrService->deleteUserKey($createdUser->id, $keys[0]->id);

        $this->assertCount(2, $this->tyrService->getUserKeys($createdUser->id));

        $this->tyrService->deleteUser($user->email);
    }

    public function testCreateBillingPlan()
    {
        $plan = $this->createRandomPlan();
        $createdPlan = $this->tyrService->createBillingPlan(
            $plan->name,
            $plan->max_request_count,
            $plan->max_object_count,
            $plan->default
        );

        $this->assertEquals(200, $this->tyrService->getLastResponse()->getStatusCode());

        $this->assertObjectHasAttribute('id', $createdPlan);

        $this->assertEquals($plan->name, $createdPlan->name);
        $this->assertEquals($plan->max_request_count, $createdPlan->max_request_count);
        $this->assertEquals($plan->max_object_count, $createdPlan->max_object_count);
        $this->assertEquals($plan->default, $createdPlan->default);

        $this->tyrService->deleteBillingPlan($createdPlan->id);
    }

    public function testGetBillingPlans()
    {
        $plans = $this->tyrService->getBillingPlans();

        $this->assertEquals(200, $this->tyrService->getLastResponse()->getStatusCode());

        $this->assertInternalType('array', $plans);
    }

    public function testGetUsers()
    {
        $users = $this->tyrService->getUsers();

        $this->assertEquals(200, $this->tyrService->getLastResponse()->getStatusCode());
        $this->assertInternalType('array', $users);
    }

    public function testGetUsersByEndPointId()
    {
        $users = $this->tyrService->getUsersByEndPointId(1);

        $this->assertEquals(200, $this->tyrService->getLastResponse()->getStatusCode());
        $this->assertInternalType('array', $users);
    }

    public function testGetBillingPlan()
    {
        $plan = $this->createRandomPlan();
        $createdPlan = $this->tyrService->createBillingPlan(
            $plan->name,
            $plan->max_request_count,
            $plan->max_object_count,
            $plan->default
        );

        $retrievedPlan = $this->tyrService->getBillingPlan($createdPlan->id);

        $this->assertEquals($plan->name, $retrievedPlan->name);
        $this->assertEquals($plan->max_request_count, $retrievedPlan->max_request_count);
        $this->assertEquals($plan->max_object_count, $retrievedPlan->max_object_count);
        $this->assertEquals($plan->default, $retrievedPlan->default);

        $this->tyrService->deleteBillingPlan($createdPlan->id);
    }

    public function testUpdateBillingPlan()
    {
        $plan = $this->createRandomPlan();
        $createdPlan = $this->tyrService->createBillingPlan(
            $plan->name,
            $plan->max_request_count,
            $plan->max_object_count,
            $plan->default
        );

        $updatedPlan = $this->tyrService->updateBillingPlan($createdPlan->id, 'updated', 20, 30, false);

        $this->tyrService->getBillingPlan($createdPlan->id);

        $this->assertEquals($updatedPlan->name, 'updated');
        $this->assertEquals($updatedPlan->max_request_count, 20);
        $this->assertEquals($updatedPlan->max_object_count, 30);
        $this->assertEquals($updatedPlan->default, false);

        $this->tyrService->deleteBillingPlan($createdPlan->id);
    }

    public function testDeleteBillingPlan()
    {
        $plan = $this->createRandomPlan();
        $createdPlan = $this->tyrService->createBillingPlan(
            $plan->name,
            $plan->max_request_count,
            $plan->max_object_count,
            $plan->default
        );

        $this->tyrService->getBillingPlan($createdPlan->id);

        $this->assertEquals(200, $this->tyrService->getLastResponse()->getStatusCode());

        $this->tyrService->deleteBillingPlan($createdPlan->id);

        $this->assertEquals(204, $this->tyrService->getLastResponse()->getStatusCode());

        $this->tyrService->deleteBillingPlan($createdPlan->id);

        $this->assertEquals(404, $this->tyrService->getLastResponse()->getStatusCode());
    }

    /**
     * @expectedException CanalTP\TyrComponent\Exception\InvalidApplicationNameException
     * @expectedExceptionMessage Application name is not valid
     */
    public function testCreateTokenWithInvalidCharShouldFail()
    {
        $this->tyrService->createUserKey(1337, 'invalid app namé');
    }

    /**
     * @return \stdClass
     */
    private function createRandomUser()
    {
        $rand = rand(10000000, 99999999).'';

        return (object) array(
            'email' => $rand.'@free.fr',
            'login' => $rand,
            'password' => $rand,
        );
    }

    /**
     * @return \stdClass
     */
    private function createRandomPlan()
    {
        $rand = rand(10000000, 99999999).'';

        return (object) array(
            'name' => $rand,
            'max_request_count' => 3000,
            'max_object_count' => 6000,
            'default' => false,
        );
    }
}
