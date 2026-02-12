@extends('layouts.app')

@section('title', 'Új napzárás')

@section('content')
<div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-8">

    <!-- Fejléc -->
    <div class="mb-8">
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-xl shadow-lg px-6 py-8 text-white">
            <h1 class="text-3xl font-bold">Új napzárás rögzítése</h1>
            <p class="mt-2 text-indigo-100">{{ now()->format('Y. m. d.') }} • {{ auth()->user()->name }}</p>
        </div>
    </div>

    <form method="POST" action="{{ route('napzarasok.store') }}" class="space-y-6">
        @csrf

        <!-- Alapadatok -->
        <div class="bg-white rounded-xl shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Alapadatok</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fiók *</label>
                    <select name="fiok_id" required class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @foreach($fiokok as $fiok)
                            <option value="{{ $fiok->id }}">{{ $fiok->nev }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Dátum *</label>
                    <input type="date" name="datum" value="{{ old('datum', now()->format('Y-m-d')) }}" 
                           max="{{ now()->format('Y-m-d') }}" required
                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
            </div>
        </div>

        <!-- Bevételek -->
        <div class="bg-white rounded-xl shadow p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold">Bevételek</h2>
                <span class="text-sm text-green-600 font-medium">Összesen: <span id="bevetel-osszes">0 Ft</span></span>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Készpénz *</label>
                    <input type="number" step="0.01" min="0" name="keszpenz_bevetel" value="0" required
                           class="block w-full rounded-md border-gray-300 shadow-sm" oninput="updateOsszesBevetel()">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kártya *</label>
                    <input type="number" step="0.01" min="0" name="kartya_bevetel" value="0" required
                           class="block w-full rounded-md border-gray-300 shadow-sm" oninput="updateOsszesBevetel()">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Online</label>
                    <input type="number" step="0.01" min="0" name="online_bevetel" value="0"
                           class="block w-full rounded-md border-gray-300 shadow-sm" oninput="updateOsszesBevetel()">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Egyéb</label>
                    <input type="number" step="0.01" min="0" name="egyeb_bevetel" value="0"
                           class="block w-full rounded-md border-gray-300 shadow-sm" oninput="updateOsszesBevetel()">
                </div>
            </div>
        </div>

        <!-- Napi bérek - Dolgozók szerinti bontás -->
        <div class="bg-white rounded-xl shadow p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold">Napi bérek (dolgozók szerinti bontás)</h2>
                <button type="button" id="add-dolgozo-btn" 
                        class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 text-sm">
                    + Dolgozó hozzáadása
                </button>
            </div>

            <div id="napi-berek-container">
                <div class="text-center text-gray-500 py-6" id="no-dolgozo-msg">
                    Még nincs hozzáadott dolgozó. Kattints a "Dolgozó hozzáadása" gombra!
                </div>
            </div>

            <!-- Template sor -->
            <template id="dolgozo-row-template">
                <div class="dolgozo-row grid grid-cols-1 md:grid-cols-3 gap-4 p-4 bg-gray-50 rounded-md mb-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Dolgozó neve *</label>
                        <input type="text" name="dolgozo_nev[]" required placeholder="pl. Kovács János"
                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Napi bér (Ft) *</label>
                        <input type="number" step="1" min="0" name="dolgozo_ber[]" required placeholder="15000"
                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                               oninput="updateOsszesNapiBer()">
                    </div>
                    <div class="flex items-end">
                        <button type="button" class="remove-dolgozo-btn text-red-600 hover:text-red-800 font-medium">
                            Eltávolítás
                        </button>
                    </div>
                </div>
            </template>

            <div class="mt-4 text-right">
                <span class="text-sm text-red-600 font-medium">Összes napi bér: <span id="napiberek-osszes">0 Ft</span></span>
            </div>
        </div>

        <!-- Egyéb költségek -->
        <div class="bg-white rounded-xl shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Egyéb költségek</h2>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Egyéb kiadások (Ft)</label>
                <input type="number" step="0.01" min="0" name="koltsegek" value="0"
                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
        </div>

        <!-- Fotók/Linkek - ÚJ RÉSZ -->
        <div class="bg-white rounded-xl shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Dokumentumok (opcionális)</h2>
            <p class="text-sm text-gray-600 mb-4">
                💡 <strong>Tipp:</strong> Készíts fotót telefonon → Szinkronizáld Google Fotókba → 
                Nyisd meg a képet → Kattints a "Megosztás" gombra → Másold ki a linket és illeszd be ide
            </p>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        📄 NAV-os blokk fotó link (Google Photos URL)
                    </label>
                    <input type="url" name="nav_foto_link" placeholder="https://photos.app.goo.gl/..."
                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <p class="mt-1 text-xs text-gray-500">Pl: https://photos.app.goo.gl/ABC123...</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        💳 Kártyás terminál fotó link (Google Photos URL)
                    </label>
                    <input type="url" name="terminal_foto_link" placeholder="https://photos.app.goo.gl/..."
                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <p class="mt-1 text-xs text-gray-500">Pl: https://photos.app.goo.gl/XYZ789...</p>
                </div>
            </div>
        </div>

        <!-- Megjegyzés -->
        <div class="bg-white rounded-xl shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Megjegyzés</h2>
            <textarea name="megjegyzes" rows="3" placeholder="Opcionális megjegyzés..."
                      class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
        </div>

        <!-- Gombok -->
        <div class="flex justify-end gap-4">
            <a href="{{ route('napzarasok.index') }}"
               class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                Mégse
            </a>
            <button type="submit"
                    class="px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                Napzárás rögzítése
            </button>
        </div>
    </form>
</div>

<script>
// Bevétel összegzés
function updateOsszesBevetel() {
    let sum = 0;
    document.querySelectorAll('input[name$="_bevetel"]').forEach(el => {
        sum += parseFloat(el.value) || 0;
    });
    document.getElementById('bevetel-osszes').textContent = sum.toLocaleString('hu-HU') + ' Ft';
}

// Napi bérek összegzés
<script>
document.addEventListener('DOMContentLoaded', () => {
    const berTipus = document.getElementById('ber_tipus');
    const alapBerWrapper = document.getElementById('alapBerWrapper');
    const alapBerInput = document.getElementById('alap_ber');

    function toggleAlapBer() {
        if (berTipus.value === 'fix') {
            alapBerWrapper.style.display = 'block';
            alapBerInput.required = false; // opcionális marad
        } else {
            alapBerWrapper.style.display = 'none';
            alapBerInput.value = '';
            alapBerInput.required = false;
        }
    }

    berTipus.addEventListener('change', toggleAlapBer);
    toggleAlapBer(); // kezdeti állapot
});
    </script>
@endsection