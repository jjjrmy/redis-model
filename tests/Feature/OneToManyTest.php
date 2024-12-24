<?php

use Illuminate\Database\Eloquent\Model as EloquentModel;
use Alvin0\RedisModel\Model as RedisModel;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Schema::create('posts', function (Blueprint $table) {
        $table->id();
        $table->string('title')->nullable();
        $table->string('category_id')->nullable();
        $table->timestamps();
    });

    Schema::create('comments', function (Blueprint $table) {
        $table->id();
        $table->string('post_id');
        $table->string('title');
        $table->timestamps();
    });

    Schema::create('categories', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->timestamps();
    });
});

class EloquentPost extends EloquentModel
{
    use \Alvin0\RedisModel\Traits\HasRedisRelationships;
    protected $table = 'posts';
    protected $fillable = ['id', 'title'];
    
    public function eloquentComments()
    {
        return $this->hasMany(EloquentComment::class, 'post_id');
    }
    
    public function redisComments()
    {
        return $this->hasMany(RedisComment::class, 'post_id');
    }

    public function eloquentCategory()
    {
        return $this->belongsTo(EloquentCategory::class, 'category_id');
    }

    public function redisCategory()
    {
        return $this->belongsTo(RedisCategory::class, 'category_id');
    }

    public function eloquentCategoryWithDefault()
    {
        return $this->belongsTo(EloquentCategory::class, 'category_id')->withDefault();
    }

    public function redisCategoryWithDefault()
    {
        return $this->belongsTo(RedisCategory::class, 'category_id')->withDefault();
    }

    public function eloquentCategoryWithDefaultAttribute()
    {
        return $this->belongsTo(EloquentCategory::class, 'category_id')->withDefault(['name' => 'Default Category']);
    }

    public function redisCategoryWithDefaultAttribute()
    {
        return $this->belongsTo(RedisCategory::class, 'category_id')->withDefault(['name' => 'Default Category']);
    }

    public function eloquentCategoryWithDefaultClosure()
    {
        return $this->belongsTo(EloquentCategory::class, 'category_id')->withDefault(function ($model) {
            $model->name = 'Default Category';
        });
    }

    public function redisCategoryWithDefaultClosure()
    {
        return $this->belongsTo(RedisCategory::class, 'category_id')->withDefault(function ($model) {
            $model->name = 'Default Category';
        });
    }
}

class RedisPost extends RedisModel
{
    protected $fillable = ['id', 'title', 'category_id'];
    
    public function eloquentComments()
    {
        return $this->hasMany(EloquentComment::class, 'post_id');
    }
    
    public function redisComments()
    {
        return $this->hasMany(RedisComment::class, 'post_id');
    }

    public function eloquentCategory()
    {
        return $this->belongsTo(EloquentCategory::class, 'category_id');
    }

    public function redisCategory()
    {
        return $this->belongsTo(RedisCategory::class, 'category_id');
    }

    public function eloquentCategoryWithDefault()
    {
        return $this->belongsTo(EloquentCategory::class, 'category_id')->withDefault();
    }

    public function redisCategoryWithDefault()
    {
        return $this->belongsTo(RedisCategory::class, 'category_id')->withDefault();
    }

    public function eloquentCategoryWithDefaultAttribute()
    {
        return $this->belongsTo(EloquentCategory::class, 'category_id')->withDefault(['name' => 'Default Category']);
    }

    public function redisCategoryWithDefaultAttribute()
    {
        return $this->belongsTo(RedisCategory::class, 'category_id')->withDefault(['name' => 'Default Category']);
    }

    public function eloquentCategoryWithDefaultClosure()
    {
        return $this->belongsTo(EloquentCategory::class, 'category_id')->withDefault(function ($model) {
            $model->name = 'Default Category';
        });
    }

    public function redisCategoryWithDefaultClosure()
    {
        return $this->belongsTo(RedisCategory::class, 'category_id')->withDefault(function ($model) {
            $model->name = 'Default Category';
        });
    }
}

