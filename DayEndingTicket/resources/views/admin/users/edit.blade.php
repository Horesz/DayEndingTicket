@extends('layouts.app')

@section('title', 'Felhasználó szerkesztése')

@section('content')
<div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-8">

    <!-- Hero -->
    <div class="mb-10">
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-2xl shadow-2xl overflow-hidden">
            <div class="px-8 py-12 text-white">
                <h1 class="text-3xl md:text-4xl font-bold">Felhasználó szerkesztése</h1>
                <p class="mt-2 text-indigo-100 text-lg">{{ $user->name }} adatainak módosítása</p>
            </div>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white shadow-xl rounded-2xl border border-gray-100 overflow-hidden">
        <div class="p-8">
            <form method="POST" action="{{ route('admin.users.update', $user) }}">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Bal oszlop -->
                    <div class="space-y-6">
                        <!-- Név -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Név *</label>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                                   class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                            <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                                   class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Jelszó -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Új jelszó</label>
                            <input type="password" name="password"
                                   class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <p class="mt-1 text-xs text-gray-500">Hagyd üresen, ha nem akarod megváltoztatni</p>
                            @error('password')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Jelszó megerősítés -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Jelszó megerősítése</label>
                            <input type="password" name="password_confirmation"
                                   class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </div>

                    <!-- Jobb oszlop -->
                    <div class="space-y-6">
                        <!-- Szerepkör -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Szerepkör *</label>
                            <select name="role_id" required class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}" {{ old('role_id', $user->role_id) == $role->id ? 'selected' : '' }}>
                                        {{ $role->display_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Bér típusa -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Bér típusa *</label>
                            <select name="ber_tipus" id="ber_tipus" required class="block w-full rounded-lg border-gray-300 shadow-sm">
                                <option value="napi" {{ old('ber_tipus', $user->ber_tipus) === 'napi' ? 'selected' : '' }}>Napi béres</option>
                                <option value="fix" {{ old('ber_tipus', $user->ber_tipus) === 'fix' ? 'selected' : '' }}>Fix</option>
                            </select>
                        </div>

                        <!-- Alapbér -->
                        <div id="alapBerWrapper">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Alapbér (fix dolgozó)</label>
                            <input type="number" step="0.01" name="alap_ber" id="alap_ber"
                                   value="{{ old('alap_ber', $user->alap_ber) }}"
                                   class="block w-full rounded-lg border-gray-300 shadow-sm">
                        </div>

                        <!-- Fiók -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Fiók</label>
                            <select name="fiok_id" class="block w-full rounded-lg border-gray-300 shadow-sm">
                                <option value="">— Nincs / Rendszergazda —</option>
                                @foreach($fiokok as $fiok)
                                    <option value="{{ $fiok->id }}" {{ old('fiok_id', $user->fiok_id) == $fiok->id ? 'selected' : '' }}>
                                        {{ $fiok->nev }} ({{ $fiok->kod }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Gombok -->
                <div class="mt-10 flex justify-end gap-4">
                    <a href="{{ route('admin.users.index') }}"
                       class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        Mégse
                    </a>
                    <button type="submit"
                            class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                        Mentés
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const berTipus = document.getElementById('ber_tipus');
    const alapBerWrapper = document.getElementById('alapBerWrapper');
    const alapBerInput = document.getElementById('alap_ber');

    function toggleAlapBer() {
        if (berTipus.value === 'fix') {
            alapBerWrapper.style.display = 'block';
        } else {
            alapBerWrapper.style.display = 'none';
            alapBerInput.value = '';
        }
    }

    berTipus.addEventListener('change', toggleAlapBer);
    toggleAlapBer();
});
</script>
@endsection