<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
        'ber_tipus',   // napi | fix
        'alap_ber',    // havi alapbér (fixnél)
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
            'alap_ber' => 'decimal:2',
        ];
    }

    /* =========================
     |  KAPCSOLATOK
     ========================= */

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function fiok(): BelongsTo
    {
        return $this->belongsTo(Fiok::class);
    }

    /**
     * Napzárások – pivot táblával (napzaras_user)
     */
    public function napzarasok(): BelongsToMany
    {
        return $this->belongsToMany(Napzaras::class, 'napzaras_user')
            ->withPivot(['osszeg', 'megjegyzes'])
            ->withTimestamps();
    }

    public function beosztasok(): HasMany
    {
        return $this->hasMany(Beosztas::class);
    }

    /* =========================
     |  SZEREPKÖR SEGÉDEK
     ========================= */

    public function isDolgozo(): bool
    {
        return $this->role?->name === 'dolgozo';
    }

    public function isAdmin(): bool
    {
        return $this->role?->name === 'admin';
    }

    public function isRendszergazda(): bool
    {
        return $this->role?->name === 'rendszergazda';
    }

    /* =========================
     |  JOGOSULTSÁGOK
     ========================= */

    public function hasPermission(string $permission): bool
    {
        if ($this->isRendszergazda()) {
            return true;
        }

        $permissions = [
            'dolgozo' => [
                'create_napzaras',
                'view_own_napzaras',
                'view_beosztas',
            ],
            'admin' => [
                'create_napzaras',
                'view_napzaras',
                'approve_napzaras',
                'view_reports',
                'manage_beosztas',
            ],
        ];

        return in_array(
            $permission,
            $permissions[$this->role?->name] ?? [],
            true
        );
    }

    /* =========================
     |  BÉR LOGIKA (KRITIKUS RÉSZ)
     ========================= */

    public function isNapiBeres(): bool
    {
        return $this->ber_tipus === 'napi';
    }

    public function isFixBeres(): bool
    {
        return $this->ber_tipus === 'fix';
    }

    /**
     * Fix dolgozó napi díja (egyszerűsített számítás)
     */
    public function napiFixBer(): ?float
    {
        if ($this->isFixBeres() && $this->alap_ber) {
            return round($this->alap_ber / 30, 2);
        }

        return null;
    }

    /**
     * Napzáráskor ténylegesen fizetendő összeg
     */
    public function szamoltNapiBer(?float $manualOsszeg = null): float
    {
        if ($this->isFixBeres()) {
            return $this->napiFixBer() ?? 0;
        }

        return $manualOsszeg ?? 0;
    }
}
