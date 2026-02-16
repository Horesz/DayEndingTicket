@extends('layouts.app')

@section('title', 'Napzárások')

@section('content')
<div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-6">

    <!-- Fejléc / Hero rész -->
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-8">
        <div class="p-6 bg-gradient-to-r from-indigo-600 to-purple-600 text-white">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold">Napzárások</h1>
                    <p class="mt-2 text-indigo-100 text-opacity-90">
                        Napi zárások kezelése • {{ $napzarasok->total() }} db összesen
                    </p>
                </div>

                <a href="{{ route('napzarasok.create') }}"
                   class="inline-flex items-center px-5 py-2.5 bg-white text-indigo-700 font-medium rounded-lg shadow-md hover:bg-indigo-50 transition">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Új napzárás
                </a>
            </div>
        </div>
    </div>

    <!-- Flash üzenet -->
    @if(session('success'))
        <div class="bg-green-50 border-l-4 border-green-400 p-4 rounded-lg mb-6">
            <p class="text-green-700">{{ session('success') }}</p>
        </div>
    @endif

    <!-- Szűrők -->
    <div class="bg-white shadow-sm sm:rounded-lg mb-8 overflow-hidden">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Szűrés</h3>

            <form method="GET" action="{{ route('napzarasok.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Dátum-tól</label>
                    <input type="date" name="datum_tol" value="{{ request('datum_tol') }}"
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Dátum-ig</label>
                    <input type="date" name="datum_ig" value="{{ request('datum_ig') }}"
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Státusz</label>
                    <select name="statusz" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">-- Mind --</option>
                        <option value="pending"  {{ request('statusz') == 'pending'  ? 'selected' : '' }}>Függőben</option>
                        <option value="approved" {{ request('statusz') == 'approved' ? 'selected' : '' }}>Jóváhagyva</option>
                        <option value="rejected" {{ request('statusz') == 'rejected' ? 'selected' : '' }}>Elutasítva</option>
                    </select>
                </div>

                @if(auth()->user()->isRendszergazda())
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Telephely</label>
                    <select name="fiok_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">-- Mind --</option>
                        @foreach($fiokok as $fiok)
                            <option value="{{ $fiok->id }}" {{ request('fiok_id') == $fiok->id ? 'selected' : '' }}>
                                {{ $fiok->nev }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div class="sm:col-span-2 lg:col-span-4 flex flex-wrap gap-3 mt-2">
                    <button type="submit" class="px-5 py-2.5 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700 transition shadow-sm">
                        Szűrés
                    </button>
                    <a href="{{ route('napzarasok.index') }}"
                       class="px-5 py-2.5 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200 transition shadow-sm">
                        Szűrők törlése
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Táblázat / Lista -->
    <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Dátum</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Telephely</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Munkakör</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Feltöltő</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Bevétel</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Nettó</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Státusz</th>
                        <th class="px-6 py-4"></th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($napzarasok as $nz)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $nz->datum->format('Y. m. d. (l)') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                {{ $nz->fiok->nev ?? '–' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span class="px-2 py-1 bg-indigo-100 text-indigo-800 rounded-full text-xs font-medium">
                                    {{ $nz->munkakor->nev ?? '–' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                {{ $nz->user->name ?? '–' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-gray-900">
                                {{ number_format($nz->ossz_bevetel, 0, ',', ' ') }} Ft
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold text-gray-900">
                                {{ number_format($nz->eredmeny, 0, ',', ' ') }} Ft
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($nz->statusz === 'approved')
                                    <span class="inline-flex px-2.5 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                        Jóváhagyva
                                    </span>
                                @elseif($nz->statusz === 'rejected')
                                    <span class="inline-flex px-2.5 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                        Elutasítva
                                    </span>
                                @else
                                    <span class="inline-flex px-2.5 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                        Függőben
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-3">
                                    <a href="{{ route('napzarasok.show', $nz) }}"
                                       class="text-indigo-600 hover:text-indigo-900">Megtekintés</a>

                                    @if($nz->statusz === 'pending' && $nz->user_id === auth()->id())
                                        <a href="{{ route('napzarasok.edit', $nz) }}"
                                           class="text-blue-600 hover:text-blue-900">Szerkesztés</a>
                                    @endif

                                    @if(($nz->statusz === 'pending' && $nz->user_id === auth()->id()) || auth()->user()->isRendszergazda())
                                        <form action="{{ route('napzarasok.destroy', $nz) }}" method="POST" class="inline">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900"
                                                    onclick="return confirm('Biztosan törlöd?')">
                                                Törlés
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-10 text-center text-gray-500">
                                Nincs találat a jelenlegi szűrőkkel.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-5 border-t border-gray-200 flex justify-between items-center">
            <div class="text-sm text-gray-600">
                {{ $napzarasok->firstItem() }}–{{ $napzarasok->lastItem() }} / {{ $napzarasok->total() }}
            </div>
            {{ $napzarasok->appends(request()->query())->links('pagination::tailwind') }}
        </div>
    </div>

</div>
@endsection