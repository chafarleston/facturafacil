<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ActualizarSeeder extends Seeder
{
    public function run()
    {
        $this->call(\Database\Seeders\PermissionsSeeder::class);
        $this->call(\Database\Seeders\PrinterSeeder::class);
    }
}
