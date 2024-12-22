<?php

namespace Alvin0\RedisModel\Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use Alvin0\RedisModel\RedisModelServiceProvider;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            RedisModelServiceProvider::class,
        ];
    }
}
