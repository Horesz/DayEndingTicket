@extends('layouts.app')

@section('title', 'Munkabeosztás')

@section('content')
<div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Hero fejléc -->
    <div class="mb-8">
        <div class="bg-gradient-to-r from-purple-600 to-indigo-600 rounded-xl shadow-lg px-6 py-8 text-white">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-3xl text-purple font-bold">Munkabeosztás</h1>
                    <p class="mt-2 text-purple-100">
                        {{ $honap->locale('hu')->isoFormat('YYYY. MMMM') }}
                    </p>
                </div>
                
                <div class="flex flex-wrap gap-3">
                    
                    <!-- Hónap váltó -->
                    <form method="GET" class="flex items-center gap-2">
                        <input type="month" name="honap" 
                               value="{{ $honap->format('Y-m') }}"
                               class="rounded-md border-gray-300 text-gray-900 shadow-sm">
                        <button type="submit" class="px-4 py-2 bg-white text-purple-700 rounded-md hover:bg-purple-50">
                            Frissít
                        </button>
                    </form>

                    <!-- Google Calendar Export -->
                    <a href="{{ route('beosztas.export.google', ['honap' => $honap->format('Y-m')]) }}" 
                       class="px-4 py-2 bg-white text-purple-700 rounded-md hover:bg-purple-50 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V10h14v10zM9 14H7v-2h2v2zm4 0h-2v-2h2v2zm4 0h-2v-2h2v2zm-8 4H7v-2h2v2zm4 0h-2v-2h2v2zm4 0h-2v-2h2v2z"/>
                        </svg>
                        Google Naptár
                    </a>

                    <!-- Nyomtatás -->
                    <button onclick="window.print()" 
                            class="px-4 py-2 bg-white text-purple-700 rounded-md hover:bg-purple-50 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                        </svg>
                        Nyomtatás
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Flash üzenetek -->
    @if(session('success'))
        <div class="mb-6 bg-green-50 border-l-4 border-green-400 p-4 rounded-lg">
            <p class="text-green-700">{{ session('success') }}</p>
        </div>
    @endif

    <!-- Naptár táblázat -->
    <div class="bg-white rounded-xl shadow-xl overflow-hidden overflow-x-auto">
        <table class="min-w-full border-collapse text-sm">
            <thead>
                <tr class="bg-gray-100">
                    <th class="border border-gray-300 px-4 py-3 text-left font-semibold text-gray-700 sticky left-0 bg-gray-100 z-10">
                        Hónap / Dolgozó
                    </th>
                    @foreach($dolgozok as $dolgozo)
                        <th class="border border-gray-300 px-3 py-3 text-center font-semibold text-gray-700 min-w-[100px]">
                            <div class="flex flex-col items-center">
                                <div class="w-8 h-8 rounded-full bg-indigo-500 text-white flex items-center justify-center text-xs font-bold mb-1">
                                    {{ strtoupper(substr($dolgozo->name, 0, 2)) }}
                                </div>
                                <span class="text-xs">{{ $dolgozo->name }}</span>
                            </div>
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($naptar as $nap => $napAdat)
                    <tr class="{{ $napAdat['datum']->isWeekend() ? 'bg-gray-50' : 'bg-white' }}">
                        <!-- Nap oszlop -->
                        <td class="border border-gray-300 px-4 py-2 font-medium sticky left-0 {{ $napAdat['datum']->isWeekend() ? 'bg-gray-50' : 'bg-white' }} z-10">
                            <div class="flex items-center gap-2">
                                <span class="text-gray-900">{{ $nap }}</span>
                                <span class="text-xs text-gray-500">({{ $napAdat['hetNapja'] }})</span>
                            </div>
                        </td>

                        <!-- Dolgozók oszlopai -->
                        @foreach($dolgozok as $dolgozo)
                            @php
                                $beosztas = $napAdat['beosztasok'][$dolgozo->id] ?? null;
                            @endphp
                            <td class="border border-gray-300 px-2 py-1 text-center relative group hover:bg-indigo-50 cursor-pointer"
                                onclick="openBeosztasModal({{ $dolgozo->id }}, '{{ $napAdat['datum']->format('Y-m-d') }}', {{ $beosztas ? $beosztas->id : 'null' }})">
                                @if($beosztas)
                                    <div class="bg-green-100 text-green-800 rounded px-2 py-1 text-xs font-semibold">
                                        1
                                        @if($beosztas->kezdes)
                                            <div class="text-xs mt-1">
                                                {{ substr($beosztas->kezdes, 0, 5) }} - {{ substr($beosztas->befejezes, 0, 5) }}
                                            </div>
                                        @endif
                                        @if($beosztas->kommentek->count() > 0)
                                            <span class="inline-block w-2 h-2 bg-red-500 rounded-full"></span>
                                        @endif
                                    </div>
                                @endif

                                <!-- Hover tooltip -->
                                @if($beosztas)
                                    <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 hidden group-hover:block z-20">
                                        <div class="bg-gray-900 text-white text-xs rounded py-2 px-3 whitespace-nowrap">
                                            {{ $beosztas->fiok->nev }}<br>
                                            {{ $beosztas->kezdes }} - {{ $beosztas->befejezes }}
                                            @if($beosztas->megjegyzes)
                                                <br><em>{{ $beosztas->megjegyzes }}</em>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</div>

