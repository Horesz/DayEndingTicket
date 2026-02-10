<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;


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
        'statusz',
        'jovahagyta_user_id',
        'jovahagyva_at',
        'jovahagyas_megjegyzes'
    ];

    protected $casts = [
        'datum' => 'date',
        'kartya_bevetel' => 'decimal:2',
        'keszpenz_bevetel' => 'decimal:2',
        'online_bevetel' => 'decimal:2',
        'egyeb_bevetel' => 'decimal:2',
        'napi_ber' => 'decimal:2',
        'koltsegek' => 'decimal:2',
        'jovahagyva_at' => 'datetime',
    ];

public function dolgozok()
{
    return $this->belongsToMany(User::class, 'napzaras_dolgozo')
                ->withPivot('napi_ber', 'megjegyzes')
                ->withTimestamps();
}

// Összesített napi bér (a pivot táblából számolva)
public function getNapiBerAttribute()
{
    return $this->dolgozok->sum('pivot.napi_ber');
}
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

    // Számított mezők
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
}