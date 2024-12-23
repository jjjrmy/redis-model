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

it('can lazy load hasOne relationships', function (
    EloquentModel|RedisModel $parent,
    EloquentModel|RedisModel $child,
    array $expected
) {
    // Get a fresh instance of the model without the relationship loaded
    $modelClass = get_class($parent);
    $result = $modelClass::first();
    
    // Verify relationship is not loaded
    expect($result->relationLoaded($expected['hasOne']))
        ->toBeFalse();
    
    // Load the relationship
    $result->load($expected['hasOne']);
    
    // Verify relationship is now loaded
    expect($result->relationLoaded($expected['hasOne']))
        ->toBeTrue();
    
    expect($result)
        ->toBeInstanceOf($expected['parent'])
        ->and($result->{$expected['hasOne']})
        ->toBeInstanceOf(get_class($child))
        ->toBeInstanceOf($expected['child']);
})->with('OneToOne');

it('can lazy load belongsTo relationships', function (
    EloquentModel|RedisModel $parent,
    EloquentModel|RedisModel $child,
    array $expected
) {
    // Get a fresh instance of the model without the relationship loaded
    $modelClass = get_class($child);
    $result = $modelClass::first();
    
    // Verify relationship is not loaded
    expect($result->relationLoaded($expected['belongsTo']))
        ->toBeFalse();
    
    // Load the relationship
    $result->load($expected['belongsTo']);
    
    // Verify relationship is now loaded
    expect($result->relationLoaded($expected['belongsTo']))
        ->toBeTrue();
    
    expect($result)
        ->toBeInstanceOf($expected['child'])
        ->and($result->{$expected['belongsTo']})
        ->toBeInstanceOf(get_class($parent))
        ->toBeInstanceOf($expected['parent']);
})->with('OneToOne');

it('can lazy load multiple relationships', function (
    EloquentModel|RedisModel $parent,
    EloquentModel|RedisModel $child,
    array $expected
) {
    // Get fresh instances of the models without relationships loaded
    $parentClass = get_class($parent);
    $childClass = get_class($child);
    
    $parentResult = $parentClass::first();
    $childResult = $childClass::first();
    
    // Verify relationships are not loaded
    expect($parentResult->relationLoaded($expected['hasOne']))
        ->toBeFalse()
        ->and($childResult->relationLoaded($expected['belongsTo']))
        ->toBeFalse();
    
    // Load multiple relationships
    $parentResult->load($expected['hasOne']);
    $childResult->load($expected['belongsTo']);
    
    // Verify relationships are now loaded
    expect($parentResult->relationLoaded($expected['hasOne']))
        ->toBeTrue()
        ->and($childResult->relationLoaded($expected['belongsTo']))
        ->toBeTrue();
    
    // Verify parent relationship
    expect($parentResult)
        ->toBeInstanceOf($expected['parent'])
        ->and($parentResult->{$expected['hasOne']})
        ->toBeInstanceOf(get_class($child))
        ->toBeInstanceOf($expected['child']);
    
    // Verify child relationship
    expect($childResult)
        ->toBeInstanceOf($expected['child'])
        ->and($childResult->{$expected['belongsTo']})
        ->toBeInstanceOf(get_class($parent))
        ->toBeInstanceOf($expected['parent']);
})->with('OneToOne');

it('can lazy load missing relationships', function (
    EloquentModel|RedisModel $parent,
    EloquentModel|RedisModel $child,
    array $expected
) {
    // Get fresh instances of the models without relationships loaded
    $parentClass = get_class($parent);
    $childClass = get_class($child);
    
    $parentResult = $parentClass::first();
    $childResult = $childClass::first();
    
    // Verify relationships are not loaded
    expect($parentResult->relationLoaded($expected['hasOne']))
        ->toBeFalse()
        ->and($childResult->relationLoaded($expected['belongsTo']))
        ->toBeFalse();
    
    // Load missing relationships
    $parentResult->loadMissing($expected['hasOne']);
    $childResult->loadMissing($expected['belongsTo']);
    
    // Verify relationships are now loaded
    expect($parentResult->relationLoaded($expected['hasOne']))
        ->toBeTrue()
        ->and($childResult->relationLoaded($expected['belongsTo']))
        ->toBeTrue();
    
    // Verify parent relationship
    expect($parentResult)
        ->toBeInstanceOf($expected['parent'])
        ->and($parentResult->{$expected['hasOne']})
        ->toBeInstanceOf(get_class($child))
        ->toBeInstanceOf($expected['child']);
    
    // Verify child relationship
    expect($childResult)
        ->toBeInstanceOf($expected['child'])
        ->and($childResult->{$expected['belongsTo']})
        ->toBeInstanceOf(get_class($parent))
        ->toBeInstanceOf($expected['parent']);
})->with('OneToOne');