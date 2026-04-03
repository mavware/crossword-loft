<?php

namespace App\Models;

use Database\Factories\RoadmapItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'title', 'description', 'type', 'status',
    'sort_order', 'target_date', 'completed_date',
])]
class RoadmapItem extends Model
{
    /** @use HasFactory<RoadmapItemFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'target_date' => 'date',
            'completed_date' => 'date',
        ];
    }
}
