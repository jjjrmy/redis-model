<?php

namespace Alvin0\RedisModel\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use Alvin0\RedisModel\RedisModelServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Use SQLite in memory
        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
            ],
        ]);
        
        // Run migrations
        $this->loadMigrationsFrom(__DIR__ . '/../workbench/database/migrations');
    }

    protected function getPackageProviders($app)
    {
        return [
            RedisModelServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        // Set base path to workbench
        $app->setBasePath(__DIR__ . '/../workbench');
    }
}