<!-- Modal - Beosztás részletek / hozzáadás -->
<div id="beosztasModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6">
            <div class="flex justify-between items-start mb-4">
                <h3 class="text-xl font-bold text-gray-900" id="modalTitle">Beosztás</h3>
                <button onclick="closeBeosztasModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div id="modalContent">
                <!-- Dinamikusan töltődik JavaScript-tel -->
            </div>
        </div>
    </div>
</div>
<script>
function openBeosztasModal(dolgozoId, datum, beosztasId) {

    let modal = document.getElementById('beosztasModal');
    let content = document.getElementById('modalContent');

    // Ha már létezik beosztás → szerkesztés
    if (beosztasId) {

        content.innerHTML = `
            <form method="POST" action="/beosztas/${beosztasId}">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <label class="block text-sm font-medium">Kezdés</label>
                    <input type="time" name="kezdes" class="w-full border rounded px-3 py-2">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium">Befejezés</label>
                    <input type="time" name="befejezes" class="w-full border rounded px-3 py-2">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium">Megjegyzés</label>
                    <textarea name="megjegyzes" class="w-full border rounded px-3 py-2"></textarea>
                </div>

                <div class="flex justify-between">
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">
                        Mentés
                    </button>
                    <a href="/beosztas/${beosztasId}" 
                       onclick="event.preventDefault(); 
                       document.getElementById('delete-form-${beosztasId}').submit();"
                       class="bg-red-600 text-white px-4 py-2 rounded">
                        Törlés
                    </a>
                </div>
            </form>

            <form id="delete-form-${beosztasId}" 
                  action="/beosztas/${beosztasId}" 
                  method="POST" class="hidden">
                @csrf
                @method('DELETE')
            </form>
        `;

    } else {

        // ÚJ dolgozós nap létrehozás
        content.innerHTML = `
            <form method="POST" action="{{ route('beosztas.store') }}">
                @csrf

                <input type="hidden" name="user_id" value="${dolgozoId}">
                <input type="hidden" name="datum" value="${datum}">
                <input type="hidden" name="fiok_id" value="{{ auth()->user()->fiok_id ?? 1 }}">

                <div class="mb-4">
                    <label class="block text-sm font-medium">Dolgozós nap?</label>
                    <div class="mt-2">
                        <input type="time" name="kezdes" class="w-full border rounded px-3 py-2" value="08:00">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium">Befejezés</label>
                    <input type="time" name="befejezes" class="w-full border rounded px-3 py-2" value="16:00">
                </div>

                <button type="submit" class="w-full bg-green-600 text-white px-4 py-2 rounded">
                    Dolgozós nap mentése
                </button>
            </form>
        `;
    }

    modal.classList.remove('hidden');
}

function closeBeosztasModal() {
    document.getElementById('beosztasModal').classList.add('hidden');
}
</script>

@endsection