@extends('layouts.app')

@section('title', 'Új felhasználó felvétele')

@section('content')
<div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-8">

    <!-- Hero / fejléc -->
    <div class="mb-10">
        <div class="bg-gradient-to-r from-indigo-600 via-indigo-700 to-purple-700 rounded-2xl shadow-2xl overflow-hidden">
            <div class="px-8 py-12 md:py-14 text-white">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                    <div>
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <div>
                                <h1 class="text-3xl md:text-4xl font-bold tracking-tight">Új felhasználó felvétele</h1>
                                <p class="mt-2 text-indigo-100 text-lg">
                                    Csak rendszergazda vagy manager hozhat létre új fiókot
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white/15 backdrop-blur-md px-6 py-4 rounded-xl border border-white/20 text-center">
                        <p class="text-sm uppercase tracking-wide text-indigo-200">Mai dátum</p>
                        <p class="text-2xl font-semibold mt-1">{{ now()->format('Y. m. d.') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Form kártya -->
    <div class="bg-white shadow-xl rounded-2xl border border-gray-100 overflow-hidden">
        <div class="p-8">
            <form method="POST" action="{{ route('admin.users.store') }}">
                @csrf

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Bal oszlop -->
                    <div class="space-y-6">
                        <!-- Név -->
                        <div class="relative">
                            <input type="text" name="name" id="name" value="{{ old('name') }}" required
                                   class="peer block w-full rounded-lg border-gray-300 bg-gray-50 px-4 py-3 text-gray-900 placeholder-transparent focus:border-indigo-500 focus:ring-indigo-500 focus:bg-white transition-all duration-200"
                                   placeholder="Teljes név">
                            <label for="name" class="absolute left-4 -top-2.5 bg-white px-2 text-sm font-medium text-gray-600 transition-all peer-placeholder-shown:top-3 peer-placeholder-shown:text-base peer-placeholder-shown:text-gray-400 peer-focus:-top-2.5 peer-focus:text-sm peer-focus:text-indigo-600">
                                Név *
                            </label>
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div class="relative">
                            <input type="email" name="email" id="email" value="{{ old('email') }}" required
                                   class="peer block w-full rounded-lg border-gray-300 bg-gray-50 px-4 py-3 text-gray-900 placeholder-transparent focus:border-indigo-500 focus:ring-indigo-500 focus:bg-white transition-all duration-200"
                                   placeholder="email@pelda.hu">
                            <label for="email" class="absolute left-4 -top-2.5 bg-white px-2 text-sm font-medium text-gray-600 transition-all peer-placeholder-shown:top-3 peer-placeholder-shown:text-base peer-placeholder-shown:text-gray-400 peer-focus:-top-2.5 peer-focus:text-sm peer-focus:text-indigo-600">
                                Email cím *
                            </label>
                            @error('email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Jelszó -->
                        <div class="relative">
                            <input type="password" name="password" id="password" required
                                   class="peer block w-full rounded-lg border-gray-300 bg-gray-50 px-4 py-3 text-gray-900 placeholder-transparent focus:border-indigo-500 focus:ring-indigo-500 focus:bg-white transition-all duration-200"
                                   placeholder="••••••••">
                            <label for="password" class="absolute left-4 -top-2.5 bg-white px-2 text-sm font-medium text-gray-600 transition-all peer-placeholder-shown:top-3 peer-placeholder-shown:text-base peer-placeholder-shown:text-gray-400 peer-focus:-top-2.5 peer-focus:text-sm peer-focus:text-indigo-600">
                                Jelszó *
                            </label>
                            @error('password')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Jelszó megerősítés -->
                        <div class="relative">
                            <input type="password" name="password_confirmation" id="password_confirmation" required
                                   class="peer block w-full rounded-lg border-gray-300 bg-gray-50 px-4 py-3 text-gray-900 placeholder-transparent focus:border-indigo-500 focus:ring-indigo-500 focus:bg-white transition-all duration-200"
                                   placeholder="••••••••">
                            <label for="password_confirmation" class="absolute left-4 -top-2.5 bg-white px-2 text-sm font-medium text-gray-600 transition-all peer-placeholder-shown:top-3 peer-placeholder-shown:text-base peer-placeholder-shown:text-gray-400 peer-focus:-top-2.5 peer-focus:text-sm peer-focus:text-indigo-600">
                                Jelszó megerősítése *
                            </label>
                        </div>
                    </div>

                    <!-- Jobb oszlop -->
                    <div class="space-y-6">
                        <!-- Szerepkör -->
                        <div class="relative">
                            <select name="role_id" id="role_id" required
                                    class="peer block w-full rounded-lg border-gray-300 bg-gray-50 px-4 py-3 text-gray-900 focus:border-indigo-500 focus:ring-indigo-500 focus:bg-white transition-all duration-200">
                                <option value="" disabled selected hidden></option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                        {{ $role->display_name }} ({{ $role->name }})
                                    </option>
                                @endforeach
                            </select>
                            <label for="role_id" class="absolute left-4 -top-2.5 bg-white px-2 text-sm font-medium text-gray-600 transition-all peer-placeholder-shown:top-3 peer-placeholder-shown:text-base peer-placeholder-shown:text-gray-400 peer-focus:-top-2.5 peer-focus:text-sm peer-focus:text-indigo-600">
                                Szerepkör *
                            </label>
                            @error('role_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Fiók -->
                        <div class="relative">
                            <select name="fiok_id" id="fiok_id"
                                    class="peer block w-full rounded-lg border-gray-300 bg-gray-50 px-4 py-3 text-gray-900 focus:border-indigo-500 focus:ring-indigo-500 focus:bg-white transition-all duration-200">
                                <option value="">— Nincs / Rendszergazda —</option>
                                @foreach($fiokok as $fiok)
                                    <option value="{{ $fiok->id }}" {{ old('fiok_id') == $fiok->id ? 'selected' : '' }}>
                                        {{ $fiok->nev }} ({{ $fiok->kod }})
                                    </option>
                                @endforeach
                            </select>
                            <label for="fiok_id" class="absolute left-4 -top-2.5 bg-white px-2 text-sm font-medium text-gray-600 transition-all peer-placeholder-shown:top-3 peer-placeholder-shown:text-base peer-placeholder-shown:text-gray-400 peer-focus:-top-2.5 peer-focus:text-sm peer-focus:text-indigo-600">
                                Fiók (telephely)
                            </label>
                        </div>

                        <!-- Opcionális segédszöveg -->
                        <div class="text-sm text-gray-500 mt-2">
                            <p>• Rendszergazda → nincs fiók hozzárendelve</p>
                            <p>• Dolgozó / Manager → válaszd ki a telephelyet</p>
                        </div>
                    </div>
                </div>

                <!-- Gombok -->
                <div class="mt-12 flex flex-col sm:flex-row sm:justify-end gap-4">
                    <a href="{{ route('admin.users.index') }}"
                       class="inline-flex justify-center rounded-lg border border-gray-300 bg-white px-8 py-3.5 text-base font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition">
                        Mégse
                    </a>
                    <button type="submit"
                            class="inline-flex justify-center rounded-lg bg-gradient-to-r from-indigo-600 to-indigo-700 px-10 py-3.5 text-base font-semibold text-white shadow-lg hover:from-indigo-700 hover:to-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition">
                        Felhasználó létrehozása
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection