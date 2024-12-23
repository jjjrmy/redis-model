<?php

use Illuminate\Database\Eloquent\Model as EloquentModel;
use Alvin0\RedisModel\Model as RedisModel;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Schema::create('users', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->timestamps();
    });

    Schema::create('roles', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->timestamps();
    });

    Schema::create('role_user', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->foreignId('role_id')->constrained()->onDelete('cascade');
        $table->unique(['user_id', 'role_id']);
        $table->timestamps();
    });
});

class EloquentUser extends EloquentModel
{
    use \Alvin0\RedisModel\Traits\HasRedisRelationships;
    protected $table = 'users';
    protected $fillable = ['id', 'name'];
    
    public function eloquentRoles()
    {
        return $this->belongsToMany(EloquentRole::class, 'role_user', 'user_id', 'role_id');
    }
    
    public function redisRoles()
    {
        return $this->belongsToMany(RedisRole::class, 'role_user', 'user_id', 'role_id');
    }
}

class RedisUser extends RedisModel
{
    protected $fillable = ['id', 'name'];
    
    public function eloquentRoles()
    {
        return $this->belongsToMany(EloquentRole::class, 'role_user', 'user_id', 'role_id');
    }
    
    public function redisRoles()
    {
        return $this->belongsToMany(RedisRole::class, 'role_user', 'user_id', 'role_id');
    }
}

class EloquentRole extends EloquentModel
{
    use \Alvin0\RedisModel\Traits\HasRedisRelationships;
    protected $fillable = ['id', 'name'];
    protected $table = 'roles';
    
    public function eloquentUsers()
    {
        return $this->belongsToMany(EloquentUser::class, 'role_user', 'role_id', 'user_id');
    }
    
    public function redisUsers()
    {
        return $this->belongsToMany(RedisUser::class, 'role_user', 'role_id', 'user_id');
    }
}

class RedisRole extends RedisModel {
    protected $fillable = ['id', 'name'];
    
    public function redisUsers()
    {
        return $this->belongsToMany(RedisUser::class, 'role_user', 'role_id', 'user_id');
    }
    
    public function eloquentUsers()
    {
        return $this->belongsToMany(EloquentUser::class, 'role_user', 'role_id', 'user_id');
    }
}

dataset('ManyToMany', [
    'Eloquent -(belongsToMany)-> Eloquent' => [
        fn () => EloquentUser::create(['id' => 1, 'name' => 'John']),
        fn () => EloquentRole::create(['id' => 1, 'name' => 'Admin']),
        [
            'parent' => EloquentModel::class,
            'child' => EloquentModel::class,
            'belongsToMany' => 'eloquentRoles',
            'inverse' => 'eloquentUsers'
        ]
    ],
    'Eloquent -(belongsToMany)-> Redis' => [
        fn () => EloquentUser::create(['id' => 1, 'name' => 'John']),
        fn () => RedisRole::create(['id' => 1, 'name' => 'Admin']),
        [
            'parent' => EloquentModel::class,
            'child' => RedisModel::class,
            'belongsToMany' => 'redisRoles',
            'inverse' => 'eloquentUsers'
        ]
    ],
    'Redis -(belongsToMany)-> Eloquent' => [
        fn () => RedisUser::create(['id' => 1, 'name' => 'John']),
        fn () => EloquentRole::create(['id' => 1, 'name' => 'Admin']),
        [
            'parent' => RedisModel::class,
            'child' => EloquentModel::class,
            'belongsToMany' => 'eloquentRoles',
            'inverse' => 'redisUsers'
        ]
    ],
    'Redis -(belongsToMany)-> Redis' => [
        fn () => RedisUser::create(['id' => 1, 'name' => 'John']),
        fn () => RedisRole::create(['id' => 1, 'name' => 'Admin']),
        [
            'parent' => RedisModel::class,
            'child' => RedisModel::class,
            'belongsToMany' => 'redisRoles',
            'inverse' => 'redisUsers'
        ]
    ],
]);

it('can attach and get belongsToMany relationships', function (
    EloquentModel|RedisModel $first,
    EloquentModel|RedisModel $second,
    array $expected
) {
    // Attach the relationship
    $first->{$expected['belongsToMany']}()->attach($second->id);

    // Test first model's relationship
    expect($first)
        ->toBeInstanceOf($expected['parent']);
    expect($first->{$expected['belongsToMany']})
        ->toBeCollection()
        ->toHaveCount(1)
        ->sequence(
            fn ($item) => $item
                ->toBeInstanceOf($expected['child'])
                ->toBeInstanceOf(get_class($second))
        );

    // Test second model's relationship
    expect($second)
        ->toBeInstanceOf($expected['child']);
    expect($second->{$expected['inverse']})
        ->toBeCollection()
        ->toHaveCount(1)
        ->sequence(
            fn ($item) => $item
                ->toBeInstanceOf($expected['parent'])
                ->toBeInstanceOf(get_class($first))
        );
})->with('ManyToMany');

