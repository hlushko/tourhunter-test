<?php

namespace tests\unit\models;

use app\models\LoginForm;
use app\models\User;

class LoginFormTest extends \tests\unit\AbstractUnit
{
    /**
     * @test
     * @dataProvider usernameValidationProvider
     *
     * @param array $params
     * @param bool $expected
     * @param string $message
     */
    public function shouldValidateUsername(array $params, $expected, $message)
    {
        // given
        $model = $this->buildModel($params);

        // when
        $result = $model->validate();

        $this->assertEquals($expected, $result, $message);
    }

    /**
     * Provides different username cases for validation
     * @return array
     */
    public function usernameValidationProvider()
    {
        return [
            'should fail on empty value' => [
                ['username' => ''],
                false,
                'User "username" should not be blank',
            ],
            'should fail on russian symbols' => [
                ['username' => 'привіт'],
                false,
                'User "username" should not contains russian symbols',
            ],
            'should allow lower latin letters' => [
                ['username' => 'hello'],
                true,
                'User "username" can contain lower letters',
            ],
            'should not contain upper letters' => [
                ['username' => 'hEllO'],
                true,
                'User "username" should not contains upper letters',
            ],
            'should allow numbers' => [
                ['username' => 'hello4you'],
                true,
                'User "username" can contain numbers',
            ],
            'should allow underscore' => [
                ['username' => 'hello_you2'],
                true,
                'User "username" can contain underscore',
            ],
            'should allow hyphen' => [
                ['username' => 'hello-you2'],
                true,
                'User "username" can contain hyphen',
            ],
            'should allow dots' => [
                ['username' => 'hello.you2'],
                true,
                'User "username" can contain dots',
            ],
        ];
    }

    /**
     * @test
     */
    public function shouldTrimUsername()
    {
        // given
        $username = self::$faker->userName;
        $params = ['username' => ' '.$username.'  '];
        $model = $this->buildModel($params);

        // when
        $result = $model->validate();

        $this->assertTrue(
            $result,
            'User "username" with empty spaces should be allowed.',
        );
        $this->assertEquals(
            $username,
            $model->username,
            'Empty spaces in "username" at start and end should be removed.',
        );
    }

    /**
     * @test
     */
    public function shouldLoginExistingUser()
    {
        // given
        $userAttrs = $this->generateUserAttrs();
        $this->tester->haveRecord(User::class, $userAttrs);
        $model = $this->buildModel(['username' => $userAttrs['username']]);

        // when
        $result = $model->login();

        // then
        $this->assertTrue($result, 'Existing User should be login successfully.');
        $this->assertEmpty($model->getErrors(), 'Model should not have an errors.');
        $this->assertFalse(\Yii::$app->user->isGuest, 'User should be login in the System.');
        $this->assertNotNull(
            \Yii::$app->user->identity,
            'Identity of auth User should be filled.',
        );
        $this->assertInstanceOf(
            User::class,
            \Yii::$app->user->identity,
            'User identity should be correct class instance.',
        );
        $this->assertEquals(
            $userAttrs,
            \Yii::$app->user->identity->attributes,
            'User identity attributes should be correct one.',
        );
    }

    /**
     * @test
     */
    public function shouldCreateNonExistingUserOnLogin()
    {
        // given
        do {
            $userAttrs = $this->generateUserAttrs();
        } while (User::findByUsername($userAttrs['username']));
        $model = $this->buildModel(['username' => $userAttrs['username']]);

        // when
        $result = $model->login();

        // then
        $this->assertTrue($result, 'Existing User should be login successfully.');
        $this->assertEmpty($model->getErrors(), 'Model should not have an errors.');
        $this->assertFalse(\Yii::$app->user->isGuest, 'User should be login in the System.');
        $this->assertNotNull(
            \Yii::$app->user->identity,
            'Identity of auth User should be filled.',
        );
        $this->assertInstanceOf(
            User::class,
            \Yii::$app->user->identity,
            'User identity should be correct class instance.',
        );
        $this->assertEquals(
            ['username' => $userAttrs['username'], 'balance' => '0.00'],
            \Yii::$app->user->identity->getAttributes(['username', 'balance']),
            'User identity attributes should be correct one.',
        );
    }

    /**
     * Builds model for test
     * @param array $params
     *
     * @return LoginForm
     */
    private function buildModel(array $params)
    {
        return new LoginForm($params);
    }
}
