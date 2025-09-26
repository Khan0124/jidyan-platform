<?php

use App\Models\PlayerProfile;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('profiles_players', function (Blueprint $table) {
            $table->text('searchable_text')->nullable();
        });

        $driver = DB::connection()->getDriverName();

        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE profiles_players ADD COLUMN search_vector tsvector");
            DB::statement(<<<'SQL'
                CREATE OR REPLACE FUNCTION update_profiles_players_search()
                RETURNS trigger AS $$
                DECLARE
                    user_name text;
                BEGIN
                    SELECT name INTO user_name FROM users WHERE id = NEW.user_id;
                    NEW.searchable_text := trim(
                        concat_ws(' ',
                            coalesce(user_name, ''),
                            coalesce(NEW.position, ''),
                            coalesce(NEW.preferred_foot, ''),
                            coalesce(NEW.current_club, ''),
                            coalesce(NEW.city, ''),
                            coalesce(NEW.country, ''),
                            coalesce(NEW.bio, ''),
                            array_to_string(NEW.previous_clubs, ' '),
                            array_to_string(NEW.achievements, ' '),
                            array_to_string(NEW.injuries, ' ')
                        )
                    );
                    NEW.search_vector := to_tsvector('simple', coalesce(NEW.searchable_text, ''));
                    RETURN NEW;
                END;
                $$ LANGUAGE plpgsql;
            SQL);

            DB::statement(<<<'SQL'
                CREATE TRIGGER trg_profiles_players_search
                BEFORE INSERT OR UPDATE ON profiles_players
                FOR EACH ROW EXECUTE FUNCTION update_profiles_players_search();
            SQL);
        }

        $userNames = User::query()->pluck('name', 'id');

        PlayerProfile::query()
            ->select(['id', 'user_id', 'position', 'preferred_foot', 'current_club', 'city', 'country', 'bio', 'previous_clubs', 'achievements', 'injuries'])
            ->orderBy('id')
            ->chunkById(500, function (Collection $profiles) use ($userNames) {
                foreach ($profiles as $profile) {
                    $parts = collect([
                        $userNames->get($profile->user_id),
                        $profile->position,
                        $profile->preferred_foot,
                        $profile->current_club,
                        $profile->city,
                        $profile->country,
                        $profile->bio,
                        is_array($profile->previous_clubs) ? $profile->previous_clubs : null,
                        is_array($profile->achievements) ? $profile->achievements : null,
                        is_array($profile->injuries) ? $profile->injuries : null,
                    ])->flatMap(function ($value) {
                        if (is_array($value)) {
                            return array_filter($value, fn ($item) => filled($item));
                        }

                        return [$value];
                    })->map(fn ($value) => trim((string) $value))->filter();

                    DB::table('profiles_players')
                        ->where('id', $profile->id)
                        ->update(['searchable_text' => $parts->implode(' ')]);
                }
            });
    }

    public function down(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('DROP TRIGGER IF EXISTS trg_profiles_players_search ON profiles_players');
            DB::statement('DROP FUNCTION IF EXISTS update_profiles_players_search');
            DB::statement('ALTER TABLE profiles_players DROP COLUMN IF EXISTS search_vector');
        }

        Schema::table('profiles_players', function (Blueprint $table) {
            $table->dropColumn('searchable_text');
        });
    }
};
