<?php

namespace App\Http\Controllers;

use App\Models\Beosztas;
use App\Models\User;
use App\Models\Fiok;
use Illuminate\Http\Request;
use Carbon\Carbon;

class BeosztasController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        
        // Alapértelmezett hónap: aktuális
        $honap = $request->filled('honap') 
            ? Carbon::parse($request->honap) 
            : Carbon::now()->startOfMonth();

        $query = Beosztas::with(['user', 'fiok'])
            ->whereYear('datum', $honap->year)
            ->whereMonth('datum', $honap->month);

        // Dolgozó csak sajátját látja
        if ($user->isDolgozo()) {
            $query->where('user_id', $user->id);
        }

        // Admin csak saját fiókját látja
        if ($user->isAdmin() && $user->fiok_id) {
            $query->where('fiok_id', $user->fiok_id);
        }

        $beosztasok = $query->orderBy('datum')->get();

        // Naptár nézet előkészítése
        $naptar = [];
        $kezdoNap = $honap->copy()->startOfMonth();
        $vegNap = $honap->copy()->endOfMonth();

        for ($nap = $kezdoNap; $nap->lte($vegNap); $nap->addDay()) {
            $naptar[$nap->format('Y-m-d')] = $beosztasok->where('datum', $nap->format('Y-m-d'));
        }

        $fiokok = $user->isRendszergazda() ? Fiok::all() : collect();
        $dolgozok = $user->hasPermission('manage_beosztas') 
            ? User::where('role_id', function($query) {
                $query->select('id')->from('roles')->where('name', 'dolgozo');
            })->get()
            : collect();

        return view('beosztas.index', compact('naptar', 'honap', 'fiokok', 'dolgozok'));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->hasPermission('manage_beosztas')) {
            abort(403);
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'fiok_id' => 'required|exists:fiokok,id',
            'datum' => 'required|date',
            'kezdes' => 'nullable|date_format:H:i',
            'befejezes' => 'nullable|date_format:H:i|after:kezdes',
            'megjegyzes' => 'nullable|string|max:500',
        ]);

        Beosztas::create($validated);

        return back()->with('success', 'Beosztás sikeresen létrehozva!');
    }

    public function update(Request $request, Beosztas $beosztas)
    {
        if (!auth()->user()->hasPermission('manage_beosztas')) {
            abort(403);
        }

        $validated = $request->validate([
            'kezdes' => 'nullable|date_format:H:i',
            'befejezes' => 'nullable|date_format:H:i|after:kezdes',
            'megjegyzes' => 'nullable|string|max:500',
        ]);

        $beosztas->update($validated);

        return back()->with('success', 'Beosztás módosítva!');
    }

    public function destroy(Beosztas $beosztas)
    {
        if (!auth()->user()->hasPermission('manage_beosztas')) {
            abort(403);
        }

        $beosztas->delete();

        return back()->with('success', 'Beosztás törölve!');
    }
}