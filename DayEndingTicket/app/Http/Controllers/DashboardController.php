<?php

namespace App\Http\Controllers;

use App\Models\Napzaras;
use App\Models\Beosztas;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $ma = Carbon::today();

        // Mai beosztás
        $mai_beosztas = null;
        if ($user->isDolgozo()) {
            $mai_beosztas = Beosztas::with('fiok')
                ->where('user_id', $user->id)
                ->where('datum', $ma)
                ->first();
        }

        // Statisztikák és adatok inicializálása
        $stats = [];
        $utolso_napzarasok = collect();
        $pending_napzarasok = collect(); // FONTOS: mindig inicializáljuk!

        if ($user->isDolgozo()) {
            // Dolgozó statisztikái
            $stats['sajat_napzarasok'] = Napzaras::where('user_id', $user->id)->count();
            $stats['pending_napzarasok'] = Napzaras::where('user_id', $user->id)
                ->where('statusz', 'pending')->count();
            $stats['approved_napzarasok'] = Napzaras::where('user_id', $user->id)
                ->where('statusz', 'approved')->count();
                
            // Utolsó 5 napzárás
            $utolso_napzarasok = Napzaras::with('fiok')
                ->where('user_id', $user->id)
                ->orderBy('datum', 'desc')
                ->limit(5)
                ->get();
        } else {
            // Admin/Rendszergazda statisztikái
            $query = Napzaras::query();
            
            if ($user->isAdmin() && $user->fiok_id) {
                $query->where('fiok_id', $user->fiok_id);
            }

            $stats['osszes_napzaras'] = (clone $query)->count();
            $stats['pending_napzarasok'] = (clone $query)->where('statusz', 'pending')->count();
            $stats['approved_napzarasok'] = (clone $query)->where('statusz', 'approved')->count();
            $stats['rejected_napzarasok'] = (clone $query)->where('statusz', 'rejected')->count();

            // Havi bevétel
            $havi_bevetel = (clone $query)
                ->whereMonth('datum', $ma->month)
                ->whereYear('datum', $ma->year)
                ->where('statusz', 'approved')
                ->sum(DB::raw('kartya_bevetel + keszpenz_bevetel + online_bevetel + egyeb_bevetel'));
            $stats['havi_bevetel'] = $havi_bevetel;

            // Jóváhagyásra váró napzárások
            $pending_napzarasok = (clone $query)
                ->with(['user', 'fiok'])
                ->where('statusz', 'pending')
                ->orderBy('datum', 'desc')
                ->limit(10)
                ->get();

            // Utolsó 5 napzárás
            $utolso_napzarasok = (clone $query)
                ->with(['user', 'fiok'])
                ->orderBy('datum', 'desc')
                ->limit(5)
                ->get();
        }

        return view('dashboard', compact(
            'stats', 
            'mai_beosztas', 
            'utolso_napzarasok',
            'pending_napzarasok' // Most már mindig létezik
        ));
    }
}