<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        //        general
        Schema::create('statuses', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name')->unique()->nullable(false);
            $table->text('description')->unique();
            $table->string('color')->nullable();
            $table->string('type')->nullable(false);
            $table->timestamps();
        });

        Schema::create('tags', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name')->unique()->nullable(false);
            $table->text('description')->nullable(false);
            $table->string('color')->nullable();
            $table->string('type')->nullable(false);
            $table->timestamps();
        });

//        content
        Schema::create('locations', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->string('name');
            $table->string('residential')->nullable();
            $table->string('road')->nullable();
            $table->string('city_block')->nullable();
            $table->string('suburb')->nullable();
            $table->string('city_district')->nullable();
            $table->string('village')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('region')->nullable();
            $table->string('country')->nullable();
            $table->string('country_code')->nullable();
            $table->string('postcode')->nullable();
            $table->timestamps();
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('title')->nullable(false);
            $table->text('message')->nullable(false);
            $table->boolean('is_read')->default(false);
            $table->string('type')->nullable(false);
            $table->json('data')->nullable();
            $table->timestamps();
        });

        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->text('title')->nullable();
            $table->text('body')->nullable(false);
            $table->decimal('rating')->nullable();
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

        Schema::create('fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('court_id')->constrained('courts')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('name');
            $table->string('image')->nullable();
            $table->decimal('default_price_per_hour')->nullable();
            $table->boolean('is_available')->default(true);

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
            $table->decimal('price')->nullable();

            $table->text('address')->nullable(false);
            $table->decimal('latitude')->nullable(false);
            $table->decimal('longitude', 10, 7)->nullable();
            $table->foreignId('location_id')->constrained('locations')->cascadeOnUpdate()->cascadeOnDelete();

            $table->foreignId('community_id')->constrained('communities')->cascadeOnUpdate()->cascadeOnDelete();
//            $table->foreignId('status_id')->constrained('statuses')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('status_id')->nullable();
            $table->foreign('status_id')->references('id')->on('statuses')->cascadeOnDelete()->cascadeOnUpdate();
            $table->dateTime('start_time')->nullable(false);
            $table->dateTime('end_time')->nullable(false);
            $table->timestamps();
        });

        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->string('initial')->nullable(false)->unique();
            $table->string('name')->nullable(false)->unique();
            $table->string('logo')->nullable();
            $table->foreignId('team_owner_id')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable(false);
            $table->text('description')->nullable(false);
            $table->foreignId('field_id')->nullable()->constrained('fields')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('home_team_id')->constrained('teams')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('away_team_id')->constrained('teams')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamp('start_time')->nullable();
            $table->timestamp('end_time')->nullable();
            $table->integer('home_score')->default(0);
            $table->integer('away_score')->default(0);
//            $table->foreignId('status_id')->constrained('statuses')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('status_id')->nullable();
            $table->foreign('status_id')->references('id')->on('statuses')->cascadeOnDelete()->cascadeOnUpdate();
            $table->timestamps();
        });

        Schema::create('tournaments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->decimal('price')->nullable()->default(0);
            $table->string('poster')->nullable();
            $table->foreignId('event_id')->constrained('events')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('court_id')->constrained('courts')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('user_community', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('community_id')->constrained('communities')->cascadeOnUpdate()->cascadeOnDelete();
//            $table->foreignId('role_id')->constrained('roles')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('role_id')->nullable();
            $table->foreign('role_id')->references('id')->on('roles')->cascadeOnDelete()->cascadeOnUpdate();
//            $table->foreignId('status_id')->constrained('statuses')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('status_id')->nullable();
            $table->foreign('status_id')->references('id')->on('statuses')->cascadeOnDelete()->cascadeOnUpdate();
            $table->timestamps();
        });

        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('field_id')->constrained('fields')->cascadeOnUpdate()->cascadeOnDelete();
            $table->dateTime('start_time')->nullable(false);
            $table->dateTime('end_time')->nullable(false);
            $table->decimal('price_per_hour')->nullable(false);
            $table->boolean('is_available')->default(true);
            $table->timestamps();

            $table->unique(['field_id', 'start_time']);
        });

        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('game_id')->nullable()->constrained('games')->cascadeOnUpdate()->cascadeOnDelete();
