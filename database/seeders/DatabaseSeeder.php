<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();
        $this->call(\Database\Seeders\AdminUserSeeder::class);
        $this->call(\Database\Seeders\SuperAdminSeeder::class);
        $this->call(\Database\Seeders\TestUsersSeeder::class);
        $this->call(\Database\Seeders\SeriesSeeder::class);
		$this->call(\Database\Seeders\SunatProductSeeder::class);
		
    }
}
