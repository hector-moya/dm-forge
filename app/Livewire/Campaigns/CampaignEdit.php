<?php

namespace App\Livewire\Campaigns;

use App\Ai\Agents\FactionGenerator;
use App\Ai\Agents\LocationGenerator;
use App\Ai\Agents\NpcGenerator;
use App\Models\Campaign;
use App\Models\Faction;
use App\Models\Location;
use App\Models\Npc;
use Flux;
use Livewire\Component;

class CampaignEdit extends Component
{
    public Campaign $campaign;

    // Campaign fields
    public string $name = '';

    public string $premise = '';

    public string $lore = '';

    public string $theme_tone = '';

    public string $world_rules = '';

    public string $special_mechanics = '';

    public string $status = 'draft';

    // Faction inline form
    public bool $showFactionForm = false;

    public ?int $editingFactionId = null;

    public string $factionName = '';

    public string $factionDescription = '';

    public string $factionAlignment = '';

    public string $factionGoals = '';

    public string $factionResources = '';

    // Location inline form
    public bool $showLocationForm = false;

    public ?int $editingLocationId = null;

    public string $locationName = '';

    public string $locationDescription = '';

    public string $locationRegion = '';

    public ?int $locationParentId = null;

    // NPC inline form
    public bool $showNpcForm = false;

    public ?int $editingNpcId = null;

    public string $npcName = '';

    public string $npcRole = '';

    public string $npcDescription = '';

    public string $npcPersonality = '';

    public string $npcMotivation = '';

    public ?int $npcFactionId = null;

    public ?int $npcLocationId = null;

    public bool $npcIsAlive = true;

    public string $npcVoiceDescription = '';

    public string $npcSpeechPatterns = '';

    public string $npcCatchphrases = '';

    // Faction Generator
    public bool $showGenerateFactionModal = false;

    public string $generateFactionContext = '';

    public bool $generatingFaction = false;

    // Location Generator
    public bool $showGenerateLocationModal = false;

    public string $generateLocationContext = '';

    public bool $generatingLocation = false;

    // NPC Generator
    public bool $showGenerateNpcModal = false;

    public string $generateNpcContext = '';

    public bool $generatingNpc = false;

    // Delete confirmation
    public bool $showDeleteModal = false;