it('can eager load belongsToMany relationships', function (
    EloquentModel|RedisModel $parent,
    EloquentModel|RedisModel $child,
    array $expected
) {
    // Attach the relationship first
    $parent->{$expected['belongsToMany']}()->attach($child->id);

    $modelClass = get_class($parent);
    $result = $modelClass::with($expected['belongsToMany'])->first();
    
    expect($result)
        ->toBeInstanceOf($expected['parent'])
        ->and($result->{$expected['belongsToMany']})
        ->toBeCollection()
        ->toHaveCount(1)
        ->sequence(
            fn ($item) => $item
                ->toBeInstanceOf($expected['child'])
                ->toBeInstanceOf(get_class($child))
        );
})->with('ManyToMany');

it('can lazy load belongsToMany relationships', function (
    EloquentModel|RedisModel $parent,
    EloquentModel|RedisModel $child,
    array $expected
) {
    // Attach the relationship first
    $parent->{$expected['belongsToMany']}()->attach($child->id);

    // Get a fresh instance of the model without the relationship loaded
    $modelClass = get_class($parent);
    $result = $modelClass::first();
    
    // Verify relationship is not loaded
    expect($result->relationLoaded($expected['belongsToMany']))
        ->toBeFalse();
    
    // Load the relationship
    $result->load($expected['belongsToMany']);
    
    // Verify relationship is now loaded
    expect($result->relationLoaded($expected['belongsToMany']))
        ->toBeTrue();
    
    expect($result)
        ->toBeInstanceOf($expected['parent'])
        ->and($result->{$expected['belongsToMany']})
        ->toBeCollection()
        ->toHaveCount(1)
        ->sequence(
            fn ($item) => $item
                ->toBeInstanceOf($expected['child'])
                ->toBeInstanceOf(get_class($child))
        );
})->with('ManyToMany');

it('can lazy load multiple belongsToMany relationships', function (
    EloquentModel|RedisModel $parent,
    EloquentModel|RedisModel $child,
    array $expected
) {
    // Attach the relationship first
    $parent->{$expected['belongsToMany']}()->attach($child->id);

    // Get fresh instances of the models without the relationships loaded
    $modelClass = get_class($parent);
    $childClass = get_class($child);
    $parentResult = $modelClass::first();
    $childResult = $childClass::first();
    
    // Verify relationships are not loaded
    expect($parentResult->relationLoaded($expected['belongsToMany']))
        ->toBeFalse()
        ->and($childResult->relationLoaded($expected['inverse']))
        ->toBeFalse();
    
    // Load relationships
    $parentResult->load($expected['belongsToMany']);
    $childResult->load($expected['inverse']);
    
    // Verify relationships are now loaded
    expect($parentResult->relationLoaded($expected['belongsToMany']))
        ->toBeTrue()
        ->and($childResult->relationLoaded($expected['inverse']))
        ->toBeTrue();
    
    // Verify parent relationship
    expect($parentResult)
        ->toBeInstanceOf($expected['parent'])
        ->and($parentResult->{$expected['belongsToMany']})
        ->toBeCollection()
        ->toHaveCount(1)
        ->sequence(
            fn ($item) => $item
                ->toBeInstanceOf($expected['child'])
                ->toBeInstanceOf(get_class($child))
        );
    
    // Verify child relationship
    expect($childResult)
        ->toBeInstanceOf($expected['child'])
        ->and($childResult->{$expected['inverse']})
        ->toBeCollection()
        ->toHaveCount(1)
        ->sequence(
            fn ($item) => $item
                ->toBeInstanceOf($expected['parent'])
                ->toBeInstanceOf(get_class($parent))
        );
})->with('ManyToMany');

it('can lazy load missing belongsToMany relationships', function (
    EloquentModel|RedisModel $parent,
    EloquentModel|RedisModel $child,
    array $expected
) {
    // Attach the relationship first
    $parent->{$expected['belongsToMany']}()->attach($child->id);

    // Get a fresh instance of the model without the relationship loaded
    $modelClass = get_class($parent);
    $result = $modelClass::first();
    
    // Verify relationship is not loaded
    expect($result->relationLoaded($expected['belongsToMany']))
        ->toBeFalse();
    
    // Load the missing relationship
    $result->loadMissing($expected['belongsToMany']);
    
    // Verify relationship is now loaded
    expect($result->relationLoaded($expected['belongsToMany']))
        ->toBeTrue();
    
    expect($result)
        ->toBeInstanceOf($expected['parent'])
        ->and($result->{$expected['belongsToMany']})
        ->toBeCollection()
        ->toHaveCount(1)
        ->sequence(
            fn ($item) => $item
                ->toBeInstanceOf($expected['child'])
                ->toBeInstanceOf(get_class($child))
        );
})->with('ManyToMany');