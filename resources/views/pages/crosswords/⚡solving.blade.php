<?php

use App\Models\Crossword;
use App\Models\CrosswordLike;
use App\Models\PuzzleAttempt;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Solving')] class extends Component {
    public string $search = '';

    #[Computed]
    public function attempts()
    {
        return Auth::user()
            ->puzzleAttempts()
            ->with('crossword.user')
            ->latest('updated_at')
            ->get();
    }

    #[Computed]
    public function availablePuzzles()
    {
        $attemptedIds = Auth::user()
            ->puzzleAttempts()
            ->pluck('crossword_id');

        return Crossword::where('is_published', true)
            ->where('user_id', '!=', Auth::id())
            ->whereNotIn('id', $attemptedIds)
            ->when($this->search, fn ($query) => $query->where('title', 'like', "%{$this->search}%"))
            ->with('user')
            ->withCount('likes')
            ->latest()
            ->get();
    }

    /** @return array<int, bool> */
    #[Computed]
    public function likedIds(): array
    {
        return Auth::user()
            ->crosswordLikes()
            ->pluck('crossword_id')
            ->flip()
            ->map(fn () => true)
            ->all();
    }

    public function toggleLike(int $crosswordId): void
    {
        $like = CrosswordLike::where('user_id', Auth::id())
            ->where('crossword_id', $crosswordId)
            ->first();

        if ($like) {
            $like->delete();
        } else {
            CrosswordLike::create([
                'user_id' => Auth::id(),
                'crossword_id' => $crosswordId,
            ]);
        }

        unset($this->likedIds, $this->availablePuzzles);
    }

    public function startSolving(int $crosswordId): void
    {
        $crossword = Crossword::findOrFail($crosswordId);
        $this->authorize('solve', $crossword);

        $this->redirect(route('crosswords.solver', $crossword), navigate: true);
    }

    public function removeAttempt(int $attemptId): void
    {
        $attempt = PuzzleAttempt::findOrFail($attemptId);

        if ($attempt->user_id !== Auth::id()) {
            abort(403);
        }

        $attempt->delete();
    }
}
?>

