<?php

namespace App\Livewire\Forms;

use App\Models\Campaign;
use Livewire\Attributes\Validate;
use App\Models\Lore;
use Livewire\Form;

class LoreForm extends Form
{
    #[Validate('required', 'string', 'min:2|max:50')]
    public string $name = '';

    #[Validate('required', 'string', 'min:10')]
    public string $description = '';

    #[Validate('nullable', 'string')]
    public string $dmNotes = '';

    public function setLore(Lore $lore): void
    {
        $this->name = $lore->name ?? '';
        $this->description = $lore->description ?? '';
        $this->dmNotes = $lore->dm_notes ?? '';
    }

    public function store(Campaign $campaign): void
    {
        $this->validate();

        // Lore has a many-to-many relationship with Campaign, so we need to create the Lore first and then attach it to the campaign
        $lore = Lore::create([
            'user_id' => auth()->id(),
            'name' => $this->name,
            'description' => $this->description,
            'dm_notes' => $this->dmNotes,
        ]);

        $campaign->lores()->attach($lore->id);

        $this->resetForm();
    }

    public function update(Lore $lore): void
    {
        $this->validate();

        $lore->update([
            'name' => $this->name,
            'description' => $this->description,
            'dm_notes' => $this->dmNotes,
        ]);

        $this->resetForm();
    }

    public function destroy(Lore $lore): void
    {
        $lore->delete();
    }

    public function resetForm(): void
    {
        $this->reset(['name', 'description', 'dmNotes']);
    }

}