class EloquentComment extends EloquentModel
{
    use \Alvin0\RedisModel\Traits\HasRedisRelationships;
    protected $fillable = ['post_id', 'title'];
    protected $table = 'comments';
    
    public function eloquentPost()
    {
        return $this->belongsTo(EloquentPost::class, 'post_id');
    }
    
    public function redisPost()
    {
        return $this->belongsTo(RedisPost::class, 'post_id');
    }
}

class RedisComment extends RedisModel {
    protected $fillable = ['post_id', 'title'];
    protected $subKeys = ['post_id'];
    
    public function redisPost()
    {
        return $this->belongsTo(RedisPost::class, 'post_id');
    }
    
    public function eloquentPost()
    {
        return $this->belongsTo(EloquentPost::class, 'post_id');
    }
}

class EloquentCategory extends EloquentModel
{
    use \Alvin0\RedisModel\Traits\HasRedisRelationships;
    protected $fillable = ['name'];
    protected $table = 'categories';

    public function eloquentPosts()
    {
        return $this->hasMany(EloquentPost::class, 'category_id');
    }

    public function redisPosts()
    {
        return $this->hasMany(RedisPost::class, 'category_id');
    }
}

class RedisCategory extends RedisModel
{
    protected $fillable = ['name'];
    protected $subKeys = ['name'];

    public function eloquentPosts()
    {
        return $this->hasMany(EloquentPost::class, 'category_id');
    }

    public function redisPosts()
    {
        return $this->hasMany(RedisPost::class, 'category_id');
    }
}

dataset('OneToMany', [
    'Eloquent -(hasMany)-> Eloquent' => [
        fn () => EloquentPost::create(['id' => '1']),
        fn () => EloquentComment::create(['post_id' => '1', 'title' => 'foo']),
        [
            'parent' => EloquentModel::class,
            'child' => EloquentModel::class,
            'hasMany' => 'eloquentComments',
            'belongsTo' => 'eloquentPost',
        ]
    ],
    'Eloquent -(hasMany)-> Redis' => [
        fn () => EloquentPost::create(['id' => '1']),
        fn () => RedisComment::create(['id' => '1', 'post_id' => '1', 'title' => 'foo']),
        [
            'parent' => EloquentModel::class,
            'child' => RedisModel::class,
            'hasMany' => 'redisComments',
            'belongsTo' => 'eloquentPost',
        ]
    ],
    'Redis -(hasMany)-> Eloquent' => [
        fn () => RedisPost::create(['id' => '1']),
        fn () => EloquentComment::create(['post_id' => '1', 'title' => 'foo']),
        [
            'parent' => RedisModel::class,
            'child' => EloquentModel::class,
            'hasMany' => 'eloquentComments',
            'belongsTo' => 'redisPost',
        ]
    ],
    'Redis -(hasMany)-> Redis' => [
        fn () => RedisPost::create(['id' => '1']),
        fn () => RedisComment::create(['id' => '1', 'post_id' => '1', 'title' => 'foo']),
        [
            'parent' => RedisModel::class,
            'child' => RedisModel::class,
            'hasMany' => 'redisComments',
            'belongsTo' => 'redisPost',
        ]
    ],
]);

