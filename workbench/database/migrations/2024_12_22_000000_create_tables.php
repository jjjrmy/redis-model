<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password')->default('password');
            $table->timestamps();
        });

        /*
         * Locations
         * - string: id (like "CA")
         * - string: name (like "California")
         */

        Schema::create('mountains', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('location_id')->index(); // Redis Key
            $table->timestamps();
        });

        Schema::create('ski_lifts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('mountain_id');
            $table->string('starting_trail_id')->index(); // Redis Key
            $table->string('ending_trail_id')->index(); // Redis Key
            $table->timestamps();
        });

        /*
         * Lift Chairs
         * - string: id
         * - integer: lift_id
         */

        Schema::create('instructors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('mountain_id');
            $table->timestamps();
        });

        Schema::create('operators', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('ski_lift_id');
            $table->timestamps();
        });

        Schema::create('equipment', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['ski', 'snowboard', 'helmet', 'boots', 'poles', 'goggles']);
            $table->string('brand_slug')->index(); // Redis Key
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment');
        Schema::dropIfExists('operators');
        Schema::dropIfExists('instructors');
        Schema::dropIfExists('lifts');
        Schema::dropIfExists('mountains');
        Schema::dropIfExists('users');
    }
}; 