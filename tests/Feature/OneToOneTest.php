<?php

use Illuminate\Database\Eloquent\Model as EloquentModel;
use Alvin0\RedisModel\Model as RedisModel;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Schema::create('customers', function (Blueprint $table) {
        $table->id();
        $table->timestamps();
    });

    Schema::create('phones', function (Blueprint $table) {
        $table->id();
        $table->foreignId('customer_id');
        $table->timestamps();
    });
});

class EloquentCustomer extends EloquentModel
{
    use \Alvin0\RedisModel\Traits\HasRedisRelationships;
    protected $table = 'customers';
    protected $fillable = ['id'];

    public function eloquentPhone()
    {
        return $this->hasOne(EloquentPhone::class, 'customer_id');
    }

    public function redisPhone()
    {
        return $this->hasOne(RedisPhone::class, 'customer_id');
    }
}

class RedisCustomer extends RedisModel
{
    protected $fillable = ['id'];

    public function eloquentPhone()
    {
        return $this->hasOne(EloquentPhone::class, 'customer_id');
    }

    public function redisPhone()
    {
        return $this->hasOne(RedisPhone::class, 'customer_id');
    }
}

class EloquentPhone extends EloquentModel
{
    use \Alvin0\RedisModel\Traits\HasRedisRelationships;
    protected $fillable = ['customer_id'];
    protected $table = 'phones';

    public function eloquentCustomer()
    {
        return $this->belongsTo(EloquentCustomer::class, 'customer_id');
    }

    public function redisCustomer()
    {
        return $this->belongsTo(RedisCustomer::class, 'customer_id');
    }
}

class RedisPhone extends RedisModel {
    protected $fillable = ['customer_id'];

    public function redisCustomer()
    {
        return $this->belongsTo(RedisCustomer::class, 'customer_id');
    }

    public function eloquentCustomer()
    {
        return $this->belongsTo(EloquentCustomer::class, 'customer_id');
    }
}

dataset('OneToOne', [
    'Eloquent -(hasOne)-> Eloquent' => [
        fn () => EloquentCustomer::create(),
        fn () => EloquentPhone::create(['customer_id' => 1]),
        [
            'parent' => EloquentModel::class,
            'child' => EloquentModel::class,
            'hasOne' => 'eloquentPhone',
            'belongsTo' => 'eloquentCustomer',
        ]
    ],
    'Eloquent -(hasOne)-> Redis' => [
        fn () => EloquentCustomer::create(['id' => 1]),
        fn () => RedisPhone::create(['customer_id' => 1]),
        [
            'parent' => EloquentModel::class,
            'child' => RedisModel::class,
            'hasOne' => 'redisPhone',
            'belongsTo' => 'eloquentCustomer',
        ]
    ],
    'Redis -(hasOne)-> Eloquent' => [
        fn () => RedisCustomer::create(['id' => 1]),
        fn () => EloquentPhone::create(['customer_id' => 1]),
        [
            'parent' => RedisModel::class,
            'child' => EloquentModel::class,
            'hasOne' => 'eloquentPhone',
            'belongsTo' => 'redisCustomer',
        ]
    ],
    'Redis -(hasOne)-> Redis' => [
        fn () => RedisCustomer::create(['id' => 1]),
        fn () => RedisPhone::create(['customer_id' => 1]),
        [
            'parent' => RedisModel::class,
            'child' => RedisModel::class,
            'hasOne' => 'redisPhone',
            'belongsTo' => 'redisCustomer',
        ]
    ],
]);

it('can get hasOne relationships', function (
    EloquentModel|RedisModel $parent,
    EloquentModel|RedisModel $child,
    array $expected
) {
    expect($parent)
        ->toBeInstanceOf($expected['parent']);
    expect($parent->{$expected['hasOne']})
        ->toBeInstanceOf(get_class($child))
        ->toBeInstanceOf($expected['child']);
})->with('OneToOne');

it('can get belongsTo relationships', function (
    EloquentModel|RedisModel $parent,
    EloquentModel|RedisModel $child,
    array $expected
) {
    expect($child)
        ->toBeInstanceOf($expected['child']);
    expect($child->{$expected['belongsTo']})
        ->toBeInstanceOf(get_class($parent))
        ->toBeInstanceOf($expected['parent']);
})->with('OneToOne');

it('can eager load hasOne relationships', function (
    EloquentModel|RedisModel $parent,
    EloquentModel|RedisModel $child,
    array $expected
) {
    $modelClass = get_class($parent);
    $result = $modelClass::with($expected['hasOne'])->first();
    
    expect($result)
        ->toBeInstanceOf($expected['parent'])
        ->and($result->{$expected['hasOne']})
        ->toBeInstanceOf(get_class($child))
        ->toBeInstanceOf($expected['child']);
})->with('OneToOne');

it('can eager load belongsTo relationships', function (
    EloquentModel|RedisModel $parent,
    EloquentModel|RedisModel $child,
    array $expected
) {
    $modelClass = get_class($child);
    $result = $modelClass::with($expected['belongsTo'])->first();
    
    expect($result)
        ->toBeInstanceOf($expected['child'])
        ->and($result->{$expected['belongsTo']})
        ->toBeInstanceOf(get_class($parent))
        ->toBeInstanceOf($expected['parent']);
})->with('OneToOne');