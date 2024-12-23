<?php

use Illuminate\Database\Eloquent\Model as EloquentModel;
use Alvin0\RedisModel\Model as RedisModel;
use Illuminate\Support\Collection;

it('can get relationships', function (
    EloquentModel|RedisModel $parent,
    EloquentModel|RedisModel $child,
    array $expected
) {
    expect($parent)
        ->toBeInstanceOf($expected['parent']);
    expect($parent->{$expected['relationship']})
        ->toBeInstanceOf(Collection::class)
        ->first()
        ->toBeInstanceOf(get_class($child))
        ->toBeInstanceOf($expected['child']);
})->with([
    'Eloquent -(hasMany)-> Eloquent' => [
        fn () => \App\Models\Mountain::create(['name' => 'Big Bear', 'location_id' => 'CA', 'company_id' => 'alterra']),
        fn () => \App\Models\SkiLift::create(['name' => 'Snow Valley Express', 'mountain_id' => 1, 'starting_trail_id' => Str::uuid(), 'ending_trail_id' => Str::uuid()]),
        [
            'parent' => EloquentModel::class,
            'child' => EloquentModel::class,
            'relationship' => 'skiLifts',
        ]
    ],
    'Eloquent -(hasMany)-> Redis' => [
        fn () => \App\Models\Mountain::create(['name' => 'Big Bear', 'location_id' => 'CA', 'company_id' => 'alterra']),
        fn () => \App\Models\Trail::create(['name' => 'Mambo Alley', 'mountain_id' => 1]),
        [
            'parent' => EloquentModel::class,
            'child' => RedisModel::class,
            'relationship' => 'trails',
        ]
    ],
    'Redis -(hasMany)-> Eloquent' => [
        fn () => \App\Models\Location::create(['id' => 'CA', 'name' => 'California']),
        fn () => \App\Models\Mountain::create(['name' => 'Big Bear', 'location_id' => 'CA', 'company_id' => 'alterra']),
        [
            'parent' => RedisModel::class,
            'child' => EloquentModel::class,
            'relationship' => 'mountains',
        ]
    ],
    'Redis -(hasMany)-> Redis' => [
        fn () => \App\Models\Customer::create(['id' => '1', 'name' => 'John Doe', 'email' => 'john@doe.com']),
        fn () => \App\Models\Ticket::create(['customer_id' => '1', 'mountain_id' => 1, 'valid_from' => '2023-12-01', 'valid_until' => '2023-12-10']),
        [
            'parent' => RedisModel::class,
            'child' => RedisModel::class,
            'relationship' => 'tickets',
        ]
    ],
]);