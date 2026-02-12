@extends('layouts.app')

@section('title', 'Napzárás megtekintése')

@section('content')
<div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-8">

    <!-- Fejléc -->
    <div class="mb-8">
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-xl shadow-lg px-6 py-8 text-white">
            <div class="flex justify-between items-start">
                <div>
                    <h1 class="text-3xl font-bold">Napzárás részletei</h1>
                    <p class="mt-2 text-indigo-100">
                        {{ $napzaras->datum ? $napzaras->datum->format('Y. m. d. (l)') : 'Nincs dátum' }} • 
                        {{ $napzaras->fiok->nev ?? 'Nincs fiók' }}
                    </p>
                </div>
                <div class="text-right">
                    @if($napzaras->statusz === 'approved')
                        <span class="inline-flex px-4 py-2 text-sm font-semibold rounded-full bg-green-100 text-green-800">
                            ✓ Jóváhagyva
                        </span>
                    @elseif($napzaras->statusz === 'rejected')
                        <span class="inline-flex px-4 py-2 text-sm font-semibold rounded-full bg-red-100 text-red-800">
                            ✗ Elutasítva
                        </span>
                    @else
                        <span class="inline-flex px-4 py-2 text-sm font-semibold rounded-full bg-yellow-100 text-yellow-800">
                            ⏳ Függőben
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Bal oldal: Adatok -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Alapadatok -->
            <div class="bg-white rounded-xl shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Alapadatok</h2>
                <dl class="grid grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Fiók</dt>
                        <dd class="mt-1 text-base font-semibold text-gray-900">{{ $napzaras->fiok->nev ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Dátum</dt>
                        <dd class="mt-1 text-base font-semibold text-gray-900">
                            {{ $napzaras->datum ? $napzaras->datum->format('Y. m. d.') : 'N/A' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Feltöltő</dt>
                        <dd class="mt-1 text-base font-semibold text-gray-900">{{ $napzaras->user->name ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Feltöltés ideje</dt>
                        <dd class="mt-1 text-base text-gray-900">
                            {{ $napzaras->created_at ? $napzaras->created_at->format('Y.m.d. H:i') : 'N/A' }}
                        </dd>
                    </div>
                </dl>
            </div>

            <!-- Bevételek -->
            <div class="bg-white rounded-xl shadow p-6">
                <h2 class="text-xl font-semibold mb-4 text-green-700">Bevételek</h2>
                <dl class="space-y-3">
                    <div class="flex justify-between">
                        <dt class="text-gray-600">Készpénz</dt>
                        <dd class="font-semibold text-gray-900">{{ number_format($napzaras->keszpenz_bevetel ?? 0, 0, ',', ' ') }} Ft</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-600">Kártya</dt>
                        <dd class="font-semibold text-gray-900">{{ number_format($napzaras->kartya_bevetel ?? 0, 0, ',', ' ') }} Ft</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-600">Online</dt>
                        <dd class="font-semibold text-gray-900">{{ number_format($napzaras->online_bevetel ?? 0, 0, ',', ' ') }} Ft</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-600">Egyéb</dt>
                        <dd class="font-semibold text-gray-900">{{ number_format($napzaras->egyeb_bevetel ?? 0, 0, ',', ' ') }} Ft</dd>
                    </div>
                    <div class="flex justify-between pt-3 border-t-2 border-green-200">
                        <dt class="font-bold text-gray-900">Összes bevétel</dt>
                        <dd class="font-bold text-green-700 text-xl">{{ number_format($napzaras->ossz_bevetel ?? 0, 0, ',', ' ') }} Ft</dd>
                    </div>
                </dl>
            </div>

            <!-- Napi bérek -->
            <div class="bg-white rounded-xl shadow p-6">
                <h2 class="text-xl font-semibold mb-4 text-red-700">Napi bérek</h2>
                @if(!empty($napzaras->dolgozok_json) && count($napzaras->dolgozok_json) > 0)
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="text-left text-sm font-medium text-gray-600 pb-2">Dolgozó</th>
                                <th class="text-right text-sm font-medium text-gray-600 pb-2">Bér</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($napzaras->dolgozok_json as $dolgozo)
                                <tr>
                                    <td class="py-2 text-gray-900">{{ $dolgozo['nev'] ?? 'N/A' }}</td>
                                    <td class="py-2 text-right font-semibold text-gray-900">
                                        {{ number_format($dolgozo['ber'] ?? 0, 0, ',', ' ') }} Ft
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="flex justify-between pt-3 mt-3 border-t-2 border-red-200">
                        <dt class="font-bold text-gray-900">Összes napi bér</dt>
                        <dd class="font-bold text-red-700 text-xl">{{ number_format($napzaras->napi_ber ?? 0, 0, ',', ' ') }} Ft</dd>
                    </div>
                @else
                    <p class="text-gray-500 text-sm">Nincs rögzített napi bér.</p>
                @endif
            </div>

            <!-- Egyéb költségek -->
            <div class="bg-white rounded-xl shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Egyéb költségek</h2>
                <div class="flex justify-between">
                    <dt class="text-gray-600">Összeg</dt>
                    <dd class="font-semibold text-gray-900">{{ number_format($napzaras->koltsegek ?? 0, 0, ',', ' ') }} Ft</dd>
                </div>
            </div>

            <!-- Dokumentumok -->
            @if($napzaras->nav_foto_link || $napzaras->terminal_foto_link)
            <div class="bg-white rounded-xl shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Dokumentumok</h2>
                <div class="space-y-3">
                    @if($napzaras->nav_foto_link)
                        <div>
                            <p class="text-sm text-gray-600 mb-1">📄 NAV-os blokk</p>
                            <a href="{{ $napzaras->nav_foto_link }}" target="_blank" 
                               class="text-indigo-600 hover:text-indigo-800 underline text-sm break-all">
                                {{ $napzaras->nav_foto_link }}
                            </a>
                        </div>
                    @endif
                    
                    @if($napzaras->terminal_foto_link)
                        <div>
                            <p class="text-sm text-gray-600 mb-1">💳 Terminál</p>
                            <a href="{{ $napzaras->terminal_foto_link }}" target="_blank" 
                               class="text-indigo-600 hover:text-indigo-800 underline text-sm break-all">
                                {{ $napzaras->terminal_foto_link }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Megjegyzés -->
            @if($napzaras->megjegyzes)
            <div class="bg-white rounded-xl shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Megjegyzés</h2>
                <p class="text-gray-700">{{ $napzaras->megjegyzes }}</p>
            </div>
            @endif

        </div>

        <!-- Jobb oldal: Összesítő + Műveletek -->
        <div class="space-y-6">
            
            <!-- Összesítő -->
            <div class="bg-white rounded-xl shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Összesítő</h2>
                <dl class="space-y-3">
                    <div class="flex justify-between">
                        <dt class="text-green-600">Bevétel</dt>
                        <dd class="font-semibold text-green-700">{{ number_format($napzaras->ossz_bevetel ?? 0, 0, ',', ' ') }} Ft</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-red-600">Kiadás</dt>
                        <dd class="font-semibold text-red-700">{{ number_format($napzaras->ossz_kiadas ?? 0, 0, ',', ' ') }} Ft</dd>
                    </div>
                    <div class="flex justify-between pt-3 border-t-2 border-gray-300">
                        <dt class="font-bold text-gray-900">Eredmény</dt>
                        <dd class="font-bold text-2xl {{ ($napzaras->eredmeny ?? 0) >= 0 ? 'text-blue-600' : 'text-red-600' }}">
                            {{ number_format($napzaras->eredmeny ?? 0, 0, ',', ' ') }} Ft
                        </dd>
                    </div>
                </dl>
            </div>

            <!-- Jóváhagyás info -->
            @if($napzaras->statusz !== 'pending')
            <div class="bg-white rounded-xl shadow p-6">
                <h2 class="text-lg font-semibold mb-3">
                    @if($napzaras->statusz === 'approved')
                        Jóváhagyva
                    @else
                        Elutasítva
                    @endif
                </h2>
                <dl class="space-y-2 text-sm">
                    <div>
                        <dt class="text-gray-600">Jóváhagyta</dt>
                        <dd class="font-medium text-gray-900">{{ $napzaras->jovahagyo?->name ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-600">Időpont</dt>
                        <dd class="font-medium text-gray-900">
                            {{ $napzaras->jovahagyva_at ? $napzaras->jovahagyva_at->format('Y.m.d. H:i') : '-' }}
                        </dd>
                    </div>
                    @if($napzaras->jovahagyas_megjegyzes)
                    <div>
                        <dt class="text-gray-600">Megjegyzés</dt>
                        <dd class="text-gray-900">{{ $napzaras->jovahagyas_megjegyzes }}</dd>
                    </div>
                    @endif
                </dl>
            </div>
            @endif

            <!-- Műveletek -->
            <div class="bg-white rounded-xl shadow p-6">
                <h2 class="text-lg font-semibold mb-3">Műveletek</h2>
                <div class="space-y-2">
                    <a href="{{ route('napzarasok.index') }}" 
                       class="block w-full text-center px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                        Vissza a listához
                    </a>

                    @if($napzaras->statusz === 'pending' && $napzaras->user_id === auth()->id())
                        <a href="{{ route('napzarasok.edit', $napzaras) }}" 
                           class="block w-full text-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                            Szerkesztés
                        </a>
                    @endif

                    @if(auth()->user()->hasPermission('approve_napzaras') && $napzaras->statusz === 'pending')
                        <form action="{{ route('napzarasok.approve', $napzaras) }}" method="POST">
                            @csrf
                            <button type="submit" 
                                    class="block w-full px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                                Jóváhagyás
                            </button>
                        </form>

                        <button type="button" onclick="document.getElementById('reject-form').classList.toggle('hidden')"
                                class="block w-full px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                            Elutasítás
                        </button>

                        <form id="reject-form" action="{{ route('napzarasok.reject', $napzaras) }}" method="POST" class="hidden mt-3">
                            @csrf
                            <textarea name="jovahagyas_megjegyzes" rows="3" required placeholder="Elutasítás oka..."
                                      class="block w-full rounded-md border-gray-300 mb-2"></textarea>
                            <button type="submit" class="w-full px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                                Elutasítás megerősítése
                            </button>
                        </form>
                    @endif
                </div>
            </div>

        </div>
    </div>

</div>
@endsection