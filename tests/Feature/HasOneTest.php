<?php

use Illuminate\Database\Eloquent\Model as EloquentModel;
use Alvin0\RedisModel\Model as RedisModel;
use Illuminate\Support\Str;

it('can get relationships', function (
    EloquentModel|RedisModel $parent,
    EloquentModel|RedisModel $child,
    array $expected
) {
    expect($parent)
        ->toBeInstanceOf($expected['parent']);
    expect($parent->{$expected['relationship']})
        ->toBeInstanceOf(get_class($child))
        ->toBeInstanceOf($expected['child']);
})->with([
    'Eloquent -(hasOne)-> Eloquent' => [
        fn () => \App\Models\SkiLift::create(['name' => 'Snow Valley Express', 'mountain_id' => 1, 'starting_trail_id' => Str::uuid(), 'ending_trail_id' => Str::uuid()]),
        fn () => \App\Models\Operator::create(['name' => 'Taylor Otwell', 'ski_lift_id' => 1]),
        [
            'parent' => EloquentModel::class,
            'child' => EloquentModel::class,
            'relationship' => 'operator',
        ]
    ],
    'Eloquent -(hasOne)-> Redis' => [
        fn () => \App\Models\Equipment::create(['type' => 'ski', 'brand_slug' => 'blizzard']),
        fn () => \App\Models\Rental::create(['customer_id' => 1, 'equipment_id' => 1, 'start_date' => '2023-12-01', 'end_date' => '2023-12-10']),
        [
            'parent' => EloquentModel::class,
            'child' => RedisModel::class,
            'relationship' => 'rental',
        ]
    ],
    'Redis -(hasOne)-> Eloquent' => [
        fn () => \App\Models\Location::create(['id' => 'CA', 'name' => 'California']),
        fn () => \App\Models\Mountain::create(['name' => 'Big Bear', 'location_id' => 'CA']),
        [
            'parent' => RedisModel::class,
            'child' => EloquentModel::class,
            'relationship' => 'mountain',
        ]
    ],
    'Redis -(hasOne)-> Redis' => [
        fn () => \App\Models\Customer::create(['id' => '1', 'name' => 'John Doe', 'email' => 'john@doe.com']),
        fn () => \App\Models\Rental::create(['id' => '1', 'customer_id' => '1', 'equipment_id' => '1']),
        [
            'parent' => RedisModel::class,
            'child' => RedisModel::class,
            'relationship' => 'rental',
        ]
    ],
]);