dataset('OneToManyWithDefault', [
    'Eloquent Post -> Default Eloquent Category' => [
        fn () => EloquentPost::create(),
        [
            'parent' => EloquentModel::class,
            'child' => EloquentModel::class,
            'belongsTo' => 'eloquentCategoryWithDefault',
            'name' => null,
        ]
    ],
    'Redis Post -> Default Eloquent Category' => [
        fn () => RedisPost::create(['id' => 1]),
        [
            'parent' => EloquentModel::class,
            'child' => RedisModel::class,
            'belongsTo' => 'eloquentCategoryWithDefault',
            'name' => null,
        ]
    ],
    'Eloquent Post -> Default Redis Category' => [
        fn () => EloquentPost::create(),
        [
            'parent' => RedisModel::class,
            'child' => EloquentModel::class,
            'belongsTo' => 'redisCategoryWithDefault',
            'name' => null,
        ]
    ],
    'Redis Post -> Default Redis Category' => [
        fn () => RedisPost::create(['id' => 1]),
        [
            'parent' => RedisModel::class,
            'child' => RedisModel::class,
            'belongsTo' => 'redisCategoryWithDefault',
            'name' => null,
        ]
    ],
    'Eloquent Post -> Default Eloquent Category with attributes' => [
        fn () => EloquentPost::create(),
        [
            'parent' => EloquentModel::class,
            'child' => EloquentModel::class,
            'belongsTo' => 'eloquentCategoryWithDefaultAttribute',
            'name' => 'Default Category',
        ]
    ],
    'Redis Post -> Default Eloquent Category with attributes' => [
        fn () => RedisPost::create(['id' => 1]),
        [
            'parent' => EloquentModel::class,
            'child' => RedisModel::class,
            'belongsTo' => 'eloquentCategoryWithDefaultAttribute',
            'name' => 'Default Category',
        ]
    ],
    'Eloquent Post -> Default Redis Category with attributes' => [
        fn () => EloquentPost::create(),
        [
            'parent' => RedisModel::class,
            'child' => EloquentModel::class,
            'belongsTo' => 'redisCategoryWithDefaultAttribute',
            'name' => 'Default Category',
        ]
    ],
    'Redis Post -> Default Redis Category with attributes' => [
        fn () => RedisPost::create(['id' => 1]),
        [
            'parent' => RedisModel::class,
            'child' => RedisModel::class,
            'belongsTo' => 'redisCategoryWithDefaultAttribute',
            'name' => 'Default Category',
        ]
    ],
    'Eloquent Post -> Default Eloquent Category with closure' => [
        fn () => EloquentPost::create(),
        [
            'parent' => EloquentModel::class,
            'child' => EloquentModel::class,
            'belongsTo' => 'eloquentCategoryWithDefaultClosure',
            'name' => 'Default Category',
        ]
    ],
    'Redis Post -> Default Eloquent Category with closure' => [
        fn () => RedisPost::create(['id' => 1]),
        [
            'parent' => EloquentModel::class,
            'child' => RedisModel::class,
            'belongsTo' => 'eloquentCategoryWithDefaultClosure',
            'name' => 'Default Category',
        ]
    ],
    'Eloquent Post -> Default Redis Category with closure' => [
        fn () => EloquentPost::create(),
        [
            'parent' => RedisModel::class,
            'child' => EloquentModel::class,
            'belongsTo' => 'redisCategoryWithDefaultClosure',
            'name' => 'Default Category',
        ]
    ],
    'Redis Post -> Default Redis Category with closure' => [
        fn () => RedisPost::create(['id' => 1]),
        [
            'parent' => RedisModel::class,
            'child' => RedisModel::class,
            'belongsTo' => 'redisCategoryWithDefaultClosure',
            'name' => 'Default Category',
        ]
    ],
]);

it('can get hasMany relationships', function (
    EloquentModel|RedisModel $parent,
    EloquentModel|RedisModel $child,
    array $expected
) {
    expect($parent)
        ->toBeInstanceOf($expected['parent']);
    expect($parent->{$expected['hasMany']})
        ->toBeCollection()
        ->toHaveCount(1)
        ->sequence(
            fn ($item) => $item
                ->toBeInstanceOf($expected['child'])
                ->toBeInstanceOf(get_class($child))
        );
})->with('OneToMany');

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
})->with('OneToMany');

it('can query hasMany relationships with where clause', function (
    EloquentModel|RedisModel $parent,
    EloquentModel|RedisModel $child,
    array $expected
) {
    expect($parent)
        ->toBeInstanceOf($expected['parent']);
        
    $relationship = $parent->{$expected['hasMany']}();
    $result = $relationship->where('title', 'foo')->first();
        
    expect($result)
        ->not->toBeNull()
        ->toBeInstanceOf($expected['child'])
        ->toBeInstanceOf(get_class($child));
        
    expect($result->title)
        ->toBe('foo');
})->with('OneToMany');

