<?php

use App\Livewire\Campaigns\CampaignCreate;
use App\Livewire\Campaigns\CampaignEdit;
use App\Livewire\Campaigns\CampaignShow;
use App\Livewire\Campaigns\CampaignWizard;
use App\Livewire\Campaigns\FactionManager;
use App\Livewire\Campaigns\LocationManager;
use App\Livewire\Campaigns\NpcManager;
use App\Livewire\Campaigns\WorldTimeline;
use App\Livewire\Characters\AlignmentCompass;
use App\Livewire\Characters\CharacterForm;
use App\Livewire\Characters\CharacterIndex;
use App\Livewire\Dashboard;
use App\Livewire\Library\LootLibrary;
use App\Livewire\Library\MonsterLibrary;
use App\Livewire\Sessions\CombatTracker;
use App\Livewire\Sessions\SessionBuilder;
use App\Livewire\Sessions\SessionIndex;
use App\Livewire\Sessions\SessionRecap;
use App\Livewire\Sessions\SessionRunner;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/docs', function () {
    return view('docs');
})->name('docs');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', Dashboard::class)->name('dashboard');

    // Campaigns
    Route::get('campaigns/wizard', CampaignWizard::class)->name('campaigns.wizard');
    Route::get('campaigns/create', CampaignCreate::class)->name('campaigns.create');
    Route::get('campaigns/{campaign}', CampaignShow::class)->name('campaigns.show');
    Route::get('campaigns/{campaign}/edit', CampaignEdit::class)->name('campaigns.edit');
    Route::get('campaigns/{campaign}/timeline', WorldTimeline::class)->name('campaigns.timeline');
    Route::get('campaigns/{campaign}/factions', FactionManager::class)->name('campaigns.factions');
    Route::get('campaigns/{campaign}/locations', LocationManager::class)->name('campaigns.locations');
    Route::get('campaigns/{campaign}/npcs', NpcManager::class)->name('campaigns.npcs');

    // Characters
    Route::get('campaigns/{campaign}/characters', CharacterIndex::class)->name('campaigns.characters');
    Route::get('campaigns/{campaign}/characters/create', CharacterForm::class)->name('characters.create');
    Route::get('campaigns/{campaign}/characters/{character}/edit', CharacterForm::class)->name('characters.edit');
    Route::get('characters/{character}/alignment', AlignmentCompass::class)->name('characters.alignment');

    // Library
    Route::get('library/monsters', MonsterLibrary::class)->name('library.monsters');
    Route::get('library/loot', LootLibrary::class)->name('library.loot');

    // Sessions
    Route::get('campaigns/{campaign}/sessions', SessionIndex::class)->name('campaigns.sessions');
    Route::get('campaigns/{campaign}/sessions/create', SessionBuilder::class)->name('sessions.create');
    Route::get('sessions/{session}/edit', SessionBuilder::class)->name('sessions.edit');
    Route::get('sessions/{session}/run', SessionRunner::class)->name('sessions.run');
    Route::get('sessions/{session}/combat/{encounter}', CombatTracker::class)->name('sessions.combat');
    Route::get('sessions/{session}/recap', SessionRecap::class)->name('sessions.recap');
});

require __DIR__.'/settings.php';
