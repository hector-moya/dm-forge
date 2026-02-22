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

    // Pending rules to attach on creation
    public array $specialMechanicRules = [];

    public string $pendingRuleName = '';

    public string $pendingRuleDescription = '';

    public string $pendingRuleNotes = '';

    public function addRule(): void
    {
        $this->validate([
            'pendingRuleName' => ['required', 'string', 'max:255'],
            'pendingRuleDescription' => ['nullable', 'string', 'max:5000'],
            'pendingRuleNotes' => ['nullable', 'string', 'max:5000'],
        ]);

        $this->specialMechanicRules[] = [
            'name' => $this->pendingRuleName,
            'description' => $this->pendingRuleDescription ?: null,
            'notes' => $this->pendingRuleNotes ?: null,
        ];

        $this->pendingRuleName = '';
        $this->pendingRuleDescription = '';
        $this->pendingRuleNotes = '';
    }

    public function removeRule(int $index): void
    {
        array_splice($this->specialMechanicRules, $index, 1);
    }

    public function setSpecialMechanic(SpecialMechanic $specialMechanic): void
    {
        $this->name = $specialMechanic->name ?? '';
        $this->description = $specialMechanic->description ?? '';
        $this->dmNotes = $specialMechanic->dm_notes ?? '';
    }

    public function store(Campaign $campaign): SpecialMechanic
    {
        $this->validate();

        $specialMechanic = SpecialMechanic::create([
            'user_id' => auth()->id(),
            'name' => $this->name,
            'description' => $this->description,
            'dm_notes' => $this->dmNotes,
        ]);

        $campaign->specialMechanics()->attach($specialMechanic->id);

        foreach ($this->specialMechanicRules as $ruleData) {
            $specialMechanic->rules()->create($ruleData);
        }

        $this->resetForm();

        return $specialMechanic;
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
        $this->reset(['name', 'description', 'dmNotes', 'specialMechanicRules', 'pendingRuleName', 'pendingRuleDescription', 'pendingRuleNotes']);
    }
}
