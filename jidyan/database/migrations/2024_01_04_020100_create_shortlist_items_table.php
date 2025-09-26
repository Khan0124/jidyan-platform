<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('shortlist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shortlist_id')->constrained('shortlists')->cascadeOnDelete();
            $table->foreignId('player_id')->constrained('profiles_players')->cascadeOnDelete();
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shortlist_items');
    }
};
