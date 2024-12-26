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

    // HasManyThrough migrations
    Schema::create('projects', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->timestamps();
    });

    Schema::create('environments', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('project_id');
        $table->timestamps();
    });

    Schema::create('deployments', function (Blueprint $table) {
        $table->id();
        $table->string('environment_id');
        $table->string('commit_hash');
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

    public function eloquentCars()
    {
        return $this->hasMany(EloquentCar::class, 'mechanic_id');
    }

    public function redisCars()
    {
        return $this->hasMany(RedisCar::class, 'mechanic_id');
    }

    public function fluentEloquentCarOwner()
    {
        return $this->through('eloquentCars')->has('eloquentOwner');
    }

    public function fluentRedisCarOwner()
    {
        return $this->through('redisCars')->has('redisOwner');
    }

    public function fluentEloquentRedisCarOwner()
    {
        return $this->through('redisCars')->has('eloquentOwner');
    }

    public function fluentRedisEloquentCarOwner()
    {
        return $this->through('eloquentCars')->has('redisOwner');
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

    public function eloquentCars()
    {
        return $this->hasMany(EloquentCar::class, 'mechanic_id');
    }

    public function redisCars()
    {
        return $this->hasMany(RedisCar::class, 'mechanic_id');
    }

    public function fluentEloquentCarOwner()
    {
        return $this->through('eloquentCars')->has('eloquentOwner');
    }

    public function fluentRedisCarOwner()
    {
        return $this->through('redisCars')->has('redisOwner');
    }

    public function fluentEloquentRedisCarOwner()
    {
        return $this->through('redisCars')->has('eloquentOwner');
    }

    public function fluentRedisEloquentCarOwner()
    {
        return $this->through('eloquentCars')->has('redisOwner');
    }
}

class EloquentCar extends EloquentModel
{
    use \Alvin0\RedisModel\Traits\HasRedisRelationships;
    protected $fillable = ['mechanic_id', 'model'];
    protected $table = 'cars';

    public function eloquentOwner()
    {
        return $this->hasOne(EloquentOwner::class, 'car_id');
    }

    public function redisOwner()
    {
        return $this->hasOne(RedisOwner::class, 'car_id');
    }
}

class RedisCar extends RedisModel
{
    protected $fillable = ['mechanic_id', 'model'];
    protected $subKeys = ['mechanic_id'];

    public function eloquentOwner()
    {
        return $this->hasOne(EloquentOwner::class, 'car_id');
    }

    public function redisOwner()
    {
        return $this->hasOne(RedisOwner::class, 'car_id');
    }
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

// HasManyThrough Models
class EloquentProject extends EloquentModel
{
    use \Alvin0\RedisModel\Traits\HasRedisRelationships;
    protected $table = 'projects';
    protected $fillable = ['id', 'name'];

    public function eloquentDeployments()
    {
        return $this->hasManyThrough(EloquentDeployment::class, EloquentEnvironment::class, 'project_id', 'environment_id');
    }

    public function redisDeployments()
    {
        return $this->hasManyThrough(RedisDeployment::class, RedisEnvironment::class, 'project_id', 'environment_id');
    }

    public function eloquentRedisDeployments()
    {
        return $this->hasManyThrough(EloquentDeployment::class, RedisEnvironment::class, 'project_id', 'environment_id');
    }

    public function redisEloquentDeployments()
    {
        return $this->hasManyThrough(RedisDeployment::class, EloquentEnvironment::class, 'project_id', 'environment_id');
    }

    public function eloquentEnvironments()
    {
        return $this->hasMany(EloquentEnvironment::class, 'project_id');
    }

    public function redisEnvironments()
    {
        return $this->hasMany(RedisEnvironment::class, 'project_id');
    }

    // Fluent string syntax methods
    public function fluentEloquentDeployments()
    {
        return $this->through('eloquentEnvironments')->has('eloquentDeployments');
    }

    public function fluentRedisDeployments()
    {
        return $this->through('redisEnvironments')->has('redisDeployments');
    }

    public function fluentEloquentRedisDeployments()
    {
        return $this->through('redisEnvironments')->has('eloquentDeployments');
    }

    public function fluentRedisEloquentDeployments()
    {
        return $this->through('eloquentEnvironments')->has('redisDeployments');
    }
}

class RedisProject extends RedisModel
{
    protected $fillable = ['id', 'name'];

    public function eloquentDeployments()
    {
        return $this->hasManyThrough(EloquentDeployment::class, EloquentEnvironment::class, 'project_id', 'environment_id');
    }

    public function redisDeployments()
    {
        return $this->hasManyThrough(RedisDeployment::class, RedisEnvironment::class, 'project_id', 'environment_id');
    }

    public function eloquentRedisDeployments()
    {
        return $this->hasManyThrough(EloquentDeployment::class, RedisEnvironment::class, 'project_id', 'environment_id');
    }

    public function redisEloquentDeployments()
    {
        return $this->hasManyThrough(RedisDeployment::class, EloquentEnvironment::class, 'project_id', 'environment_id');
    }

    public function eloquentEnvironments()
    {
        return $this->hasMany(EloquentEnvironment::class, 'project_id');
    }

    public function redisEnvironments()
    {
        return $this->hasMany(RedisEnvironment::class, 'project_id');
    }

    // Fluent string syntax methods
    public function fluentEloquentDeployments()
    {
        return $this->through('eloquentEnvironments')->has('eloquentDeployments');
    }

    public function fluentRedisDeployments()
    {
        return $this->through('redisEnvironments')->has('redisDeployments');
    }

    public function fluentEloquentRedisDeployments()
    {
        return $this->through('redisEnvironments')->has('eloquentDeployments');
    }

    public function fluentRedisEloquentDeployments()
    {
        return $this->through('eloquentEnvironments')->has('redisDeployments');
    }
}

class EloquentEnvironment extends EloquentModel
{
    use \Alvin0\RedisModel\Traits\HasRedisRelationships;
    protected $fillable = ['project_id', 'name'];
    protected $table = 'environments';

    public function eloquentDeployments()
    {
        return $this->hasMany(EloquentDeployment::class, 'environment_id');
    }

    public function redisDeployments()
    {
        return $this->hasMany(RedisDeployment::class, 'environment_id');
    }
}

class RedisEnvironment extends RedisModel
{
    protected $fillable = ['project_id', 'name'];
    protected $subKeys = ['project_id'];

    public function eloquentDeployments()
    {
        return $this->hasMany(EloquentDeployment::class, 'environment_id');
    }

    public function redisDeployments()
    {
        return $this->hasMany(RedisDeployment::class, 'environment_id');
    }
}

class EloquentDeployment extends EloquentModel
{
    use \Alvin0\RedisModel\Traits\HasRedisRelationships;
    protected $fillable = ['environment_id', 'commit_hash'];
    protected $table = 'deployments';
}

class RedisDeployment extends RedisModel
{
    protected $fillable = ['environment_id', 'commit_hash'];
    protected $subKeys = ['environment_id'];
}

dataset('HasOneThrough', [
    'Eloquent -(through)-> Eloquent -(to)-> Eloquent' => [
        function () {
            EloquentMechanic::create(['name' => 'John']);
            EloquentMechanic::create(['id' => 2, 'name' => 'Jane']); // Another mechanic
            EloquentCar::create(['mechanic_id' => 1, 'model' => 'Toyota']);
            EloquentCar::create(['mechanic_id' => 2, 'model' => 'Honda']); // Car for other mechanic
            EloquentOwner::create(['car_id' => 1, 'name' => 'Alice']);
            EloquentOwner::create(['car_id' => 2, 'name' => 'Bob']); // Owner for other car
            return EloquentMechanic::find(1);
        },
        fn () => EloquentCar::where('mechanic_id', 1)->first(),
        fn () => EloquentOwner::where('car_id', 1)->first(),
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

dataset('HasOneThroughFluent', [
    'Eloquent -(through)-> Eloquent -(to)-> Eloquent' => [
        fn () => EloquentMechanic::create(['name' => 'John']),
        fn () => EloquentCar::create(['mechanic_id' => 1, 'model' => 'Toyota']),
        fn () => EloquentOwner::create(['car_id' => 1, 'name' => 'Alice']),
        [
            'mechanic' => EloquentModel::class,
            'car' => EloquentModel::class,
            'owner' => EloquentModel::class,
            'carOwner' => 'fluentEloquentCarOwner',
            'throughRelationship' => 'eloquentCars',
            'hasRelationship' => 'eloquentOwner'
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
            'carOwner' => 'fluentRedisEloquentCarOwner',
            'throughRelationship' => 'eloquentCars',
            'hasRelationship' => 'redisOwner'
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
            'carOwner' => 'fluentEloquentRedisCarOwner',
            'throughRelationship' => 'redisCars',
            'hasRelationship' => 'eloquentOwner'
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
            'carOwner' => 'fluentRedisCarOwner',
            'throughRelationship' => 'redisCars',
            'hasRelationship' => 'redisOwner'
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
            'carOwner' => 'fluentEloquentCarOwner',
            'throughRelationship' => 'eloquentCars',
            'hasRelationship' => 'eloquentOwner'
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
            'carOwner' => 'fluentRedisEloquentCarOwner',
            'throughRelationship' => 'eloquentCars',
            'hasRelationship' => 'redisOwner'
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
            'carOwner' => 'fluentEloquentRedisCarOwner',
            'throughRelationship' => 'redisCars',
            'hasRelationship' => 'eloquentOwner'
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
            'carOwner' => 'fluentRedisCarOwner',
            'throughRelationship' => 'redisCars',
            'hasRelationship' => 'redisOwner'
        ]
    ],
]);

dataset('HasManyThrough', [
    'Eloquent -(through)-> Eloquent -(to)-> Eloquent' => [
        function () {
            EloquentProject::create(['name' => 'Project A']);
            EloquentProject::create(['id' => 2, 'name' => 'Project B']); // Another project
            
            EloquentEnvironment::create(['project_id' => 1, 'name' => 'Production']);
            EloquentEnvironment::create(['project_id' => 1, 'name' => 'Staging']); // Another env for same project
            EloquentEnvironment::create(['project_id' => 2, 'name' => 'Production']); // Env for other project
            
            EloquentDeployment::create(['environment_id' => 1, 'commit_hash' => 'abc123']);
            EloquentDeployment::create(['environment_id' => 1, 'commit_hash' => 'def456']); // Another deployment for same env
            EloquentDeployment::create(['environment_id' => 2, 'commit_hash' => 'ghi789']); // Deployment for other env
            EloquentDeployment::create(['environment_id' => 3, 'commit_hash' => 'jkl012']); // Deployment for other project's env
            
            return EloquentProject::find(1);
        },
        fn () => EloquentEnvironment::where('project_id', 1)->first(),
        fn () => EloquentDeployment::where('environment_id', 1)->first(),
        [
            'project' => EloquentModel::class,
            'environment' => EloquentModel::class,
            'deployment' => EloquentModel::class,
            'deployments' => 'eloquentDeployments',
            'expected_count' => 3, // 2 from env 1 + 1 from env 2
        ]
    ],
    'Eloquent -(through)-> Eloquent -(to)-> Redis' => [
        function () {
            EloquentProject::create(['name' => 'Project A']);
            EloquentProject::create(['id' => 2, 'name' => 'Project B']);
            
            EloquentEnvironment::create(['project_id' => 1, 'name' => 'Production']);
            EloquentEnvironment::create(['project_id' => 1, 'name' => 'Staging']);
            EloquentEnvironment::create(['project_id' => 2, 'name' => 'Production']);
            
            RedisDeployment::create(['environment_id' => 1, 'commit_hash' => 'abc123']);
            RedisDeployment::create(['environment_id' => 1, 'commit_hash' => 'def456']);
            RedisDeployment::create(['environment_id' => 2, 'commit_hash' => 'ghi789']);
            RedisDeployment::create(['environment_id' => 3, 'commit_hash' => 'jkl012']);
            
            return EloquentProject::find(1);
        },
        fn () => EloquentEnvironment::where('project_id', 1)->first(),
        fn () => RedisDeployment::where('environment_id', 1)->first(),
        [
            'project' => EloquentModel::class,
            'environment' => EloquentModel::class,
            'deployment' => RedisModel::class,
            'deployments' => 'redisEloquentDeployments',
            'expected_count' => 3,
        ]
    ],
    'Eloquent -(through)-> Redis -(to)-> Eloquent' => [
        fn () => EloquentProject::create(['name' => 'Project A']),
        fn () => RedisEnvironment::create(['project_id' => 1, 'name' => 'Production', 'id' => 1]),
        fn () => EloquentDeployment::create(['environment_id' => 1, 'commit_hash' => 'abc123']),
        [
            'project' => EloquentModel::class,
            'environment' => RedisModel::class,
            'deployment' => EloquentModel::class,
            'deployments' => 'eloquentRedisDeployments',
        ]
    ],
    'Eloquent -(through)-> Redis -(to)-> Redis' => [
        fn () => EloquentProject::create(['name' => 'Project A']),
        fn () => RedisEnvironment::create(['project_id' => 1, 'name' => 'Production', 'id' => 1]),
        fn () => RedisDeployment::create(['environment_id' => 1, 'commit_hash' => 'abc123']),
        [
            'project' => EloquentModel::class,
            'environment' => RedisModel::class,
            'deployment' => RedisModel::class,
            'deployments' => 'redisDeployments',
        ]
    ],
    'Redis -(through)-> Eloquent -(to)-> Eloquent' => [
        fn () => RedisProject::create(['id' => 1, 'name' => 'Project A']),
        fn () => EloquentEnvironment::create(['project_id' => 1, 'name' => 'Production']),
        fn () => EloquentDeployment::create(['environment_id' => 1, 'commit_hash' => 'abc123']),
        [
            'project' => RedisModel::class,
            'environment' => EloquentModel::class,
            'deployment' => EloquentModel::class,
            'deployments' => 'eloquentDeployments',
        ]
    ],
    'Redis -(through)-> Eloquent -(to)-> Redis' => [
        fn () => RedisProject::create(['id' => 1, 'name' => 'Project A']),
        fn () => EloquentEnvironment::create(['project_id' => 1, 'name' => 'Production']),
        fn () => RedisDeployment::create(['environment_id' => 1, 'commit_hash' => 'abc123']),
        [
            'project' => RedisModel::class,
            'environment' => EloquentModel::class,
            'deployment' => RedisModel::class,
            'deployments' => 'redisEloquentDeployments',
        ]
    ],
    'Redis -(through)-> Redis -(to)-> Eloquent' => [
        fn () => RedisProject::create(['id' => 1, 'name' => 'Project A']),
        fn () => RedisEnvironment::create(['project_id' => 1, 'name' => 'Production', 'id' => 1]),
        fn () => EloquentDeployment::create(['environment_id' => 1, 'commit_hash' => 'abc123']),
        [
            'project' => RedisModel::class,
            'environment' => RedisModel::class,
            'deployment' => EloquentModel::class,
            'deployments' => 'eloquentRedisDeployments',
        ]
    ],
    'Redis -(through)-> Redis -(to)-> Redis' => [
        fn () => RedisProject::create(['id' => 1, 'name' => 'Project A']),
        fn () => RedisEnvironment::create(['project_id' => 1, 'name' => 'Production', 'id' => 1]),
        fn () => RedisDeployment::create(['environment_id' => 1, 'commit_hash' => 'abc123']),
        [
            'project' => RedisModel::class,
            'environment' => RedisModel::class,
            'deployment' => RedisModel::class,
            'deployments' => 'redisDeployments',
        ]
    ],
]);

dataset('HasManyThroughFluent', [
    'Eloquent -(through)-> Eloquent -(to)-> Eloquent' => [
        function () {
            EloquentProject::create(['name' => 'Project A']);
            EloquentProject::create(['id' => 2, 'name' => 'Project B']);
            
            EloquentEnvironment::create(['project_id' => 1, 'name' => 'Production']);
            EloquentEnvironment::create(['project_id' => 1, 'name' => 'Staging']);
            EloquentEnvironment::create(['project_id' => 2, 'name' => 'Production']);
            
            EloquentDeployment::create(['environment_id' => 1, 'commit_hash' => 'abc123']);
            EloquentDeployment::create(['environment_id' => 1, 'commit_hash' => 'def456']);
            EloquentDeployment::create(['environment_id' => 2, 'commit_hash' => 'ghi789']);
            EloquentDeployment::create(['environment_id' => 3, 'commit_hash' => 'jkl012']);
            
            return EloquentProject::find(1);
        },
        fn () => EloquentEnvironment::where('project_id', 1)->first(),
        fn () => EloquentDeployment::where('environment_id', 1)->first(),
        [
            'project' => EloquentModel::class,
            'environment' => EloquentModel::class,
            'deployment' => EloquentModel::class,
            'deployments' => 'fluentEloquentDeployments',
            'throughRelationship' => 'eloquentEnvironments',
            'hasRelationship' => 'eloquentDeployments',
            'expected_count' => 3,
        ]
    ],
    'Eloquent -(through)-> Eloquent -(to)-> Redis' => [
        fn () => EloquentProject::create(['name' => 'Project A']),
        fn () => EloquentEnvironment::create(['project_id' => 1, 'name' => 'Production']),
        fn () => RedisDeployment::create(['environment_id' => 1, 'commit_hash' => 'abc123']),
        [
            'project' => EloquentModel::class,
            'environment' => EloquentModel::class,
            'deployment' => RedisModel::class,
            'deployments' => 'fluentRedisEloquentDeployments',
            'throughRelationship' => 'eloquentEnvironments',
            'hasRelationship' => 'redisDeployments'
        ]
    ],
    'Eloquent -(through)-> Redis -(to)-> Eloquent' => [
        fn () => EloquentProject::create(['name' => 'Project A']),
        fn () => RedisEnvironment::create(['project_id' => 1, 'name' => 'Production', 'id' => 1]),
        fn () => EloquentDeployment::create(['environment_id' => 1, 'commit_hash' => 'abc123']),
        [
            'project' => EloquentModel::class,
            'environment' => RedisModel::class,
            'deployment' => EloquentModel::class,
            'deployments' => 'fluentEloquentRedisDeployments',
            'throughRelationship' => 'redisEnvironments',
            'hasRelationship' => 'eloquentDeployments'
        ]
    ],
    'Eloquent -(through)-> Redis -(to)-> Redis' => [
        fn () => EloquentProject::create(['name' => 'Project A']),
        fn () => RedisEnvironment::create(['project_id' => 1, 'name' => 'Production', 'id' => 1]),
        fn () => RedisDeployment::create(['environment_id' => 1, 'commit_hash' => 'abc123']),
        [
            'project' => EloquentModel::class,
            'environment' => RedisModel::class,
            'deployment' => RedisModel::class,
            'deployments' => 'fluentRedisDeployments',
            'throughRelationship' => 'redisEnvironments',
            'hasRelationship' => 'redisDeployments'
        ]
    ],
    'Redis -(through)-> Eloquent -(to)-> Eloquent' => [
        fn () => RedisProject::create(['id' => 1, 'name' => 'Project A']),
        fn () => EloquentEnvironment::create(['project_id' => 1, 'name' => 'Production']),
        fn () => EloquentDeployment::create(['environment_id' => 1, 'commit_hash' => 'abc123']),
        [
            'project' => RedisModel::class,
            'environment' => EloquentModel::class,
            'deployment' => EloquentModel::class,
            'deployments' => 'fluentEloquentDeployments',
            'throughRelationship' => 'eloquentEnvironments',
            'hasRelationship' => 'eloquentDeployments'
        ]
    ],
    'Redis -(through)-> Eloquent -(to)-> Redis' => [
        fn () => RedisProject::create(['id' => 1, 'name' => 'Project A']),
        fn () => EloquentEnvironment::create(['project_id' => 1, 'name' => 'Production']),
        fn () => RedisDeployment::create(['environment_id' => 1, 'commit_hash' => 'abc123']),
        [
            'project' => RedisModel::class,
            'environment' => EloquentModel::class,
            'deployment' => RedisModel::class,
            'deployments' => 'fluentRedisEloquentDeployments',
            'throughRelationship' => 'eloquentEnvironments',
            'hasRelationship' => 'redisDeployments'
        ]
    ],
    'Redis -(through)-> Redis -(to)-> Eloquent' => [
        fn () => RedisProject::create(['id' => 1, 'name' => 'Project A']),
        fn () => RedisEnvironment::create(['project_id' => 1, 'name' => 'Production', 'id' => 1]),
        fn () => EloquentDeployment::create(['environment_id' => 1, 'commit_hash' => 'abc123']),
        [
            'project' => RedisModel::class,
            'environment' => RedisModel::class,
            'deployment' => EloquentModel::class,
            'deployments' => 'fluentEloquentRedisDeployments',
            'throughRelationship' => 'redisEnvironments',
            'hasRelationship' => 'eloquentDeployments'
        ]
    ],
    'Redis -(through)-> Redis -(to)-> Redis' => [
        fn () => RedisProject::create(['id' => 1, 'name' => 'Project A']),
        fn () => RedisEnvironment::create(['project_id' => 1, 'name' => 'Production', 'id' => 1]),
        fn () => RedisDeployment::create(['environment_id' => 1, 'commit_hash' => 'abc123']),
        [
            'project' => RedisModel::class,
            'environment' => RedisModel::class,
            'deployment' => RedisModel::class,
            'deployments' => 'fluentRedisDeployments',
            'throughRelationship' => 'redisEnvironments',
            'hasRelationship' => 'redisDeployments'
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

it('can get hasOneThrough relationships using fluent string syntax', function (
    EloquentModel|RedisModel $mechanic,
    EloquentModel|RedisModel $car,
    EloquentModel|RedisModel $owner,
    array $expected
) {
    expect($mechanic)
        ->toBeInstanceOf($expected['mechanic']);

    $result = $mechanic->through($expected['throughRelationship'])->has($expected['hasRelationship']);
    expect($result)
        ->toBeInstanceOf(get_class($owner))
        ->toBeInstanceOf($expected['owner']);
})->with('HasOneThroughFluent');

it('can get hasOneThrough relationships using fluent dynamic syntax', function (
    EloquentModel|RedisModel $mechanic,
    EloquentModel|RedisModel $car,
    EloquentModel|RedisModel $owner,
    array $expected
) {
    expect($mechanic)
        ->toBeInstanceOf($expected['mechanic']);

    $throughMethod = 'through' . ucfirst($expected['throughRelationship']);
    $hasMethod = 'has' . ucfirst($expected['hasRelationship']);
    $result = $mechanic->$throughMethod()->$hasMethod();
    expect($result)
        ->toBeInstanceOf(get_class($owner))
        ->toBeInstanceOf($expected['owner']);
})->with('HasOneThroughFluent');

it('can get hasManyThrough relationships', function (
    EloquentModel|RedisModel $project,
    EloquentModel|RedisModel $environment,
    EloquentModel|RedisModel $deployment,
    array $expected
) {
    expect($project)
        ->toBeInstanceOf($expected['project']);
    expect($project->{$expected['deployments']})
        ->toBeCollection()
        ->and($project->{$expected['deployments']}->count())
        ->toBe($expected['expected_count'] ?? 1)
        ->and($project->{$expected['deployments']}->first())
        ->toBeInstanceOf(get_class($deployment))
        ->toBeInstanceOf($expected['deployment']);
})->with('HasManyThrough');

it('can eager load hasManyThrough relationships', function (
    EloquentModel|RedisModel $project,
    EloquentModel|RedisModel $environment,
    EloquentModel|RedisModel $deployment,
    array $expected
) {
    $modelClass = get_class($project);

    $result = $modelClass::with($expected['deployments'])->first();
    
    expect($result)
        ->toBeInstanceOf($expected['project'])
        ->and($result->{$expected['deployments']})
        ->toBeCollection()
        ->and($result->{$expected['deployments']}->count())
        ->toBe($expected['expected_count'] ?? 1)
        ->and($result->{$expected['deployments']}->first())
        ->toBeInstanceOf(get_class($deployment))
        ->toBeInstanceOf($expected['deployment']);
})->with('HasManyThrough');

it('can lazy load hasManyThrough relationships', function (
    EloquentModel|RedisModel $project,
    EloquentModel|RedisModel $environment,
    EloquentModel|RedisModel $deployment,
    array $expected
) {
    $modelClass = get_class($project);
    $result = $modelClass::first();
    
    expect($result->relationLoaded($expected['deployments']))
        ->toBeFalse();
    
    $result->load($expected['deployments']);
    
    expect($result->relationLoaded($expected['deployments']))
        ->toBeTrue()
        ->and($result)
        ->toBeInstanceOf($expected['project'])
        ->and($result->{$expected['deployments']})
        ->toBeCollection()
        ->and($result->{$expected['deployments']}->count())
        ->toBe($expected['expected_count'] ?? 1)
        ->and($result->{$expected['deployments']}->first())
        ->toBeInstanceOf(get_class($deployment))
        ->toBeInstanceOf($expected['deployment']);
})->with('HasManyThrough');

it('can get hasManyThrough relationships using fluent string syntax', function (
    EloquentModel|RedisModel $project,
    EloquentModel|RedisModel $environment,
    EloquentModel|RedisModel $deployment,
    array $expected
) {
    expect($project)
        ->toBeInstanceOf($expected['project']);

    $result = $project->through($expected['throughRelationship'])->has($expected['hasRelationship']);
    expect($result)
        ->toBeCollection()
        ->and($result->count())
        ->toBe($expected['expected_count'] ?? 1)
        ->and($result->first())
        ->toBeInstanceOf(get_class($deployment))
        ->toBeInstanceOf($expected['deployment']);
})->with('HasManyThroughFluent');

it('can get hasManyThrough relationships using fluent dynamic syntax', function (
    EloquentModel|RedisModel $project,
    EloquentModel|RedisModel $environment,
    EloquentModel|RedisModel $deployment,
    array $expected
) {
    expect($project)
        ->toBeInstanceOf($expected['project']);

    $throughMethod = 'through' . ucfirst($expected['throughRelationship']);
    $hasMethod = 'has' . ucfirst($expected['hasRelationship']);
    $result = $project->$throughMethod()->$hasMethod();
    expect($result)
        ->toBeCollection()
        ->and($result->count())
        ->toBe($expected['expected_count'] ?? 1)
        ->and($result->first())
        ->toBeInstanceOf(get_class($deployment))
        ->toBeInstanceOf($expected['deployment']);
})->with('HasManyThroughFluent');