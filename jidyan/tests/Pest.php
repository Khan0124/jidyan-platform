<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses()->beforeEach(function () {
    $this->seed(Database\Seeders\RolesAndPermissionsSeeder::class);
});

uses(RefreshDatabase::class)->in('Feature');
