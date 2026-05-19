<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run()
    {
        User::where('email', 'superadmin@example.com')->delete();

        User::updateOrCreate(
            ['email' => 'Caja@gmail.com'],
            [
                'name' => 'Cajero',
                'password' => Hash::make('222938'),
                'role' => 'cajero',
            ]
        );
    }
}
