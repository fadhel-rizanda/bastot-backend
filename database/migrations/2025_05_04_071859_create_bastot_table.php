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
        //        general
        Schema::create('roles', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->text('description')->unique();
            $table->timestamps();
        });

        Schema::create('statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('label');
            $table->string('color')->nullable();
            $table->timestamps();
        });

        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable(false);
            $table->text('description')->nullable(false);
            $table->timestamps();
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('title')->nullable(false);
            $table->text('message')->nullable(false);
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });

        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->text('title')->nullable();
            $table->text('body')->nullable(false);
            $table->decimal('rating')->nullable();
            $table->timestamps();
        });

        Schema::create('user_community', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('community_id')->constrained('communities')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('role_id')->constrained('roles')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('communities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->unique();
            $table->foreignId('base_court')->constrained('courts')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description');

            $table->text('address')->nullable(false);
            $table->decimal('latitude')->nullable(false);
            $table->decimal('longitude', 10, 7)->nullable();
            $table->foreignId('location_id')->constrained('locations')->cascadeOnUpdate()->cascadeOnDelete();

            $table->foreignId('community_id')->constrained('communities')->cascadeOnUpdate()->cascadeOnDelete();
            $table->dateTime('start_time')->nullable(false);
            $table->dateTime('end_time')->nullable(false);
            $table->timestamps();
        });

        Schema::create('tournaments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('court_id')->constrained('courts')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamps();
        });

        //      combined events
        /*        Schema::create('events', function (Blueprint $table) {
                    $table->id();
                    $table->string('name');
                    $table->text('description');
                    $table->string('event_type'); // 'general' or 'tournament'

                    // Location information
                    $table->text('address')->nullable();
                    $table->decimal('latitude', 10, 7)->nullable();
                    $table->decimal('longitude', 10, 7)->nullable();
                    $table->foreignId('location_id')->nullable()->constrained('locations')->cascadeOnUpdate()->cascadeOnDelete();
                    $table->foreignId('court_id')->nullable()->constrained('courts')->cascadeOnUpdate()->cascadeOnDelete();

                    $table->foreignId('community_id')->constrained('communities')->cascadeOnUpdate()->cascadeOnDelete();
                    $table->dateTime('start_time')->nullable(false);
                    $table->dateTime('end_time')->nullable(false);
                    $table->timestamps();

                    // Ensure tournaments have unique names
                    $table->unique(['name', 'event_type']);
                });
           */

        Schema::create('tournament_game', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')->constrained('tournaments')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('game_id')->constrained('games')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('round')->nullable(false);
            $table->timestamps();
        });

        Schema::create('courts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->unique();
            $table->string('profile_picture')->nullable();

            $table->text('address')->unique()->nullable(false);
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            $table->foreignId('location_id')->constrained('locations')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('court_id')->constrained('courts')->cascadeOnUpdate()->cascadeOnDelete();
            $table->dateTime('start_time')->nullable(false);
            $table->dateTime('end_time')->nullable(false);
            $table->decimal('price')->nullable(false);
            $table->boolean('is_available')->default(true);
            $table->timestamps();
        });

        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('status_id')->constrained('statuses');
            $table->timestamps();
        });

        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('place_id')->unique()->nullable(false);
            $table->string('residential')->nullable(false);
            $table->string('village')->nullable(false);
            $table->string('city')->nullable(false);
            $table->string('state')->nullable(false);
            $table->string('region')->nullable(false);
            $table->string('country')->nullable(false);
            $table->string('country_code')->nullable(false);
            $table->string('postal_code')->nullable(false);
            $table->timestamps();
        });

        Schema::create('career_opportunity', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable(false);
            $table->text('description')->nullable(false)->unique();
            $table->text('requirements')->nullable(false);
            $table->text('benefits')->nullable(false);
            $table->dateTime('deadline')->nullable(false);

            $table->string('external_link')->nullable();
            $table->string('type')->nullable(false);
            $table->boolean('is_active')->default(true);
            $table->foreignId('user_id')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('career_opportunity_application', function (Blueprint $table) {
            $table->id();
            $table->foreignId('career_opportunity_id')->constrained('career_opportunity')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('requirements_links')->nullable();
            $table->foreignId('status_id')->constrained('statuses');
            $table->timestamps();
        });

        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable(false);
            $table->text('description')->nullable(false)->unique();
            $table->foreignId('court_id')->constrained('courts')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('home_team_id')->constrained('teams')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('away_team_id')->constrained('teams')->cascadeOnUpdate()->cascadeOnDelete();
        });

        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable(false);
            $table->string('logo')->nullable();
            $table->timestamps();
        });

        Schema::create('user_team', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('role_id')->constrained('roles')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('player_game_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('game_id')->constrained('games')->cascadeOnUpdate()->cascadeOnDelete();
            $table->integer('points')->nullable(false);
            $table->integer('rebounds')->nullable(false);
            $table->integer('assists')->nullable(false);
            $table->integer('steals')->nullable(false);
            $table->integer('blocks')->nullable(false);
            $table->integer('turnovers')->nullable(false);
            $table->timestamps();
        });

        Schema::create('highlights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stat_id')->constrained('player_game_stats')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('content')->nullable(false);
            $table->string('type')->nullable(false);
        });

        Schema::create('workout_plan', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable(false);
            $table->text('description')->nullable(false);
            $table->integer('duration')->nullable(false);
            $table->foreignId('tag_id')->constrained('tags')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('training_session', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable(false);
            $table->text('description')->nullable(false);
            $table->string('video')->nullable(false);
            $table->integer('duration')->nullable(false);
            $table->foreignId('workout_plan_id')->constrained('workout_plan')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('user_training_session', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('workout_plan_id')->constrained('workout_plan')->cascadeOnUpdate()->cascadeOnDelete();
            $table->decimal('progress')->default(0.0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_community');
        Schema::dropIfExists('communities');
        Schema::dropIfExists('events');
        Schema::dropIfExists('tournaments');
        Schema::dropIfExists('tournament_game');
        Schema::dropIfExists('courts');
        Schema::dropIfExists('schedules');
        Schema::dropIfExists('reservations');
        Schema::dropIfExists('locations');
        Schema::dropIfExists('career_opportunity');
        Schema::dropIfExists('career_opportunity_application');
        Schema::dropIfExists('games');
        Schema::dropIfExists('teams');
        Schema::dropIfExists('user_team');
        Schema::dropIfExists('player_game_stats');
        Schema::dropIfExists('highlights');
        Schema::dropIfExists('workout_plan');
        Schema::dropIfExists('training_session');
        Schema::dropIfExists('user_training_session');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('statuses');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('reviews');
    }
};
