<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('profiles_players', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('dob')->nullable();
            $table->string('nationality')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->unsignedSmallInteger('height_cm')->nullable();
            $table->unsignedSmallInteger('weight_kg')->nullable();
            $table->string('position')->nullable();
            $table->string('preferred_foot')->nullable();
            $table->string('current_club')->nullable();
            $table->json('previous_clubs')->nullable();
            $table->text('bio')->nullable();
            $table->json('injuries')->nullable();
            $table->json('achievements')->nullable();
            $table->string('visibility')->default('private');
            $table->date('available_from')->nullable();
            $table->json('preferred_roles')->nullable();
            $table->timestamp('verified_identity_at')->nullable();
            $table->timestamp('verified_academy_at')->nullable();
            $table->unsignedInteger('view_count')->default(0);
            $table->unsignedTinyInteger('rating')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profiles_players');
    }
};
