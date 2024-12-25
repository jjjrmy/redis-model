<?php

use Illuminate\Database\Eloquent\Model as EloquentModel;
use Alvin0\RedisModel\Model as RedisModel;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Schema::create('mechanics', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->timestamps();
    });

    Schema::create('cars', function (Blueprint $table) {
        $table->id();
        $table->string('model');
        $table->string('mechanic_id');
        $table->timestamps();
    });

    Schema::create('owners', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('car_id');
        $table->timestamps();
    });
});

class EloquentMechanic extends EloquentModel
{
    use \Alvin0\RedisModel\Traits\HasRedisRelationships;
    protected $table = 'mechanics';
    protected $fillable = ['id', 'name'];

    public function eloquentCarOwner()
    {
        return $this->hasOneThrough(EloquentOwner::class, EloquentCar::class, 'mechanic_id', 'car_id');
    }

    public function redisCarOwner()
    {
        return $this->hasOneThrough(RedisOwner::class, RedisCar::class, 'mechanic_id', 'car_id');
    }

    public function eloquentRedisCarOwner()
    {
        return $this->hasOneThrough(EloquentOwner::class, RedisCar::class, 'mechanic_id', 'car_id');
    }

    public function redisEloquentCarOwner()
    {
        return $this->hasOneThrough(RedisOwner::class, EloquentCar::class, 'mechanic_id', 'car_id');
    }
}

class RedisMechanic extends RedisModel
{
    protected $fillable = ['id', 'name'];

    public function eloquentCarOwner()
    {
        return $this->hasOneThrough(EloquentOwner::class, EloquentCar::class, 'mechanic_id', 'car_id');
    }

    public function redisCarOwner()
    {
        return $this->hasOneThrough(RedisOwner::class, RedisCar::class, 'mechanic_id', 'car_id');
    }

    public function eloquentRedisCarOwner()
    {
        return $this->hasOneThrough(EloquentOwner::class, RedisCar::class, 'mechanic_id', 'car_id');
    }

    public function redisEloquentCarOwner()
    {
        return $this->hasOneThrough(RedisOwner::class, EloquentCar::class, 'mechanic_id', 'car_id');
    }
}

class EloquentCar extends EloquentModel
{
    use \Alvin0\RedisModel\Traits\HasRedisRelationships;
    protected $fillable = ['mechanic_id', 'model'];
    protected $table = 'cars';
}

class RedisCar extends RedisModel
{
    protected $fillable = ['mechanic_id', 'model'];
    protected $subKeys = ['mechanic_id'];
}

class EloquentOwner extends EloquentModel
{
    use \Alvin0\RedisModel\Traits\HasRedisRelationships;
    protected $fillable = ['car_id', 'name'];
    protected $table = 'owners';
}

class RedisOwner extends RedisModel
{
    protected $fillable = ['car_id', 'name'];
    protected $subKeys = ['car_id'];
}

