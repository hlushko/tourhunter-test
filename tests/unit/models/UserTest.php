<?php

namespace tests\unit\models;

use app\models\User;

class UserTest extends \tests\unit\AbstractUnit
{
    /**
     * @test
     */
    public function shouldFindUserById()
    {
        // given
        $userAttrs = $this->generateUserAttrs();
        $this->tester->haveRecord(User::class, $userAttrs);

        // when
        $user = User::findIdentity($userAttrs['id']);

        // then
        $this->assertNotNull($user, 'User identity should be found.');
        $this->assertInstanceOf(
            User::class,
            $user,
            'User identity should be correct class instance.',
        );
        $this->assertEquals(
            $userAttrs['id'],
            $user->getId(),
            'User identity "id" attr should be correct one.',
        );
        $this->assertEquals(
            $userAttrs,
            $user->attributes,
            'User identity attributes should be correct one.',
        );
    }

    /**
     * @test
     */
    public function shouldReturnNullForNonExistingUser()
    {
        // given
        $userId = static::$faker->randomDigitNotNull;

        // when
        $user = User::findIdentity($userId);

        // then
        $this->assertNull($user, 'Should return NULL if User does not exits.');
    }

    /**
     * @test
     */
    public function shouldFindUserByUsername()
    {
        // given
        $userAttrs = $this->generateUserAttrs();
        $this->tester->haveRecord(User::class, $userAttrs);

        // when
        $user = User::findByUsername($userAttrs['username']);

        // then
        $this->assertNotNull($user, 'User identity should be found.');
        $this->assertInstanceOf(
            User::class,
            $user,
            'User should be correct class instance.',
        );
        $this->assertEquals(
            $userAttrs,
            $user->attributes,
            'User attributes should be correct one.',
        );
    }

    /**
     * @test
     */
    public function shouldReturnNullOfFindNonExistingUserByUsername()
    {
        // given
        $userAttrs = $this->generateUserAttrs();

        // when
        $user = User::findByUsername($userAttrs['username']);

        // then
        $this->assertNull(
            $user,
            'User identity for non existing user should be NULL.',
        );
    }

    /**
     * @test
     */
    public function shouldFindUserByAccessToken()
    {
        // given
        $userAttrs = $this->generateUserAttrs();
        $this->tester->haveRecord(User::class, $userAttrs);

        // when
        $user = User::findIdentityByAccessToken($userAttrs['username']);

        // then
        $this->assertNotNull($user, 'User identity should be found.');
        $this->assertInstanceOf(
            User::class,
            $user,
            'User identity should be correct class instance.',
        );
        $this->assertEquals(
            $userAttrs,
            $user->attributes,
            'User identity attributes should be correct one.',
        );
    }

    /**
     * @test
     */
    public function shouldReturnNullOfFindNonExistingUserByAccessToken()
    {
        // given
        $userAttrs = $this->generateUserAttrs();

        // when
        $user = User::findIdentityByAccessToken($userAttrs['username']);

        // then
        $this->assertNull(
            $user,
            'User identity for non existing user should be NULL.',
        );
    }

    /**
     * @test
     * @depends shouldFindUserByUsername
     */
    public function shouldValidateAccessToken()
    {
        // given
        $userAttrs = $this->generateUserAttrs();
        $this->tester->haveRecord(User::class, $userAttrs);
        $user = User::findByUsername($userAttrs['username']);

        // when
        $result = $user->validateAuthKey($userAttrs['username']);

        // then
        $this->assertTrue(
            $result,
            'User identity should be validated successfully by "username".',
        );
    }

    /**
     * @test
     * @depends shouldFindUserByUsername
     */
    public function shouldNotValidateWrongAccessToken()
    {
        // given
        $userAttrs = $this->generateUserAttrs();
        $this->tester->haveRecord(User::class, $userAttrs);
        $user = User::findByUsername($userAttrs['username']);

        do {
            $wrongToken = static::$faker->userName;
        } while ($wrongToken === $userAttrs['username']);

        // when
        $result = $user->validateAuthKey($wrongToken);

        // then
        $this->assertFalse(
            $result,
            'User identity should not be validated successfully by wrong "username".',
        );
    }

    /**
     * @test
     * @depends shouldFindUserByUsername
     */
    public function shouldCreateUser()
    {
        // given
        do {
            $userAttrs = $this->generateUserAttrs();
            $user = User::findByUsername($userAttrs['username']);
        } while ($user);

        // when
        $result = User::createByUsername($userAttrs['username']);

        // then
        $this->assertNotEmpty(
            $result,
            'User should be created successfully.',
        );
        $this->assertInstanceOf(
            User::class,
            $result,
            'Correct class instance should be created.',
        );
        $this->assertEquals(
            ['username' => $userAttrs['username'], 'balance' => 0],
            ['username' => $result->username, 'balance' => $result->balance],
            'User with correct attributes should be created.'
        );
        $this->tester->seeRecord(
            User::class,
            ['username' => $result->username, 'balance' => $result->balance],
        );
    }

    /**
     * @test
     * @depends shouldFindUserByUsername
     * @expectedException \yii\base\InvalidArgumentException
     */
    public function shouldNotCreateDuplicateUser()
    {
        // given
        $userAttrs = $this->generateUserAttrs();
        $this->tester->haveRecord(User::class, $userAttrs);

        // when
        User::createByUsername($userAttrs['username']);

        // then
        // throw an exception
    }
}
