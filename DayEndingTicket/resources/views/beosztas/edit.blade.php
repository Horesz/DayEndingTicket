
@extends('layouts.app')

@section('title', 'Munkabeosztás módosítása')

@section('content')
<form method="POST" action="{{ route('beosztas.update', $beosztas->id) }}">
    @csrf
    @method('PUT')

    {{-- Fiók (csak megjelenítés) --}}
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">Fiók</label>
        <input type="text" 
               value="{{ $beosztas->fiok->nev }}" 
               disabled
               class="w-full rounded-md border-gray-300 bg-gray-100">
    </div>

    {{-- Kezdés --}}
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">Kezdés</label>
        <input type="time" 
               name="kezdes"
               value="{{ $beosztas->kezdes }}"
               class="w-full rounded-md border-gray-300 shadow-sm">
    </div>

    {{-- Befejezés --}}
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">Befejezés</label>
        <input type="time" 
               name="befejezes"
               value="{{ $beosztas->befejezes }}"
               class="w-full rounded-md border-gray-300 shadow-sm">
    </div>

    {{-- Megjegyzés --}}
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">Megjegyzés</label>
        <textarea name="megjegyzes"
                  class="w-full rounded-md border-gray-300 shadow-sm"
                  rows="3">{{ $beosztas->megjegyzes }}</textarea>
    </div>

    <div class="flex justify-between">
        {{-- Törlés --}}
        <form method="POST" action="{{ route('beosztas.destroy', $beosztas->id) }}">
            @csrf
            @method('DELETE')
            <button type="submit"
                    class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                Törlés
            </button>
        </form>

        {{-- Mentés --}}
        <button type="submit"
                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
            Módosítás
        </button>
    </div>
</form>
@endsection