<div class="space-y-8">
    {{-- In Progress --}}
    <div class="space-y-4">
        <flux:heading size="xl">{{ __('Solving') }}</flux:heading>

        @if($this->attempts->isEmpty())
            <div class="flex flex-col items-center justify-center rounded-xl border border-dashed border-zinc-300 py-12 dark:border-zinc-600">
                <flux:icon name="puzzle-piece" class="mb-4 size-12 text-zinc-400" />
                <flux:heading size="lg" class="mb-2">{{ __('No puzzles in progress') }}</flux:heading>
                <flux:text>{{ __('Browse published puzzles below and start solving.') }}</flux:text>
            </div>
        @else
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($this->attempts as $attempt)
                    <div
                        wire:key="attempt-{{ $attempt->id }}"
                        class="group relative rounded-xl border border-zinc-200 p-4 transition-colors hover:border-zinc-400 dark:border-zinc-700 dark:hover:border-zinc-500"
                    >
                        <a href="{{ route('crosswords.solver', $attempt->crossword) }}" wire:navigate class="block">
                            {{-- Mini grid thumbnail --}}
                            <div class="mb-3 flex justify-center">
                                <div
                                    class="inline-grid gap-px rounded border border-zinc-200 bg-zinc-200 p-px dark:border-zinc-600 dark:bg-zinc-600"
                                    style="grid-template-columns: repeat({{ $attempt->crossword->width }}, minmax(0, 1fr)); width: {{ min($attempt->crossword->width * 8, 120) }}px;"
                                >
                                    @for($row = 0; $row < $attempt->crossword->height; $row++)
                                        @for($col = 0; $col < $attempt->crossword->width; $col++)
                                            <div class="{{ $attempt->crossword->grid[$row][$col] === null ? 'invisible' : (($attempt->crossword->grid[$row][$col] ?? 0) === '#' ? 'bg-zinc-800 dark:bg-zinc-300' : 'bg-white dark:bg-zinc-800') }}" style="aspect-ratio: 1;"></div>
                                        @endfor
                                    @endfor
                                </div>
                            </div>

                            <flux:heading size="sm" class="truncate">{{ $attempt->crossword->title ?: __('Untitled Puzzle') }}</flux:heading>
                            <flux:text size="sm" class="mt-1">
                                {{ __('by :author', ['author' => $attempt->crossword->user->name ?? __('Unknown')]) }}
                                &middot;
                                {{ $attempt->crossword->width }}&times;{{ $attempt->crossword->height }}
                            </flux:text>
                            <div class="mt-1.5 flex items-center gap-2">
                                @if($attempt->is_completed)
                                    <flux:badge size="sm" variant="solid" color="green">{{ __('Completed') }}</flux:badge>
                                @else
                                    <flux:badge size="sm" variant="solid" color="sky">{{ __('In Progress') }}</flux:badge>
                                @endif
                                <flux:text size="sm" class="text-zinc-400">{{ $attempt->updated_at->diffForHumans() }}</flux:text>
                            </div>
                        </a>

                        <div class="absolute top-2 right-2 opacity-0 transition-opacity group-hover:opacity-100">
                            <flux:dropdown position="bottom" align="end">
                                <flux:button variant="ghost" size="sm" icon="ellipsis-vertical" />
                                <flux:menu>
                                    <flux:menu.item icon="trash" variant="danger" wire:click="removeAttempt({{ $attempt->id }})" wire:confirm="{{ __('Remove this puzzle from your solving list?') }}">
                                        {{ __('Remove') }}
                                    </flux:menu.item>
                                </flux:menu>
                            </flux:dropdown>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Browse Published Puzzles --}}
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <flux:heading size="lg">{{ __('Browse Puzzles') }}</flux:heading>
            <flux:input
                size="sm"
                icon="magnifying-glass"
                placeholder="{{ __('Search puzzles...') }}"
                wire:model.live.debounce.300ms="search"
                class="max-w-48"
            />
        </div>

        @if($this->availablePuzzles->isEmpty())
            <div class="rounded-xl border border-dashed border-zinc-300 px-6 py-8 text-center dark:border-zinc-600">
                <flux:text class="text-zinc-400">
                    @if($search)
                        {{ __('No puzzles match your search.') }}
                    @else
                        {{ __('No new puzzles available right now.') }}
                    @endif
                </flux:text>
            </div>
        @else
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($this->availablePuzzles as $crossword)
                    <div
                        wire:key="browse-{{ $crossword->id }}"
                        class="group rounded-xl border border-zinc-200 p-4 transition-colors hover:border-zinc-400 dark:border-zinc-700 dark:hover:border-zinc-500"
                    >
                        {{-- Mini grid thumbnail --}}
                        <div class="mb-3 flex justify-center">
                            <div
                                class="inline-grid gap-px rounded border border-zinc-200 bg-zinc-200 p-px dark:border-zinc-600 dark:bg-zinc-600"
                                style="grid-template-columns: repeat({{ $crossword->width }}, minmax(0, 1fr)); width: {{ min($crossword->width * 8, 120) }}px;"
                            >
                                @for($row = 0; $row < $crossword->height; $row++)
                                    @for($col = 0; $col < $crossword->width; $col++)
                                        <div class="{{ $crossword->grid[$row][$col] === null ? 'invisible' : (($crossword->grid[$row][$col] ?? 0) === '#' ? 'bg-zinc-800 dark:bg-zinc-300' : 'bg-white dark:bg-zinc-800') }}" style="aspect-ratio: 1;"></div>
                                    @endfor
                                @endfor
                            </div>
                        </div>

                        <flux:heading size="sm" class="truncate">{{ $crossword->title ?: __('Untitled Puzzle') }}</flux:heading>
                        <flux:text size="sm" class="mt-1">
                            {{ __('by :author', ['author' => $crossword->user->name ?? __('Unknown')]) }}
                            &middot;
                            {{ $crossword->width }}&times;{{ $crossword->height }}
                        </flux:text>

                        <div class="mt-3 flex items-center justify-between">
                            <flux:button size="sm" variant="primary" wire:click="startSolving({{ $crossword->id }})">
                                {{ __('Start Solving') }}
                            </flux:button>
                            <button
                                wire:click.stop="toggleLike({{ $crossword->id }})"
                                class="flex items-center gap-1 rounded-lg px-2 py-1 text-xs transition-colors {{ isset($this->likedIds[$crossword->id]) ? 'text-red-500' : 'text-zinc-400 hover:text-red-400' }}"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 24 24" fill="{{ isset($this->likedIds[$crossword->id]) ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                                </svg>
                                <span>{{ $crossword->likes_count }}</span>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
