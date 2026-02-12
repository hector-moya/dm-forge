<?php

namespace App\Livewire\Campaigns;

use App\Ai\Agents\CampaignWizardAgent;
use App\Models\Campaign;
use Flux;
use Livewire\Component;

class CampaignWizard extends Component
{
    public int $currentStep = 1;

    public int $totalSteps = 6;

    // Step 1: Basics
    public string $name = '';

    public string $premise = '';

    public string $theme_tone = '';

    // Step 2: World Building
    public string $lore = '';

    public string $world_rules = '';

    // Step 3: Factions
    /** @var array<int, array{name: string, description: string, alignment: string, goals: string}> */
    public array $factions = [];

    public string $factionName = '';

    public string $factionDescription = '';

    public string $factionAlignment = '';

    public string $factionGoals = '';

    // Step 4: Locations
    /** @var array<int, array{name: string, description: string, region: string}> */
    public array $locations = [];

    public string $locationName = '';

    public string $locationDescription = '';

    public string $locationRegion = '';

    // Step 5: Characters
    /** @var array<int, array{name: string, player_name: string, class: string, level: int}> */
    public array $characters = [];

    public string $characterName = '';

    public string $characterPlayerName = '';

    public string $characterClass = '';

    public int $characterLevel = 1;

    // AI generation state
    public bool $generating = false;

    // ── Navigation ────────────────────────────────────────────────────

    public function nextStep(): void
    {
        if ($this->currentStep === 1) {
            $this->validate([
                'name' => ['required', 'string', 'max:255'],
                'premise' => ['nullable', 'string', 'max:5000'],
                'theme_tone' => ['nullable', 'string', 'max:255'],
            ]);
        }

        if ($this->currentStep < $this->totalSteps) {
            $this->currentStep++;
        }
    }

    public function previousStep(): void
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    public function goToStep(int $step): void
    {
        if ($step >= 1 && $step <= $this->totalSteps && $step <= $this->currentStep) {
            $this->currentStep = $step;
        }
    }

    // ── AI Suggestions ────────────────────────────────────────────────

    public function suggestWorld(): void
    {
        $this->generating = true;

        try {
            $agent = new CampaignWizardAgent('world', [
                'name' => $this->name,
                'premise' => $this->premise,
                'theme_tone' => $this->theme_tone,
            ]);

            $response = $agent->prompt('Generate world lore and rules for this campaign.');

            $this->lore = $response['lore'] ?? '';
            $this->world_rules = $response['world_rules'] ?? '';

            Flux::toast(__('World details generated!'));
        } catch (\Throwable $e) {
            Flux::toast(__('Generation failed: ').$e->getMessage());
        }

        $this->generating = false;
    }

    public function suggestFactions(): void
    {
        $this->generating = true;

        try {
            $agent = new CampaignWizardAgent('factions', [
                'name' => $this->name,
                'premise' => $this->premise,
                'theme_tone' => $this->theme_tone,
                'lore' => $this->lore,
            ]);

            $response = $agent->prompt('Suggest factions for this campaign.');

            foreach ($response['factions'] ?? [] as $faction) {
                $this->factions[] = [
                    'name' => $faction['name'],
                    'description' => $faction['description'],
                    'alignment' => $faction['alignment'],
                    'goals' => $faction['goals'],
                ];
            }

            Flux::toast(__('Factions suggested!'));
        } catch (\Throwable $e) {
            Flux::toast(__('Generation failed: ').$e->getMessage());
        }

        $this->generating = false;
    }

    public function suggestLocations(): void
    {
        $this->generating = true;

        try {
            $agent = new CampaignWizardAgent('locations', [
                'name' => $this->name,
                'premise' => $this->premise,
                'theme_tone' => $this->theme_tone,
                'lore' => $this->lore,
            ]);

            $response = $agent->prompt('Suggest key locations for this campaign.');

            foreach ($response['locations'] ?? [] as $location) {
                $this->locations[] = [
                    'name' => $location['name'],
                    'description' => $location['description'],
                    'region' => $location['region'],
                ];
            }

            Flux::toast(__('Locations suggested!'));
        } catch (\Throwable $e) {
            Flux::toast(__('Generation failed: ').$e->getMessage());
        }

        $this->generating = false;
    }

