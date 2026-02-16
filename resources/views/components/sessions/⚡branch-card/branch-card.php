<?php

use App\Models\BranchOption;
use Livewire\Component;

new class extends Component
{
    public BranchOption $branch;

    public ?int $sceneId = null;

    public bool $showForm = false;

    public bool $showConsequenceForm = false;

    public string $label = '';

    public string $description = '';

    public ?int $destinationSceneId = null;

    public function mount(): void
    {
        $this->sceneId = $this->branch->scene_id;
        $this->destinationSceneId = $this->branch->destination_scene_id;
    }

    public function openForm(): void
    {
        $this->showForm = true;
        $this->label = $this->branch->label;
        $this->description = $this->branch->description ?? '';
        $this->destinationSceneId = $this->branch->destination_scene_id;
    }

    public function save(): void
    {
        $this->validate([
            'label' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'destinationSceneId' => ['nullable', 'integer', 'exists:scenes,id'],
        ]);

        $this->branch->update([
            'label' => $this->label,
            'description' => $this->description ?: null,
            'destination_scene_id' => $this->destinationSceneId ?: null,
        ]);

        \Flux::toast(__('Branch option updated successfully'));
        $this->showForm = false;
        $this->dispatch('$refresh');
    }

    public function delete(): void
    {
        $this->branch->delete();
        \Flux::toast(__('Branch option deleted successfully'));
        $this->dispatch('$refresh');
    }

    public function deleteConsequence(int $consequenceId): void
    {
        $this->branch->consequences()->where('id', $consequenceId)->delete();
        \Flux::toast(__('Consequence removed successfully'));
        $this->dispatch('$refresh');
    }

    public function openConsequenceForm(): void
    {
        $this->showConsequenceForm = true;
    }
};
