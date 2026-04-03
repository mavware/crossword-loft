<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    Route::livewire('crosswords', 'pages::crosswords.index')->name('crosswords.index');
    Route::livewire('solving', 'pages::crosswords.solving')->name('crosswords.solving');
    Route::livewire('crosswords/{crossword}', 'pages::crosswords.editor')->name('crosswords.editor');
    Route::livewire('crosswords/{crossword}/solve', 'pages::crosswords.solver')->name('crosswords.solver');

    Route::livewire('clues', 'pages::clues.index')->name('clues.index');

    Route::livewire('favorites', 'pages::favorites.index')->name('favorites.index');

    Route::livewire('roadmap', 'pages::roadmap.index')->name('roadmap.index');
});

require __DIR__.'/settings.php';
