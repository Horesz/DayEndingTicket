<?php

namespace App\Http\Controllers;

use App\Models\Napzaras;
use App\Models\Fiok;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\NapzarasExport;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->hasPermission('view_reports')) {
            abort(403);
        }

        $user = auth()->user();
        $datum_tol = $request->filled('datum_tol') 
            ? Carbon::parse($request->datum_tol) 
            : Carbon::now()->startOfMonth();
            
        $datum_ig = $request->filled('datum_ig') 
            ? Carbon::parse($request->datum_ig) 
            : Carbon::now()->endOfMonth();

        $fiok_id = $request->filled('fiok_id') ? $request->fiok_id : null;

        $query = Napzaras::whereBetween('datum', [$datum_tol, $datum_ig])
            ->where('statusz', 'approved');

        // Admin csak saját fiókját látja
        if ($user->isAdmin() && $user->fiok_id) {
            $query->where('fiok_id', $user->fiok_id);
        } elseif ($fiok_id && $user->isRendszergazda()) {
            $query->where('fiok_id', $fiok_id);
        }

        // Összesítések
        $osszesites = [
            'kartya_bevetel' => $query->sum('kartya_bevetel'),
            'keszpenz_bevetel' => $query->sum('keszpenz_bevetel'),
            'online_bevetel' => $query->sum('online_bevetel'),
            'egyeb_bevetel' => $query->sum('egyeb_bevetel'),
            'napi_ber' => $query->sum('napi_ber'),
            'koltsegek' => $query->sum('koltsegek'),
        ];

        $osszesites['ossz_bevetel'] = $osszesites['kartya_bevetel'] + 
                                      $osszesites['keszpenz_bevetel'] + 
                                      $osszesites['online_bevetel'] + 
                                      $osszesites['egyeb_bevetel'];
        
        $osszesites['ossz_kiadas'] = $osszesites['napi_ber'] + $osszesites['koltsegek'];
        $osszesites['eredmeny'] = $osszesites['ossz_bevetel'] - $osszesites['ossz_kiadas'];

        // Napi bontás
        $napi_adatok = Napzaras::selectRaw('
                datum,
                SUM(kartya_bevetel + keszpenz_bevetel + online_bevetel + egyeb_bevetel) as bevetel,
                SUM(napi_ber + koltsegek) as kiadas
            ')
            ->whereBetween('datum', [$datum_tol, $datum_ig])
            ->where('statusz', 'approved')
            ->when($user->isAdmin() && $user->fiok_id, function($q) use ($user) {
                return $q->where('fiok_id', $user->fiok_id);
            })
            ->when($fiok_id && $user->isRendszergazda(), function($q) use ($fiok_id) {
                return $q->where('fiok_id', $fiok_id);
            })
            ->groupBy('datum')
            ->orderBy('datum')
            ->get();

        // Fiók szerinti bontás (csak rendszergazdának)
        $fiok_osszesites = [];
        if ($user->isRendszergazda()) {
            $fiok_osszesites = Napzaras::selectRaw('
                    fiok_id,
                    SUM(kartya_bevetel + keszpenz_bevetel + online_bevetel + egyeb_bevetel) as bevetel,
                    SUM(napi_ber + koltsegek) as kiadas,
                    COUNT(*) as napzarasok_szama
                ')
                ->with('fiok')
                ->whereBetween('datum', [$datum_tol, $datum_ig])
                ->where('statusz', 'approved')
                ->groupBy('fiok_id')
                ->get();
        }

        $fiokok = $user->isRendszergazda() ? Fiok::all() : collect();

        return view('reports.index', compact(
            'osszesites', 
            'napi_adatok', 
            'fiok_osszesites', 
            'datum_tol', 
            'datum_ig',
            'fiokok'
        ));
    }

    public function export(Request $request)
    {
        if (!auth()->user()->hasPermission('view_reports')) {
            abort(403);
        }

        $datum_tol = $request->filled('datum_tol') ? $request->datum_tol : Carbon::now()->startOfMonth();
        $datum_ig = $request->filled('datum_ig') ? $request->datum_ig : Carbon::now()->endOfMonth();
        $fiok_id = $request->filled('fiok_id') ? $request->fiok_id : null;

        return Excel::download(
            new NapzarasExport($datum_tol, $datum_ig, $fiok_id), 
            'napzarasok_' . now()->format('Y-m-d') . '.xlsx'
        );
    }
}