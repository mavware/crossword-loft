<?php

namespace App\Models;

use Database\Factories\FavoriteListFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['user_id', 'name'])]
class FavoriteList extends Model
{
    /** @use HasFactory<FavoriteListFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsToMany<Crossword, $this>
     */
    public function crosswords(): BelongsToMany
    {
        return $this->belongsToMany(Crossword::class)->withTimestamps();
    }
}
