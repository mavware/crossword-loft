<?php

use App\Models\ClueEntry;
use App\Models\ClueReport;
use App\Models\Crossword;
use App\Models\User;
use Livewire\Livewire;

test('authenticated users can view the clue library', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('clues.index'))
        ->assertSuccessful();
});

test('guests cannot view the clue library', function () {
    $this->get(route('clues.index'))
        ->assertRedirect();
});

test('users can add standalone clues', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::clues.index')
        ->set('newAnswer', 'OCEAN')
        ->set('newClue', 'Large body of water')
        ->call('addClue');

    expect(ClueEntry::where('answer', 'OCEAN')->where('user_id', $user->id)->exists())->toBeTrue();

    $entry = ClueEntry::where('answer', 'OCEAN')->first();
    expect($entry->crossword_id)->toBeNull()
        ->and($entry->direction)->toBeNull()
        ->and($entry->clue_number)->toBeNull()
        ->and($entry->clue)->toBe('Large body of water');
});

test('answer is uppercased on save', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::clues.index')
        ->set('newAnswer', 'ocean')
        ->set('newClue', 'Large body of water')
        ->call('addClue');

    expect(ClueEntry::where('answer', 'OCEAN')->exists())->toBeTrue();
});

test('answer must contain only letters', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::clues.index')
        ->set('newAnswer', 'ABC123')
        ->set('newClue', 'Some clue')
        ->call('addClue')
        ->assertHasErrors('newAnswer');
});

test('duplicate standalone clue by same user is rejected', function () {
    $user = User::factory()->create();

    ClueEntry::create([
        'answer' => 'OCEAN',
        'clue' => 'Large body of water',
        'user_id' => $user->id,
    ]);

    Livewire::actingAs($user)
        ->test('pages::clues.index')
        ->set('newAnswer', 'OCEAN')
        ->set('newClue', 'Large body of water')
        ->call('addClue')
        ->assertSet('addError', fn ($val) => str_contains($val, 'already'));

    expect(ClueEntry::where('answer', 'OCEAN')->where('user_id', $user->id)->count())->toBe(1);
});

test('users can edit their own clues', function () {
    $user = User::factory()->create();
    $entry = ClueEntry::create([
        'answer' => 'OCEAN',
        'clue' => 'Large body of water',
        'user_id' => $user->id,
    ]);

    Livewire::actingAs($user)
        ->test('pages::clues.index')
        ->call('startEditing', $entry->id)
        ->set('editAnswer', 'SEA')
        ->set('editClue', 'Body of salt water')
        ->call('saveEdit');

    $entry->refresh();
    expect($entry->answer)->toBe('SEA')
        ->and($entry->clue)->toBe('Body of salt water');
});

test('users cannot edit other users clues', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $entry = ClueEntry::create([
        'answer' => 'OCEAN',
        'clue' => 'Large body of water',
        'user_id' => $other->id,
    ]);

    Livewire::actingAs($user)
        ->test('pages::clues.index')
        ->call('startEditing', $entry->id)
        ->assertForbidden();
});

test('users can delete their own clues', function () {
    $user = User::factory()->create();
    $entry = ClueEntry::create([
        'answer' => 'OCEAN',
        'clue' => 'Large body of water',
        'user_id' => $user->id,
    ]);

    Livewire::actingAs($user)
        ->test('pages::clues.index')
        ->call('deleteClue', $entry->id);

    expect(ClueEntry::find($entry->id))->toBeNull();
});

test('users cannot delete other users clues', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $entry = ClueEntry::create([
        'answer' => 'OCEAN',
        'clue' => 'Large body of water',
        'user_id' => $other->id,
    ]);

    Livewire::actingAs($user)
        ->test('pages::clues.index')
        ->call('deleteClue', $entry->id)
        ->assertForbidden();
});

test('users can report a clue', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $entry = ClueEntry::create([
        'answer' => 'OCEAN',
        'clue' => 'Wrong clue',
        'user_id' => $other->id,
    ]);

    Livewire::actingAs($user)
        ->test('pages::clues.index')
        ->call('openReportModal', $entry->id)
        ->set('reportReason', 'invalid')
        ->set('reportNotes', 'This clue is incorrect')
        ->call('submitReport');

    expect(ClueReport::where('clue_entry_id', $entry->id)->where('user_id', $user->id)->exists())->toBeTrue();

    $report = ClueReport::first();
    expect($report->reason)->toBe('invalid')
        ->and($report->notes)->toBe('This clue is incorrect');
});

