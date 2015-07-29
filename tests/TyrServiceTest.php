<?php

namespace CanalTP\TyrComponent\Tests;

use CanalTP\TyrComponent\TyrService;

class TyrServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TyrService
     */
    private $tyrService;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->tyrService = new TyrService('http://tyr.dev.canaltp.fr/v0/', 2);
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

    public function testGetUserKeys()
    {
        $user = $this->createRandomUser();
        $createdUser = $this->tyrService->createUser($user->email, $user->login);

        $this->assertCount(0, $this->tyrService->getUserKeys($createdUser->id));

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

        $this->assertCount(0, $this->tyrService->getUserKeys($createdUser->id));

        $this->tyrService->createUserKey($createdUser->id);
        $this->tyrService->createUserKey($createdUser->id);
        $this->tyrService->createUserKey($createdUser->id);

        $keys = $this->tyrService->getUserKeys($createdUser->id);

        $this->assertCount(3, $keys);

        $this->tyrService->deleteUserKey($createdUser->id, $keys[0]->id);

        $this->assertCount(2, $this->tyrService->getUserKeys($createdUser->id));

        $this->tyrService->deleteUser($user->email);
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
}
