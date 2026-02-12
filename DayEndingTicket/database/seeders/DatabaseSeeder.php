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
        // Szerepkörök létrehozása (duplikáció elkerülése)
        $rendszergazda = Role::firstOrCreate(
            ['name' => 'rendszergazda'],
            ['display_name' => 'Rendszergazda']
        );

        $admin = Role::firstOrCreate(
            ['name' => 'admin'],
            ['display_name' => 'Manager/Admin']
        );

        $dolgozo = Role::firstOrCreate(
            ['name' => 'dolgozo'],
            ['display_name' => 'Dolgozó']
        );

        // Fiókok létrehozása
        $fiok1 = Fiok::firstOrCreate(
            ['kod' => 'F001'],
            [
                'nev' => 'Központi Fiók',
                'cim' => 'Budapest, Fő utca 1.',
                'aktiv' => true
            ]
        );

        $fiok2 = Fiok::firstOrCreate(
            ['kod' => 'F002'],
            [
                'nev' => 'Belváros Fiók',
                'cim' => 'Budapest, Váci utca 10.',
                'aktiv' => true
            ]
        );

        // Rendszergazda felhasználó
        User::firstOrCreate(
            ['email' => 'admin@rendszer.hu'],
            [
                'name' => 'Rendszergazda',
                'password' => Hash::make('password'),
                'role_id' => $rendszergazda->id,
                'fiok_id' => null,
                'ber_tipus' => 'fix',
                'alap_ber' => null,
            ]
        );

        // Manager felhasználó
        User::firstOrCreate(
            ['email' => 'manager@rendszer.hu'],
            [
                'name' => 'Kovács János',
                'password' => Hash::make('password'),
                'role_id' => $admin->id,
                'fiok_id' => $fiok1->id,
                'ber_tipus' => 'fix',
                'alap_ber' => 500000,
            ]
        );

        // Dolgozó felhasználók
        User::firstOrCreate(
            ['email' => 'anna@rendszer.hu'],
            [
                'name' => 'Nagy Anna',
                'password' => Hash::make('password'),
                'role_id' => $dolgozo->id,
                'fiok_id' => $fiok1->id,
                'ber_tipus' => 'napi',
                'alap_ber' => null,
            ]
        );

        User::firstOrCreate(
            ['email' => 'peter@rendszer.hu'],
            [
                'name' => 'Szabó Péter',
                'password' => Hash::make('password'),
                'role_id' => $dolgozo->id,
                'fiok_id' => $fiok2->id,
                'ber_tipus' => 'fix',
                'alap_ber' => 300000,
            ]
        );

        // Több dolgozó a táblázathoz (mint a képen)
        $dolgozokAdatok = [
            ['Anita', 'anita@rendszer.hu', $fiok1->id, 'napi'],
            ['Noémi', 'noemi@rendszer.hu', $fiok1->id, 'napi'],
            ['Szabolcs', 'szabolcs@rendszer.hu', $fiok1->id, 'napi'],
            ['Tomi', 'tomi@rendszer.hu', $fiok1->id, 'napi'],
            ['Atis', 'atis@rendszer.hu', $fiok2->id, 'napi'],
            ['Kata', 'kata@rendszer.hu', $fiok2->id, 'napi'],
            ['Alexandra', 'alexandra@rendszer.hu', $fiok2->id, 'fix'],
            ['Barni', 'barni@rendszer.hu', $fiok1->id, 'napi'],
            ['Martin', 'martin@rendszer.hu', $fiok1->id, 'napi'],
            ['Betti', 'betti@rendszer.hu', $fiok2->id, 'napi'],
        ];

        foreach ($dolgozokAdatok as [$nev, $email, $fiokId, $berTipus]) {
            User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $nev,
                    'password' => Hash::make('password'),
                    'role_id' => $dolgozo->id,
                    'fiok_id' => $fiokId,
                    'ber_tipus' => $berTipus,
                    'alap_ber' => $berTipus === 'fix' ? 280000 : null,
                ]
            );
        }
    }
}