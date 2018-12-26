<?php

namespace tests\functional;

use app\models\User;
use FunctionalTester;

class LoginFormCest extends AbstractCest
{
    /**
     * @param FunctionalTester $I
     */
    public function _before(FunctionalTester $I)
    {
        $I->amOnRoute('site/login');
    }

    /**
     * Checks elements at Login page
     * @param FunctionalTester $I
     */
    public function openLoginPage(\FunctionalTester $I)
    {
        $I->see('Login', 'h1');
        $I->seeElement(
            'input#loginform-username',
            ['name' => 'LoginForm[username]', 'value' => ''],
        );
        $I->seeCheckboxIsChecked('#loginform-rememberme');
        $I->seeElement(
            'button',
            ['name' => 'login-button'],
        );
    }

    /**
     * @param FunctionalTester $I
     */
    public function loginWithEmptyCredentials(\FunctionalTester $I)
    {
        $I->fillField('#loginform-username', '');
        $I->click('#login-form button[type=submit]');

        $I->expectTo('see validations errors');

        $I->see('Username cannot be blank.');
    }

    /**
     * @param FunctionalTester $I
     */
    public function loginWithWrongCredentials(\FunctionalTester $I)
    {
        $I->fillField('#loginform-username', 'привіт');
        $I->click('#login-form button[type=submit]');

        $I->expectTo('see validations errors');

        $I->see('Username is invalid.');
    }

    /**
     * @param FunctionalTester $I
     */
    public function loginExistingUserSuccessfully(\FunctionalTester $I)
    {
        $userAttrs = $this->generateUserAttrs();
        $I->haveRecord(User::class, $userAttrs);

        $I->fillField('#loginform-username', $userAttrs['username']);
        $I->click('#login-form button[type=submit]');

        $I->expectTo('Login successfully');

        $I->seeResponseCodeIsSuccessful();
        $I->seeCurrentUrlEquals($this->getHomeRoute());
        $I->see(sprintf(
            'Logout (%s: %s)',
            $userAttrs['username'],
            number_format($userAttrs['balance'], 2, '.', ''),
        ));
        $I->dontSeeElement('#login-form');
    }

    /**
     * @param FunctionalTester $I
     */
    public function loginNoUserSuccessfully(\FunctionalTester $I)
    {
        do {
            $userAttrs = $this->generateUserAttrs();
        } while (User::findByUsername($userAttrs['username']));

        $I->fillField('#loginform-username', $userAttrs['username']);
        $I->click('#login-form button[type=submit]');

        $I->expectTo('Login successfully');

        $I->seeResponseCodeIsSuccessful();
        $I->seeCurrentUrlEquals($this->getHomeRoute());
        $I->see(sprintf(
            'Logout (%s: %s)',
            $userAttrs['username'],
            '0.00',
        ));
        $I->dontSeeElement('#login-form');

        $I->seeRecord(
            User::class,
            ['username' => $userAttrs['username'], 'balance' => 0]
        );
    }
}