    // ── Faction CRUD ──────────────────────────────────────────────────

    public function addFaction(): void
    {
        $this->validate([
            'factionName' => ['required', 'string', 'max:255'],
            'factionDescription' => ['nullable', 'string', 'max:5000'],
            'factionAlignment' => ['nullable', 'string', 'max:100'],
            'factionGoals' => ['nullable', 'string', 'max:5000'],
        ]);

        $this->factions[] = [
            'name' => $this->factionName,
            'description' => $this->factionDescription,
            'alignment' => $this->factionAlignment,
            'goals' => $this->factionGoals,
        ];

        $this->factionName = '';
        $this->factionDescription = '';
        $this->factionAlignment = '';
        $this->factionGoals = '';
    }

    public function removeFaction(int $index): void
    {
        unset($this->factions[$index]);
        $this->factions = array_values($this->factions);
    }

    // ── Location CRUD ─────────────────────────────────────────────────

    public function addLocation(): void
    {
        $this->validate([
            'locationName' => ['required', 'string', 'max:255'],
            'locationDescription' => ['nullable', 'string', 'max:5000'],
            'locationRegion' => ['nullable', 'string', 'max:255'],
        ]);

        $this->locations[] = [
            'name' => $this->locationName,
            'description' => $this->locationDescription,
            'region' => $this->locationRegion,
        ];

        $this->locationName = '';
        $this->locationDescription = '';
        $this->locationRegion = '';
    }

    public function removeLocation(int $index): void
    {
        unset($this->locations[$index]);
        $this->locations = array_values($this->locations);
    }

    // ── Character CRUD ────────────────────────────────────────────────

    public function addCharacter(): void
    {
        $this->validate([
            'characterName' => ['required', 'string', 'max:255'],
            'characterPlayerName' => ['nullable', 'string', 'max:255'],
            'characterClass' => ['nullable', 'string', 'max:100'],
            'characterLevel' => ['required', 'integer', 'min:1', 'max:20'],
        ]);

        $this->characters[] = [
            'name' => $this->characterName,
            'player_name' => $this->characterPlayerName,
            'class' => $this->characterClass,
            'level' => $this->characterLevel,
        ];

        $this->characterName = '';
        $this->characterPlayerName = '';
        $this->characterClass = '';
        $this->characterLevel = 1;
    }

    public function removeCharacter(int $index): void
    {
        unset($this->characters[$index]);
        $this->characters = array_values($this->characters);
    }

    // ── Final: Create Campaign ────────────────────────────────────────

    public function createCampaign(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $campaign = auth()->user()->campaigns()->create([
            'name' => $this->name,
            'premise' => $this->premise ?: null,
            'lore' => $this->lore ?: null,
            'theme_tone' => $this->theme_tone ?: null,
            'world_rules' => $this->world_rules ?: null,
            'status' => 'draft',
        ]);

        foreach ($this->factions as $faction) {
            $campaign->factions()->create([
                'name' => $faction['name'],
                'description' => $faction['description'] ?: null,
                'alignment' => $faction['alignment'] ?: null,
                'goals' => $faction['goals'] ?: null,
            ]);
        }

        foreach ($this->locations as $location) {
            $campaign->locations()->create([
                'name' => $location['name'],
                'description' => $location['description'] ?: null,
                'region' => $location['region'] ?: null,
            ]);
        }

        foreach ($this->characters as $character) {
            $campaign->characters()->create([
                'name' => $character['name'],
                'player_name' => $character['player_name'] ?: null,
                'class' => $character['class'] ?: null,
                'level' => $character['level'],
                'hp_max' => 10,
                'hp_current' => 10,
                'armor_class' => 10,
            ]);
        }

        Flux::toast(__('Campaign created successfully!'));

        $this->redirect(route('campaigns.show', $campaign), navigate: true);
    }

    public function render()
    {
        return view('livewire.campaigns.campaign-wizard')
            ->title(__('Campaign Wizard'));
    }
}
