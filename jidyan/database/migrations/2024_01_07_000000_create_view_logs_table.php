<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('view_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('viewer_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('player_id')->constrained('profiles_players')->cascadeOnDelete();
            $table->timestamp('viewed_at');
            $table->index(['player_id', 'viewed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('view_logs');
    }
};
