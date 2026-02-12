@extends('layouts.admin')

@section('content')
<div class="container">
    <h1>Felhasználók</h1>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <a href="{{ route('admin.users.create') }}" class="btn btn-primary mb-3">
        + Új felhasználó
    </a>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Név</th>
                <th>Email</th>
                <th>Szerepkör</th>
                <th>Fiók</th>
                <th>Bér típus</th>
                <th>Alap bér</th>
                <th>Műveletek</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->role?->name }}</td>
                    <td>{{ $user->fiok?->nev ?? '-' }}</td>
                    <td>{{ $user->ber_tipus }}</td>
                    <td>{{ $user->alap_ber ?? '-' }}</td>
                    <td>
                        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-warning">
                            Szerkesztés
                        </a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