//            $table->foreignId('status_id')->constrained('statuses');
            $table->string('status_id')->nullable();
            $table->foreign('status_id')->references('id')->on('statuses')->cascadeOnDelete()->cascadeOnUpdate();
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

        Schema::create('applicants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('career_opportunity_id')->constrained('career_opportunity')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('requirements_link')->nullable();
//            $table->foreignId('status_id')->constrained('statuses')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('status_id')->nullable();
            $table->foreign('status_id')->references('id')->on('statuses')->cascadeOnDelete()->cascadeOnUpdate();
            $table->timestamps();
        });

        Schema::create('stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('game_id')->constrained('games')->cascadeOnUpdate()->cascadeOnDelete();
            $table->integer('minutes')->nullable(false);
            $table->integer('points')->nullable(false);
            $table->integer('rebounds')->nullable(false);
            $table->integer('assists')->nullable(false);
            $table->integer('steals')->nullable(false);
            $table->integer('blocks')->nullable(false);
            $table->integer('turnovers')->nullable(false);
            $table->integer('3pm')->nullable(false);
            $table->integer('3pa')->nullable(false);
            $table->integer('2pm')->nullable(false);
            $table->integer('2pa')->nullable(false);
            $table->integer('ftm')->nullable(false);
            $table->integer('fta')->nullable(false);
            $table->text('notes')->nullable();
            $table->unique(["user_id", "game_id"]);
            $table->timestamps();
        });

        Schema::create('highlights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stat_id')->constrained('stats')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('content')->nullable(false);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('workout_plan', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable(false);
            $table->text('description')->nullable(false);
            $table->integer('duration')->nullable(false);
            $table->string('image')->nullable();
            $table->string('difficulty')->nullable();
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

        Schema::create('schools', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable(false);
            $table->text('description')->nullable();
            $table->text('additional_link')->nullable();
            $table->string('address')->nullable(false);
            $table->decimal('latitude')->nullable(false);
            $table->decimal('longitude')->nullable();
            $table->foreignId('location_id')->constrained('locations')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('achievements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('title')->nullable(false);
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->text('additional_link')->nullable();
            $table->string('type')->nullable(false); // acd/nonacd
            $table->date('issue_date')->nullable(false);
            $table->date('expiration_date')->nullable();
            $table->timestamps();
        });

        Schema::create('play_by_play', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->nullable(false)->constrained('games');
            $table->foreignId('team_id')->nullable()->constrained('teams');
            $table->foreignId('user_id')->nullable()->constrained('users');
//            $table->foreignId('status_id')->nullable()->constrained('statuses');
            $table->string('status_id')->nullable();
            $table->foreign('status_id')->references('id')->on('statuses')->cascadeOnDelete()->cascadeOnUpdate();
            $table->integer('quarter')->nullable(false);
            $table->integer('time_seconds')->nullable(false);
            $table->integer('home_score')->nullable(false);
            $table->integer('away_score')->nullable(false);
            $table->string('title')->nullable(false);
            $table->text('description')->nullable(false);
            $table->timestamps();
        });

        Schema::create('chats', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable(); // null for DM
            $table->text('description')->nullable();
            $table->string('avatar')->nullable();
            $table->string('channel_name')->nullable()->unique();
            $table->enum('type', ['group', 'private', 'channel', 'forum'])->default('private');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('settings')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['type', 'is_active']);
            $table->index('last_activity_at');
        });

