<?php

use App\Models\Crossword;
use App\Models\PuzzleAttempt;
use App\Models\User;
use Livewire\Livewire;

test('solving page shows user attempts', function () {
    $user = User::factory()->create();
    $creator = User::factory()->create();
    $crossword = Crossword::factory()->published()->for($creator)->create(['title' => 'Diamond Puzzle']);

    PuzzleAttempt::factory()->for($user)->create(['crossword_id' => $crossword->id]);

    $this->actingAs($user);

    Livewire::test('pages::crosswords.solving')
        ->assertSee('Diamond Puzzle')
        ->assertSee($creator->name);
});

test('solving page shows available published puzzles via discovery component', function () {
    $user = User::factory()->create();
    $creator = User::factory()->create();
    Crossword::factory()->published()->for($creator)->create(['title' => 'Public Puzzle']);
    Crossword::factory()->for($creator)->create(['title' => 'Private Puzzle']);

    $this->actingAs($user);

    Livewire::test('puzzle-discovery', ['excludeAttempted' => true])
        ->assertSee('Public Puzzle')
        ->assertDontSee('Private Puzzle');
});

test('discovery component shows own published puzzles', function () {
    $user = User::factory()->create();
    Crossword::factory()->published()->for($user)->create(['title' => 'My Own Puzzle']);

    $this->actingAs($user);

    Livewire::test('puzzle-discovery', ['excludeAttempted' => true])
        ->assertSee('My Own Puzzle');
});

test('discovery component does not show already attempted puzzles', function () {
    $user = User::factory()->create();
    $creator = User::factory()->create();
    $crossword = Crossword::factory()->published()->for($creator)->create(['title' => 'Already Started']);

    PuzzleAttempt::factory()->for($user)->create(['crossword_id' => $crossword->id]);

    $this->actingAs($user);

    Livewire::test('puzzle-discovery', ['excludeAttempted' => true])
        ->assertDontSee('Already Started');
});

test('user can start solving a published puzzle via discovery component', function () {
    $user = User::factory()->create();
    $creator = User::factory()->create();
    $crossword = Crossword::factory()->published()->for($creator)->create();

    $this->actingAs($user);

    Livewire::test('puzzle-discovery', ['excludeAttempted' => true])
        ->call('startSolving', $crossword->id)
        ->assertRedirect(route('crosswords.solver', $crossword));
});

test('user cannot start solving an unpublished puzzle they do not own', function () {
    $user = User::factory()->create();
    $creator = User::factory()->create();
    $crossword = Crossword::factory()->for($creator)->create();

    $this->actingAs($user);

    Livewire::test('puzzle-discovery')
        ->call('startSolving', $crossword->id)
        ->assertForbidden();
});

test('user can remove an attempt', function () {
    $user = User::factory()->create();
    $attempt = PuzzleAttempt::factory()->for($user)->create();

    $this->actingAs($user);

    expect(PuzzleAttempt::count())->toBe(1);

    Livewire::test('pages::crosswords.solving')
        ->call('removeAttempt', $attempt->id);

    expect(PuzzleAttempt::count())->toBe(0);
});

test('user cannot remove another users attempt', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $attempt = PuzzleAttempt::factory()->for($other)->create();

    $this->actingAs($user);

    Livewire::test('pages::crosswords.solving')
        ->call('removeAttempt', $attempt->id)
        ->assertStatus(403);
});

test('discovery component search filters available puzzles', function () {
    $user = User::factory()->create();
    $creator = User::factory()->create();
    Crossword::factory()->published()->for($creator)->create(['title' => 'Ocean Theme']);
    Crossword::factory()->published()->for($creator)->create(['title' => 'Space Theme']);

    $this->actingAs($user);

    Livewire::test('puzzle-discovery', ['excludeAttempted' => true])
        ->set('search', 'Ocean')
        ->assertSee('Ocean Theme')
        ->assertDontSee('Space Theme');
});
