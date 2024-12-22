<?php

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
});

it('can get a user', function () {
    // Seed the database
    $user = User::find(1);
    expect($user->name)->toEqual('John Doe');
});
