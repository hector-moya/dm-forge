<?php

namespace App\Livewire\Campaigns;

use App\Ai\Agents\NpcGenerator;
use App\Models\Campaign;
use App\Models\Npc;
use Flux;
use Illuminate\Support\Collection;
use Livewire\Component;

class NpcManager extends Component
{
    public Campaign $campaign;

    public string $search = '';

    public string $factionFilter = '';

    public string $aliveFilter = 'all';

    // Detail flyout
    public ?int $viewingNpcId = null;

    // Inline form
    public bool $showForm = false;

    public ?int $editingNpcId = null;

    public string $npcName = '';

    public string $npcRole = '';

    public string $npcDescription = '';

    public string $npcPersonality = '';

    public string $npcMotivation = '';

    public string $npcVoiceDescription = '';

    public string $npcSpeechPatterns = '';

    public string $npcCatchphrases = '';

    public ?int $npcFactionId = null;

    public ?int $npcLocationId = null;

    public bool $npcIsAlive = true;

    // Generator
    public bool $showGenerateModal = false;

    public string $generateContext = '';

    public bool $generating = false;

    public function mount(Campaign $campaign): void
    {
        abort_unless($campaign->user_id === auth()->id(), 403);

        $this->campaign = $campaign;
    }

    public function viewNpc(int $id): void
    {
        $this->viewingNpcId = $id;
        $this->modal('view-npc')->show();
    }

    public function getViewingNpcProperty(): ?Npc
    {
        if (! $this->viewingNpcId) {
            return null;
        }

        return $this->campaign->npcs()
            ->with(['faction', 'location'])
            ->find($this->viewingNpcId);
    }

    // ── CRUD ──────────────────────────────────────────────────────────

    public function openForm(?int $npcId = null): void
    {
        $this->resetForm();
        $this->showForm = true;

        if ($npcId) {
            $npc = $this->campaign->npcs()->findOrFail($npcId);
            $this->editingNpcId = $npc->id;
            $this->npcName = $npc->name;
            $this->npcRole = $npc->role ?? '';
            $this->npcDescription = $npc->description ?? '';
            $this->npcPersonality = $npc->personality ?? '';
            $this->npcMotivation = $npc->motivation ?? '';
            $this->npcVoiceDescription = $npc->voice_description ?? '';
            $this->npcSpeechPatterns = $npc->speech_patterns ?? '';
            $this->npcCatchphrases = $npc->catchphrases ? implode("\n", $npc->catchphrases) : '';
            $this->npcFactionId = $npc->faction_id;
            $this->npcLocationId = $npc->location_id;
            $this->npcIsAlive = $npc->is_alive;
        }
    }

    public function save(): void
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
            Flux::toast(__('NPC updated.'));
        } else {
            $this->campaign->npcs()->create($data);
            Flux::toast(__('NPC created.'));
        }

        $this->resetForm();
    }

    public function delete(int $npcId): void
    {
        $this->campaign->npcs()->findOrFail($npcId)->delete();

        if ($this->viewingNpcId === $npcId) {
            $this->viewingNpcId = null;
        }

        Flux::toast(__('NPC deleted.'));
    }

    private function resetForm(): void
    {
        $this->showForm = false;
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

    // ── Generator ─────────────────────────────────────────────────────

    public function openGenerateModal(): void
    {
        $this->showGenerateModal = true;
        $this->generateContext = '';
        $this->generating = false;
    }

    public function generate(): void
    {
        $this->generating = true;

        try {
            $generator = new NpcGenerator($this->campaign);
            $prompt = 'Generate a unique NPC for this campaign.';
            if ($this->generateContext) {
                $prompt .= " Context: {$this->generateContext}";
            }

            $response = $generator->prompt($prompt);

            $this->showGenerateModal = false;

            $this->resetForm();
            $this->showForm = true;
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
            Flux::toast(__('Generation failed: ').$e->getMessage());
        }

        $this->generating = false;
    }

    // ── Render ─────────────────────────────────────────────────────────

    public function getNpcs(): Collection
    {
        $query = $this->campaign->npcs()->with(['faction', 'location']);

        if ($this->search !== '') {
            $query->where('name', 'like', "%{$this->search}%");
        }

        if ($this->factionFilter !== '') {
            $query->where('faction_id', $this->factionFilter);
        }

        if ($this->aliveFilter === 'alive') {
            $query->where('is_alive', true);
        } elseif ($this->aliveFilter === 'dead') {
            $query->where('is_alive', false);
        }

        return $query->orderBy('name')->get();
    }

    public function render()
    {
        $viewingNpc = $this->viewingNpc;

        return view('livewire.campaigns.npc-manager', [
            'npcs' => $this->getNpcs(),
            'viewingNpc' => $viewingNpc,
            'factions' => $this->campaign->factions()->orderBy('name')->get(),
            'locations' => $this->campaign->locations()->orderBy('name')->get(),
            'history' => $viewingNpc
                ? $viewingNpc->worldEvents()->with(['faction', 'location', 'gameSession'])->orderByDesc('occurred_at')->limit(20)->get()
                : collect(),
        ])->title(__('NPCs').' — '.$this->campaign->name);
    }
}
