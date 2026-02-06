<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Fiok extends Model
{
    protected $table = 'fiokok';
    
    protected $fillable = [
        'nev',
        'cim',
        'kod',
        'aktiv'
    ];

    protected $casts = [
        'aktiv' => 'boolean',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
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