<?php

use App\Models\{User, Mountain, Location};
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Alvin0\RedisModel\Model as RedisModel;

beforeEach(function () {
    // $this->seed(\Database\Seeders\DatabaseSeeder::class);
});

it('can get relationships', function (
    EloquentModel|RedisModel $parent,
    EloquentModel|RedisModel $child,
    array $expected
) {
    expect($child)
        ->toBeInstanceOf($expected['child'])
        ->{$expected['relationship']}
        ->toBeInstanceOf(get_class($parent))
        ->toBeInstanceOf($expected['parent'])
    ;
})->with([
    'Eloquent -> Redis' => [
        fn () => Location::create(['id' => 'CA', 'name' => 'California']),
        fn () => Mountain::create(['name' => 'Big Bear', 'location_id' => 'CA']),
        [
            'parent' => RedisModel::class,
            'child' => EloquentModel::class,
            'relationship' => 'location',
        ]
    ]
]);