dataset('HasOneThrough', [
    'Eloquent -(through)-> Eloquent -(to)-> Eloquent' => [
        fn () => EloquentMechanic::create(['name' => 'John']),
        fn () => EloquentCar::create(['mechanic_id' => 1, 'model' => 'Toyota']),
        fn () => EloquentOwner::create(['car_id' => 1, 'name' => 'Alice']),
        [
            'mechanic' => EloquentModel::class,
            'car' => EloquentModel::class,
            'owner' => EloquentModel::class,
            'carOwner' => 'eloquentCarOwner',
        ]
    ],
    'Eloquent -(through)-> Eloquent -(to)-> Redis' => [
        fn () => EloquentMechanic::create(['name' => 'John']),
        fn () => EloquentCar::create(['mechanic_id' => 1, 'model' => 'Toyota']),
        fn () => RedisOwner::create(['car_id' => 1, 'name' => 'Alice']),
        [
            'mechanic' => EloquentModel::class,
            'car' => EloquentModel::class,
            'owner' => RedisModel::class,
            'carOwner' => 'redisEloquentCarOwner',
        ]
    ],
    'Eloquent -(through)-> Redis -(to)-> Eloquent' => [
        fn () => EloquentMechanic::create(['name' => 'John']),
        fn () => RedisCar::create(['mechanic_id' => 1, 'model' => 'Toyota', 'id' => 1]),
        fn () => EloquentOwner::create(['car_id' => 1, 'name' => 'Alice']),
        [
            'mechanic' => EloquentModel::class,
            'car' => RedisModel::class,
            'owner' => EloquentModel::class,
            'carOwner' => 'eloquentRedisCarOwner',
        ]
    ],
    'Eloquent -(through)-> Redis -(to)-> Redis' => [
        fn () => EloquentMechanic::create(['name' => 'John']),
        fn () => RedisCar::create(['mechanic_id' => 1, 'model' => 'Toyota', 'id' => 1]),
        fn () => RedisOwner::create(['car_id' => 1, 'name' => 'Alice']),
        [
            'mechanic' => EloquentModel::class,
            'car' => RedisModel::class,
            'owner' => RedisModel::class,
            'carOwner' => 'redisCarOwner',
        ]
    ],
    'Redis -(through)-> Eloquent -(to)-> Eloquent' => [
        fn () => RedisMechanic::create(['id' => 1, 'name' => 'John']),
        fn () => EloquentCar::create(['mechanic_id' => 1, 'model' => 'Toyota']),
        fn () => EloquentOwner::create(['car_id' => 1, 'name' => 'Alice']),
        [
            'mechanic' => RedisModel::class,
            'car' => EloquentModel::class,
            'owner' => EloquentModel::class,
            'carOwner' => 'eloquentCarOwner',
        ]
    ],
    'Redis -(through)-> Eloquent -(to)-> Redis' => [
        fn () => RedisMechanic::create(['id' => 1, 'name' => 'John']),
        fn () => EloquentCar::create(['mechanic_id' => 1, 'model' => 'Toyota']),
        fn () => RedisOwner::create(['car_id' => 1, 'name' => 'Alice']),
        [
            'mechanic' => RedisModel::class,
            'car' => EloquentModel::class,
            'owner' => RedisModel::class,
            'carOwner' => 'redisEloquentCarOwner',
        ]
    ],
    'Redis -(through)-> Redis -(to)-> Eloquent' => [
        fn () => RedisMechanic::create(['id' => 1, 'name' => 'John']),
        fn () => RedisCar::create(['mechanic_id' => 1, 'model' => 'Toyota', 'id' => 1]),
        fn () => EloquentOwner::create(['car_id' => 1, 'name' => 'Alice']),
        [
            'mechanic' => RedisModel::class,
            'car' => RedisModel::class,
            'owner' => EloquentModel::class,
            'carOwner' => 'eloquentRedisCarOwner',
        ]
    ],
    'Redis -(through)-> Redis -(to)-> Redis' => [
        fn () => RedisMechanic::create(['id' => 1, 'name' => 'John']),
        fn () => RedisCar::create(['mechanic_id' => 1, 'model' => 'Toyota', 'id' => 1]),
        fn () => RedisOwner::create(['car_id' => 1, 'name' => 'Alice']),
        [
            'mechanic' => RedisModel::class,
            'car' => RedisModel::class,
            'owner' => RedisModel::class,
            'carOwner' => 'redisCarOwner',
        ]
    ],
]);

it('can get hasOneThrough relationships', function (
    EloquentModel|RedisModel $mechanic,
    EloquentModel|RedisModel $car,
    EloquentModel|RedisModel $owner,
    array $expected
) {
    expect($mechanic)
        ->toBeInstanceOf($expected['mechanic']);
    expect($mechanic->{$expected['carOwner']})
        ->toBeInstanceOf(get_class($owner))
        ->toBeInstanceOf($expected['owner']);
})->with('HasOneThrough');

it('can eager load hasOneThrough relationships', function (
    EloquentModel|RedisModel $mechanic,
    EloquentModel|RedisModel $car,
    EloquentModel|RedisModel $owner,
    array $expected
) {
    $modelClass = get_class($mechanic);

    $result = $modelClass::with($expected['carOwner'])->first();
    
    expect($result)
        ->toBeInstanceOf($expected['mechanic'])
        ->and($result->{$expected['carOwner']})
        ->toBeInstanceOf(get_class($owner))
        ->toBeInstanceOf($expected['owner']);
})->with('HasOneThrough');

it('can lazy load hasOneThrough relationships', function (
    EloquentModel|RedisModel $mechanic,
    EloquentModel|RedisModel $car,
    EloquentModel|RedisModel $owner,
    array $expected
) {
    $modelClass = get_class($mechanic);
    $result = $modelClass::first();
    
    expect($result->relationLoaded($expected['carOwner']))
        ->toBeFalse();
    
    $result->load($expected['carOwner']);
    
    expect($result->relationLoaded($expected['carOwner']))
        ->toBeTrue()
        ->and($result)
        ->toBeInstanceOf($expected['mechanic'])
        ->and($result->{$expected['carOwner']})
        ->toBeInstanceOf(get_class($owner))
        ->toBeInstanceOf($expected['owner']);
})->with('HasOneThrough');