    public function mount(Campaign $campaign): void
    {
        abort_unless($campaign->user_id === auth()->id(), 403);

        $this->campaign = $campaign;
        $this->name = $campaign->name;
        $this->premise = $campaign->premise ?? '';
        $this->lore = $campaign->lore ?? '';
        $this->theme_tone = $campaign->theme_tone ?? '';
        $this->world_rules = $campaign->world_rules ?? '';
        $this->special_mechanics = $campaign->special_mechanics ? json_encode($campaign->special_mechanics, JSON_PRETTY_PRINT) : '';
        $this->status = $campaign->status ?? 'draft';
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'premise' => ['nullable', 'string', 'max:5000'],
            'lore' => ['nullable', 'string', 'max:10000'],
            'theme_tone' => ['nullable', 'string', 'max:255'],
            'world_rules' => ['nullable', 'string', 'max:10000'],
            'special_mechanics' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', 'in:draft,active,archived'],
        ];
    }

    // ── Campaign CRUD ─────────────────────────────────────────────────

    public function save(): void
    {
        $validated = $this->validate();

        $mechanicsArray = null;
        if (! empty($validated['special_mechanics'])) {
            $decoded = json_decode($validated['special_mechanics'], true);
            $mechanicsArray = $decoded !== null ? $decoded : [$validated['special_mechanics']];
        }

        $this->campaign->update([
            'name' => $validated['name'],
            'premise' => $validated['premise'] ?: null,
            'lore' => $validated['lore'] ?: null,
            'theme_tone' => $validated['theme_tone'] ?: null,
            'world_rules' => $validated['world_rules'] ?: null,
            'special_mechanics' => $mechanicsArray,
            'status' => $validated['status'],
        ]);

        session()->flash('message', 'Campaign updated successfully.');

        $this->redirect(route('campaigns.show', $this->campaign), navigate: true);
    }

    public function confirmDelete(): void
    {
        $this->showDeleteModal = true;
    }

    public function deleteCampaign(): void
    {
        abort_unless($this->campaign->user_id === auth()->id(), 403);

        $this->campaign->delete();

        session()->flash('message', 'Campaign deleted.');

        $this->redirect(route('dashboard'), navigate: true);
    }

    // ── Faction CRUD ──────────────────────────────────────────────────

    public function openFactionForm(?int $factionId = null): void
    {
        $this->resetFactionForm();
        $this->showFactionForm = true;

        if ($factionId) {
            $faction = $this->campaign->factions()->findOrFail($factionId);
            $this->editingFactionId = $faction->id;
            $this->factionName = $faction->name;
            $this->factionDescription = $faction->description ?? '';
            $this->factionAlignment = $faction->alignment ?? '';
            $this->factionGoals = $faction->goals ?? '';
            $this->factionResources = $faction->resources ?? '';
        }
    }

    public function saveFaction(): void
    {
        $this->validate([
            'factionName' => ['required', 'string', 'max:255'],
            'factionDescription' => ['nullable', 'string', 'max:5000'],
            'factionAlignment' => ['nullable', 'string', 'max:100'],
            'factionGoals' => ['nullable', 'string', 'max:5000'],
            'factionResources' => ['nullable', 'string', 'max:5000'],
        ]);

        $data = [
            'name' => $this->factionName,
            'description' => $this->factionDescription ?: null,
            'alignment' => $this->factionAlignment ?: null,
            'goals' => $this->factionGoals ?: null,
            'resources' => $this->factionResources ?: null,
        ];

        if ($this->editingFactionId) {
            $this->campaign->factions()->findOrFail($this->editingFactionId)->update($data);
        } else {
            $this->campaign->factions()->create($data);
        }

        $this->resetFactionForm();
        $this->campaign->refresh();
    }

    public function deleteFaction(int $factionId): void
    {
        $this->campaign->factions()->findOrFail($factionId)->delete();
        $this->campaign->refresh();
    }

    private function resetFactionForm(): void
    {
        $this->showFactionForm = false;
        $this->editingFactionId = null;
        $this->factionName = '';
        $this->factionDescription = '';
        $this->factionAlignment = '';
        $this->factionGoals = '';
        $this->factionResources = '';
        $this->resetValidation(['factionName', 'factionDescription', 'factionAlignment', 'factionGoals', 'factionResources']);
    }

    // ── Location CRUD ─────────────────────────────────────────────────

    public function openLocationForm(?int $locationId = null): void
    {
        $this->resetLocationForm();
        $this->showLocationForm = true;

        if ($locationId) {
            $location = $this->campaign->locations()->findOrFail($locationId);
            $this->editingLocationId = $location->id;
            $this->locationName = $location->name;
            $this->locationDescription = $location->description ?? '';
            $this->locationRegion = $location->region ?? '';
            $this->locationParentId = $location->parent_location_id;
        }
    }

    public function saveLocation(): void
    {
        $this->validate([
            'locationName' => ['required', 'string', 'max:255'],
            'locationDescription' => ['nullable', 'string', 'max:5000'],
            'locationRegion' => ['nullable', 'string', 'max:255'],
            'locationParentId' => ['nullable', 'exists:locations,id'],
        ]);

        $data = [
            'name' => $this->locationName,
            'description' => $this->locationDescription ?: null,
            'region' => $this->locationRegion ?: null,
            'parent_location_id' => $this->locationParentId,
        ];

        if ($this->editingLocationId) {
            $this->campaign->locations()->findOrFail($this->editingLocationId)->update($data);
        } else {
            $this->campaign->locations()->create($data);
        }

        $this->resetLocationForm();
        $this->campaign->refresh();
    }

    public function deleteLocation(int $locationId): void
    {
        $this->campaign->locations()->findOrFail($locationId)->delete();
        $this->campaign->refresh();
    }

    private function resetLocationForm(): void
    {
        $this->showLocationForm = false;
        $this->editingLocationId = null;
        $this->locationName = '';
        $this->locationDescription = '';
        $this->locationRegion = '';
        $this->locationParentId = null;
        $this->resetValidation(['locationName', 'locationDescription', 'locationRegion', 'locationParentId']);
    }

    // ── NPC CRUD ──────────────────────────────────────────────────────

    public function openNpcForm(?int $npcId = null): void
    {
        $this->resetNpcForm();
        $this->showNpcForm = true;

        if ($npcId) {
            $npc = $this->campaign->npcs()->findOrFail($npcId);
            $this->editingNpcId = $npc->id;
            $this->npcName = $npc->name;
            $this->npcRole = $npc->role ?? '';
            $this->npcDescription = $npc->description ?? '';
            $this->npcPersonality = $npc->personality ?? '';
            $this->npcMotivation = $npc->motivation ?? '';
            $this->npcFactionId = $npc->faction_id;
            $this->npcLocationId = $npc->location_id;
            $this->npcIsAlive = $npc->is_alive;
            $this->npcVoiceDescription = $npc->voice_description ?? '';
            $this->npcSpeechPatterns = $npc->speech_patterns ?? '';
            $this->npcCatchphrases = $npc->catchphrases ? implode("\n", $npc->catchphrases) : '';
        }
    }

    public function saveNpc(): void
    {
        $this->validate([
            'npcName' => ['required', 'string', 'max:255'],
            'npcRole' => ['nullable', 'string', 'max:255'],
            'npcDescription' => ['nullable', 'string', 'max:5000'],
            'npcPersonality' => ['nullable', 'string', 'max:2000'],
            'npcMotivation' => ['nullable', 'string', 'max:2000'],
            'npcVoiceDescription' => ['nullable', 'string', 'max:2000'],
            'npcSpeechPatterns' => ['nullable', 'string', 'max:2000'],
            'npcCatchphrases' => ['nullable', 'string', 'max:2000'],
            'npcFactionId' => ['nullable', 'exists:factions,id'],
            'npcLocationId' => ['nullable', 'exists:locations,id'],
            'npcIsAlive' => ['boolean'],
        ]);

        $catchphrases = $this->npcCatchphrases
            ? array_values(array_filter(array_map('trim', explode("\n", $this->npcCatchphrases))))
            : null;

        $data = [
            'name' => $this->npcName,
            'role' => $this->npcRole ?: null,
            'description' => $this->npcDescription ?: null,
            'personality' => $this->npcPersonality ?: null,
            'motivation' => $this->npcMotivation ?: null,
            'voice_description' => $this->npcVoiceDescription ?: null,
            'speech_patterns' => $this->npcSpeechPatterns ?: null,
            'catchphrases' => $catchphrases,
            'faction_id' => $this->npcFactionId,
            'location_id' => $this->npcLocationId,
            'is_alive' => $this->npcIsAlive,
        ];

        if ($this->editingNpcId) {
            $this->campaign->npcs()->findOrFail($this->editingNpcId)->update($data);
        } else {
            $this->campaign->npcs()->create($data);
        }

        $this->resetNpcForm();
        $this->campaign->refresh();
    }

    public function deleteNpc(int $npcId): void
    {
        $this->campaign->npcs()->findOrFail($npcId)->delete();
        $this->campaign->refresh();
    }

    private function resetNpcForm(): void
    {
        $this->showNpcForm = false;
        $this->editingNpcId = null;
        $this->npcName = '';
        $this->npcRole = '';
        $this->npcDescription = '';
        $this->npcPersonality = '';
        $this->npcMotivation = '';
        $this->npcVoiceDescription = '';
        $this->npcSpeechPatterns = '';
        $this->npcCatchphrases = '';
        $this->npcFactionId = null;
        $this->npcLocationId = null;
        $this->npcIsAlive = true;
        $this->resetValidation();
    }

    // ── Faction Generator ──────────────────────────────────────────────

    public function openGenerateFactionModal(): void
    {
        $this->showGenerateFactionModal = true;
        $this->generateFactionContext = '';
        $this->generatingFaction = false;
    }

    public function generateFaction(): void
    {
        $this->generatingFaction = true;

        try {
            $generator = new FactionGenerator($this->campaign);
            $prompt = 'Generate a unique faction for this campaign.';
            if ($this->generateFactionContext) {
                $prompt .= " Context: {$this->generateFactionContext}";
            }

            $response = $generator->prompt($prompt);

            $this->showGenerateFactionModal = false;

            $this->resetFactionForm();
            $this->showFactionForm = true;
            $this->factionName = $response['name'] ?? '';
            $this->factionDescription = $response['description'] ?? '';
            $this->factionAlignment = $response['alignment'] ?? '';
            $this->factionGoals = $response['goals'] ?? '';
            $this->factionResources = $response['resources'] ?? '';

            Flux::toast(__('Faction generated! Review and save below.'));
        } catch (\Throwable $e) {
            Flux::toast(__('Faction generation failed: ').$e->getMessage());
        }

        $this->generatingFaction = false;
    }

    // ── Location Generator ────────────────────────────────────────────

    public function openGenerateLocationModal(): void
    {
        $this->showGenerateLocationModal = true;
        $this->generateLocationContext = '';
        $this->generatingLocation = false;
    }

    public function generateLocation(): void
    {
        $this->generatingLocation = true;

        try {
            $generator = new LocationGenerator($this->campaign);
            $prompt = 'Generate a unique location for this campaign.';
            if ($this->generateLocationContext) {
                $prompt .= " Context: {$this->generateLocationContext}";
            }

            $response = $generator->prompt($prompt);

            $this->showGenerateLocationModal = false;

            $this->resetLocationForm();
            $this->showLocationForm = true;
            $this->locationName = $response['name'] ?? '';
            $this->locationDescription = $response['description'] ?? '';
            $this->locationRegion = $response['region'] ?? '';

            if (! empty($response['history'])) {
                $this->locationDescription .= "\n\nHistory: ".$response['history'];
            }

            Flux::toast(__('Location generated! Review and save below.'));
        } catch (\Throwable $e) {
            Flux::toast(__('Location generation failed: ').$e->getMessage());
        }

        $this->generatingLocation = false;
    }

    // ── NPC Generator ─────────────────────────────────────────────────

    public function openGenerateNpcModal(): void
    {
        $this->showGenerateNpcModal = true;
        $this->generateNpcContext = '';
        $this->generatingNpc = false;
    }

    public function generateNpc(): void
    {
        $this->generatingNpc = true;

        try {
            $generator = new NpcGenerator($this->campaign);
            $prompt = 'Generate a unique NPC for this campaign.';
            if ($this->generateNpcContext) {
                $prompt .= " Context: {$this->generateNpcContext}";
            }

            $response = $generator->prompt($prompt);

            $this->showGenerateNpcModal = false;

            $this->resetNpcForm();
            $this->showNpcForm = true;
            $this->npcName = $response['name'] ?? '';
            $this->npcRole = $response['role'] ?? '';
            $this->npcDescription = $response['description'] ?? '';
            $this->npcPersonality = $response['personality'] ?? '';
            $this->npcMotivation = $response['motivation'] ?? '';
            $this->npcVoiceDescription = $response['voice_description'] ?? '';
            $this->npcSpeechPatterns = $response['speech_patterns'] ?? '';
            $this->npcCatchphrases = isset($response['catchphrases']) ? implode("\n", $response['catchphrases']) : '';

            Flux::toast(__('NPC generated! Review and save below.'));
        } catch (\Throwable $e) {
            Flux::toast(__('NPC generation failed: ').$e->getMessage());
        }

        $this->generatingNpc = false;
    }

    public function render()
    {
        return view('livewire.campaigns.campaign-edit', [
            'factions' => $this->campaign->factions()->orderBy('sort_order')->orderBy('name')->get(),
            'locations' => $this->campaign->locations()->orderBy('name')->get(),
            'npcs' => $this->campaign->npcs()->with(['faction', 'location'])->orderBy('name')->get(),
        ])->title(__('Edit').' '.$this->campaign->name);
    }
}
