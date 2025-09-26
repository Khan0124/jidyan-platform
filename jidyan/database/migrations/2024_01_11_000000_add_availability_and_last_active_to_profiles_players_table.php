<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('profiles_players', function (Blueprint $table) {
            $table->string('availability')->default('unknown')->after('visibility');
            $table->timestamp('last_active_at')->nullable()->after('availability');

            $table->index('availability');
            $table->index('last_active_at');
        });
    }

    public function down(): void
    {
        Schema::table('profiles_players', function (Blueprint $table) {
            $table->dropIndex('profiles_players_availability_index');
            $table->dropIndex('profiles_players_last_active_at_index');
            $table->dropColumn(['availability', 'last_active_at']);
        });
    }
};
