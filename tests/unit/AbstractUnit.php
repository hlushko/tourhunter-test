<?php

namespace tests\unit;

use Codeception\Module\Yii2;
use Faker\Factory as FakerFactory;
use Faker\Generator;

/**
 * @property Yii2 tester
 */
abstract class AbstractUnit extends \Codeception\Test\Unit
{
    /**
     * @var Generator
     */
    protected static $faker;

    /**
     * @return Generator
     */
    public static function getFaker()
    {
        if (empty(static::$faker)) {
            static::$faker = FakerFactory::create();
        }

        return static::$faker;
    }

    /**
     * @return array
     */
    protected function generateUserAttrs()
    {
        $faker = static::getFaker();

        return [
            'id' => $faker->randomNumber(null, true),
            'username' => $faker->userName,
            'balance' => $faker->randomFloat(2, -1000, 99999),
        ];
    }

    /**
     * @return array
     */
    protected function generateTransferAttrs()
    {
        $faker = static::getFaker();

        return [
            'username' => $faker->userName,
            'amount' => $faker->randomFloat(2, 0.01, 99999),
        ];
    }
}
