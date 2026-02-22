<?php

namespace App\Livewire\Forms;

use App\Models\Campaign;
use App\Models\SpecialMechanic;
use Livewire\Attributes\Validate;
use Livewire\Form;

class SpecialMechanicForm extends Form
{
    #[Validate(['required', 'string', 'min:2', 'max:50'])]
    public string $name = '';

    #[Validate(['required', 'string', 'min:10'])]
    public string $description = '';

    #[Validate(['nullable', 'string'])]
    public string $dmNotes = '';

    public function setSpecialMechanic(SpecialMechanic $specialMechanic): void
    {
        $this->name = $specialMechanic->name ?? '';
        $this->description = $specialMechanic->description ?? '';
        $this->dmNotes = $specialMechanic->dm_notes ?? '';
    }

    public function store(Campaign $campaign): void
    {
        $this->validate();

        $specialMechanic = SpecialMechanic::create([
            'user_id' => auth()->id(),
            'name' => $this->name,
            'description' => $this->description,
            'dm_notes' => $this->dmNotes,
        ]);

        $campaign->specialMechanics()->attach($specialMechanic->id);

        $this->resetForm();
    }

    public function update(SpecialMechanic $specialMechanic): void
    {
        $this->validate();

        $specialMechanic->update([
            'name' => $this->name,
            'description' => $this->description,
            'dm_notes' => $this->dmNotes,
        ]);

        $this->resetForm();
    }

    public function destroy(SpecialMechanic $specialMechanic): void
    {
        $specialMechanic->delete();
    }

    public function resetForm(): void
    {
        $this->reset(['name', 'description', 'dmNotes']);
    }
}
