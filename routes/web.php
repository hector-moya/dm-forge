<?php

use App\Livewire\Campaigns\CampaignCreate;
use App\Livewire\Campaigns\CampaignEdit;
use App\Livewire\Campaigns\CampaignShow;
use App\Livewire\Characters\AlignmentCompass;
use App\Livewire\Characters\CharacterForm;
use App\Livewire\Characters\CharacterIndex;
use App\Livewire\Dashboard;
use App\Livewire\Sessions\SessionBuilder;
use App\Livewire\Sessions\SessionIndex;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', Dashboard::class)->name('dashboard');

    // Campaigns
    Route::get('campaigns/create', CampaignCreate::class)->name('campaigns.create');
    Route::get('campaigns/{campaign}', CampaignShow::class)->name('campaigns.show');
    Route::get('campaigns/{campaign}/edit', CampaignEdit::class)->name('campaigns.edit');

    // Characters
    Route::get('campaigns/{campaign}/characters', CharacterIndex::class)->name('campaigns.characters');
    Route::get('campaigns/{campaign}/characters/create', CharacterForm::class)->name('characters.create');
    Route::get('campaigns/{campaign}/characters/{character}/edit', CharacterForm::class)->name('characters.edit');
    Route::get('characters/{character}/alignment', AlignmentCompass::class)->name('characters.alignment');

    // Sessions
    Route::get('campaigns/{campaign}/sessions', SessionIndex::class)->name('campaigns.sessions');
    Route::get('campaigns/{campaign}/sessions/create', SessionBuilder::class)->name('sessions.create');
    Route::get('sessions/{session}/edit', SessionBuilder::class)->name('sessions.edit');
});

require __DIR__.'/settings.php';
