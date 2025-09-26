<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('player_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained('profiles_players')->cascadeOnDelete();
            $table->string('type');
            $table->string('provider');
            $table->string('path');
            $table->string('hls_path')->nullable();
            $table->string('poster_path')->nullable();
            $table->unsignedSmallInteger('duration_sec')->nullable();
            $table->string('quality_label')->nullable();
            $table->string('status')->default('processing');
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('player_media');
    }
};