it('can eager load hasMany relationships', function (
    EloquentModel|RedisModel $parent,
    EloquentModel|RedisModel $child,
    array $expected
) {
    $modelClass = get_class($parent);
    
    $result = $modelClass::with($expected['hasMany'])->first();
    
    expect($result)
        ->toBeInstanceOf($expected['parent'])
        ->and($result->{$expected['hasMany']})
        ->toBeCollection()
        ->toHaveCount(1)
        ->sequence(
            fn ($item) => $item
                ->toBeInstanceOf($expected['child'])
                ->toBeInstanceOf(get_class($child))
        );
})->with('OneToMany');

it('can hydrate parent models on child models when using chaperone', function (
    EloquentModel|RedisModel $parent,
    EloquentModel|RedisModel $child,
    array $expected
) {
    // Create additional posts and comments for testing
    $modelClass = get_class($parent);
    $childClass = get_class($child);
    
    // Create a second post and comment
    $parent2 = $modelClass::create(['id' => 2]);
    $child2 = $childClass::create(['post_id' => 2, 'title' => 'bar']);
    
    // Eager load the relationships
    $results = $modelClass::with($expected['hasMany'])->get();
    
    // Verify that each comment's parent post is properly hydrated
    foreach ($results as $post) {
        foreach ($post->{$expected['hasMany']} as $comment) {
            expect($comment->{$expected['belongsTo']})
                ->toBeInstanceOf($expected['parent'])
                ->toBeInstanceOf(get_class($post))
                ->and((string)$comment->{$expected['belongsTo']}->id)
                ->toBe((string)$comment->post_id);
        }
    }
})->with('OneToMany');

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
})->with('OneToMany');

it('can get default model for belongsTo relationship when relation is null', function (
    EloquentModel|RedisModel $child,
    array $expected
) {
    $defaultModel = $child->{$expected['belongsTo']};
    
    expect($defaultModel)
        ->toBeInstanceOf($expected['parent'])
        ->and($defaultModel->exists)
        ->toBeFalse()
        ->when(
            $expected['name'] !== null,
            fn ($expectation) => $expectation
                ->and($defaultModel->name)
                ->toBe($expected['name'])
        )
        ->and($defaultModel->id)
        ->toBeNull();
})->with('OneToManyWithDefault');

it('can lazy load hasMany relationships', function (
    EloquentModel|RedisModel $parent,
    EloquentModel|RedisModel $child,
    array $expected
) {
    // Get a fresh instance of the model without the relationship loaded
    $modelClass = get_class($parent);
    $result = $modelClass::first();
    
    // Verify relationship is not loaded
    expect($result->relationLoaded($expected['hasMany']))
        ->toBeFalse();
    
    // Load the relationship
    $result->load($expected['hasMany']);
    
    // Verify relationship is now loaded
    expect($result->relationLoaded($expected['hasMany']))
        ->toBeTrue();
    
    expect($result)
        ->toBeInstanceOf($expected['parent'])
        ->and($result->{$expected['hasMany']})
        ->toBeCollection()
        ->toHaveCount(1)
        ->sequence(
            fn ($item) => $item
                ->toBeInstanceOf($expected['child'])
                ->toBeInstanceOf(get_class($child))
        );
})->with('OneToMany');

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
})->with('OneToMany');

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
    expect($parentResult->relationLoaded($expected['hasMany']))
        ->toBeFalse()
        ->and($childResult->relationLoaded($expected['belongsTo']))
        ->toBeFalse();
    
    // Load multiple relationships
    $parentResult->load($expected['hasMany']);
    $childResult->load($expected['belongsTo']);
    
    // Verify relationships are now loaded
    expect($parentResult->relationLoaded($expected['hasMany']))
        ->toBeTrue()
        ->and($childResult->relationLoaded($expected['belongsTo']))
        ->toBeTrue();
    
    // Verify parent relationship
    expect($parentResult)
        ->toBeInstanceOf($expected['parent'])
        ->and($parentResult->{$expected['hasMany']})
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
        ->and($childResult->{$expected['belongsTo']})
        ->toBeInstanceOf(get_class($parent))
        ->toBeInstanceOf($expected['parent']);
})->with('OneToMany');

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
    expect($parentResult->relationLoaded($expected['hasMany']))
        ->toBeFalse()
        ->and($childResult->relationLoaded($expected['belongsTo']))
        ->toBeFalse();
    
    // Load missing relationships
    $parentResult->loadMissing($expected['hasMany']);
    $childResult->loadMissing($expected['belongsTo']);
    
    // Verify relationships are now loaded
    expect($parentResult->relationLoaded($expected['hasMany']))
        ->toBeTrue()
        ->and($childResult->relationLoaded($expected['belongsTo']))
        ->toBeTrue();
    
    // Verify parent relationship
    expect($parentResult)
        ->toBeInstanceOf($expected['parent'])
        ->and($parentResult->{$expected['hasMany']})
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
        ->and($childResult->{$expected['belongsTo']})
        ->toBeInstanceOf(get_class($parent))
        ->toBeInstanceOf($expected['parent']);
})->with('OneToMany');

