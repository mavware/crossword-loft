<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClueReport extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'clue_entry_id',
        'user_id',
        'reason',
        'notes',
    ];

    public function clueEntry(): BelongsTo
    {
        return $this->belongsTo(ClueEntry::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
