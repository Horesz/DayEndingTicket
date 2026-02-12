
@extends('layouts.app')

@section('title', 'Munkabeosztás-létrehozása')

@section('content')
<form method="POST" action="{{ route('beosztas.store') }}">
    @csrf

    <input type="hidden" name="user_id" value="{{ $dolgozoId }}">
    <input type="hidden" name="datum" value="{{ $datum }}">
    
    {{-- Fiok --}}
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">Fiók</label>
        <select name="fiok_id" required class="w-full rounded-md border-gray-300 shadow-sm">
            @foreach(\App\Models\Fiok::all() as $fiok)
                <option value="{{ $fiok->id }}">
                    {{ $fiok->nev }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- Kezdés --}}
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">Kezdés</label>
        <input type="time" name="kezdes"
               class="w-full rounded-md border-gray-300 shadow-sm">
    </div>

    {{-- Befejezés --}}
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">Befejezés</label>
        <input type="time" name="befejezes"
               class="w-full rounded-md border-gray-300 shadow-sm">
    </div>

    {{-- Megjegyzés --}}
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">Megjegyzés</label>
        <textarea name="megjegyzes"
                  class="w-full rounded-md border-gray-300 shadow-sm"
                  rows="3"></textarea>
    </div>

    <div class="flex justify-end">
        <button type="submit"
                class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
            Mentés
        </button>
    </div>
</form>
@endsection