test('users cannot report the same clue twice', function () {
    $user = User::factory()->create();
    $entry = ClueEntry::create([
        'answer' => 'OCEAN',
        'clue' => 'Wrong clue',
        'user_id' => User::factory()->create()->id,
    ]);

    ClueReport::create([
        'clue_entry_id' => $entry->id,
        'user_id' => $user->id,
        'reason' => 'invalid',
    ]);

    Livewire::actingAs($user)
        ->test('pages::clues.index')
        ->call('openReportModal', $entry->id)
        ->set('reportReason', 'duplicate')
        ->call('submitReport')
        ->assertSet('reportError', fn ($val) => str_contains($val, 'already reported'));

    expect(ClueReport::where('clue_entry_id', $entry->id)->count())->toBe(1);
});

test('report reason must be valid', function () {
    $user = User::factory()->create();
    $entry = ClueEntry::create([
        'answer' => 'OCEAN',
        'clue' => 'Wrong clue',
        'user_id' => User::factory()->create()->id,
    ]);

    Livewire::actingAs($user)
        ->test('pages::clues.index')
        ->call('openReportModal', $entry->id)
        ->set('reportReason', 'nonsense')
        ->call('submitReport')
        ->assertHasErrors('reportReason');
});

test('search filters clues by answer', function () {
    $user = User::factory()->create();

    ClueEntry::create(['answer' => 'OCEAN', 'clue' => 'Large body of water', 'user_id' => $user->id]);
    ClueEntry::create(['answer' => 'RIVER', 'clue' => 'Flowing water', 'user_id' => $user->id]);

    Livewire::actingAs($user)
        ->test('pages::clues.index')
        ->set('search', 'OCEAN')
        ->assertSee('OCEAN')
        ->assertDontSee('RIVER');
});

test('search filters clues by clue text', function () {
    $user = User::factory()->create();

    ClueEntry::create(['answer' => 'XYZABC', 'clue' => 'Large body of water', 'user_id' => $user->id]);
    ClueEntry::create(['answer' => 'QWERTY', 'clue' => 'Flowing stream', 'user_id' => $user->id]);

    Livewire::actingAs($user)
        ->test('pages::clues.index')
        ->set('search', 'stream')
        ->assertDontSee('XYZABC')
        ->assertSee('QWERTY');
});

test('mine filter shows only current user clues', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    ClueEntry::create(['answer' => 'MINE', 'clue' => 'My clue', 'user_id' => $user->id]);
    ClueEntry::create(['answer' => 'THEIRS', 'clue' => 'Their clue', 'user_id' => $other->id]);

    Livewire::actingAs($user)
        ->test('pages::clues.index')
        ->set('filter', 'mine')
        ->assertSee('MINE')
        ->assertDontSee('THEIRS');
});

test('flagged filter shows only reported clues', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    $flagged = ClueEntry::create(['answer' => 'BAD', 'clue' => 'Bad clue', 'user_id' => $other->id]);
    ClueEntry::create(['answer' => 'GOOD', 'clue' => 'Good clue', 'user_id' => $other->id]);

    ClueReport::create(['clue_entry_id' => $flagged->id, 'user_id' => $user->id, 'reason' => 'invalid']);

    Livewire::actingAs($user)
        ->test('pages::clues.index')
        ->set('filter', 'flagged')
        ->assertSee('BAD')
        ->assertDontSee('GOOD');
});

test('duplicates filter shows clues with identical answer and clue text', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    ClueEntry::create(['answer' => 'SAME', 'clue' => 'Identical clue', 'user_id' => $user->id]);
    ClueEntry::create(['answer' => 'SAME', 'clue' => 'Identical clue', 'user_id' => $other->id]);
    ClueEntry::create(['answer' => 'UNIQUE', 'clue' => 'One of a kind', 'user_id' => $user->id]);

    Livewire::actingAs($user)
        ->test('pages::clues.index')
        ->set('filter', 'duplicates')
        ->assertSee('SAME')
        ->assertDontSee('UNIQUE');
});

test('standalone filter shows only clues without crossword', function () {
    $user = User::factory()->create();
    $crossword = Crossword::factory()->for($user)->create();

    ClueEntry::create(['answer' => 'STANDALONE', 'clue' => 'No puzzle', 'user_id' => $user->id]);
    ClueEntry::create(['answer' => 'HARVESTED', 'clue' => 'From puzzle', 'user_id' => $user->id, 'crossword_id' => $crossword->id, 'direction' => 'across', 'clue_number' => 1]);

    Livewire::actingAs($user)
        ->test('pages::clues.index')
        ->set('filter', 'standalone')
        ->assertSee('STANDALONE')
        ->assertDontSee('HARVESTED');
});

test('clue library page appears in navigation', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('clues.index'))
        ->assertSee('Clue Library');
});
