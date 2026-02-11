<?php

namespace App\Livewire\Campaigns;

use App\Models\Campaign;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Create Campaign')]
class CampaignCreate extends Component
{
    public string $name = '';
    public string $premise = '';
    public string $lore = '';
    public string $theme_tone = '';
    public string $world_rules = '';
    public string $special_mechanics = '';

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'premise' => ['nullable', 'string', 'max:5000'],
            'lore' => ['nullable', 'string', 'max:10000'],
            'theme_tone' => ['nullable', 'string', 'max:255'],
            'world_rules' => ['nullable', 'string', 'max:10000'],
            'special_mechanics' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function save(): void
    {
        $validated = $this->validate();

        $mechanicsArray = null;
        if (! empty($validated['special_mechanics'])) {
            $decoded = json_decode($validated['special_mechanics'], true);
            $mechanicsArray = $decoded !== null ? $decoded : [$validated['special_mechanics']];
        }

        $campaign = auth()->user()->campaigns()->create([
            'name' => $validated['name'],
            'premise' => $validated['premise'] ?: null,
            'lore' => $validated['lore'] ?: null,
            'theme_tone' => $validated['theme_tone'] ?: null,
            'world_rules' => $validated['world_rules'] ?: null,
            'special_mechanics' => $mechanicsArray,
            'status' => 'draft',
        ]);

        session()->flash('message', 'Campaign created successfully.');

        $this->redirect(route('campaigns.show', $campaign), navigate: true);
    }

    public function render()
    {
        return view('livewire.campaigns.campaign-create');
    }
}
