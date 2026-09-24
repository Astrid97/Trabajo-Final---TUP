<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\RiplatSeeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RiplatSeeder::class,
        ]);
    }
}