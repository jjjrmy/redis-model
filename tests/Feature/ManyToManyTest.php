<?php

use Illuminate\Database\Eloquent\Model as EloquentModel;
use Alvin0\RedisModel\Model as RedisModel;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;
use Illuminate\Database\Eloquent\Relations\Pivot;

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
        $table->boolean('active')->default(true);
        $table->string('created_by')->nullable();
        $table->unique(['user_id', 'role_id']);
        $table->timestamps();
    });
});

class RoleUser extends Pivot
{
    protected $table = 'role_user';
    
    protected $fillable = [
        'user_id',
        'role_id',
        'active',
        'created_by'
    ];

    protected $casts = [
        'active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
}

class EloquentUser extends EloquentModel
{
    use \Alvin0\RedisModel\Traits\HasRedisRelationships;
    protected $table = 'users';
    protected $fillable = ['id', 'name'];
    
    public function eloquentRoles()
    {
        return $this->belongsToMany(EloquentRole::class, 'role_user', 'user_id', 'role_id')
                    ->using(RoleUser::class)
                    ->withPivot('active', 'created_by')
                    ->withTimestamps();
    }
    
    public function redisRoles()
    {
        return $this->belongsToMany(RedisRole::class, 'role_user', 'user_id', 'role_id')
                    ->using(RoleUser::class)
                    ->withPivot('active', 'created_by')
                    ->withTimestamps();
    }

    public function eloquentRolesWithCustomPivot()
    {
        return $this->belongsToMany(EloquentRole::class, 'role_user', 'user_id', 'role_id')
                    ->using(RoleUser::class)
                    ->as('assignment')
                    ->withPivot('active', 'created_by')
                    ->withTimestamps();
    }
    
    public function redisRolesWithCustomPivot()
    {
        return $this->belongsToMany(RedisRole::class, 'role_user', 'user_id', 'role_id')
                    ->using(RoleUser::class)
                    ->as('assignment')
                    ->withPivot('active', 'created_by')
                    ->withTimestamps();
    }
}

class RedisUser extends RedisModel
{
    protected $fillable = ['id', 'name'];
    
    public function eloquentRoles()
    {
        return $this->belongsToMany(EloquentRole::class, 'role_user', 'user_id', 'role_id')
                    ->using(RoleUser::class)
                    ->withPivot('active', 'created_by')
                    ->withTimestamps();
    }
    
    public function redisRoles()
    {
        return $this->belongsToMany(RedisRole::class, 'role_user', 'user_id', 'role_id')
                    ->using(RoleUser::class)
                    ->withPivot('active', 'created_by')
                    ->withTimestamps();
    }

    public function eloquentRolesWithCustomPivot()
    {
        return $this->belongsToMany(EloquentRole::class, 'role_user', 'user_id', 'role_id')
                    ->using(RoleUser::class)
                    ->as('assignment')
                    ->withPivot('active', 'created_by')
                    ->withTimestamps();
    }
    
    public function redisRolesWithCustomPivot()
    {
        return $this->belongsToMany(RedisRole::class, 'role_user', 'user_id', 'role_id')
                    ->using(RoleUser::class)
                    ->as('assignment')
                    ->withPivot('active', 'created_by')
                    ->withTimestamps();
    }
}

class EloquentRole extends EloquentModel
{
    use \Alvin0\RedisModel\Traits\HasRedisRelationships;
    protected $fillable = ['id', 'name'];
    protected $table = 'roles';
    
    public function eloquentUsers()
    {
        return $this->belongsToMany(EloquentUser::class, 'role_user', 'role_id', 'user_id')
                    ->using(RoleUser::class)
                    ->withPivot('active', 'created_by')
                    ->withTimestamps();
    }
    
    public function redisUsers()
    {
        return $this->belongsToMany(RedisUser::class, 'role_user', 'role_id', 'user_id')
                    ->using(RoleUser::class)
                    ->withPivot('active', 'created_by')
                    ->withTimestamps();
    }
}

class RedisRole extends RedisModel {
    protected $fillable = ['id', 'name'];
    
    public function redisUsers()
    {
        return $this->belongsToMany(RedisUser::class, 'role_user', 'role_id', 'user_id')
                    ->using(RoleUser::class)
                    ->withPivot('active', 'created_by')
                    ->withTimestamps();
    }
    
