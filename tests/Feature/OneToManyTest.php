<?php

use Illuminate\Database\Eloquent\Model as EloquentModel;
use Alvin0\RedisModel\Model as RedisModel;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Schema::create('posts', function (Blueprint $table) {
        $table->id();
        $table->string('title')->nullable();
        $table->timestamps();
    });

    Schema::create('comments', function (Blueprint $table) {
        $table->id();
        $table->foreignId('post_id')->nullable();
        $table->string('title');
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
}

class RedisPost extends RedisModel
{
    protected $fillable = ['id', 'title'];
    
    public function eloquentComments()
    {
        return $this->hasMany(EloquentComment::class, 'post_id');
    }
    
    public function redisComments()
    {
        return $this->hasMany(RedisComment::class, 'post_id');
    }
}

class EloquentComment extends EloquentModel
{
    use \Alvin0\RedisModel\Traits\HasRedisRelationships;
    protected $fillable = ['post_id', 'title'];
    protected $table = 'comments';
    
    public function eloquentPost()
    {
        return $this->belongsTo(EloquentPost::class, 'post_id')->withDefault();
    }
    
    public function redisPost()
    {
        return $this->belongsTo(RedisPost::class, 'post_id')->withDefault();
    }
}

class RedisComment extends RedisModel {
    protected $fillable = ['post_id', 'title'];
    protected $subKeys = ['title'];
    
    public function redisPost()
    {
        return $this->belongsTo(RedisPost::class, 'post_id')->withDefault();
    }
    
    public function eloquentPost()
    {
        return $this->belongsTo(EloquentPost::class, 'post_id')->withDefault();
    }
}

class EloquentCommentWithAttributes extends EloquentComment
{
    public function eloquentPost()
    {
        return $this->belongsTo(EloquentPost::class, 'post_id')->withDefault([
            'id' => 0,
            'title' => 'Default Post'
        ]);
    }
    
    public function redisPost()
    {
        return $this->belongsTo(RedisPost::class, 'post_id')->withDefault([
            'id' => 0,
            'title' => 'Default Post'
        ]);
    }
}

class RedisCommentWithAttributes extends RedisComment
{
    public function redisPost()
    {
        return $this->belongsTo(RedisPost::class, 'post_id')->withDefault([
            'id' => 0,
            'title' => 'Default Post'
        ]);
    }
    
    public function eloquentPost()
    {
        return $this->belongsTo(EloquentPost::class, 'post_id')->withDefault([
            'id' => 0,
            'title' => 'Default Post'
        ]);
    }
}

class EloquentCommentWithClosure extends EloquentComment
{
    public function eloquentPost()
    {
        return $this->belongsTo(EloquentPost::class, 'post_id')->withDefault(function ($post, $comment) {
            $post->title = "Default Post for comment: " . $comment->title;
            $post->id = 0;
        });
    }
    
    public function redisPost()
    {
        return $this->belongsTo(RedisPost::class, 'post_id')->withDefault(function ($post, $comment) {
            $post->title = "Default Post for comment: " . $comment->title;
            $post->id = 0;
        });
    }
}

class RedisCommentWithClosure extends RedisComment
{
    public function redisPost()
    {
        return $this->belongsTo(RedisPost::class, 'post_id')->withDefault(function ($post, $comment) {
            $post->title = "Default Post for comment: " . $comment->title;
            $post->id = 0;
        });
    }
    
    public function eloquentPost()
    {
        return $this->belongsTo(EloquentPost::class, 'post_id')->withDefault(function ($post, $comment) {
            $post->title = "Default Post for comment: " . $comment->title;
            $post->id = 0;
        });
    }
}

dataset('OneToMany', [
    'Eloquent -(hasMany)-> Eloquent' => [
        fn () => EloquentPost::create(),
        fn () => EloquentComment::create(['post_id' => 1, 'title' => 'foo']),
        [
            'parent' => EloquentModel::class,
            'child' => EloquentModel::class,
            'hasMany' => 'eloquentComments',
            'belongsTo' => 'eloquentPost',
        ]
    ],
    'Eloquent -(hasMany)-> Redis' => [
        fn () => EloquentPost::create(['id' => 1]),
        fn () => RedisComment::create(['id' => 1, 'post_id' => 1, 'title' => 'foo']),
        [
            'parent' => EloquentModel::class,
            'child' => RedisModel::class,
            'hasMany' => 'redisComments',
            'belongsTo' => 'eloquentPost',
        ]
    ],
    'Redis -(hasMany)-> Eloquent' => [
        fn () => RedisPost::create(['id' => 1]),
        fn () => EloquentComment::create(['post_id' => 1, 'title' => 'foo']),
        [
            'parent' => RedisModel::class,
            'child' => EloquentModel::class,
            'hasMany' => 'eloquentComments',
            'belongsTo' => 'redisPost',
        ]
    ],
    'Redis -(hasMany)-> Redis' => [
        fn () => RedisPost::create(['id' => 1]),
        fn () => RedisComment::create(['id' => 1, 'post_id' => 1, 'title' => 'foo']),
        [
            'parent' => RedisModel::class,
            'child' => RedisModel::class,
            'hasMany' => 'redisComments',
            'belongsTo' => 'redisPost',
        ]
    ],
]);

dataset('OneToManyWithAttributes', [
    'Eloquent -(hasMany)-> Eloquent' => [
        fn () => EloquentPost::class,
        fn () => EloquentCommentWithAttributes::class,
        [
            'parent' => EloquentModel::class,
            'child' => EloquentModel::class,
            'belongsTo' => 'eloquentPost',
        ]
    ],
    'Eloquent -(hasMany)-> Redis' => [
        fn () => EloquentPost::class,
        fn () => RedisCommentWithAttributes::class,
        [
            'parent' => EloquentModel::class,
            'child' => RedisModel::class,
            'belongsTo' => 'eloquentPost',
        ]
    ],
    'Redis -(hasMany)-> Eloquent' => [
        fn () => RedisPost::class,
        fn () => EloquentCommentWithAttributes::class,
        [
            'parent' => RedisModel::class,
            'child' => EloquentModel::class,
            'belongsTo' => 'redisPost',
        ]
    ],
    'Redis -(hasMany)-> Redis' => [
        fn () => RedisPost::class,
        fn () => RedisCommentWithAttributes::class,
        [
            'parent' => RedisModel::class,
            'child' => RedisModel::class,
            'belongsTo' => 'redisPost',
        ]
    ],
]);

dataset('OneToManyWithClosure', [
    'Eloquent -(hasMany)-> Eloquent' => [
        fn () => EloquentPost::class,
        fn () => EloquentCommentWithClosure::class,
        [
            'parent' => EloquentModel::class,
            'child' => EloquentModel::class,
            'belongsTo' => 'eloquentPost',
        ]
    ],
    'Eloquent -(hasMany)-> Redis' => [
        fn () => EloquentPost::class,
        fn () => RedisCommentWithClosure::class,
        [
            'parent' => EloquentModel::class,
            'child' => RedisModel::class,
            'belongsTo' => 'eloquentPost',
        ]
    ],
    'Redis -(hasMany)-> Eloquent' => [
        fn () => RedisPost::class,
        fn () => EloquentCommentWithClosure::class,
        [
            'parent' => RedisModel::class,
            'child' => EloquentModel::class,
            'belongsTo' => 'redisPost',
        ]
    ],
    'Redis -(hasMany)-> Redis' => [
        fn () => RedisPost::class,
        fn () => RedisCommentWithClosure::class,
        [
            'parent' => RedisModel::class,
            'child' => RedisModel::class,
            'belongsTo' => 'redisPost',
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
    EloquentModel|RedisModel $parent,
    EloquentModel|RedisModel $child,
    array $expected
) {
    // Create a new comment without a post
    $childClass = get_class($child);
    $newChild = $childClass::create(['title' => 'orphaned comment']);

    expect($newChild->{$expected['belongsTo']})
        ->toBeInstanceOf(get_class($parent))
        ->toBeInstanceOf($expected['parent'])
        ->and($newChild->{$expected['belongsTo']}->exists)
        ->toBeFalse();
})->with('OneToMany');

it('can get default model with attributes for belongsTo relationship when relation is null', function (
    callable $parentClass,
    callable $childClass,
    array $expected
) {
    // Create a new comment without a post
    $childModel = $childClass();
    $newChild = $childModel::create(['title' => 'orphaned comment']);

    expect($newChild->{$expected['belongsTo']})
        ->toBeInstanceOf($parentClass())
        ->toBeInstanceOf($expected['parent'])
        ->and($newChild->{$expected['belongsTo']}->exists)
        ->toBeFalse()
        ->and($newChild->{$expected['belongsTo']}->title)
        ->toBe('Default Post')
        ->and((string)$newChild->{$expected['belongsTo']}->id)
        ->toBe('0');
})->with('OneToManyWithAttributes');

it('can get default model with closure for belongsTo relationship when relation is null', function (
    callable $parentClass,
    callable $childClass,
    array $expected
) {
    // Create a new comment without a post
    $childModel = $childClass();
    $newChild = $childModel::create(['title' => 'orphaned comment']);

    expect($newChild->{$expected['belongsTo']})
        ->toBeInstanceOf($parentClass())
        ->toBeInstanceOf($expected['parent'])
        ->and($newChild->{$expected['belongsTo']}->exists)
        ->toBeFalse()
        ->and($newChild->{$expected['belongsTo']}->title)
        ->toBe('Default Post for comment: orphaned comment')
        ->and((string)$newChild->{$expected['belongsTo']}->id)
        ->toBe('0');
})->with('OneToManyWithClosure');