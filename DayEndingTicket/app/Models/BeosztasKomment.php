<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BeosztasKomment extends Model
{
    protected $table = 'beosztas_kommentek';
    
    protected $fillable = [
        'beosztas_id',
        'user_id',
        'komment',
        'tipus',
    ];

    public function beosztas(): BelongsTo
    {
        return $this->belongsTo(Beosztas::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}