@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
    <!-- Üdvözlő üzenet -->
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
        <div class="p-6 bg-gradient-to-r from-indigo-500 to-purple-600 text-white">
            <h1 class="text-2xl font-bold">Üdvözöllek, {{ auth()->user()->name }}! 👋</h1>
            <p class="mt-2 text-indigo-100">
                @if(auth()->user()->isDolgozo())
                    Dolgozói fiók | {{ auth()->user()->fiok?->nev }}
                @elseif(auth()->user()->isAdmin())
                    Manager fiók | {{ auth()->user()->fiok?->nev }}
                @else
                    Rendszergazda fiók
                @endif
            </p>
        </div>
    </div>

    @if(isset($mai_beosztas) && $mai_beosztas)
    <!-- Mai beosztás -->
    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6 rounded">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm text-blue-700">
                    <strong>Mai beosztásod:</strong> {{ $mai_beosztas->fiok->nev }}
                    @if($mai_beosztas->kezdes)
                        - {{ substr($mai_beosztas->kezdes, 0, 5) }} - {{ substr($mai_beosztas->befejezes, 0, 5) }}
                    @endif
                </p>
            </div>
        </div>
    </div>
    @endif

    <!-- Statisztikai kártyák -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        @if(auth()->user()->isDolgozo())
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-indigo-500 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Összes napzárás</dt>
                                <dd class="text-2xl font-semibold text-gray-900">{{ $stats['sajat_napzarasok'] ?? 0 }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-yellow-500 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Függőben</dt>
                                <dd class="text-2xl font-semibold text-gray-900">{{ $stats['pending_napzarasok'] ?? 0 }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Jóváhagyva</dt>
                                <dd class="text-2xl font-semibold text-gray-900">{{ $stats['approved_napzarasok'] ?? 0 }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-indigo-500 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Összes napzárás</dt>
                                <dd class="text-2xl font-semibold text-gray-900">{{ $stats['osszes_napzaras'] ?? 0 }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-yellow-500 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Jóváhagyásra vár</dt>
                                <dd class="text-2xl font-semibold text-gray-900">{{ $stats['pending_napzarasok'] ?? 0 }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Havi bevétel</dt>
                                <dd class="text-2xl font-semibold text-gray-900">{{ number_format($stats['havi_bevetel'] ?? 0, 0, ',', ' ') }} Ft</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-red-500 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Elutasítva</dt>
                                <dd class="text-2xl font-semibold text-gray-900">{{ $stats['rejected_napzarasok'] ?? 0 }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Gyors műveletek -->
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Gyors műveletek</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <a href="{{ route('napzarasok.create') }}" class="flex items-center p-4 bg-indigo-50 rounded-lg hover:bg-indigo-100 transition">
                    <svg class="h-10 w-10 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-900">Új napzárás</p>
                        <p class="text-xs text-gray-500">Napzárás rögzítése</p>
                    </div>
                </a>

                <a href="{{ route('napzarasok.index') }}" class="flex items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition">
                    <svg class="h-10 w-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-900">Napzárások</p>
                        <p class="text-xs text-gray-500">Összes megtekintése</p>
                    </div>
                </a>

                <a href="{{ route('beosztas.index') }}" class="flex items-center p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition">
                    <svg class="h-10 w-10 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-900">Beosztás</p>
                        <p class="text-xs text-gray-500">Munkabeosztás nézet</p>
                    </div>
                </a>

                @if(!auth()->user()->isDolgozo())
                    <a href="{{ route('admin.users.create') }}" class="flex items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition">
                        <svg class="h-10 w-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                        </svg>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-900">Új felhasználó</p>
                            <p class="text-xs text-gray-500">Dolgozó / manager felvétel</p>
                        </div>
                    </a>
                @endif
            </div>
        </div>
    </div>

    @if(auth()->user()->hasPermission('approve_napzaras') && isset($pending_napzarasok))
    <!-- Jóváhagyásra váró napzárások -->
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Jóváhagyásra váró napzárások ({{ $pending_napzarasok->count() }})</h3>
            @if($pending_napzarasok->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dátum</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fiók</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dolgozó</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Bevétel</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Műveletek</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($pending_napzarasok as $napzaras)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $napzaras->datum->format('Y-m-d') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $napzaras->fiok->nev }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $napzaras->user->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($napzaras->ossz_bevetel, 0, ',', ' ') }} Ft</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <a href="{{ route('napzarasok.show', $napzaras) }}" class="text-indigo-600 hover:text-indigo-900">Megtekintés</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-gray-500 text-sm">Nincs jóváhagyásra váró napzárás.</p>
            @endif
        </div>
    </div>
    @endif

    <!-- Legutóbbi napzárások -->
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Legutóbbi napzárások</h3>
            @if(isset($utolso_napzarasok) && $utolso_napzarasok->count() > 0)
                <div class="space-y-4">
                    @foreach($utolso_napzarasok as $napzaras)
                        <div class="border-l-4 {{ $napzaras->statusz === 'approved' ? 'border-green-500' : ($napzaras->statusz === 'pending' ? 'border-yellow-500' : 'border-red-500') }} pl-4 py-2">
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">
                                        {{ $napzaras->datum->format('Y-m-d') }} - {{ $napzaras->fiok->nev }}
                                    </p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        Bevétel: {{ number_format($napzaras->ossz_bevetel, 0, ',', ' ') }} Ft | 
                                        Eredmény: {{ number_format($napzaras->eredmeny, 0, ',', ' ') }} Ft
                                    </p>
                                </div>
                                <div class="text-right">
                                    <span class="px-2 py-1 text-xs rounded-full {{ $napzaras->statusz === 'approved' ? 'bg-green-100 text-green-800' : ($napzaras->statusz === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                        {{ $napzaras->statusz === 'approved' ? 'Jóváhagyva' : ($napzaras->statusz === 'pending' ? 'Függőben' : 'Elutasítva') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-4">
                    <a href="{{ route('napzarasok.index') }}" class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                        Összes napzárás megtekintése →
                    </a>
                </div>
            @else
                <p class="text-gray-500 text-sm">Még nincs napzárás rögzítve.</p>
            @endif
        </div>
    </div>
</div>
@endsection