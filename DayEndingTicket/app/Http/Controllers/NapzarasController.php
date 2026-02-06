<?php

namespace App\Http\Controllers;

use App\Models\Napzaras;
use App\Models\Fiok;
use Illuminate\Http\Request;
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

        return view('napzarasok.create', compact('fiokok'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'fiok_id' => 'required|exists:fiokok,id',
            'datum' => 'required|date|before_or_equal:today',
            'kartya_bevetel' => 'required|numeric|min:0',
            'keszpenz_bevetel' => 'required|numeric|min:0',
            'online_bevetel' => 'nullable|numeric|min:0',
            'egyeb_bevetel' => 'nullable|numeric|min:0',
            'napi_ber' => 'required|numeric|min:0',
            'koltsegek' => 'nullable|numeric|min:0',
            'megjegyzes' => 'nullable|string|max:1000',
        ]);

        // Dolgozó csak saját fiókjához tölthet fel
        if ($user->isDolgozo() && $validated['fiok_id'] != $user->fiok_id) {
            abort(403, 'Csak a saját fiókodhoz tölthetsz fel napzárást.');
        }

        // Ellenőrzés: már létezik-e napzárás erre a napra
        $exists = Napzaras::where('fiok_id', $validated['fiok_id'])
            ->where('datum', $validated['datum'])
            ->exists();

        if ($exists) {
            return back()->withErrors(['datum' => 'Erre a napra már létezik napzárás ebben a fiókban.']);
        }

        $validated['user_id'] = $user->id;
        $validated['online_bevetel'] = $validated['online_bevetel'] ?? 0;
        $validated['egyeb_bevetel'] = $validated['egyeb_bevetel'] ?? 0;
        $validated['koltsegek'] = $validated['koltsegek'] ?? 0;

        Napzaras::create($validated);

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
}