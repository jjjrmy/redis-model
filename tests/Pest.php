<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Redis;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "uses()" function to bind a different classes or traits.
|
*/

uses(Alvin0\RedisModel\Tests\TestCase::class)->in('Feature');
uses(Alvin0\RedisModel\Tests\TestCase::class)->in('Unit');

uses()->beforeEach(function () {
    // Make sure we're using SQLite
    config(['database.default' => 'sqlite']);
    
    // Drop all tables
    Schema::dropAllTables();

    // Flush Redis
    Redis::flushall();

    // Run migrations using the same method as TestCase
    $this->loadMigrationsFrom(__DIR__ . '/../workbench/database/migrations');
})->in('Feature', 'Unit');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

expect()->extend('toBeCollection', function () {
    return $this->toBeInstanceOf(\Illuminate\Support\Collection::class);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

// Add a basic test to ensure our test environment is working
test('basic test', function () {
    expect(true)->toBeTrue();
});
