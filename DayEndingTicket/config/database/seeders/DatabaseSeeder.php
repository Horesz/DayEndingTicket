<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Fiok;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Szerepkörök létrehozása
        $rendszergazda = Role::create([
            'name' => 'rendszergazda',
            'display_name' => 'Rendszergazda'
        ]);

        $admin = Role::create([
            'name' => 'admin',
            'display_name' => 'Manager/Admin'
        ]);

        $dolgozo = Role::create([
            'name' => 'dolgozo',
            'display_name' => 'Dolgozó'
        ]);

        // Fiókok létrehozása
        $fiok1 = Fiok::create([
            'nev' => 'Központi Fiók',
            'cim' => 'Budapest, Fő utca 1.',
            'kod' => 'F001',
            'aktiv' => true
        ]);

        $fiok2 = Fiok::create([
            'nev' => 'Belváros Fiók',
            'cim' => 'Budapest, Váci utca 10.',
            'kod' => 'F002',
            'aktiv' => true
        ]);

        // Rendszergazda felhasználó
        User::create([
            'name' => 'Rendszergazda',
            'email' => 'admin@rendszer.hu',
            'password' => Hash::make('password'),
            'role_id' => $rendszergazda->id,
            'fiok_id' => null,
        ]);

        // Manager felhasználó
        User::create([
            'name' => 'Kovács János',
            'email' => 'manager@rendszer.hu',
            'password' => Hash::make('password'),
            'role_id' => $admin->id,
            'fiok_id' => $fiok1->id,
        ]);

        // Dolgozó felhasználók
        User::create([
            'name' => 'Nagy Anna',
            'email' => 'anna@rendszer.hu',
            'password' => Hash::make('password'),
            'role_id' => $dolgozo->id,
            'fiok_id' => $fiok1->id,
        ]);

        User::create([
            'name' => 'Szabó Péter',
            'email' => 'peter@rendszer.hu',
            'password' => Hash::make('password'),
            'role_id' => $dolgozo->id,
            'fiok_id' => $fiok2->id,
        ]);
    }
}