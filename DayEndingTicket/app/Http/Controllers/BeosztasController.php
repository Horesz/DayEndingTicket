<?php

namespace App\Http\Controllers;

use App\Models\Beosztas;
use App\Models\BeosztasKomment;
use App\Models\User;
use App\Models\Fiok;
use Illuminate\Http\Request;
use Carbon\Carbon;

class BeosztasController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        
        $honap = $request->filled('honap') 
            ? Carbon::parse($request->honap) 
            : Carbon::now()->startOfMonth();

        $dolgozokQuery = User::with('fiok')
            ->whereHas('role', function($q) {
            $q->whereIn('name', ['dolgozo', 'admin']);
        });


        if ($user->isAdmin() && $user->fiok_id) {
            $dolgozokQuery->where('fiok_id', $user->fiok_id);
        }

        $dolgozok = $dolgozokQuery->orderBy('name')->get();

        $query = Beosztas::with(['user', 'fiok', 'kommentek.user'])
            ->whereYear('datum', $honap->year)
            ->whereMonth('datum', $honap->month);

        if ($user->isDolgozo()) {
            $query->where('user_id', $user->id);
        }

        if ($user->isAdmin() && $user->fiok_id) {
            $query->where('fiok_id', $user->fiok_id);
        }

        $beosztasok = $query->get();

        $naptar = [];
        $napokSzama = $honap->daysInMonth;
        
        for ($nap = 1; $nap <= $napokSzama; $nap++) {
            $datum = $honap->copy()->day($nap);
            $naptar[$nap] = [
                'datum' => $datum,
                'hetNapja' => $datum->locale('hu')->isoFormat('dd'),
                'beosztasok' => []
            ];

            foreach ($dolgozok as $dolgozo) {
                $beosztas = $beosztasok->first(function($b) use ($dolgozo, $datum) {
                    return $b->user_id === $dolgozo->id && 
                           $b->datum->format('Y-m-d') === $datum->format('Y-m-d');
                });

                $naptar[$nap]['beosztasok'][$dolgozo->id] = $beosztas;
            }
        }

        $fiokok = $user->isRendszergazda() ? Fiok::all() : collect();

        return view('beosztas.index', compact('naptar', 'honap', 'dolgozok', 'fiokok'));
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
    public function create(Request $request)
{
    if (!auth()->user()->hasPermission('manage_beosztas')) {
        abort(403);
    }

    $datum = $request->datum ?? now()->toDateString();

    $dolgozok = User::whereHas('role', function($q) {
        $q->whereIn('name', ['dolgozo', 'admin']);
    })->orderBy('name')->get();

    $fiokok = Fiok::all();

    return view('beosztas.create', compact('datum', 'dolgozok', 'fiokok'));
}

    public function edit(Beosztas $beosztas)
    {
        if (!auth()->user()->hasPermission('manage_beosztas')) {
            abort(403);
        }

        $fiokok = Fiok::all();

        return view('beosztas.edit', compact('beosztas', 'fiokok'));
    }

    public function destroy(Beosztas $beosztas)
    {
        if (!auth()->user()->hasPermission('manage_beosztas')) {
            abort(403);
        }

        $beosztas->delete();

        return back()->with('success', 'Beosztás törölve!');
    }

    // Komment hozzáadása
    public function addKomment(Request $request, Beosztas $beosztas)
    {
        $validated = $request->validate([
            'komment' => 'required|string|max:1000',
            'tipus' => 'required|in:megjegyzes,csere_keres',
        ]);

        BeosztasKomment::create([
            'beosztas_id' => $beosztas->id,
            'user_id' => auth()->id(),
            'komment' => $validated['komment'],
            'tipus' => $validated['tipus'],
        ]);

        return back()->with('success', 'Komment hozzáadva!');
    }

    // Google Calendar export
    public function exportGoogleCalendar(Request $request)
    {
        $user = auth()->user();
        $honap = $request->filled('honap') 
            ? Carbon::parse($request->honap) 
            : Carbon::now()->startOfMonth();

        $query = Beosztas::with(['user', 'fiok'])
            ->whereYear('datum', $honap->year)
            ->whereMonth('datum', $honap->month);

        if ($user->isDolgozo()) {
            $query->where('user_id', $user->id);
        }

        $beosztasok = $query->get();

        // iCal formátum
        $ical = "BEGIN:VCALENDAR\r\n";
        $ical .= "VERSION:2.0\r\n";
        $ical .= "PRODID:-//Napzárás Rendszer//HU\r\n";
        $ical .= "CALSCALE:GREGORIAN\r\n";
        $ical .= "METHOD:PUBLISH\r\n";
        $ical .= "X-WR-CALNAME:Munkabeosztás\r\n";
        $ical .= "X-WR-TIMEZONE:Europe/Budapest\r\n";

        foreach ($beosztasok as $beosztas) {
            $kezdes = $beosztas->kezdes ?? '08:00';
            $befejezes = $beosztas->befejezes ?? '16:00';
            
            $dtstart = $beosztas->datum->format('Ymd') . 'T' . str_replace(':', '', $kezdes) . '00';
            $dtend = $beosztas->datum->format('Ymd') . 'T' . str_replace(':', '', $befejezes) . '00';

            $ical .= "BEGIN:VEVENT\r\n";
            $ical .= "UID:" . uniqid() . "@napzaras.hu\r\n";
            $ical .= "DTSTAMP:" . now()->format('Ymd\THis\Z') . "\r\n";
            $ical .= "DTSTART;TZID=Europe/Budapest:" . $dtstart . "\r\n";
            $ical .= "DTEND;TZID=Europe/Budapest:" . $dtend . "\r\n";
            $ical .= "SUMMARY:Munka - " . $beosztas->fiok->nev . "\r\n";
            $ical .= "DESCRIPTION:" . ($beosztas->megjegyzes ?? '') . "\r\n";
            $ical .= "LOCATION:" . $beosztas->fiok->cim . "\r\n";
            $ical .= "END:VEVENT\r\n";
        }

        $ical .= "END:VCALENDAR\r\n";

        $filename = 'beosztas_' . $honap->format('Y_m') . '.ics';

        return response($ical)
            ->header('Content-Type', 'text/calendar; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    // Print nézet
    public function print(Request $request)
    {
        $user = auth()->user();
        $honap = $request->filled('honap') 
            ? Carbon::parse($request->honap) 
            : Carbon::now()->startOfMonth();

        // ... ugyanaz a logika mint az index-ben ...
        
        return view('beosztas.print', compact('naptar', 'honap', 'dolgozok'));
    }
}