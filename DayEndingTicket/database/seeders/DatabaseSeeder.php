<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Fiok;
use App\Models\Munkakor;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ================================
        // SZEREPKÖRÖK
        // ================================
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

        // ================================
        // TELEPHELY (Cinema Bridge)
        // ================================
        $cinemaBridge = Fiok::firstOrCreate(
            ['kod' => 'CINEMA_BRIDGE'],
            [
                'nev' => 'Cinema Bridge',
                'cim' => 'Budapest, Margit körút 55.',
                'aktiv' => true
            ]
        );

        // ================================
        // MUNKAKÖRÖK (Kasszák)
        // ================================
        $bufePenztar = Munkakor::firstOrCreate(
            ['kod' => 'BUFE_PENZTAR', 'fiok_id' => $cinemaBridge->id],
            ['nev' => 'Bufé pénztár', 'aktiv' => true]
        );

        $jegyPenztar = Munkakor::firstOrCreate(
            ['kod' => 'JEGY_PENZTAR', 'fiok_id' => $cinemaBridge->id],
            ['nev' => 'Jegy pénztár', 'aktiv' => true]
        );

        // ================================
        // RENDSZERGAZDA (nincs fiókhoz kötve)
        // ================================
        User::firstOrCreate(
            ['email' => 'admin@cinemabridge.hu'],
            [
                'name' => 'Rendszergazda',
                'password' => Hash::make('password'),
                'role_id' => $rendszergazda->id,
                'fiok_id' => null, // Rendszergazda mindent lát
                'ber_tipus' => 'fix',
                'alap_ber' => null,
            ]
        );

        // ================================
        // MANAGER (Cinema Bridge)
        // ================================
        User::firstOrCreate(
            ['email' => 'manager@cinemabridge.hu'],
            [
                'name' => 'Kovács János (Manager)',
                'password' => Hash::make('password'),
                'role_id' => $admin->id,
                'fiok_id' => $cinemaBridge->id,
                'ber_tipus' => 'fix',
                'alap_ber' => 500000,
            ]
        );

        // ================================
        // DOLGOZÓK (Cinema Bridge)
        // ================================
        $dolgozok = [
            ['Anita', 'anita@cinemabridge.hu', 'napi'],
            ['Noémi', 'noemi@cinemabridge.hu', 'napi'],
            ['Szabolcs', 'szabolcs@cinemabridge.hu', 'napi'],
            ['Tomi', 'tomi@cinemabridge.hu', 'napi'],
            ['Barni', 'barni@cinemabridge.hu', 'napi'],
            ['Martin', 'martin@cinemabridge.hu', 'napi'],
            ['Atis', 'atis@cinemabridge.hu', 'napi'],
            ['Kata', 'kata@cinemabridge.hu', 'napi'],
            ['Alexandra', 'alexandra@cinemabridge.hu', 'fix'],
            ['Betti', 'betti@cinemabridge.hu', 'napi'],
            ['Réka', 'reka@cinemabridge.hu', 'napi'],
            ['Dávid', 'david@cinemabridge.hu', 'napi'],
        ];

        foreach ($dolgozok as [$nev, $email, $berTipus]) {
            User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $nev,
                    'password' => Hash::make('password'),
                    'role_id' => $dolgozo->id,
                    'fiok_id' => $cinemaBridge->id, // Minden dolgozó Cinema Bridge-hez tartozik
                    'ber_tipus' => $berTipus,
                    'alap_ber' => $berTipus === 'fix' ? 280000 : null,
                ]
            );
        }
    }
}