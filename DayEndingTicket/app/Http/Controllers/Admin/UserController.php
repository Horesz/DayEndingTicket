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
    public function index()
    {
        $user = auth()->user();
        
        // Admin csak saját fiókjának dolgozóit látja
        $users = User::with(['role', 'fiok'])
            ->when($user->isAdmin() && $user->fiok_id, function($query) use ($user) {
                return $query->where('fiok_id', $user->fiok_id);
            })
            ->orderBy('name')
            ->get();
        
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
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'email', 'unique:users'],
            'password'  => ['required', 'confirmed', Password::defaults()],
            'role_id'   => ['required', 'exists:roles,id'],
            'fiok_id'   => ['nullable', 'exists:fiokok,id'],
            'ber_tipus' => ['required', 'in:napi,fix'],
            'alap_ber'  => ['nullable', 'numeric', 'min:0'],
        ]);

        // Napi béres nem kap alapbért
        if ($validated['ber_tipus'] === 'napi') {
            $validated['alap_ber'] = null;
        }

        User::create([
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'password'  => Hash::make($validated['password']),
            'role_id'   => $validated['role_id'],
            'fiok_id'   => $validated['fiok_id'] ?? null,
            'ber_tipus' => $validated['ber_tipus'],
            'alap_ber'  => $validated['alap_ber'],
        ]);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Felhasználó sikeresen létrehozva!');
    }

    public function edit(User $user)
    {
        $roles = Role::all();
        $fiokok = Fiok::where('aktiv', true)->get();
        return view('admin.users.edit', compact('user', 'roles', 'fiokok'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'email', 'unique:users,email,' . $user->id],
            'password'  => ['nullable', 'confirmed', Password::defaults()],
            'role_id'   => ['required', 'exists:roles,id'],
            'fiok_id'   => ['nullable', 'exists:fiokok,id'],
            'ber_tipus' => ['required', 'in:napi,fix'],
            'alap_ber'  => ['nullable', 'numeric', 'min:0'],
        ]);

        if ($validated['ber_tipus'] === 'napi') {
            $validated['alap_ber'] = null;
        }

        $user->update([
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'role_id'   => $validated['role_id'],
            'fiok_id'   => $validated['fiok_id'] ?? null,
            'ber_tipus' => $validated['ber_tipus'],
            'alap_ber'  => $validated['alap_ber'],
        ]);

        // Ha új jelszó van megadva
        if (!empty($validated['password'])) {
            $user->update(['password' => Hash::make($validated['password'])]);
        }

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Felhasználó frissítve!');
    }

    public function destroy(User $user)
    {
        // Ne lehessen magát törölni
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Saját magadat nem törölheted!');
        }

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Felhasználó törölve!');
    }
}