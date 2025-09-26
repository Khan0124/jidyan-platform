<?php

use Illuminate\Support\Facades\Storage;

it('reports overall system health', function () {
    Storage::fake('media_inbox');
    Storage::fake('media_hls');
    Storage::fake('media_archive');

    $response = $this->getJson('/health');

    $response->assertOk()
        ->assertJsonPath('status', 'ok')
        ->assertJsonPath('services.database.status', 'ok')
        ->assertJsonPath('services.media_disks.status', 'ok');
});