//          combined events
        Schema::create('chat_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('chat_id')->constrained('chats')->cascadeOnDelete();
            $table->string('role_id')->nullable();
            $table->foreign('role_id')->references('id')->on('roles')->cascadeOnDelete()->cascadeOnUpdate();
            // Remove the last_read_message_id for now - we'll add it later
            $table->string('nickname')->nullable();
            $table->boolean('is_muted')->default(false);
            $table->boolean('is_blocked')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamp('left_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'chat_id']);
            $table->index(['chat_id', 'is_active']);
            $table->index(['user_id', 'is_active']);
        });

        Schema::create('chat_user_message', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_user_id')->constrained('chat_user')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('chat_id')->constrained('chats')->cascadeOnDelete();
            $table->foreignId('parent_message_id')->nullable()->constrained('chat_user_message')->nullOnDelete();
            $table->text('message')->nullable();
            $table->enum('type', ['text', 'image', 'video', 'file', 'audio', 'system'])->default('text');
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->string('file_size')->nullable();
            $table->string('mime_type')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('edited_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['chat_id', 'created_at']);
            $table->index(['chat_user_id', 'created_at']);
            $table->index(['parent_message_id']);
            $table->index(['user_id', 'created_at']);
        });
        Schema::table('chat_user', function (Blueprint $table) {
            $table->foreignId('last_read_message_id')->nullable()->constrained('chat_user_message')->nullOnDelete();
        });

        Schema::create('chat_message_status', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->constrained('chat_user_message')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['sent', 'delivered', 'read'])->default('sent');
            $table->timestamp('status_at')->useCurrent();
            $table->timestamps();

            $table->unique(['message_id', 'user_id']);
            $table->index(['user_id', 'status']);
        });
        Schema::table('chat_user_message', function (Blueprint $table) {
            $table->fullText(['message']); // for message search
        });

        Schema::create('tournament_game', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')->constrained('tournaments')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('game_id')->constrained('games')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('round')->nullable(false);
            $table->timestamps();
        });

        Schema::create('user_team', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnUpdate()->cascadeOnDelete();
//            $table->foreignId('role_id')->constrained('roles')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('role_id')->nullable();
            $table->foreign('role_id')->references('id')->on('roles')->cascadeOnDelete()->cascadeOnUpdate();
//            $table->foreignId('status_id')->constrained('statuses')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('status_id')->nullable();
            $table->foreign('status_id')->references('id')->on('statuses')->cascadeOnDelete()->cascadeOnUpdate();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'team_id']);
            $table->unique(['team_id', 'role_id']);
        });

        Schema::create('user_workout_plan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('workout_plan_id')->constrained('workout_plan')->cascadeOnUpdate()->cascadeOnDelete();
            $table->decimal('progress')->default(0.0);
            $table->timestamps();
        });

        Schema::create('user_education', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('degree')->nullable(false);
            $table->string('grade')->nullable(false);
            $table->text('activities')->nullable();
            $table->date('start_date')->nullable(false);
            $table->date('end_date')->nullable(false);
            $table->timestamps();
        });

