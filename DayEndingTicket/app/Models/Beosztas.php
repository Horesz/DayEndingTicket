<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Beosztas extends Model
{
    protected $table = 'beosztas';
    
    protected $fillable = [
        'user_id',
        'fiok_id',
        'datum',
        'kezdes',
        'befejezes',
        'megjegyzes'
    ];

    protected $casts = [
        'datum' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function fiok(): BelongsTo
    {
        return $this->belongsTo(Fiok::class);
    }
}