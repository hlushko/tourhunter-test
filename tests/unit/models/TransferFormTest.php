<?php

namespace tests\unit\models;

use app\models\TransferForm;
use app\models\User;
use Yii;

class TransferFormTest extends \tests\unit\AbstractUnit
{
    /**
     * @test
     * @dataProvider dataValidationProvider
     *
     * @param array $params
     * @param bool $expectedResult
     * @param array $expectedErrors
     */
    public function shouldValidate(
        array $params,
        $expectedResult,
        array $expectedErrors
    ) {
        // given
        $this->authByUser();
        $model = $this->buildModel($params);

        // when
        $result = $model->validate();

        $this->assertEquals(
            $expectedResult,
            $result,
            'Validation result should be expected one.',
        );
        $errors = $model->getErrors();
        if ($expectedResult) {
            $this->assertEmpty($errors, 'Validation errors should be empty.');
        } else {
            $this->assertNotEmpty($errors, 'Validation errors should be filled.');

            $receivedErrors = array_keys($errors);
            sort($receivedErrors);
            sort($expectedErrors);
            $this->assertEquals(
                $expectedErrors,
                $receivedErrors,
                'Error messages for expected attributes should be filled.',
            );
        }
    }

    /**
     * Provides different data cases for validation
     * @return array
     */
    public function dataValidationProvider()
    {
        return [
            'should fail on empty data' => [
                [],
                false,
                ['username', 'amount'],
            ],
            'should fail on empty "username"' => [
                ['amount' => 4.33],
                false,
                ['username'],
            ],
            'should fail on empty "amount"' => [
                ['username' => 'zavz9t'],
                false,
                ['amount'],
            ],
            'should not contain upper letters' => [
                ['username' => 'hEllO'],
                false,
                ['username', 'amount'],
            ],
            '"amount" should be a number' => [
                ['username' => 'hello4you', 'amount' => 'test'],
                false,
                ['amount'],
            ],
            '"amount" should not be a zero' => [
                ['username' => 'hello4you', 'amount' => 0],
                false,
                ['amount'],
            ],
            'should not allow negative "amount"' => [
                ['username' => 'hello_you2', 'amount' => -7.25],
                false,
                ['amount'],
            ],
            'should deny "amount" with 3 digit after dot' => [
                ['username' => 'hello_you2', 'amount' => 3.345],
                false,
                ['amount'],
            ],
            'should deny "amount" with dot and with not digit after it' => [
                ['username' => 'hello_you2', 'amount' => '37.'],
                false,
                ['amount'],
            ],
        ];
    }

    /**
     * @test
     */
    public function shouldTransferSuccessfully()
    {
        // given
        $authUserAttrs = $this->generateUserAttrs();
        $destUserAttrs = $this->generateUserAttrs();
        $transferAttrs = $this->generateTransferAttrs();

        if ($authUserAttrs['balance'] - $transferAttrs['amount'] < User::MINIMUM_BALANCE) {
            $authUserAttrs['balance'] += $transferAttrs['amount'] * 2;
        }
        $this->authByUser($authUserAttrs);

        $this->tester->haveRecord(User::class, $destUserAttrs);

        $model = $this->buildModel([
            'username' => $destUserAttrs['username'],
            'amount' => $transferAttrs['amount'],
        ]);

        // when
        $result = $model->transfer();

        // then
        $this->assertTrue($result, 'Transfer should be performed successfully.');
        $this->assertEmpty($model->getErrors(), 'Model should not have an errors.');

        $this->tester->seeRecord(
            User::class,
            [
                'username' => $authUserAttrs['username'],
                'balance' => $authUserAttrs['balance'] - $transferAttrs['amount'],
            ],
        );
        $this->tester->seeRecord(
            User::class,
            [
                'username' => $destUserAttrs['username'],
                'balance' => $destUserAttrs['balance'] + $transferAttrs['amount'],
            ],
        );
    }