//        many to many
        Schema::create('court_review', function (Blueprint $table) {
            $table->foreignId('court_id')->constrained('courts')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('review_id')->constrained('reviews')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamps();
            $table->primary(['court_id', 'review_id']);
        });

        Schema::create('field_review', function (Blueprint $table) {
            $table->foreignId('field_id')->constrained('fields')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('review_id')->constrained('reviews')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamps();
            $table->primary(['field_id', 'review_id']);
        });

        Schema::create('community_review', function (Blueprint $table) {
            $table->foreignId('community_id')->constrained('communities')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('review_id')->constrained('reviews')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamps();
            $table->primary(['community_id', 'review_id']);
        });

        Schema::create('event_review', function (Blueprint $table) {
            $table->foreignId('event_id')->constrained('events')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('review_id')->constrained('reviews')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamps();
            $table->primary(['event_id', 'review_id']);
        });

        Schema::create('tournament_review', function (Blueprint $table) {
            $table->foreignId('tournament_id')->constrained('tournaments')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('review_id')->constrained('reviews')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamps();
            $table->primary(['tournament_id', 'review_id']);
        });

        Schema::create('highlight_review', function (Blueprint $table) {
            $table->foreignId('highlight_id')->constrained('highlights')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('review_id')->constrained('reviews')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamps();
            $table->primary(['highlight_id', 'review_id']);
        });

        Schema::create('community_tag', function (Blueprint $table) {
            $table->foreignId('community_id')->constrained('communities')->cascadeOnUpdate()->cascadeOnDelete();
//            $table->foreignId('tag_id')->constrained('tags')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('tag_id')->nullable();
            $table->foreign('tag_id')->references('id')->on('tags')->cascadeOnDelete()->cascadeOnUpdate();
            $table->timestamps();
            $table->primary(['community_id', 'tag_id']);
        });

        Schema::create('event_tag', function (Blueprint $table) {
            $table->foreignId('event_id')->constrained('events');
//            $table->foreignId('tag_id')->constrained('tags')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('tag_id')->nullable();
            $table->foreign('tag_id')->references('id')->on('tags')->cascadeOnDelete()->cascadeOnUpdate();
            $table->timestamps();
            $table->primary(['event_id', 'tag_id']);
        });

        SChema::create('tournament_tag', function (Blueprint $table) {
            $table->foreignId('tournament_id')->constrained('tournaments');
//            $table->foreignId('tag_id')->constrained('tags')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('tag_id')->nullable();
            $table->foreign('tag_id')->references('id')->on('tags')->cascadeOnDelete()->cascadeOnUpdate();
            $table->timestamps();
            $table->primary(['tournament_id', 'tag_id']);
        });

        Schema::create('workout_plan_tag', function (Blueprint $table) {
            $table->foreignId('workout_plan_id')->constrained('workout_plan');
//            $table->foreignId('tag_id')->constrained('tags')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('tag_id')->nullable();
            $table->foreign('tag_id')->references('id')->on('tags')->cascadeOnDelete()->cascadeOnUpdate();
            $table->timestamps();
            $table->primary(['workout_plan_id', 'tag_id']);
        });

//        Schema::create('training_session_tag', function (Blueprint $table) {
//            $table->foreignId('training_session_id')->constrained('training_sessions');
////            $table->foreignId('tag_id')->constrained('tags')->cascadeOnUpdate()->cascadeOnDelete();
//            $table->string('tag_id')->nullable();
//            $table->foreign('tag_id')->references('id')->on('tags')->cascadeOnDelete()->cascadeOnUpdate();
//            $table->timestamps();
//            $table->primary(['training_session_id', 'tag_id']);
//        });

        Schema::create('play_by_play_tag', function (Blueprint $table) {
            $table->foreignId('play_by_play_id')->constrained('play_by_play');
//            $table->foreignId('tag_id')->constrained('tags')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('tag_id')->nullable();
            $table->foreign('tag_id')->references('id')->on('tags')->cascadeOnDelete()->cascadeOnUpdate();
            $table->timestamps();
            $table->primary(['play_by_play_id', 'tag_id']);
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
        Schema::dropIfExists('applicants');
        Schema::dropIfExists('games');
        Schema::dropIfExists('teams');
        Schema::dropIfExists('user_team');
        Schema::dropIfExists('stats');
        Schema::dropIfExists('highlights');
        Schema::dropIfExists('workout_plan');
        Schema::dropIfExists('training_session');
        Schema::dropIfExists('statuses');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('court_review');
        Schema::dropIfExists('community_review');
        Schema::dropIfExists('field_review');
        Schema::dropIfExists('event_review');
        Schema::dropIfExists('tournament_review');
        Schema::dropIfExists('community_tag');
        Schema::dropIfExists('event_tag');
        Schema::dropIfExists('tournament_tag');
        Schema::dropIfExists('workout_plan_tag');
        Schema::dropIfExists('training_session_tag');
        Schema::dropIfExists('user_education');
        Schema::dropIfExists('achievements');
        Schema::dropIfExists('schools');
        Schema::dropIfExists('chats');
        Schema::dropIfExists('chat_user');
        Schema::dropIfExists('chat_user_message');
        Schema::dropIfExists('chat_message_status');
        Schema::dropIfExists('chat_user_message');
    }
};
