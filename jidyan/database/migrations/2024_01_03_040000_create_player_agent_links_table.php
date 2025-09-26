<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('player_agent_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained('profiles_players')->cascadeOnDelete();
            $table->foreignId('agent_id')->constrained('agents')->cascadeOnDelete();
            $table->string('status')->default('pending');
            $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();
            $table->unique(['player_id', 'agent_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('player_agent_links');
    }
};