    /**
     * @test
     */
    public function shouldNotTransferToNonExistingUser()
    {
        // given
        $authUserAttrs = $this->generateUserAttrs();
        $destUserAttrs = $this->generateUserAttrs();
        $transferAttrs = $this->generateTransferAttrs();

        if ($authUserAttrs['balance'] - $transferAttrs['amount'] < User::MINIMUM_BALANCE) {
            $authUserAttrs['balance'] += $transferAttrs['amount'] * 2;
        }
        $this->authByUser($authUserAttrs);

        $model = $this->buildModel([
            'username' => $destUserAttrs['username'],
            'amount' => $transferAttrs['amount'],
        ]);

        // when
        $result = $model->transfer();

        // then
        $this->assertFalse($result, 'Transfer should not be performed.');
        $this->assertNotEmpty($model->getErrors(), 'Model should have errors.');
        $this->assertNotEmpty(
            $model->getErrors('username'),
            'Model should contain errors for "username" attribute.',
        );

        $this->tester->seeRecord(
            User::class,
            [
                'username' => $authUserAttrs['username'],
                'balance' => $authUserAttrs['balance'],
            ],
        );
        $this->tester->dontSeeRecord(
            User::class,
            ['username' => $destUserAttrs['username']],
        );
    }

    /**
     * @test
     */
    public function shouldNotTransferBiggerThenHave()
    {
        // given
        $authUserAttrs = $this->generateUserAttrs();
        $authUserAttrs['balance'] = User::MINIMUM_BALANCE;
        $this->authByUser($authUserAttrs);

        $destUserAttrs = $this->generateUserAttrs();
        $transferAttrs = $this->generateTransferAttrs();

        $this->tester->haveRecord(User::class, $destUserAttrs);

        $model = $this->buildModel([
            'username' => $destUserAttrs['username'],
            'amount' => $transferAttrs['amount'],
        ]);

        // when
        $result = $model->transfer();

        // then
        $this->assertFalse($result, 'Transfer should not be performed.');
        $this->assertNotEmpty($model->getErrors(), 'Model should have errors.');
        $this->assertNotEmpty(
            $model->getErrors('amount'),
            'Model should contain errors for "amount" attribute.',
        );

        $this->tester->seeRecord(
            User::class,
            [
                'username' => $authUserAttrs['username'],
                'balance' => $authUserAttrs['balance'],
            ],
        );
        $this->tester->seeRecord(
            User::class,
            [
                'username' => $destUserAttrs['username'],
                'balance' => $destUserAttrs['balance'],
            ],
        );
    }

    /**
     * @test
     */
    public function shouldNotTransferToYourself()
    {
        // given
        $authUserAttrs = $this->generateUserAttrs();
        $destUserAttrs = $this->generateUserAttrs();
        $transferAttrs = $this->generateTransferAttrs();

        if ($authUserAttrs['balance'] - $transferAttrs['amount'] < User::MINIMUM_BALANCE) {
            $authUserAttrs['balance'] += $transferAttrs['amount'] * 2;
        }
        $this->authByUser($authUserAttrs);

        $this->tester->haveRecord(User::class, $destUserAttrs);

        $model = $this->buildModel([
            'username' => $authUserAttrs['username'],
            'amount' => $transferAttrs['amount'],
        ]);

        // when
        $result = $model->transfer();

        // then
        $this->assertFalse($result, 'Transfer should not be performed.');
        $this->assertNotEmpty($model->getErrors(), 'Model should have errors.');
        $this->assertNotEmpty(
            $model->getErrors('username'),
            'Model should contain errors for "username" attribute.',
        );

        $this->tester->seeRecord(
            User::class,
            [
                'username' => $authUserAttrs['username'],
                'balance' => $authUserAttrs['balance'],
            ],
        );
        $this->tester->seeRecord(
            User::class,
            [
                'username' => $destUserAttrs['username'],
                'balance' => $destUserAttrs['balance'],
            ],
        );
    }

    /**
     * Builds model for test
     * @param array $params
     *
     * @return TransferForm
     */
    private function buildModel(array $params)
    {
        return new TransferForm($params);
    }

    /**
     * @param array|null $attrs Data of user for auth. Null - any generated user
     */
    private function authByUser(array $attrs = null)
    {
        if (null === $attrs) {
            $attrs = $this->generateUserAttrs();
        }

        $this->tester->haveRecord(User::class, $attrs);

        $result = Yii::$app->user->login(User::findIdentity($attrs['id']));
        $this->assertTrue($result, 'Generated user should be auth successfully');
    }
}
