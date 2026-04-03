<?php

namespace App\Models;

use Database\Factories\CrosswordLikeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'crossword_id'])]
class CrosswordLike extends Model
{
    /** @use HasFactory<CrosswordLikeFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Crossword, $this>
     */
    public function crossword(): BelongsTo
    {
        return $this->belongsTo(Crossword::class);
    }
}
