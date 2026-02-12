<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Napzaras extends Model
{
    protected $table = 'napzarasok';
    
    protected $fillable = [
        'user_id',
        'fiok_id',
        'datum',
        'kartya_bevetel',
        'keszpenz_bevetel',
        'online_bevetel',
        'egyeb_bevetel',
        'napi_ber',
        'koltsegek',
        'megjegyzes',
        'dolgozok_json',        // ÚJ - dolgozók JSON-ben
        'nav_foto_link',        // ÚJ - NAV fotó link
        'terminal_foto_link',   // ÚJ - Terminál fotó link
        'statusz',
        'jovahagyta_user_id',
        'jovahagyva_at',
        'jovahagyas_megjegyzes'
    ];

    protected $casts = [
    'datum' => 'date',  // ← Ez kell!
    'kartya_bevetel' => 'decimal:2',
    'keszpenz_bevetel' => 'decimal:2',
    'online_bevetel' => 'decimal:2',
    'egyeb_bevetel' => 'decimal:2',
    'napi_ber' => 'decimal:2',
    'koltsegek' => 'decimal:2',
    'jovahagyva_at' => 'datetime',
    'dolgozok_json' => 'array',
];

    // ===================================
    // KAPCSOLATOK
    // ===================================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function fiok(): BelongsTo
    {
        return $this->belongsTo(Fiok::class);
    }

    public function jovahagyo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'jovahagyta_user_id');
    }

    // ===================================
    // SZÁMÍTOTT MEZŐK (ACCESSORS)
    // ===================================

    public function getOsszBevetelAttribute(): float
    {
        return $this->kartya_bevetel + $this->keszpenz_bevetel + 
               $this->online_bevetel + $this->egyeb_bevetel;
    }

    public function getOsszKiadasAttribute(): float
    {
        return $this->napi_ber + $this->koltsegek;
    }

    public function getEredmenyAttribute(): float
    {
        return $this->ossz_bevetel - $this->ossz_kiadas;
    }

    // ===================================
    // DOLGOZÓK JSON-ből
    // ===================================

    public function getDolgozokListaAttribute(): array
    {
        return $this->dolgozok_json ?? [];
    }
}
