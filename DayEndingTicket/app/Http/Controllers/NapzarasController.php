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
        'fiok_id'               => 'required|exists:fiokok,id',
        'datum'                 => 'required|date|before_or_equal:today',
        'kartya_bevetel'        => 'required|numeric|min:0',
        'keszpenz_bevetel'      => 'required|numeric|min:0',
        'online_bevetel'        => 'nullable|numeric|min:0',
        'egyeb_bevetel'         => 'nullable|numeric|min:0',
        'koltsegek'             => 'nullable|numeric|min:0',
        'megjegyzes'            => 'nullable|string|max:1000',

        // Dinamikus mezők
        'napi_ber_dolgozo'      => 'nullable|array',
        'napi_ber_dolgozo.*'    => 'exists:users,id',
        'napi_ber_osszeg'       => 'nullable|array',
        'napi_ber_osszeg.*'     => 'numeric|min:0',
    ]);

    // Jogosultság + létezés ellenőrzés (marad)

    if ($user->isDolgozo() && $validated['fiok_id'] != $user->fiok_id) {
        abort(403);
    }

    $exists = Napzaras::where('fiok_id', $validated['fiok_id'])
        ->where('datum', $validated['datum'])
        ->exists();

    if ($exists) {
        return back()->withErrors(['datum' => 'Erre a napra már létezik napzárás.']);
    }

    $napzaras = Napzaras::create([
        'user_id'            => $user->id,
        'fiok_id'            => $validated['fiok_id'],
        'datum'              => $validated['datum'],
        'kartya_bevetel'     => $validated['kartya_bevetel'],
        'keszpenz_bevetel'   => $validated['keszpenz_bevetel'],
        'online_bevetel'     => $validated['online_bevetel'] ?? 0,
        'egyeb_bevetel'      => $validated['egyeb_bevetel'] ?? 0,
        'koltsegek'          => $validated['koltsegek'] ?? 0,
        'megjegyzes'         => $validated['megjegyzes'] ?? null,
        // napi_ber mezőt most nem töltjük, vagy később számoljuk
    ]);

    // Napi bérek mentése
    $osszesNapiBer = 0;
    if (!empty($validated['napi_ber_dolgozo'])) {
        $attachData = [];
        foreach ($validated['napi_ber_dolgozo'] as $index => $dolgozoId) {
            $osszeg = $validated['napi_ber_osszeg'][$index] ?? 0;
            if ($osszeg > 0) {
                $attachData[$dolgozoId] = [
                    'osszeg'      => $osszeg,
                    'megjegyzes'  => null, // ha van külön megjegyzés mező, ide jöhet
                ];
                $osszesNapiBer += $osszeg;
            }
        }

        if (!empty($attachData)) {
            $napzaras->dolgozok()->attach($attachData);
        }
    }

    // Ha megtartod a napi_ber mezőt összesítőként:
    // $napzaras->update(['napi_ber' => $osszesNapiBer]);

    return redirect()->route('napzarasok.index')
        ->with('success', 'Napzárás sikeresen rögzítve!');
}

    public function show(Napzaras $napzaras)
    {
        $this->authorize('view', $napzaras);
        $napzaras->load(['user', 'fiok', 'jovahagyo']);
        
        return view('napzarasok.show', compact('napzaras'));
    }

    public function edit(Napzaras $napzaras)
    {
        $this->authorize('update', $napzaras);

        $user = auth()->user();
        $fiokok = $user->isDolgozo() 
            ? Fiok::where('id', $user->fiok_id)->get()
            : Fiok::where('aktiv', true)->get();

        return view('napzarasok.edit', compact('napzaras', 'fiokok'));
    }

    public function update(Request $request, Napzaras $napzaras)
    {
        $this->authorize('update', $napzaras);

        $validated = $request->validate([
            'kartya_bevetel' => 'required|numeric|min:0',
            'keszpenz_bevetel' => 'required|numeric|min:0',
            'online_bevetel' => 'nullable|numeric|min:0',
            'egyeb_bevetel' => 'nullable|numeric|min:0',
            'napi_ber' => 'required|numeric|min:0',
            'koltsegek' => 'nullable|numeric|min:0',
            'megjegyzes' => 'nullable|string|max:1000',
        ]);

        $validated['online_bevetel'] = $validated['online_bevetel'] ?? 0;
        $validated['egyeb_bevetel'] = $validated['egyeb_bevetel'] ?? 0;
        $validated['koltsegek'] = $validated['koltsegek'] ?? 0;

        $napzaras->update($validated);

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
    $this->authorize('delete', $napzaras);

    if ($napzaras->statusz !== 'pending') {
        return back()->with('error', 'Csak függőben lévő napzárás törölhető.');
    }

    $napzaras->delete();

    return redirect()->route('napzarasok.index')
        ->with('success', 'Napzárás törölve!');
}
}