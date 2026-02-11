<?php

namespace App\Livewire\Characters;

use App\Models\Campaign;
use App\Models\Character;
use Livewire\Component;

class CharacterForm extends Component
{
    public Campaign $campaign;

    public ?Character $character = null;

    public string $name = '';

    public string $player_name = '';

    public string $characterClass = '';

    public int $level = 1;

    public int $hp_max = 10;

    public ?int $hp_current = null;

    public int $armor_class = 10;

    public string $alignment_label = '';

    public string $notes = '';

    public function mount(Campaign $campaign, ?Character $character = null): void
    {
        abort_unless($campaign->user_id === auth()->id(), 403);

        $this->campaign = $campaign;

        if ($character && $character->exists) {
            abort_unless($character->campaign_id === $campaign->id, 403);

            $this->character = $character;
            $this->name = $character->name;
            $this->player_name = $character->player_name ?? '';
            $this->characterClass = $character->class ?? '';
            $this->level = $character->level;
            $this->hp_max = $character->hp_max;
            $this->hp_current = $character->hp_current;
            $this->armor_class = $character->armor_class;
            $this->alignment_label = $character->alignment_label ?? '';
            $this->notes = $character->notes ?? '';
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'player_name' => ['nullable', 'string', 'max:255'],
            'characterClass' => ['nullable', 'string', 'max:255'],
            'level' => ['required', 'integer', 'min:1', 'max:30'],
            'hp_max' => ['required', 'integer', 'min:1'],
            'hp_current' => ['nullable', 'integer', 'min:0'],
            'armor_class' => ['required', 'integer', 'min:1'],
            'alignment_label' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function save(): void
    {
        $validated = $this->validate();

        $data = [
            'name' => $validated['name'],
            'player_name' => $validated['player_name'] ?: null,
            'class' => $validated['characterClass'] ?: null,
            'level' => $validated['level'],
            'hp_max' => $validated['hp_max'],
            'hp_current' => $validated['hp_current'] ?? $validated['hp_max'],
            'armor_class' => $validated['armor_class'],
            'alignment_label' => $validated['alignment_label'] ?: null,
            'notes' => $validated['notes'] ?: null,
        ];

        if ($this->character) {
            $this->character->update($data);
            session()->flash('message', 'Character updated.');
        } else {
            $this->character = $this->campaign->characters()->create($data);
            session()->flash('message', 'Character created.');
        }

        $this->redirect(route('campaigns.characters', $this->campaign), navigate: true);
    }

    public function deleteCharacter(): void
    {
        if ($this->character) {
            $this->character->delete();
            session()->flash('message', 'Character deleted.');
        }

        $this->redirect(route('campaigns.characters', $this->campaign), navigate: true);
    }

    public function render()
    {
        $title = ($this->character?->exists ? __('Edit').' '.$this->character->name : __('New Character')).' — '.$this->campaign->name;

        return view('livewire.characters.character-form')
            ->title($title);
    }
}
