<?php

use Illuminate\Database\Eloquent\Model as EloquentModel;
use Alvin0\RedisModel\Model as RedisModel;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Schema::create('posts', function (Blueprint $table) {
        $table->id();
        $table->timestamps();
    });

    Schema::create('comments', function (Blueprint $table) {
        $table->id();
        $table->foreignId('post_id');
        $table->string('title');
        $table->timestamps();
    });
});

class EloquentPost extends EloquentModel
{
    use \Alvin0\RedisModel\Traits\HasRedisRelationships;
    protected $table = 'posts';
    protected $fillable = ['id'];
    
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
    protected $fillable = ['id'];
    
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
        return $this->belongsTo(EloquentPost::class, 'post_id');
    }
    
    public function redisPost()
    {
        return $this->belongsTo(RedisPost::class, 'post_id');
    }
}

class RedisComment extends RedisModel {
    protected $fillable = ['post_id', 'title'];
    protected $subKeys = ['title'];
    
    public function redisPost()
    {
        return $this->belongsTo(RedisPost::class, 'post_id');
    }
    
    public function eloquentPost()
    {
        return $this->belongsTo(EloquentPost::class, 'post_id');
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