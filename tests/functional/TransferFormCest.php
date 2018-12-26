<?php

namespace tests\functional;

use app\models\User;
use Codeception\Example;
use FunctionalTester;

class TransferFormCest extends AbstractCest
{
    /**
     * @param FunctionalTester $I
     */
    public function openPageNotAuth(FunctionalTester $I)
    {
        $I->amOnRoute($this->getRoute());

        $I->expectTo('redirect to LoginForm');

        $I->seeResponseCodeIsSuccessful();
        $I->seeInCurrentUrl('site/login');
    }

    /**
     * @param FunctionalTester $I
     */
    public function openPageAndCheckElements(FunctionalTester $I)
    {
        $userAttrs = $this->generateUserAttrs();
        $I->haveRecord(User::class, $userAttrs);

        $I->amLoggedInAs($userAttrs['id']);
        $I->amOnRoute($this->getRoute());

        $I->expectTo('see all required elements');

        $I->seeResponseCodeIsSuccessful();
        $I->seeElement(
            '#transferform-username',
            [
                'name' => 'TransferForm[username]',
                'value' => '',
                'maxlength' => User::USERNAME_MAX_LENGTH,
            ],
        );
        $I->seeElement(
            '#transferform-amount',
            [
                'name' => 'TransferForm[amount]',
                'type' => 'number',
                'value' => '',
                'step' => '0.01',
            ],
        );
        $I->see('Transfer', 'form button');
    }

    /**
     * @depends openPageAndCheckElements
     *
     * @param FunctionalTester $I
     */
    public function autoFillUsernameFromUrl(FunctionalTester $I)
    {
        $userAttrs = $this->generateUserAttrs();
        $I->haveRecord(User::class, $userAttrs);
        $destUserAttrs = $this->generateUserAttrs();

        $I->amLoggedInAs($userAttrs['id']);
        $I->amOnRoute(
            $this->getRoute(),
            ['username' => $destUserAttrs['username']],
        );

        $I->expectTo('see "username" filled');

        $I->seeResponseCodeIsSuccessful();
        $I->seeElement(
            '#transferform-username',
            [
                'name' => 'TransferForm[username]',
                'value' => $destUserAttrs['username'],
                'maxlength' => User::USERNAME_MAX_LENGTH,
            ],
        );
        $I->seeElement(
            '#transferform-amount',
            [
                'name' => 'TransferForm[amount]',
                'type' => 'number',
                'value' => '',
                'step' => '0.01',
            ],
        );
        $I->see('Transfer', 'form button');
    }

    /**
     * @depends openPageAndCheckElements
     * @example { "username": "", "username_msg": "Username cannot be blank.", "amount": "55", "amount_msg": "" }
     * @example { "username": "test", "username_msg": "", "amount": "3.345", "amount_msg": "Amount is invalid." }
     *
     * @param FunctionalTester $I
     */
    public function transferWithWrongData(FunctionalTester $I, Example $example)
    {
        $userAttrs = $this->generateUserAttrs();
        $I->haveRecord(User::class, $userAttrs);

        $I->amLoggedInAs($userAttrs['id']);
        $I->amOnRoute($this->getRoute());

        $I->fillField('#transferform-username', $example['username']);
        $I->fillField('#transferform-amount', $example['amount']);

        $I->expectTo('see validation errors');
        $I->click('#transfer-form button[type=submit]');

        if ($example['username_msg']) {
            $I->see($example['username_msg']);
        }
        if ($example['amount_msg']) {
            $I->see($example['amount_msg']);
        }
    }

    /**
     * @depends openPageAndCheckElements
     *
     * @param FunctionalTester $I
     */
    public function transferSuccessfully(FunctionalTester $I)
    {
        $userAttrs = $this->generateUserAttrs();
        $userAttrs['balance'] = '307.95';
        $I->haveRecord(User::class, $userAttrs);
        $destUserAttrs = $this->generateUserAttrs();
        $destUserAttrs['balance'] = '-55.95';
        $I->haveRecord(User::class, $destUserAttrs);
        $amount = '27.33';
        $expectedUserBalance = '280.62';
        $expectedDestBalance = '-28.62';

        $I->amLoggedInAs($userAttrs['id']);
        $I->amOnRoute($this->getRoute());

        $I->fillField('#transferform-username', $destUserAttrs['username']);
        $I->fillField('#transferform-amount', $amount);

        $I->expectTo('successfully transfer');
        $I->click('#transfer-form button[type=submit]');

        $I->seeResponseCodeIsSuccessful();
        $I->seeCurrentUrlEquals($this->getHomeRoute());

        $I->see($userAttrs['username'], 'table td');
        $I->see($expectedUserBalance, 'table td');
        $I->see($destUserAttrs['username'], 'table td');
        $I->see($expectedDestBalance, 'table td');

        $I->seeRecord(
            User::class,
            ['username' => $userAttrs['username'], 'balance' => $expectedUserBalance],
        );
        $I->seeRecord(
            User::class,
            ['username' => $destUserAttrs['username'], 'balance' => $expectedDestBalance],
        );
    }

    /**
     * @return string
     */
    private function getRoute()
    {
        return 'site/transfer';
    }
}
