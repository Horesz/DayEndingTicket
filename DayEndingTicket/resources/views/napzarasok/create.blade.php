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

    <form method="POST" action="{{ route('napzarasok.store') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <!-- Alapadatok -->
        <div class="bg-white rounded-xl shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Alapadatok</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Telephely (Mozi) -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Telephely *</label>
                    <select name="fiok_id" id="fiok_id" required 
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            onchange="filterMunkakorok()">
                        <option value="">-- Válassz telephelyet --</option>
                        @foreach($fiokok as $fiok)
                            <option value="{{ $fiok->id }}">{{ $fiok->nev }}</option>
                        @endforeach
                    </select>
                    @error('fiok_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Munkakör (Kassza/Pozíció) -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Munkakör / Kassza *</label>
                    <select name="munkakor_id" id="munkakor_id" required 
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">-- Előbb válassz telephelyet --</option>
                        @foreach($munkakorok as $munkakor)
                            <option value="{{ $munkakor->id }}" 
                                    data-fiok="{{ $munkakor->fiok_id }}" 
                                    class="munkakor-option" 
                                    style="display:none;">
                                {{ $munkakor->nev }}
                            </option>
                        @endforeach
                    </select>
                    @error('munkakor_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Dátum -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Dátum *</label>
                    <input type="date" name="datum" value="{{ old('datum', now()->format('Y-m-d')) }}" 
                           max="{{ now()->format('Y-m-d') }}" required
                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('datum')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Bevételek -->
        <div class="bg-white rounded-xl shadow p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold">Bevételek</h2>
                <span class="text-sm text-green-600 font-medium">Összesen: <span id="bevetel-osszes">0 Ft</span></span>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
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
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        💰 Zacskóba helyezett készpénz 
                        <span class="text-xs text-gray-500">(informatív, nem számít bele az eredménybe)</span>
                    </label>
                    <input type="number" step="0.01" min="0" name="zacskos_keszpenz" value="0"
                           class="block w-full rounded-md border-gray-300 shadow-sm bg-yellow-50">
                </div>
            </div>
        </div>

        <!-- Napi bérek -->
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

        <!-- Dokumentumok -->
        <div class="bg-white rounded-xl shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Dokumentumok (opcionális)</h2>
            
            <div class="space-y-6">
                <!-- NAV-os blokk -->
                <div class="border-b pb-4">
                    <h3 class="text-lg font-medium mb-3">📄 NAV-os blokk</h3>
                    
                    <!-- Link -->
                    <div class="mb-3">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Google Photos link</label>
                        <input type="url" name="nav_foto_link" placeholder="https://photos.app.goo.gl/..."
                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <p class="mt-1 text-xs text-gray-500">Telefon → Google Fotók → Megosztás → Link másolása</p>
                    </div>
                    
                    <!-- VAGY Kép feltöltés -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">VAGY tölts fel képet</label>
                        <input type="file" name="nav_kep" accept="image/jpeg,image/png,image/jpg"
                               class="block w-full text-sm text-gray-500
                                      file:mr-4 file:py-2 file:px-4
                                      file:rounded-md file:border-0
                                      file:text-sm file:font-semibold
                                      file:bg-indigo-50 file:text-indigo-700
                                      hover:file:bg-indigo-100">
                        <p class="mt-1 text-xs text-gray-500">JPG, PNG (max 5MB)</p>
                    </div>
                </div>

                <!-- Kártyás terminál -->
                <div>
                    <h3 class="text-lg font-medium mb-3">💳 Kártyás terminál</h3>
                    
                    <!-- Link -->
                    <div class="mb-3">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Google Photos link</label>
                        <input type="url" name="terminal_foto_link" placeholder="https://photos.app.goo.gl/..."
                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    
                    <!-- VAGY Kép feltöltés -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">VAGY tölts fel képet</label>
                        <input type="file" name="terminal_kep" accept="image/jpeg,image/png,image/jpg"
                               class="block w-full text-sm text-gray-500
                                      file:mr-4 file:py-2 file:px-4
                                      file:rounded-md file:border-0
                                      file:text-sm file:font-semibold
                                      file:bg-green-50 file:text-green-700
                                      hover:file:bg-green-100">
                        <p class="mt-1 text-xs text-gray-500">JPG, PNG (max 5MB)</p>
                    </div>
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
// Munkakörök szűrése fiók szerint
function filterMunkakorok() {
    const fiokId = document.getElementById('fiok_id').value;
    const munkakorSelect = document.getElementById('munkakor_id');
    const options = munkakorSelect.querySelectorAll('.munkakor-option');
    
    munkakorSelect.value = '';
    
    options.forEach(option => {
        if (option.dataset.fiok === fiokId) {
            option.style.display = 'block';
        } else {
            option.style.display = 'none';
        }
    });
    
    const visibleOptions = Array.from(options).filter(opt => opt.style.display === 'block');
    if (visibleOptions.length === 1) {
        munkakorSelect.value = visibleOptions[0].value;
    }
}

// Bevétel összegzés
function updateOsszesBevetel() {
    let sum = 0;
    document.querySelectorAll('input[name$="_bevetel"]').forEach(el => {
        sum += parseFloat(el.value) || 0;
    });
    document.getElementById('bevetel-osszes').textContent = sum.toLocaleString('hu-HU') + ' Ft';
}

// Napi bérek összegzés
function updateOsszesNapiBer() {
    let sum = 0;
    document.querySelectorAll('input[name="dolgozo_ber[]"]').forEach(el => {
        sum += parseFloat(el.value) || 0;
    });
    document.getElementById('napiberek-osszes').textContent = sum.toLocaleString('hu-HU') + ' Ft';
}

// Dolgozó hozzáadása
document.getElementById('add-dolgozo-btn').addEventListener('click', function() {
    const template = document.getElementById('dolgozo-row-template');
    const clone = template.content.cloneNode(true);
    
    const noMsg = document.getElementById('no-dolgozo-msg');
    if (noMsg) noMsg.remove();
    
    document.getElementById('napi-berek-container').appendChild(clone);
    attachRemoveButtons();
});

function attachRemoveButtons() {
    document.querySelectorAll('.remove-dolgozo-btn').forEach(btn => {
        btn.removeEventListener('click', removeDolgozo);
        btn.addEventListener('click', removeDolgozo);
    });
}

function removeDolgozo(e) {
    e.target.closest('.dolgozo-row').remove();
    updateOsszesNapiBer();
    
    const rows = document.querySelectorAll('.dolgozo-row');
    if (rows.length === 0) {
        document.getElementById('napi-berek-container').innerHTML = 
            '<div class="text-center text-gray-500 py-6" id="no-dolgozo-msg">Még nincs hozzáadott dolgozó. Kattints a "Dolgozó hozzáadása" gombra!</div>';
    }
}

// Inicializálás
updateOsszesBevetel();
</script>
@endsection