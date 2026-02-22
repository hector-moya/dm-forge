<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/docs', function () {
    return view('docs');
})->name('docs');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('dashboard', 'pages::dashboard')->name('dashboard');

    // Campaigns
    Route::livewire('campaigns/wizard', 'pages::campaigns.wizard')->name('campaigns.wizard');
    Route::livewire('campaigns/create', 'pages::campaigns.create')->name('campaigns.create');
    Route::livewire('campaigns/{campaign}', 'pages::campaigns.show')->name('campaigns.show');
    Route::livewire('campaigns/{campaign}/edit', 'pages::campaigns.edit')->name('campaigns.edit');
    Route::livewire('campaigns/{campaign}/lore', 'pages::campaigns.lore-manager')->name('campaigns.lore');
    Route::livewire('campaigns/{campaign}/world-rules', 'pages::campaigns.world-rules-manager')->name('campaigns.world-rules');
    Route::livewire('campaigns/{campaign}/special-mechanics', 'pages::campaigns.special-mechanics-manager')->name('campaigns.special-mechanics');
    Route::livewire('campaigns/{campaign}/timeline', 'pages::campaigns.world-timeline')->name('campaigns.timeline');
    Route::livewire('campaigns/{campaign}/factions', 'pages::campaigns.faction-manager')->name('campaigns.factions');
    Route::livewire('campaigns/{campaign}/locations', 'pages::campaigns.location-manager')->name('campaigns.locations');
    Route::livewire('campaigns/{campaign}/npcs', 'pages::campaigns.npc-manager')->name('campaigns.npcs');

    // Characters
    Route::livewire('campaigns/{campaign}/characters', 'pages::characters.index')->name('campaigns.characters');
    Route::livewire('campaigns/{campaign}/characters/create', 'pages::characters.form')->name('characters.create');
    Route::livewire('campaigns/{campaign}/characters/{character}/edit', 'pages::characters.form')->name('characters.edit');
    Route::livewire('characters/{character}/alignment', 'pages::characters.alignment-compass')->name('characters.alignment');

    // Library
    Route::livewire('library/monsters', 'pages::library.monsters')->name('library.monsters');
    Route::livewire('library/loot', 'pages::library.loot')->name('library.loot');

    // Sessions
    Route::livewire('campaigns/{campaign}/sessions', 'pages::sessions.index')->name('campaigns.sessions');
    Route::livewire('campaigns/{campaign}/sessions/create', 'pages::sessions.builder')->name('sessions.create');
    Route::livewire('sessions/{session}/edit', 'pages::sessions.builder')->name('sessions.edit');
    Route::livewire('sessions/{session}/run', 'pages::sessions.runner')->name('sessions.run');
    Route::livewire('sessions/{session}/combat/{encounter}', 'pages::sessions.combat-tracker')->name('sessions.combat');
    Route::livewire('sessions/{session}/recap', 'pages::sessions.recap')->name('sessions.recap');
});

require __DIR__.'/settings.php';
