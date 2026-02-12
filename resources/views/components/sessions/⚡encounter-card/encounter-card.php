<?php

use App\Models\Encounter;
use Flux;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    public Encounter $encounter;

    public ?int $sceneId = null;

    public bool $showForm = false;

    public bool $showMonsterForm = false;

    public bool $showLootForm = false;

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

        Flux::toast(__('Encounter updated successfully'));
        $this->showForm = false;
        $this->dispatch('$refresh');
    }

    public function delete(): void
    {
        $this->encounter->delete();
        Flux::toast(__('Encounter deleted successfully'));
        $this->dispatch('$refresh');
    }

    public function deleteMonster(int $monsterId): void
    {
        $this->encounter->monsters()->where('id', $monsterId)->delete();
        Flux::toast(__('Monster removed successfully'));
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
