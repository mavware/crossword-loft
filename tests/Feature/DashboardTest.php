<?php

use App\Models\Crossword;
use App\Models\CrosswordLike;
use App\Models\PuzzleAttempt;
use App\Models\User;
use Livewire\Livewire;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
});

test('dashboard shows published puzzle count', function () {
    $user = User::factory()->create();
    Crossword::factory()->count(2)->published()->create(['user_id' => $user->id]);
    Crossword::factory()->create(['user_id' => $user->id]); // draft

    $component = Livewire::actingAs($user)->test('pages::dashboard');

    expect($component->get('publishedCount'))->toBe(2);
});

test('dashboard shows draft puzzle count', function () {
    $user = User::factory()->create();
    Crossword::factory()->count(3)->create(['user_id' => $user->id, 'is_published' => false]);

    $component = Livewire::actingAs($user)->test('pages::dashboard');

    expect($component->get('draftCount'))->toBe(3);
});

test('dashboard shows solved puzzle count', function () {
    $user = User::factory()->create();
    PuzzleAttempt::factory()->count(2)->completed()->create(['user_id' => $user->id]);
    PuzzleAttempt::factory()->create(['user_id' => $user->id]); // in progress

    $component = Livewire::actingAs($user)->test('pages::dashboard');

    expect($component->get('solvedCount'))->toBe(2);
});

test('dashboard shows in-progress attempts', function () {
    $user = User::factory()->create();
    $crossword = Crossword::factory()->published()->create(['title' => 'Active Puzzle']);
    PuzzleAttempt::factory()->create([
        'user_id' => $user->id,
        'crossword_id' => $crossword->id,
        'is_completed' => false,
    ]);

    Livewire::actingAs($user)
        ->test('pages::dashboard')
        ->assertSee('Active Puzzle');
});

test('dashboard limits in-progress attempts to 3', function () {
    $user = User::factory()->create();
    PuzzleAttempt::factory()->count(5)->create(['user_id' => $user->id, 'is_completed' => false]);

    $component = Livewire::actingAs($user)->test('pages::dashboard');

    expect($component->get('inProgressAttempts'))->toHaveCount(3);
});

test('dashboard shows recently liked puzzles', function () {
    $user = User::factory()->create();
    $crossword = Crossword::factory()->published()->create(['title' => 'Liked Dash Puzzle']);
    CrosswordLike::create(['user_id' => $user->id, 'crossword_id' => $crossword->id]);

    Livewire::actingAs($user)
        ->test('pages::dashboard')
        ->assertSee('Liked Dash Puzzle');
});

test('dashboard limits recent likes to 3', function () {
    $user = User::factory()->create();

    foreach (range(1, 5) as $i) {
        $crossword = Crossword::factory()->published()->create();
        CrosswordLike::create(['user_id' => $user->id, 'crossword_id' => $crossword->id]);
    }

    $component = Livewire::actingAs($user)->test('pages::dashboard');

    expect($component->get('recentLikes'))->toHaveCount(3);
});

test('dashboard shows community stats', function () {
    Crossword::factory()->count(4)->published()->create();
    PuzzleAttempt::factory()->count(2)->completed()->create();

    $user = User::factory()->create();
    $component = Livewire::actingAs($user)->test('pages::dashboard');

    expect($component->get('totalPublishedPuzzles'))->toBe(4)
        ->and($component->get('totalSolves'))->toBe(2);
});

test('dashboard shows empty states when user has no activity', function () {
    Livewire::actingAs(User::factory()->create())
        ->test('pages::dashboard')
        ->assertSee('No puzzles in progress')
        ->assertSee('No liked puzzles yet');
});
