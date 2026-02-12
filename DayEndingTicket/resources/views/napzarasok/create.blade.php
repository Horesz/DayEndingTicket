@extends('layouts.app')

@section('title', 'Új napzárás rögzítése')

@section('content')
<div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-8">

    <!-- Hero fejléc – dashboard stílusban -->
    <div class="mb-10">
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-2xl shadow-xl overflow-hidden">
            <div class="px-8 py-10 text-white">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                    <div>
                        <h1 class="text-3xl md:text-4xl font-bold tracking-tight">
                            Új napzárás rögzítése
                        </h1>
                        <p class="mt-3 text-indigo-100 text-lg">
                            Napi bevétel és kiadás feltöltése • {{ auth()->user()->name }}
                        </p>
                    </div>
                    <div class="bg-white/20 backdrop-blur-sm px-6 py-4 rounded-xl border border-white/30 text-center">
                        <p class="text-sm uppercase tracking-wide text-indigo-200">Mai dátum</p>
                        <p class="text-2xl font-semibold mt-1">{{ now()->format('Y. m. d.') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('napzarasok.store') }}" class="space-y-8">
        @csrf

        <!-- Fiók + Dátum kártya -->
        <div class="bg-white shadow-sm rounded-2xl border border-gray-200 overflow-hidden">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-900">Alapadatok</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fiók *</label>
                        <select name="fiok_id" required
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('fiok_id') border-red-300 @enderror">
                            @foreach($fiokok as $fiok)
                                <option value="{{ $fiok->id }}" {{ old('fiok_id') == $fiok->id ? 'selected' : '' }}>
                                    {{ $fiok->nev ?? $fiok->kod ?? 'Fiók #' . $fiok->id }}
                                </option>
                            @endforeach
                        </select>
                        @error('fiok_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Dátum *</label>
                        <input type="date" name="datum" required
                               value="{{ old('datum', now()->format('Y-m-d')) }}"
                               max="{{ now()->format('Y-m-d') }}"
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('datum') border-red-300 @enderror">
                        @error('datum')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Bevételek kártya -->
        <div class="bg-white shadow-sm rounded-2xl border border-gray-200 overflow-hidden">
            <div class="p-6 border-b border-gray-200 flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-900">Bevételek</h2>
                <span class="text-sm text-green-600 font-medium">Összesen: <span id="bevetel-osszes">0 Ft</span></span>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1 flex items-center">
                            <span class="text-green-600 mr-2">Kártyás</span> *
                        </label>
                        <input type="number" step="0.01" min="0" name="kartya_bevetel" required
                               value="{{ old('kartya_bevetel', 0) }}"
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 sm:text-sm @error('kartya_bevetel') border-red-300 @enderror"
                               oninput="updateOsszesBevetel()">
                        @error('kartya_bevetel')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1 flex items-center">
                            <span class="text-green-600 mr-2">Készpénz</span> *
                        </label>
                        <input type="number" step="0.01" min="0" name="keszpenz_bevetel" required
                               value="{{ old('keszpenz_bevetel', 0) }}"
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 sm:text-sm @error('keszpenz_bevetel') border-red-300 @enderror"
                               oninput="updateOsszesBevetel()">
                        @error('keszpenz_bevetel')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Online</label>
                        <input type="number" step="0.01" min="0" name="online_bevetel"
                               value="{{ old('online_bevetel', 0) }}"
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 sm:text-sm @error('online_bevetel') border-red-300 @enderror"
                               oninput="updateOsszesBevetel()">
                        @error('online_bevetel')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Egyéb</label>
                        <input type="number" step="0.01" min="0" name="egyeb_bevetel"
                               value="{{ old('egyeb_bevetel', 0) }}"
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 sm:text-sm @error('egyeb_bevetel') border-red-300 @enderror"
                               oninput="updateOsszesBevetel()">
                        @error('egyeb_bevetel')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
<!-- Kiadások szekció helyett -->
<div class="bg-white shadow-sm rounded-2xl border border-gray-200 overflow-hidden">
    <div class="p-6 border-b border-gray-200 flex items-center justify-between">
        <h2 class="text-xl font-semibold text-gray-900">Napi bérek (csak napi bérű dolgozók)</h2>
        <button type="button" id="add-dolgozo-btn" 
                class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Dolgozó hozzáadása
        </button>
    </div>

    <div class="p-6" id="napi-berek-container">
        <!-- Itt jelennek meg dinamikusan a sorok -->
        <div class="text-center text-gray-500 py-8" id="no-dolgozo-yet">
            Még nincs hozzáadott napi bérű dolgozó
        </div>
    </div>

    <!-- Rejtett template sor – JS fogja klónozni -->
    <template id="napi-ber-template">
        <div class="napi-ber-row grid grid-cols-1 md:grid-cols-3 gap-4 mb-4 p-4 bg-gray-50 rounded-lg relative">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Dolgozó *</label>
                <select name="napi_ber_dolgozo[]" class="dolgozo-select block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                    <option value="">-- Válassz dolgozót --</option>
                    @foreach($napi_beru_dolgozok as $dolgozo)
                        <option value="{{ $dolgozo->id }}">{{ $dolgozo->name }} ({{ $dolgozo->fiok->nev ?? 'N/A' }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Összeg (Ft) *</label>
                <input type="number" name="napi_ber_osszeg[]" step="1" min="0" required
                       class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            </div>

            <div class="flex items-end">
                <button type="button" class="remove-dolgozo text-red-600 hover:text-red-800 font-medium">
                    Eltávolítás
                </button>
            </div>
        </div>
    </template>
</div>
        <!-- Kiadások kártya -->
        <div class="bg-white shadow-sm rounded-2xl border border-gray-200 overflow-hidden">
            <div class="p-6 border-b border-gray-200 flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-900">Kiadások</h2>
                <span class="text-sm text-red-600 font-medium">Összesen: <span id="kiadas-osszes">0 Ft</span></span>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1 flex items-center">
                            <span class="text-red-600 mr-2">Napi bér</span> *
                        </label>
                        <input type="number" step="0.01" min="0" name="napi_ber" required
                               value="{{ old('napi_ber', 0) }}"
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm @error('napi_ber') border-red-300 @enderror"
                               oninput="updateOsszesKiadas()">
                        @error('napi_ber')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Egyéb költségek</label>
                        <input type="number" step="0.01" min="0" name="koltsegek"
                               value="{{ old('koltsegek', 0) }}"
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm @error('koltsegek') border-red-300 @enderror"
                               oninput="updateOsszesKiadas()">
                        @error('koltsegek')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Megjegyzés -->
        <div class="bg-white shadow-sm rounded-2xl border border-gray-200 overflow-hidden">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-900">Megjegyzés</h2>
            </div>
            <div class="p-6">
                <textarea name="megjegyzes" rows="4"
                          class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('megjegyzes') border-red-300 @enderror">{{ old('megjegyzes') }}</textarea>
                @error('megjegyzes')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Gombok -->
        <div class="flex flex-col sm:flex-row sm:justify-end gap-4 mt-10">
            <a href="{{ route('napzarasok.index') }}"
               class="inline-flex justify-center rounded-lg border border-gray-300 bg-white px-6 py-3 text-base font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                Mégse
            </a>
            <button type="submit"
                    class="inline-flex justify-center rounded-lg bg-indigo-600 px-8 py-3 text-base font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                Napzárás rögzítése
            </button>
        </div>
    </form>
</div>

<script>
function updateOsszesBevetel() {
    let sum = 0;
    document.querySelectorAll('input[name$="_bevetel"]').forEach(el => {
        sum += parseFloat(el.value) || 0;
    });
    document.getElementById('bevetel-osszes').textContent = sum.toLocaleString('hu-HU') + ' Ft';
}

function updateOsszesKiadas() {
    let sum = 0;
    document.querySelectorAll('input[name="napi_ber"], input[name="koltsegek"]').forEach(el => {
        sum += parseFloat(el.value) || 0;
    });
    document.getElementById('kiadas-osszes').textContent = sum.toLocaleString('hu-HU') + ' Ft';
}
document.getElementById('add-dolgozo-btn').addEventListener('click', function() {
    const template = document.getElementById('napi-ber-template');
    const clone = template.content.cloneNode(true);
    
    // "Nincs dolgozó" üzenet eltüntetése
    const noMsg = document.getElementById('no-dolgozo-yet');
    if (noMsg) noMsg.remove();
    
    // Clone hozzáadása
    document.getElementById('napi-berek-container').appendChild(clone);
    
    // Remove gomb eseménykezelő
    document.querySelectorAll('.remove-dolgozo').forEach(btn => {
        btn.addEventListener('click', function() {
            this.closest('.napi-ber-row').remove();
            
            // Ha nincs több sor, újra megjelenítjük az üzenetet
            const rows = document.querySelectorAll('.napi-ber-row');
            if (rows.length === 0) {
                document.getElementById('napi-berek-container').innerHTML = 
                    '<div class="text-center text-gray-500 py-8" id="no-dolgozo-yet">Még nincs hozzáadott napi bérű dolgozó</div>';
            }
        });
    });
});
// Inicializálás
updateOsszesBevetel();
updateOsszesKiadas();
</script>
@endsection