    public function eloquentUsers()
    {
        return $this->belongsToMany(EloquentUser::class, 'role_user', 'role_id', 'user_id')
                    ->using(RoleUser::class)
                    ->withPivot('active', 'created_by')
                    ->withTimestamps();
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

it('can access pivot attributes with timestamps', function (
    EloquentModel|RedisModel $parent,
    EloquentModel|RedisModel $child,
    array $expected
) {
    // Attach the relationship with pivot data
    $parent->{$expected['belongsToMany']}()->attach($child->id, [
        'active' => true,
        'created_by' => 'test_user'
    ]);

    $result = $parent->{$expected['belongsToMany']}->first();
    
    expect($result->pivot)
        ->toBeInstanceOf(RoleUser::class)
        ->and($result->pivot->active)
        ->toBeTrue()
        ->and($result->pivot->created_by)
        ->toBe('test_user')
        ->and($result->pivot->created_at)
        ->not->toBeNull()
        ->and($result->pivot->updated_at)
        ->not->toBeNull();
})->with('ManyToMany');

it('can filter relationships using pivot columns', function (
    EloquentModel|RedisModel $parent,
    EloquentModel|RedisModel $child,
    array $expected
) {
    // Create two relationships with different pivot data
    $parent->{$expected['belongsToMany']}()->attach($child->id, [
        'active' => true,
        'created_by' => 'test_user'
    ]);
    
    $child2 = get_class($child)::create(['id' => 2, 'name' => 'Role 2']);
    $parent->{$expected['belongsToMany']}()->attach($child2->id, [
        'active' => false,
        'created_by' => 'another_user'
    ]);

    // For Redis models, we'll filter the results manually
    if ($parent instanceof RedisModel || $child instanceof RedisModel) {
        $allRoles = $parent->{$expected['belongsToMany']}->filter(function ($role) {
            return $role->pivot->active === true;
        });
        $inactiveRoles = $parent->{$expected['belongsToMany']}->filter(function ($role) {
            return $role->pivot->active === false;
        });
        
        expect($allRoles)
            ->toHaveCount(1)
            ->and($allRoles->first()->pivot->active)
            ->toBeTrue()
            ->and($inactiveRoles)
            ->toHaveCount(1)
            ->and($inactiveRoles->first()->pivot->active)
            ->toBeFalse();

        $testUserRoles = $parent->{$expected['belongsToMany']}->filter(function ($role) {
            return $role->pivot->created_by === 'test_user';
        });
        expect($testUserRoles)
            ->toHaveCount(1)
            ->and($testUserRoles->first()->pivot->created_by)
            ->toBe('test_user');
    } else {
        // For Eloquent models, use wherePivot
        $activeRoles = $parent->{$expected['belongsToMany']}()->wherePivot('active', true)->get();
        $inactiveRoles = $parent->{$expected['belongsToMany']}()->wherePivot('active', false)->get();
        
        expect($activeRoles)
            ->toHaveCount(1)
            ->and($activeRoles->first()->pivot->active)
            ->toBeTrue()
            ->and($inactiveRoles)
            ->toHaveCount(1)
            ->and($inactiveRoles->first()->pivot->active)
            ->toBeFalse();

        $testUserRoles = $parent->{$expected['belongsToMany']}()->wherePivot('created_by', 'test_user')->get();
        expect($testUserRoles)
            ->toHaveCount(1)
            ->and($testUserRoles->first()->pivot->created_by)
            ->toBe('test_user');
    }
})->with('ManyToMany');

it('can order relationships by pivot columns', function (
    EloquentModel|RedisModel $parent,
    EloquentModel|RedisModel $child,
    array $expected
) {
    // Create multiple relationships with different pivot data
    $parent->{$expected['belongsToMany']}()->attach($child->id, [
        'created_by' => 'user1',
        'active' => false
    ]);
    
    $child2 = get_class($child)::create(['id' => 2, 'name' => 'Role 2']);
    $parent->{$expected['belongsToMany']}()->attach($child2->id, [
        'created_by' => 'user2',
        'active' => true
    ]);

    // For Redis models, we'll sort the results manually using collection methods
    if ($parent instanceof RedisModel || $child instanceof RedisModel) {
        $orderedByCreatedBy = $parent->{$expected['belongsToMany']}->sortBy(function ($role) {
            return $role->pivot->created_by;
        })->values();
        
        expect($orderedByCreatedBy)
            ->toHaveCount(2)
            ->and($orderedByCreatedBy->first()->pivot->created_by)
            ->toBe('user1')
            ->and($orderedByCreatedBy->last()->pivot->created_by)
            ->toBe('user2');

        $orderedByActive = $parent->{$expected['belongsToMany']}->sortByDesc(function ($role) {
            return $role->pivot->active;
        })->values();
        
        expect($orderedByActive)
            ->toHaveCount(2)
            ->and($orderedByActive->first()->pivot->active)
            ->toBeTrue()
            ->and($orderedByActive->last()->pivot->active)
            ->toBeFalse();
    } else {
        // For Eloquent models, use orderByPivot
        $orderedByCreatedBy = $parent->{$expected['belongsToMany']}()->orderByPivot('created_by', 'asc')->get();
        expect($orderedByCreatedBy)
            ->toHaveCount(2)
            ->and($orderedByCreatedBy->first()->pivot->created_by)
            ->toBe('user1')
            ->and($orderedByCreatedBy->last()->pivot->created_by)
            ->toBe('user2');

        $orderedByActive = $parent->{$expected['belongsToMany']}()->orderByPivot('active', 'desc')->get();
        expect($orderedByActive)
            ->toHaveCount(2)
            ->and($orderedByActive->first()->pivot->active)
            ->toBeTrue()
            ->and($orderedByActive->last()->pivot->active)
            ->toBeFalse();
    }
})->with('ManyToMany');

it('can customize pivot attribute name using as method', function (
    EloquentModel|RedisModel $parent,
    EloquentModel|RedisModel $child,
    array $expected
) {
    // Use the predefined relationship with custom pivot name
    $relationshipMethod = $expected['belongsToMany'] . 'WithCustomPivot';

    // Attach the relationship
    $parent->{$relationshipMethod}()->attach($child->id, [
        'active' => true,
        'created_by' => 'test_user'
    ]);

    $result = $parent->{$relationshipMethod}->first();
    
    expect($result->assignment)
        ->toBeInstanceOf(RoleUser::class)
        ->and($result->assignment->active)
        ->toBeTrue()
        ->and($result->assignment->created_by)
        ->toBe('test_user');
})->with('ManyToMany');