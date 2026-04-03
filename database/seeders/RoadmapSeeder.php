<?php

namespace Database\Seeders;

use App\Models\RoadmapItem;
use Illuminate\Database\Seeder;

class RoadmapSeeder extends Seeder
{
    /**
     * Seed the roadmap with upcoming features, fixes, and improvements.
     */
    public function run(): void
    {
        $items = [
            // In Progress
            [
                'title' => 'Solve Timer & Statistics',
                'description' => 'Track solve times per puzzle and display personal statistics — average times by difficulty, fastest solves, and a history of completed puzzles.',
                'type' => 'feature',
                'status' => 'in_progress',
                'sort_order' => 1,
                'target_date' => '2026-04-30',
            ],
            [
                'title' => 'PDF Export',
                'description' => 'Export puzzles as print-ready PDFs with grid, clues, and optional solution page for newspaper-style distribution.',
                'type' => 'feature',
                'status' => 'in_progress',
                'sort_order' => 2,
                'target_date' => '2026-04-30',
            ],

            // Planned — Constructor Features
            [
                'title' => 'Autofill Grid Assistance',
                'description' => 'AI-assisted grid filling that suggests words fitting the current pattern, scored by letter frequency and crossword-worthiness.',
                'type' => 'feature',
                'status' => 'planned',
                'sort_order' => 1,
                'target_date' => '2026-05-31',
            ],
            [
                'title' => 'Clue Quality Suggestions',
                'description' => 'Surface clue quality indicators — flag overly short clues, missing wordplay in cryptic clues, and suggest alternatives from the clue library.',
                'type' => 'improvement',
                'status' => 'planned',
                'sort_order' => 2,
                'target_date' => '2026-06-30',
            ],
            [
                'title' => 'Rebus / Multi-Letter Cell Support',
                'description' => 'Allow cells to contain multiple letters or symbols for rebus-style puzzles, with full import/export support.',
                'type' => 'feature',
                'status' => 'planned',
                'sort_order' => 3,
                'target_date' => '2026-06-30',
            ],
            [
                'title' => 'Puzzle Templates Library',
                'description' => 'Pre-built grid templates for common patterns — standard 15x15, 21x21 Sunday, themed shapes — to jumpstart construction.',
                'type' => 'feature',
                'status' => 'planned',
                'sort_order' => 4,
                'target_date' => '2026-05-31',
            ],
            [
                'title' => 'Constructor Analytics Dashboard',
                'description' => 'Show constructors how their published puzzles perform — solve count, average solve time, cell-difficulty heatmaps, and completion rates.',
                'type' => 'feature',
                'status' => 'planned',
                'sort_order' => 5,
                'target_date' => '2026-07-31',
            ],
            [
                'title' => 'Collaborative Puzzle Construction',
                'description' => 'Invite another constructor to co-edit a puzzle in real-time with shared grid, clues, and chat.',
                'type' => 'feature',
                'status' => 'planned',
                'sort_order' => 6,
                'target_date' => '2026-09-30',
            ],

            // Planned — Solver Features
            [
                'title' => 'Difficulty Rating System',
                'description' => 'Algorithm-based difficulty ratings for each puzzle so solvers can find puzzles matching their skill level.',
                'type' => 'feature',
                'status' => 'planned',
                'sort_order' => 7,
                'target_date' => '2026-06-30',
            ],
            [
                'title' => 'Solver Streaks & Achievements',
                'description' => 'Track daily solving streaks and award achievement badges for milestones — first solve, 7-day streak, 100 puzzles completed.',
                'type' => 'feature',
                'status' => 'planned',
                'sort_order' => 8,
                'target_date' => '2026-07-31',
            ],
            [
                'title' => 'Pencil Mode',
                'description' => 'Toggle pencil mode to enter tentative answers in a lighter style, distinguishing guesses from confident answers.',
                'type' => 'feature',
                'status' => 'planned',
                'sort_order' => 9,
                'target_date' => '2026-05-31',
            ],
            [
                'title' => 'Multiplayer Collaborative Solving',
                'description' => 'Solve puzzles together in real-time with up to 4 players — see each other\'s cursors and filled letters with color-coded attribution.',
                'type' => 'feature',
                'status' => 'planned',
                'sort_order' => 10,
                'target_date' => '2026-09-30',
            ],
            [
                'title' => 'Offline Puzzle Downloads',
                'description' => 'Download puzzles for offline solving on mobile devices, with automatic progress sync when back online.',
                'type' => 'feature',
                'status' => 'planned',
                'sort_order' => 11,
                'target_date' => '2026-08-31',
            ],

            // Planned — Platform & Community
            [
                'title' => 'Constructor Profiles & Following',
                'description' => 'Public constructor profiles with a portfolio of published puzzles and a follow system so solvers get notified of new puzzles.',
                'type' => 'feature',
                'status' => 'planned',
                'sort_order' => 12,
                'target_date' => '2026-07-31',
            ],
            [
                'title' => 'Puzzle Comments & Ratings',
                'description' => 'Let solvers leave comments and star ratings on puzzles after completing them, giving constructors community feedback.',
                'type' => 'feature',
                'status' => 'planned',
                'sort_order' => 13,
                'target_date' => '2026-06-30',
            ],
            [
                'title' => 'Accessibility Improvements',
                'description' => 'Screen reader support, high-contrast mode, colorblind-friendly cell indicators, and full keyboard-only navigation for visually impaired users.',
                'type' => 'improvement',
                'status' => 'planned',
                'sort_order' => 14,
                'target_date' => '2026-07-31',
            ],
            [
                'title' => 'Meta / Contest Puzzle Support',
                'description' => 'Support meta puzzles with answer submission, leaderboards, and timed contest windows for competitive solving events.',
                'type' => 'feature',
                'status' => 'planned',
                'sort_order' => 15,
                'target_date' => '2026-08-31',
            ],

            // Planned — Fixes & Improvements
            [
                'title' => 'Mobile Solving Experience Polish',
                'description' => 'Improve touch targets, add swipe gestures for clue navigation, and optimize the on-screen keyboard interaction for phone-sized screens.',
                'type' => 'improvement',
                'status' => 'planned',
                'sort_order' => 16,
                'target_date' => '2026-05-31',
            ],
            [
                'title' => 'Puzzle Search & Discovery Filters',
                'description' => 'Advanced search with filters for grid size, difficulty, constructor, date published, and puzzle type (standard, cryptic, barred).',
                'type' => 'improvement',
                'status' => 'planned',
                'sort_order' => 17,
                'target_date' => '2026-06-30',
            ],
            [
                'title' => 'Import Format Auto-Detection Improvements',
                'description' => 'Better handling of edge cases in PUZ and JPZ imports — malformed checksums, non-standard encodings, and partial metadata.',
                'type' => 'fix',
                'status' => 'planned',
                'sort_order' => 18,
                'target_date' => '2026-05-31',
            ],
            [
                'title' => 'Choose type of crossword when creating (rectangular, diamond, freestyle, etc)',
                'description' => 'Lets the user choose the type of crossword they want to create, with appropriate measurements and templates.',
                'type' => 'improvement',
                'status' => 'planned',
                'sort_order' => 19,
                'target_date' => '2026-05-31',
            ],
        ];

        foreach ($items as $item) {
            RoadmapItem::updateOrCreate(
                ['title' => $item['title']],
                $item,
            );
        }
    }
}
