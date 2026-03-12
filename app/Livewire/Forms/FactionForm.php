<?php

namespace App\Livewire\Forms;

use App\Models\Campaign;
use App\Models\Faction;
use Livewire\Attributes\Validate;
use Livewire\Form;

class FactionForm extends Form
{
    #[Validate(['required', 'string', 'max:255'])]
    public string $name = '';

    #[Validate(['nullable', 'string', 'max:5000'])]
    public string $description = '';

    #[Validate(['nullable', 'string', 'max:100'])]
    public string $alignment = '';

    #[Validate(['nullable', 'string', 'max:5000'])]
    public string $goals = '';

    #[Validate(['nullable', 'string', 'max:5000'])]
    public string $resources = '';

    public function setFaction(Faction $faction): void
    {
        $this->name = $faction->name;
        $this->description = $faction->description ?? '';
        $this->alignment = $faction->alignment ?? '';
        $this->goals = $faction->goals ?? '';
        $this->resources = $faction->resources ?? '';
    }

    public function store(Campaign $campaign): Faction
    {
        $this->validate();

        /** @var Faction $faction */
        $faction = $campaign->factions()->create([
            'name' => $this->name,
            'description' => $this->description ?: null,
            'alignment' => $this->alignment ?: null,
            'goals' => $this->goals ?: null,
            'resources' => $this->resources ?: null,
        ]);

        $this->resetForm();

        return $faction;
    }

    public function update(Faction $faction): void
    {
        $this->validate();

        $faction->update([
            'name' => $this->name,
            'description' => $this->description ?: null,
            'alignment' => $this->alignment ?: null,
            'goals' => $this->goals ?: null,
            'resources' => $this->resources ?: null,
        ]);

        $this->resetForm();
    }

    public function destroy(Faction $faction): void
    {
        $faction->delete();
    }

    public function resetForm(): void
    {
        $this->reset(['name', 'description', 'alignment', 'goals', 'resources']);
    }
}
