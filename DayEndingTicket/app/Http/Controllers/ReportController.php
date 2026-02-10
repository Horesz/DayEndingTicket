<?php

namespace App\Http\Controllers;

use App\Models\Napzaras;
use App\Models\Fiok;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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

    public function exportCsv(Request $request)
    {
        if (!auth()->user()->hasPermission('view_reports')) {
            abort(403);
        }

        $user = auth()->user();
        $datum_tol = $request->filled('datum_tol') ? $request->datum_tol : now()->startOfMonth();
        $datum_ig = $request->filled('datum_ig') ? $request->datum_ig : now()->endOfMonth();
        $fiok_id = $request->filled('fiok_id') ? $request->fiok_id : null;

        $query = Napzaras::with(['user', 'fiok', 'jovahagyo'])
            ->whereBetween('datum', [$datum_tol, $datum_ig])
            ->where('statusz', 'approved');

        if ($user->isAdmin() && $user->fiok_id) {
            $query->where('fiok_id', $user->fiok_id);
        } elseif ($fiok_id && $user->isRendszergazda()) {
            $query->where('fiok_id', $fiok_id);
        }

        $napzarasok = $query->orderBy('datum')->get();

        // CSV generálás
        $filename = 'napzarasok_' . now()->format('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function() use ($napzarasok) {
            $file = fopen('php://output', 'w');
            
            // BOM UTF-8-hoz
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Fejléc
            fputcsv($file, [
                'Dátum',
                'Fiók',
                'Fiók kód',
                'Dolgozó',
                'Kártya bevétel (Ft)',
                'Készpénz bevétel (Ft)',
                'Online bevétel (Ft)',
                'Egyéb bevétel (Ft)',
                'Össz bevétel (Ft)',
                'Napi bér (Ft)',
                'Költségek (Ft)',
                'Össz kiadás (Ft)',
                'Eredmény (Ft)',
                'Státusz',
                'Jóváhagyta',
                'Jóváhagyva',
                'Megjegyzés'
            ], ';');

            // Adatok
            foreach ($napzarasok as $napzaras) {
                fputcsv($file, [
                    $napzaras->datum->format('Y-m-d'),
                    $napzaras->fiok->nev,
                    $napzaras->fiok->kod,
                    $napzaras->user->name,
                    $napzaras->kartya_bevetel,
                    $napzaras->keszpenz_bevetel,
                    $napzaras->online_bevetel,
                    $napzaras->egyeb_bevetel,
                    $napzaras->ossz_bevetel,
                    $napzaras->napi_ber,
                    $napzaras->koltsegek,
                    $napzaras->ossz_kiadas,
                    $napzaras->eredmeny,
                    match($napzaras->statusz) {
                        'pending' => 'Függőben',
                        'approved' => 'Jóváhagyva',
                        'rejected' => 'Elutasítva',
                    },
                    $napzaras->jovahagyo?->name ?? '-',
                    $napzaras->jovahagyva_at?->format('Y-m-d H:i') ?? '-',
                    $napzaras->megjegyzes ?? ''
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportJson(Request $request)
    {
        if (!auth()->user()->hasPermission('view_reports')) {
            abort(403);
        }

        return response()->json([]);
    }
}