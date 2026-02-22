<?php

namespace App\Livewire\Forms;

use App\Models\Campaign;
use App\Models\WorldRule;
use Livewire\Attributes\Validate;
use Livewire\Form;

class WorldRuleForm extends Form
{
    #[Validate(['required', 'string', 'min:2', 'max:50'])]
    public string $name = '';

    #[Validate(['required', 'string', 'min:10'])]
    public string $description = '';

    #[Validate(['nullable', 'string'])]
    public string $dmNotes = '';

    public function setWorldRule(WorldRule $worldRule): void
    {
        $this->name = $worldRule->name ?? '';
        $this->description = $worldRule->description ?? '';
        $this->dmNotes = $worldRule->dm_notes ?? '';
    }

    public function store(Campaign $campaign): void
    {
        $this->validate();

        $worldRule = WorldRule::create([
            'user_id' => auth()->id(),
            'name' => $this->name,
            'description' => $this->description,
            'dm_notes' => $this->dmNotes,
        ]);

        $campaign->worldRules()->attach($worldRule->id);

        $this->resetForm();
    }

    public function update(WorldRule $worldRule): void
    {
        $this->validate();

        $worldRule->update([
            'name' => $this->name,
            'description' => $this->description,
            'dm_notes' => $this->dmNotes,
        ]);

        $this->resetForm();
    }

    public function destroy(WorldRule $worldRule): void
    {
        $worldRule->delete();
    }

    public function resetForm(): void
    {
        $this->reset(['name', 'description', 'dmNotes']);
    }
}
