<?php

namespace tests\functional;

use Faker\Factory as FakerFactory;
use Faker\Generator;

abstract class AbstractCest
{
    /**
     * @var Generator
     */
    protected $faker;

    /**
     * Creates tools common for all cases
     */
    protected function getFaker()
    {
        if (empty($this->faker)) {
            $this->faker = FakerFactory::create();
        }
        return $this->faker;
    }

    /**
     * @return array
     */
    protected function generateUserAttrs()
    {
        $faker = $this->getFaker();

        return [
            'id' => $faker->randomNumber(null, true),
            'username' => $faker->userName,
            'balance' => $faker->randomFloat(2, -1000, 99999),
        ];
    }

    /**
     * @return string
     */
    protected function getHomeRoute()
    {
        return '/index-test.php';
    }
}
