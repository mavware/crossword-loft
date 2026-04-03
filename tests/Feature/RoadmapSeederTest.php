<?php

use App\Models\RoadmapItem;
use Database\Seeders\RoadmapSeeder;

test('roadmap seeder creates all items', function () {
    $this->seed(RoadmapSeeder::class);

    expect(RoadmapItem::count())->toBe(21);
});

test('roadmap seeder creates correct status distribution', function () {
    $this->seed(RoadmapSeeder::class);

    expect(RoadmapItem::where('status', 'in_progress')->count())->toBe(2)
        ->and(RoadmapItem::where('status', 'planned')->count())->toBe(19);
});

test('roadmap seeder creates correct type distribution', function () {
    $this->seed(RoadmapSeeder::class);

    expect(RoadmapItem::where('type', 'feature')->count())->toBe(15)
        ->and(RoadmapItem::where('type', 'improvement')->count())->toBe(5)
        ->and(RoadmapItem::where('type', 'fix')->count())->toBe(1);
});

test('roadmap seeder sets target dates on all items', function () {
    $this->seed(RoadmapSeeder::class);

    expect(RoadmapItem::whereNull('target_date')->count())->toBe(0);
});

test('roadmap seeder is idempotent', function () {
    $this->seed(RoadmapSeeder::class);
    $this->seed(RoadmapSeeder::class);

    expect(RoadmapItem::count())->toBe(21);
});

test('roadmap seeder creates recognizable feature titles', function () {
    $this->seed(RoadmapSeeder::class);

    expect(RoadmapItem::where('title', 'Solve Timer & Statistics')->exists())->toBeTrue()
        ->and(RoadmapItem::where('title', 'PDF Export')->exists())->toBeTrue()
        ->and(RoadmapItem::where('title', 'Autofill Grid Assistance')->exists())->toBeTrue()
        ->and(RoadmapItem::where('title', 'Pencil Mode')->exists())->toBeTrue()
        ->and(RoadmapItem::where('title', 'Accessibility Improvements')->exists())->toBeTrue();
});
