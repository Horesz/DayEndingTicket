<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Munkakor extends Model
{
    protected $table = 'munkakorok';
    
    protected $fillable = [
        'nev',
        'kod',
        'fiok_id',
        'aktiv',
    ];

    protected $casts = [
        'aktiv' => 'boolean',
    ];

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
}