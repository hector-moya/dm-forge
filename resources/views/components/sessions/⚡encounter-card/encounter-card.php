<?php

use App\Models\Encounter;
use App\Models\Npc;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    public Encounter $encounter;

    public ?int $sceneId = null;

    public bool $showForm = false;

    public bool $showMonsterForm = false;

    public bool $showLootForm = false;

    public bool $showNpcForm = false;

    public ?int $selectedNpcId = null;

    public int $npcHpMax = 10;

    public int $npcArmorClass = 10;

    public string $name = '';

    public string $description = '';

    public string $environment = '';

    public function mount(): void
    {
        $this->sceneId = $this->encounter->scene_id;
    }

    public function openForm(): void
    {
        $this->showForm = true;
        $this->name = $this->encounter->name;
        $this->description = $this->encounter->description ?? '';
        $this->environment = $this->encounter->environment ?? '';
    }

    public function save(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'environment' => ['nullable', 'string', 'max:255'],
        ]);

        $this->encounter->update([
            'name' => $this->name,
            'description' => $this->description ?: null,
            'environment' => $this->environment ?: null,
        ]);

        \Flux::toast(__('Encounter updated successfully'));
        $this->showForm = false;
        $this->dispatch('$refresh');
    }

    public function delete(): void
    {
        $this->encounter->delete();
        \Flux::toast(__('Encounter deleted successfully'));
        $this->dispatch('$refresh');
    }

    public function deleteMonster(int $monsterId): void
    {
        $this->encounter->monsters()->where('id', $monsterId)->delete();
        \Flux::toast(__('Monster removed successfully'));
        $this->dispatch('$refresh');
    }

    public function openMonsterForm(): void
    {
        $this->showMonsterForm = true;
    }

    public function openLootForm(): void
    {
        $this->showLootForm = true;
    }

    // ── NPC Management ───────────────────────────────────────────────

    public function openNpcForm(): void
    {
        $this->showNpcForm = true;
        $this->selectedNpcId = null;
        $this->npcHpMax = 10;
        $this->npcArmorClass = 10;
    }

    public function addNpcToEncounter(): void
    {
        $this->validate([
            'selectedNpcId' => ['required', 'integer', 'exists:npcs,id'],
            'npcHpMax' => ['required', 'integer', 'min:1'],
            'npcArmorClass' => ['required', 'integer', 'min:1'],
        ]);

        $npc = Npc::findOrFail($this->selectedNpcId);

        $this->encounter->npcs()->create([
            'npc_id' => $npc->id,
            'name' => $npc->name,
            'hp_max' => $this->npcHpMax,
            'armor_class' => $this->npcArmorClass,
            'stats' => $npc->stats,
        ]);

        \Flux::toast(__('NPC added to encounter'));
        $this->showNpcForm = false;
        $this->selectedNpcId = null;
        $this->npcHpMax = 10;
        $this->npcArmorClass = 10;
        $this->dispatch('$refresh');
    }

    public function deleteNpc(int $encounterNpcId): void
    {
        $this->encounter->npcs()->where('id', $encounterNpcId)->delete();
        \Flux::toast(__('NPC removed successfully'));
        $this->dispatch('$refresh');
    }

    #[On('monster-form-closed')]
    public function closeMonsterForm(): void
    {
        $this->showMonsterForm = false;
    }

    #[On('loot-form-closed')]
    public function closeLootForm(): void
    {
        $this->showLootForm = false;
    }
};
