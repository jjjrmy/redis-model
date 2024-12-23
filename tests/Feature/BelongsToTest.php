<?php

use Illuminate\Database\Eloquent\Model as EloquentModel;
use Alvin0\RedisModel\Model as RedisModel;

it('can get relationships', function (
    EloquentModel|RedisModel $parent,
    EloquentModel|RedisModel $child,
    array $expected
) {
    expect($child)
        ->toBeInstanceOf($expected['child']);
    expect($child->{$expected['relationship']})
        ->toBeInstanceOf(get_class($parent))
        ->toBeInstanceOf($expected['parent']);
})->with([
    'Eloquent -(belongsTo)-> Eloquent' => [
        fn () => \App\Models\Mountain::create(['name' => 'Big Bear', 'location_id' => 'CA', 'company_id' => 'alterra']),
        fn () => \App\Models\Instructor::create(['name' => 'John Doe', 'mountain_id' => 1]),
        [
            'parent' => EloquentModel::class,
            'child' => EloquentModel::class,
            'relationship' => 'mountain',
        ]
    ],
    'Eloquent -(belongsTo)-> Redis' => [
        fn () => \App\Models\Location::create(['id' => 'CA', 'name' => 'California']),
        fn () => \App\Models\Mountain::create(['name' => 'Big Bear', 'location_id' => 'CA', 'company_id' => 'alterra']),
        [
            'parent' => RedisModel::class,
            'child' => EloquentModel::class,
            'relationship' => 'location',
        ]
    ],
    'Redis -(belongsTo)-> Eloquent' => [
        fn () => \App\Models\Mountain::create(['name' => 'Big Bear', 'location_id' => 'CA', 'company_id' => 'alterra']),
        fn () => \App\Models\Trail::create(['name' => 'Mambo Alley', 'mountain_id' => 1]),
        [
            'parent' => EloquentModel::class,
            'child' => RedisModel::class,
            'relationship' => 'mountain',
        ]
    ],
    'Redis -(belongsTo)-> Redis' => [
        fn () => \App\Models\Customer::create(['id' => '1', 'name' => 'John Doe', 'email' => 'john@doe.com']),
        fn () => \App\Models\Rental::create(['id' => '1', 'customer_id' => '1', 'equipment_id' => '1']),
        [
            'parent' => RedisModel::class,
            'child' => RedisModel::class,
            'relationship' => 'customer',
        ]
    ],
]);