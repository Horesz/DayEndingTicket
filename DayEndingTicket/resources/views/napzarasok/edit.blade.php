@extends('layouts.app')

@section('title', 'Napzárás szerkesztése – ' . $napzaras->datum->format('Y.m.d'))

@section('content')
<div class="container">
    <h1 class="h3 mb-4">Napzárás szerkesztése – {{ $napzaras->datum->format('Y.m.d') }}</h1>

    <div class="alert alert-info">
        Fiók: <strong>{{ $napzaras->fiok->nev ?? $napzaras->fiok_id }}</strong>  
        | Feltöltve: {{ $napzaras->user->name }} – {{ $napzaras->created_at->format('Y.m.d H:i') }}
    </div>

    @if($napzaras->statusz !== 'pending')
        <div class="alert alert-warning">
            Ez a napzárás már <strong>{{ $napzaras->statusz === 'approved' ? 'jóváhagyva' : 'elutasítva' }}</strong> – 
            a szerkesztés csak bizonyos jogosultságokkal lehetséges.
        </div>
    @endif

    <form method="POST" action="{{ route('napzarasok.update', $napzaras) }}">
        @csrf @method('PUT')

        <!-- A fiók és dátum mezőket nem szerkeszthetővé tesszük -->
        <fieldset disabled>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label">Fiók</label>
                    <input type="text" class="form-control" value="{{ $napzaras->fiok->nev ?? $napzaras->fiok_id }}" disabled>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Dátum</label>
                    <input type="text" class="form-control" value="{{ $napzaras->datum->format('Y.m.d') }}" disabled>
                </div>
            </div>
        </fieldset>

        <!-- Innentől ugyanaz, mint create.blade.php tartalma (bevételek, kiadások, megjegyzés) -->

        <!-- ... másold be ide a create megfelelő részeit ... -->

        <div class="mt-4">
            <button type="submit" class="btn btn-primary btn-lg">Módosítás mentése</button>
            <a href="{{ route('napzarasok.show', $napzaras) }}" class="btn btn-outline-secondary btn-lg">Vissza</a>
        </div>
    </form>
</div>
@endsection