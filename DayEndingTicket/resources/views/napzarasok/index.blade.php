@extends('layouts.app')

@section('title', 'Felhasználók kezelése')

@section('content')
<div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-8">

    <!-- Fejléc -->
    <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-xl shadow-lg px-6 py-8 text-white mb-8">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold">Felhasználók kezelése</h1>
                <p class="mt-2 text-indigo-100">{{ $users->count() }} db felhasználó</p>
            </div>
            <a href="{{ route('admin.users.create') }}" 
               class="px-6 py-3 bg-white text-indigo-700 rounded-lg font-medium hover:bg-indigo-50 transition">
                + Új felhasználó
            </a>
        </div>
    </div>

    <!-- Flash üzenet -->
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-6 py-4 rounded-lg mb-6">
            {{ session('success') }}
        </div>
    @endif

    <!-- Táblázat -->
    <div class="bg-white rounded-xl shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Név</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Szerepkör</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fiók</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Bér típus</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Műveletek</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($users as $user)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ $user->name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ $user->email }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs rounded-full 
                                {{ $user->role->name === 'rendszergazda' ? 'bg-purple-100 text-purple-800' : '' }}
                                {{ $user->role->name === 'admin' ? 'bg-blue-100 text-blue-800' : '' }}
                                {{ $user->role->name === 'dolgozo' ? 'bg-gray-100 text-gray-800' : '' }}">
                                {{ $user->role->display_name }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ $user->fiok->nev ?? '—' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <span class="px-2 py-1 text-xs rounded-full {{ $user->ber_tipus === 'napi' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800' }}">
                                {{ $user->ber_tipus === 'napi' ? 'Napi béres' : 'Fix' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                            <a href="{{ route('admin.users.edit', $user) }}" 
                               class="text-indigo-600 hover:text-indigo-900 mr-4">Szerkesztés</a>
                            
                            @if($user->id !== auth()->id())
                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900"
                                            onclick="return confirm('Biztosan törlöd?')">
                                        Törlés
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</div>
@endsection