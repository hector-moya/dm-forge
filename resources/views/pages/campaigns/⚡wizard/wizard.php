<?php

use App\Ai\Agents\CampaignWizardAgent;
use App\Models\Campaign;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Campaign Wizard')] class extends Component
{
    public int $currentStep = 1;

    public int $totalSteps = 7;

    // Step 1: Basics
    public string $name = '';

    public string $premise = '';

    public string $theme_tone = '';

    // Step 2: World Building - Lore
    /** @var array<int, array{name: string, description: string, dm_notes: string}> */
    public array $loreEntries = [];

    public string $loreName = '';

    public string $loreDescription = '';

    public string $loreDmNotes = '';

    // Step 2: World Building - World Rules
    /** @var array<int, array{name: string, description: string, dm_notes: string}> */
    public array $worldRuleEntries = [];

    public string $worldRuleName = '';

    public string $worldRuleDescription = '';

    public string $worldRuleDmNotes = '';

    // Step 2: World Building - Special Mechanics
    /** @var array<int, array{name: string, description: string, dm_notes: string}> */
    public array $specialMechanics = [];

    public string $specialMechanicName = '';

    public string $specialMechanicDescription = '';

    public string $specialMechanicDmNotes = '';

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

    // Step 5: NPCs
    /** @var array<int, array{name: string, role: string, description: string, personality: string}> */
    public array $npcs = [];

    public string $npcName = '';

    public string $npcRole = '';

    public string $npcDescription = '';

    public string $npcPersonality = '';

    // Step 6: Characters
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

            foreach ($response['lore_entries'] ?? [] as $entry) {
                $this->loreEntries[] = [
                    'name' => $entry['name'],
                    'description' => $entry['description'],
                    'dm_notes' => $entry['dm_notes'] ?? '',
                ];
            }

            foreach ($response['world_rule_entries'] ?? [] as $entry) {
                $this->worldRuleEntries[] = [
                    'name' => $entry['name'],
                    'description' => $entry['description'],
                    'dm_notes' => $entry['dm_notes'] ?? '',
                ];
            }

            \Flux::toast(__('World details generated!'));
        } catch (\Throwable $e) {
            \Flux::toast(__('Generation failed: ').$e->getMessage());
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
                'lore' => collect($this->loreEntries)->pluck('name')->implode(', '),
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

            \Flux::toast(__('Factions suggested!'));
        } catch (\Throwable $e) {
            \Flux::toast(__('Generation failed: ').$e->getMessage());
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
                'lore' => collect($this->loreEntries)->pluck('name')->implode(', '),
            ]);

            $response = $agent->prompt('Suggest key locations for this campaign.');

            foreach ($response['locations'] ?? [] as $location) {
                $this->locations[] = [
                    'name' => $location['name'],
                    'description' => $location['description'],
                    'region' => $location['region'],
                ];
            }

            \Flux::toast(__('Locations suggested!'));
        } catch (\Throwable $e) {
            \Flux::toast(__('Generation failed: ').$e->getMessage());
        }

        $this->generating = false;
    }

    public function suggestNpcs(): void
    {
        $this->generating = true;

        try {
            $agent = new CampaignWizardAgent('npcs', [
                'name' => $this->name,
                'premise' => $this->premise,
                'theme_tone' => $this->theme_tone,
                'lore' => collect($this->loreEntries)->pluck('name')->implode(', '),
            ]);

            $response = $agent->prompt('Suggest notable NPCs for this campaign.');

            foreach ($response['npcs'] ?? [] as $npc) {
                $this->npcs[] = [
                    'name' => $npc['name'],
                    'role' => $npc['role'],
                    'description' => $npc['description'],
                    'personality' => $npc['personality'],
                ];
            }

            \Flux::toast(__('NPCs suggested!'));
        } catch (\Throwable $e) {
            \Flux::toast(__('Generation failed: ').$e->getMessage());
        }

        $this->generating = false;
    }

    public function suggestSpecialMechanics(): void
    {
        $this->generating = true;

        try {
            $agent = new CampaignWizardAgent('special_mechanics', [
                'name' => $this->name,
                'premise' => $this->premise,
                'theme_tone' => $this->theme_tone,
            ]);

            $response = $agent->prompt('Suggest special mechanics for this campaign.');

            foreach ($response['special_mechanics'] ?? [] as $mechanic) {
                $this->specialMechanics[] = [
                    'name' => $mechanic['name'],
                    'description' => $mechanic['description'],
                    'dm_notes' => $mechanic['dm_notes'] ?? '',
                ];
            }

            \Flux::toast(__('Special mechanics suggested!'));
        } catch (\Throwable $e) {
            \Flux::toast(__('Generation failed: ').$e->getMessage());
        }

        $this->generating = false;
    }

    // ── Lore CRUD ──────────────────────────────────────────────────────

    public function addLoreEntry(): void
    {
        $this->validate([
            'loreName' => ['required', 'string', 'max:255'],
            'loreDescription' => ['nullable', 'string', 'max:5000'],
            'loreDmNotes' => ['nullable', 'string', 'max:5000'],
        ]);

        $this->loreEntries[] = [
            'name' => $this->loreName,
            'description' => $this->loreDescription,
            'dm_notes' => $this->loreDmNotes,
        ];

        $this->loreName = '';
        $this->loreDescription = '';
        $this->loreDmNotes = '';
    }

    public function removeLoreEntry(int $index): void
    {
        unset($this->loreEntries[$index]);
        $this->loreEntries = array_values($this->loreEntries);
    }

    // ── World Rule CRUD ────────────────────────────────────────────────

    public function addWorldRuleEntry(): void
    {
        $this->validate([
            'worldRuleName' => ['required', 'string', 'max:255'],
            'worldRuleDescription' => ['nullable', 'string', 'max:5000'],
            'worldRuleDmNotes' => ['nullable', 'string', 'max:5000'],
        ]);

        $this->worldRuleEntries[] = [
            'name' => $this->worldRuleName,
            'description' => $this->worldRuleDescription,
            'dm_notes' => $this->worldRuleDmNotes,
        ];

        $this->worldRuleName = '';
        $this->worldRuleDescription = '';
        $this->worldRuleDmNotes = '';
    }

    public function removeWorldRuleEntry(int $index): void
    {
        unset($this->worldRuleEntries[$index]);
        $this->worldRuleEntries = array_values($this->worldRuleEntries);
    }

    // ── Special Mechanic CRUD ──────────────────────────────────────────

    public function addSpecialMechanic(): void
    {
        $this->validate([
            'specialMechanicName' => ['required', 'string', 'max:255'],
            'specialMechanicDescription' => ['nullable', 'string', 'max:5000'],
            'specialMechanicDmNotes' => ['nullable', 'string', 'max:5000'],
        ]);

        $this->specialMechanics[] = [
            'name' => $this->specialMechanicName,
            'description' => $this->specialMechanicDescription,
            'dm_notes' => $this->specialMechanicDmNotes,
        ];

        $this->specialMechanicName = '';
        $this->specialMechanicDescription = '';
        $this->specialMechanicDmNotes = '';
    }

    public function removeSpecialMechanic(int $index): void
    {
        unset($this->specialMechanics[$index]);
        $this->specialMechanics = array_values($this->specialMechanics);
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

    // ── NPC CRUD ───────────────────────────────────────────────────────

    public function addNpc(): void
    {
        $this->validate([
            'npcName' => ['required', 'string', 'max:255'],
            'npcRole' => ['nullable', 'string', 'max:255'],
            'npcDescription' => ['nullable', 'string', 'max:5000'],
            'npcPersonality' => ['nullable', 'string', 'max:2000'],
        ]);

        $this->npcs[] = [
            'name' => $this->npcName,
            'role' => $this->npcRole,
            'description' => $this->npcDescription,
            'personality' => $this->npcPersonality,
        ];

        $this->npcName = '';
        $this->npcRole = '';
        $this->npcDescription = '';
        $this->npcPersonality = '';
    }

    public function removeNpc(int $index): void
    {
        unset($this->npcs[$index]);
        $this->npcs = array_values($this->npcs);
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
            'theme_tone' => $this->theme_tone ?: null,
            'status' => 'draft',
        ]);

        foreach ($this->loreEntries as $entry) {
            $lore = auth()->user()->lores()->create([
                'name' => $entry['name'],
                'description' => $entry['description'] ?: null,
                'dm_notes' => $entry['dm_notes'] ?: null,
            ]);
            $campaign->lores()->attach($lore->id);
        }

        foreach ($this->worldRuleEntries as $entry) {
            $worldRule = auth()->user()->worldRules()->create([
                'name' => $entry['name'],
                'description' => $entry['description'] ?: null,
                'dm_notes' => $entry['dm_notes'] ?: null,
            ]);
            $campaign->worldRules()->attach($worldRule->id);
        }

        foreach ($this->specialMechanics as $mechanic) {
            $specialMechanic = auth()->user()->specialMechanics()->create([
                'name' => $mechanic['name'],
                'description' => $mechanic['description'] ?: null,
                'dm_notes' => $mechanic['dm_notes'] ?: null,
            ]);
            $campaign->specialMechanics()->attach($specialMechanic->id);
        }

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

        foreach ($this->npcs as $npc) {
            $campaign->npcs()->create([
                'name' => $npc['name'],
                'role' => $npc['role'] ?: null,
                'description' => $npc['description'] ?: null,
                'personality' => $npc['personality'] ?: null,
                'is_alive' => true,
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

        \Flux::toast(__('Campaign created successfully!'));

        $this->redirect(route('campaigns.show', $campaign), navigate: true);
    }
};
