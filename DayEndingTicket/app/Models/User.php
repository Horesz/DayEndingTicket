<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'fiok_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function fiok(): BelongsTo
    {
        return $this->belongsTo(Fiok::class);
    }

    public function napzarasok(): HasMany
    {
        return $this->hasMany(Napzaras::class);
    }

    public function beosztasok(): HasMany
    {
        return $this->hasMany(Beosztas::class);
    }

    // Helper methods
    public function isDolgozo(): bool
    {
        return $this->role->name === 'dolgozo';
    }

    public function isAdmin(): bool
    {
        return $this->role->name === 'admin';
    }

    public function isRendszergazda(): bool
    {
        return $this->role->name === 'rendszergazda';
    }

    public function hasPermission(string $permission): bool
    {
        $permissions = [
            'dolgozo' => ['create_napzaras', 'view_own_napzaras', 'view_beosztas'],
            'admin' => ['create_napzaras', 'view_napzaras', 'approve_napzaras', 'view_reports', 'manage_beosztas'],
            'rendszergazda' => ['*'], // Minden jog
        ];

        if ($this->role->name === 'rendszergazda') {
            return true;
        }

        return in_array($permission, $permissions[$this->role->name] ?? []);
    }
}