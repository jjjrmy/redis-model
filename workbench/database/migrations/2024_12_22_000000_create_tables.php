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
            $table->string('password');
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

        Schema::create('lifts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('mountain_id')->constrained('mountains');
            $table->unsignedInteger('starting_trail_id')->index();
            $table->unsignedInteger('ending_trail_id')->index();
            $table->timestamps();
        });

        Schema::create('instructors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('operators', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('equipment', function (Blueprint $table) {
            $table->id();
            $table->string('name');
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