it('can query belongsTo relationships using whereBelongsTo with single model', function (
    EloquentModel|RedisModel $parent,
    EloquentModel|RedisModel $child,
    array $expected
) {
    $childClass = get_class($child);

    $childClass::create(['post_id' => 123, 'title' => 'foo']);

    $results = $childClass::whereBelongsTo($parent)->get();

    expect($results)
        ->toBeCollection()
        ->toHaveCount(1)
        ->each(fn ($item) => $item
            ->toBeInstanceOf($expected['child'])
            // ->post_id
            // ->toBe($parent->id)
        );
})->with('OneToMany');

it('can query belongsTo relationships using whereBelongsTo with collection', function (
    EloquentModel|RedisModel $parent,
    EloquentModel|RedisModel $child,
    array $expected
) {
    $parentClass = get_class($parent);
    $childClass = get_class($child);

    // Create multiple parents
    $parent2 = $parentClass::create(['id' => '2']); // Create Post 2
    $parent3 = $parentClass::create(['id' => '3']); // Create Post 3
    $parents = $parentClass::whereIn('id', [$parent->id, $parent2->id, $parent3->id])->get();
    // Eloquent();

    // Create children for each parent
    $childClass::create(['post_id' => $parent2->id, 'title' => 'bar']);
    $childClass::create(['post_id' => $parent3->id, 'title' => 'baz']);
    $childClass::create(['post_id' => 9, 'title' => 'baz']);
    $childClass::create(['post_id' => 9, 'title' => 'baz']);

    $results = $childClass::whereBelongsTo($parents)->get();

    expect($results)
        ->toBeCollection()
        ->toHaveCount(3)
        ->each(fn ($item) => $item
            ->toBeInstanceOf($expected['child'])
        );
})->with('OneToMany');

it('can create related models through relationship', function (
    EloquentModel|RedisModel $parent,
    EloquentModel|RedisModel $child,
    array $expected
) {
    $child->delete();

    $newChild = $parent->{$expected['hasMany']}()->create([
        'post_id' => $parent->id,
        'title' => 'Created through relationship'
    ]);
    
    expect($newChild)
        ->toBeInstanceOf($expected['child'])
        ->toBeInstanceOf(get_class($child))
        ->and($newChild->post_id)
        ->toBe($parent->id)
        ->and($newChild->title)
        ->toBe('Created through relationship')
        ->and($parent->{$expected['hasMany']})
        ->toBeCollection()
        ->toHaveCount(1)
        ->sequence(
            fn ($item) => $item
                ->toBeInstanceOf($expected['child'])
                ->toBeInstanceOf(get_class($child))
        );
})->with('OneToMany');
