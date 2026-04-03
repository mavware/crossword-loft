<?php

use App\Models\Crossword;
use App\Models\CrosswordLike;
use App\Models\FavoriteList;
use App\Models\User;
use Livewire\Livewire;

test('authenticated users can view the favorites page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('favorites.index'))
        ->assertSuccessful();
});

test('guests cannot view the favorites page', function () {
    $this->get(route('favorites.index'))
        ->assertRedirect();
});

test('favorites page shows liked puzzles', function () {
    $user = User::factory()->create();
    $crossword = Crossword::factory()->published()->create(['title' => 'My Liked Puzzle']);

    CrosswordLike::create(['user_id' => $user->id, 'crossword_id' => $crossword->id]);

    Livewire::actingAs($user)
        ->test('pages::favorites.index')
        ->assertSee('My Liked Puzzle');
});

test('favorites page shows empty state when no liked puzzles', function () {
    Livewire::actingAs(User::factory()->create())
        ->test('pages::favorites.index')
        ->assertSee('No puzzles here yet');
});

test('users can create a favorites list', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::favorites.index')
        ->set('newListName', 'Weekend Puzzles')
        ->call('createList');

    expect(FavoriteList::where('user_id', $user->id)->where('name', 'Weekend Puzzles')->exists())->toBeTrue();
});

test('list name is required', function () {
    Livewire::actingAs(User::factory()->create())
        ->test('pages::favorites.index')
        ->set('newListName', '')
        ->call('createList')
        ->assertHasErrors(['newListName' => 'required']);
});

test('duplicate list names are rejected', function () {
    $user = User::factory()->create();
    FavoriteList::create(['user_id' => $user->id, 'name' => 'My List']);

    Livewire::actingAs($user)
        ->test('pages::favorites.index')
        ->set('newListName', 'My List')
        ->call('createList')
        ->assertHasErrors('newListName');
});

test('users can rename a favorites list', function () {
    $user = User::factory()->create();
    $list = FavoriteList::create(['user_id' => $user->id, 'name' => 'Old Name']);

    Livewire::actingAs($user)
        ->test('pages::favorites.index')
        ->call('openRenameModal', $list->id)
        ->set('renameListName', 'New Name')
        ->call('renameList');

    expect($list->fresh()->name)->toBe('New Name');
});

test('users can delete a favorites list', function () {
    $user = User::factory()->create();
    $list = FavoriteList::create(['user_id' => $user->id, 'name' => 'Doomed List']);

    Livewire::actingAs($user)
        ->test('pages::favorites.index')
        ->call('deleteList', $list->id);

    expect(FavoriteList::find($list->id))->toBeNull();
});

test('users can add a crossword to a list', function () {
    $user = User::factory()->create();
    $list = FavoriteList::create(['user_id' => $user->id, 'name' => 'Best Puzzles']);
    $crossword = Crossword::factory()->published()->create();
    CrosswordLike::create(['user_id' => $user->id, 'crossword_id' => $crossword->id]);

    Livewire::actingAs($user)
        ->test('pages::favorites.index')
        ->call('openAddToListModal', $crossword->id)
        ->call('addToList', $list->id);

    expect($list->crosswords()->where('crossword_id', $crossword->id)->exists())->toBeTrue();
});

test('adding the same crossword to a list twice does not duplicate', function () {
    $user = User::factory()->create();
    $list = FavoriteList::create(['user_id' => $user->id, 'name' => 'My List']);
    $crossword = Crossword::factory()->published()->create();
    CrosswordLike::create(['user_id' => $user->id, 'crossword_id' => $crossword->id]);

    $component = Livewire::actingAs($user)->test('pages::favorites.index');

    $component->call('openAddToListModal', $crossword->id)->call('addToList', $list->id);
    $component->call('openAddToListModal', $crossword->id)->call('addToList', $list->id);

    expect($list->crosswords()->count())->toBe(1);
});

test('users can remove a crossword from a list', function () {
    $user = User::factory()->create();
    $list = FavoriteList::create(['user_id' => $user->id, 'name' => 'My List']);
    $crossword = Crossword::factory()->published()->create();
    $list->crosswords()->attach($crossword);

    Livewire::actingAs($user)
        ->test('pages::favorites.index', ['list' => (string) $list->id])
        ->call('removeFromList', $crossword->id);

    expect($list->crosswords()->where('crossword_id', $crossword->id)->exists())->toBeFalse();
});

test('users can unlike a crossword from the favorites page', function () {
    $user = User::factory()->create();
    $crossword = Crossword::factory()->published()->create();
    CrosswordLike::create(['user_id' => $user->id, 'crossword_id' => $crossword->id]);

    Livewire::actingAs($user)
        ->test('pages::favorites.index', ['list' => 'liked'])
        ->call('removeFromList', $crossword->id);

    expect(CrosswordLike::where('user_id', $user->id)->where('crossword_id', $crossword->id)->exists())->toBeFalse();
});

test('viewing a custom list shows its crosswords', function () {
    $user = User::factory()->create();
    $list = FavoriteList::create(['user_id' => $user->id, 'name' => 'Custom List']);
    $crossword = Crossword::factory()->published()->create(['title' => 'Listed Puzzle']);
    $list->crosswords()->attach($crossword);

    Livewire::actingAs($user)
        ->test('pages::favorites.index', ['list' => (string) $list->id])
        ->assertSee('Listed Puzzle');
});

test('deleting a list does not delete the crosswords', function () {
    $user = User::factory()->create();
    $list = FavoriteList::create(['user_id' => $user->id, 'name' => 'Temp List']);
    $crossword = Crossword::factory()->published()->create();
    $list->crosswords()->attach($crossword);

    Livewire::actingAs($user)
        ->test('pages::favorites.index')
        ->call('deleteList', $list->id);

    expect(Crossword::find($crossword->id))->not->toBeNull();
});

test('deleting a user cascades to their favorite lists', function () {
    $user = User::factory()->create();
    $list = FavoriteList::create(['user_id' => $user->id, 'name' => 'My Faves']);
    $crossword = Crossword::factory()->published()->create();
    $list->crosswords()->attach($crossword);

    $user->delete();

    expect(FavoriteList::find($list->id))->toBeNull();
});

test('favorites sidebar navigation is visible', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('favorites.index'))
        ->assertSee('Favorites');
});

test('cannot access another users favorite list', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $list = FavoriteList::create(['user_id' => $other->id, 'name' => 'Private List']);

    $component = Livewire::actingAs($user)
        ->test('pages::favorites.index', ['list' => (string) $list->id]);

    expect($component->get('activeListCrosswords'))->toBeEmpty();
});

test('list name max length is validated', function () {
    Livewire::actingAs(User::factory()->create())
        ->test('pages::favorites.index')
        ->set('newListName', str_repeat('a', 101))
        ->call('createList')
        ->assertHasErrors(['newListName' => 'max']);
});
