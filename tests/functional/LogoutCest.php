<?php

namespace tests\functional;

use app\models\User;
use FunctionalTester;

class LogoutCest extends AbstractCest
{
    /**
     * Checks elements at Login page
     * @param FunctionalTester $I
     */
    public function logout(\FunctionalTester $I)
    {
        $userAttrs = $this->generateUserAttrs();
        $I->haveRecord(User::class, $userAttrs);

        $I->amLoggedInAs($userAttrs['id']);
        $I->amOnRoute('/');

        $I->see('Logout', '.navbar-nav form');
        $I->click('.navbar-nav form button[type=submit]');

        $I->expectTo('Logout successfully');

        $I->seeResponseCodeIsSuccessful();
        $I->seeCurrentUrlEquals($this->getHomeRoute());
        $I->see('Login', '.navbar-nav a');
    }
}
