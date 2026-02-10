<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Fiok;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    // NINCS __construct() !!!

    public function index()
    {
        $users = User::with(['role', 'fiok'])->orderBy('name')->get();
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $roles = Role::all();
        $fiokok = Fiok::where('aktiv', true)->get();
        return view('admin.users.create', compact('roles', 'fiokok'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'role_id'  => ['required', 'exists:roles,id'],
            'fiok_id'  => ['nullable', 'exists:fiokok,id'],
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role_id'  => $validated['role_id'],
            'fiok_id'  => $validated['fiok_id'] ?? null,
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', "Felhasználó létrehozva: {$user->name}");
    }

    // edit, update, destroy stb. hasonlóan...
}