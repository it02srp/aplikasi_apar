<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['username' => 'adminsrp'],
            [
                'password' => Hash::make('srppastibisa'),
                'role'     => 'superadmin',
            ]
        );

         User::updateOrCreate(
            ['username' => 'rickyhendrata'],
            [
                'password' => Hash::make('pasword'),
                'role'     => 'admin',
            ]
        );
         User::updateOrCreate(
            ['username' => 'puji'],
            [
                'password' => Hash::make('pasword'),
                'role'     => 'admin',
            ]
        );
    }
}
