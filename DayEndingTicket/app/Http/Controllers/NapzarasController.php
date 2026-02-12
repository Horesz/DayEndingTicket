<?php

namespace App\Http\Controllers;

use App\Models\Napzaras;
use App\Models\Fiok;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class NapzarasController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Napzaras::with(['user', 'fiok', 'jovahagyo']);

        // Dolgozó csak sajátját látja
        if ($user->isDolgozo()) {
            $query->where('user_id', $user->id);
        }
        
        // Admin csak saját fiókját látja
        if ($user->isAdmin() && $user->fiok_id) {
            $query->where('fiok_id', $user->fiok_id);
        }

        // Szűrés
        if ($request->filled('datum_tol')) {
            $query->where('datum', '>=', $request->datum_tol);
        }
        
        if ($request->filled('datum_ig')) {
            $query->where('datum', '<=', $request->datum_ig);
        }

        if ($request->filled('statusz')) {
            $query->where('statusz', $request->statusz);
        }

        if ($request->filled('fiok_id') && $user->isRendszergazda()) {
            $query->where('fiok_id', $request->fiok_id);
        }

        $napzarasok = $query->orderBy('datum', 'desc')->paginate(15);
        $fiokok = $user->isRendszergazda() ? Fiok::all() : collect();

        return view('napzarasok.index', compact('napzarasok', 'fiokok'));
    }

   public function create()
{
    $user = auth()->user();
    
    // Dolgozó csak saját fiókjához tud napzárást készíteni
    $fiokok = $user->isDolgozo() 
        ? Fiok::where('id', $user->fiok_id)->get()
        : Fiok::where('aktiv', true)->get();

    // Napi bérű dolgozók lekérése
    $napi_beru_dolgozok = User::with('fiok')
        ->where('ber_tipus', 'napi')
        ->whereHas('role', function($query) {
            $query->where('name', 'dolgozo');
        })
        ->when($user->isAdmin() && $user->fiok_id, function($query) use ($user) {
            // Admin csak saját fiókjának dolgozóit látja
            return $query->where('fiok_id', $user->fiok_id);
        })
        ->orderBy('name')
        ->get();

    return view('napzarasok.create', compact('fiokok', 'napi_beru_dolgozok'));
}

    public function store(Request $request)
{
    $user = auth()->user();

    $validated = $request->validate([
        'fiok_id'           => 'required|exists:fiokok,id',
        'datum'             => 'required|date|before_or_equal:today',
        'kartya_bevetel'    => 'required|numeric|min:0',
        'keszpenz_bevetel'  => 'required|numeric|min:0',
        'online_bevetel'    => 'nullable|numeric|min:0',
        'egyeb_bevetel'     => 'nullable|numeric|min:0',
        'koltsegek'         => 'nullable|numeric|min:0',
        'megjegyzes'        => 'nullable|string|max:1000',
        'nav_foto_link'     => 'nullable|url',
        'terminal_foto_link'=> 'nullable|url',
        
        // Dolgozók
        'dolgozo_nev'       => 'nullable|array',
        'dolgozo_nev.*'     => 'string|max:255',
        'dolgozo_ber'       => 'nullable|array',
        'dolgozo_ber.*'     => 'numeric|min:0',
    ]);

    // Jogosultság ellenőrzés
    if ($user->isDolgozo() && $validated['fiok_id'] != $user->fiok_id) {
        abort(403);
    }

    // Létezés ellenőrzés
    $exists = Napzaras::where('fiok_id', $validated['fiok_id'])
        ->where('datum', $validated['datum'])
        ->exists();

    if ($exists) {
        return back()->withErrors(['datum' => 'Erre a napra már létezik napzárás.'])->withInput();
    }

    // Napi bérek összesítése
    $osszesNapiBer = 0;
    if (!empty($validated['dolgozo_ber'])) {
        $osszesNapiBer = array_sum($validated['dolgozo_ber']);
    }

    // Dolgozók adatainak JSON-be mentése
    $dolgozokJson = [];
    if (!empty($validated['dolgozo_nev'])) {
        foreach ($validated['dolgozo_nev'] as $index => $nev) {
            if (!empty($nev) && !empty($validated['dolgozo_ber'][$index])) {
                $dolgozokJson[] = [
                    'nev' => $nev,
                    'ber' => $validated['dolgozo_ber'][$index]
                ];
            }
        }
    }

    // Napzárás létrehozása
    Napzaras::create([
        'user_id'            => $user->id,
        'fiok_id'            => $validated['fiok_id'],
        'datum'              => $validated['datum'],
        'kartya_bevetel'     => $validated['kartya_bevetel'],
        'keszpenz_bevetel'   => $validated['keszpenz_bevetel'],
        'online_bevetel'     => $validated['online_bevetel'] ?? 0,
        'egyeb_bevetel'      => $validated['egyeb_bevetel'] ?? 0,
        'napi_ber'           => $osszesNapiBer,
        'koltsegek'          => $validated['koltsegek'] ?? 0,
        'megjegyzes'         => $validated['megjegyzes'],
        'nav_foto_link'      => $validated['nav_foto_link'],
        'terminal_foto_link' => $validated['terminal_foto_link'],
        'dolgozok_json'      => json_encode($dolgozokJson), // ÚJ mező kell hozzá!
    ]);

    return redirect()->route('napzarasok.index')
        ->with('success', 'Napzárás sikeresen rögzítve!');
}

    public function show(Napzaras $napzaras)
{
    // Policy helyett egyszerű jogosultság ellenőrzés
    $user = auth()->user();
    
    if ($user->isDolgozo() && $napzaras->user_id !== $user->id) {
        abort(403, 'Nincs jogosultságod megtekinteni ezt a napzárást.');
    }
    
    if ($user->isAdmin() && $napzaras->fiok_id !== $user->fiok_id) {
        abort(403, 'Nincs jogosultságod megtekinteni ezt a napzárást.');
    }
    
    $napzaras->load(['user', 'fiok', 'jovahagyo']);
    
    return view('napzarasok.show', compact('napzaras'));
}

    public function edit(Napzaras $napzaras)
{
    $user = auth()->user();
    
    // Csak pending napzárást lehet szerkeszteni
    if ($napzaras->statusz !== 'pending') {
        abort(403, 'Csak függőben lévő napzárást lehet szerkeszteni.');
    }
    
    // Csak a létrehozó szerkesztheti
    if ($napzaras->user_id !== $user->id) {
        abort(403, 'Csak a saját napzárásodat szerkesztheted.');
    }

    $fiokok = $user->isDolgozo() 
        ? Fiok::where('id', $user->fiok_id)->get()
        : Fiok::where('aktiv', true)->get();

    return view('napzarasok.edit', compact('napzaras', 'fiokok'));
}


    public function update(Request $request, Napzaras $napzaras)
{
    $user = auth()->user();
    
    // Jogosultság ellenőrzés
    if ($napzaras->statusz !== 'pending') {
        abort(403, 'Csak függőben lévő napzárást lehet módosítani.');
    }
    
    if ($napzaras->user_id !== $user->id) {
        abort(403, 'Csak a saját napzárásodat módosíthatod.');
    }

    $validated = $request->validate([
        'kartya_bevetel' => 'required|numeric|min:0',
        'keszpenz_bevetel' => 'required|numeric|min:0',
        'online_bevetel' => 'nullable|numeric|min:0',
        'egyeb_bevetel' => 'nullable|numeric|min:0',
        'koltsegek' => 'nullable|numeric|min:0',
        'megjegyzes' => 'nullable|string|max:1000',
        'nav_foto_link' => 'nullable|url',
        'terminal_foto_link' => 'nullable|url',
        'dolgozo_nev' => 'nullable|array',
        'dolgozo_nev.*' => 'string|max:255',
        'dolgozo_ber' => 'nullable|array',
        'dolgozo_ber.*' => 'numeric|min:0',
    ]);

    // Napi bérek összesítése
    $osszesNapiBer = 0;
    if (!empty($validated['dolgozo_ber'])) {
        $osszesNapiBer = array_sum($validated['dolgozo_ber']);
    }

    // Dolgozók JSON
    $dolgozokJson = [];
    if (!empty($validated['dolgozo_nev'])) {
        foreach ($validated['dolgozo_nev'] as $index => $nev) {
            if (!empty($nev) && !empty($validated['dolgozo_ber'][$index])) {
                $dolgozokJson[] = [
                    'nev' => $nev,
                    'ber' => $validated['dolgozo_ber'][$index]
                ];
            }
        }
    }

    $napzaras->update([
        'kartya_bevetel' => $validated['kartya_bevetel'],
        'keszpenz_bevetel' => $validated['keszpenz_bevetel'],
        'online_bevetel' => $validated['online_bevetel'] ?? 0,
        'egyeb_bevetel' => $validated['egyeb_bevetel'] ?? 0,
        'napi_ber' => $osszesNapiBer,
        'koltsegek' => $validated['koltsegek'] ?? 0,
        'megjegyzes' => $validated['megjegyzes'],
        'nav_foto_link' => $validated['nav_foto_link'],
        'terminal_foto_link' => $validated['terminal_foto_link'],
        'dolgozok_json' => json_encode($dolgozokJson),
    ]);

    return redirect()->route('napzarasok.show', $napzaras)
        ->with('success', 'Napzárás sikeresen módosítva!');
}

    public function approve(Request $request, Napzaras $napzaras)
    {
        if (!auth()->user()->hasPermission('approve_napzaras')) {
            abort(403);
        }

        $validated = $request->validate([
            'jovahagyas_megjegyzes' => 'nullable|string|max:1000',
        ]);

        $napzaras->update([
            'statusz' => 'approved',
            'jovahagyta_user_id' => auth()->id(),
            'jovahagyva_at' => now(),
            'jovahagyas_megjegyzes' => $validated['jovahagyas_megjegyzes'] ?? null,
        ]);

        // Email értesítés
        // $napzaras->user->notify(new NapzarasApproved($napzaras));

        return back()->with('success', 'Napzárás jóváhagyva!');
    }

    public function reject(Request $request, Napzaras $napzaras)
    {
        if (!auth()->user()->hasPermission('approve_napzaras')) {
            abort(403);
        }

        $validated = $request->validate([
            'jovahagyas_megjegyzes' => 'required|string|max:1000',
        ]);

        $napzaras->update([
            'statusz' => 'rejected',
            'jovahagyta_user_id' => auth()->id(),
            'jovahagyva_at' => now(),
            'jovahagyas_megjegyzes' => $validated['jovahagyas_megjegyzes'],
        ]);

        // Email értesítés
        // $napzaras->user->notify(new NapzarasRejected($napzaras));

        return back()->with('success', 'Napzárás elutasítva!');
    }
    public function destroy(Napzaras $napzaras)
{
    $user = auth()->user();
    
    // Jogosultság ellenőrzés
    if ($napzaras->statusz !== 'pending') {
        return back()->with('error', 'Csak függőben lévő napzárás törölhető.');
    }
    
    if ($napzaras->user_id !== $user->id && !$user->isRendszergazda()) {
        abort(403, 'Nincs jogosultságod törölni ezt a napzárást.');
    }

    $napzaras->delete();

    return redirect()->route('napzarasok.index')
        ->with('success', 'Napzárás törölve!');
}
}