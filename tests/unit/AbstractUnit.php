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
     * Creates tools common for all cases
     */
    public static function setUpBeforeClass()
    {
        self::$faker = FakerFactory::create();
    }

    /**
     * @return array
     */
    protected function generateUserAttrs()
    {
        return [
            'id' => self::$faker->randomDigitNotNull,
            'username' => self::$faker->userName,
            'balance' => self::$faker->randomFloat(2, -1000, 99999),
        ];
    }
}
