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
        fn () => EloquentUser::create(['name' => 'John']),
        fn () => EloquentRole::create(['name' => 'Admin']),
        [
            'first' => EloquentModel::class,
            'second' => EloquentModel::class,
            'firstRelation' => 'eloquentRoles',
            'secondRelation' => 'eloquentUsers',
        ]
    ],
    'Eloquent -(belongsToMany)-> Redis' => [
        fn () => EloquentUser::create(['id' => 1, 'name' => 'John']),
        fn () => RedisRole::create(['id' => 1, 'name' => 'Admin']),
        [
            'first' => EloquentModel::class,
            'second' => RedisModel::class,
            'firstRelation' => 'redisRoles',
            'secondRelation' => 'eloquentUsers',
        ]
    ],
    'Redis -(belongsToMany)-> Eloquent' => [
        fn () => RedisUser::create(['id' => 1, 'name' => 'John']),
        fn () => EloquentRole::create(['id' => 1, 'name' => 'Admin']),
        [
            'first' => RedisModel::class,
            'second' => EloquentModel::class,
            'firstRelation' => 'eloquentRoles',
            'secondRelation' => 'redisUsers',
        ]
    ],
    'Redis -(belongsToMany)-> Redis' => [
        fn () => RedisUser::create(['id' => 1, 'name' => 'John']),
        fn () => RedisRole::create(['id' => 1, 'name' => 'Admin']),
        [
            'first' => RedisModel::class,
            'second' => RedisModel::class,
            'firstRelation' => 'redisRoles',
            'secondRelation' => 'redisUsers',
        ]
    ],
]);

it('can attach and get belongsToMany relationships', function (
    EloquentModel|RedisModel $first,
    EloquentModel|RedisModel $second,
    array $expected
) {
    // Attach the relationship
    $first->{$expected['firstRelation']}()->attach($second->id);

    // Test first model's relationship
    expect($first)
        ->toBeInstanceOf($expected['first']);
    expect($first->{$expected['firstRelation']})
        ->toBeCollection()
        ->toHaveCount(1)
        ->sequence(
            fn ($item) => $item
                ->toBeInstanceOf($expected['second'])
                ->toBeInstanceOf(get_class($second))
        );

    // Test second model's relationship
    expect($second)
        ->toBeInstanceOf($expected['second']);
    expect($second->{$expected['secondRelation']})
        ->toBeCollection()
        ->toHaveCount(1)
        ->sequence(
            fn ($item) => $item
                ->toBeInstanceOf($expected['first'])
                ->toBeInstanceOf(get_class($first))
        );
})->with('ManyToMany');