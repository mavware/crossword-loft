<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClueEntry extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'answer',
        'clue',
        'crossword_id',
        'user_id',
        'direction',
        'clue_number',
    ];

    public function crossword(): BelongsTo
    {
        return $this->belongsTo(Crossword::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<ClueReport, $this>
     */
    public function reports(): HasMany
    {
        return $this->hasMany(ClueReport::class);
    }
}
