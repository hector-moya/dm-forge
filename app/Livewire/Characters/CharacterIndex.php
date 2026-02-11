<?php

namespace App\Livewire\Characters;

use App\Models\Campaign;
use Livewire\Component;

class CharacterIndex extends Component
{
    public Campaign $campaign;

    public function mount(Campaign $campaign): void
    {
        abort_unless($campaign->user_id === auth()->id(), 403);

        $this->campaign = $campaign;
    }

    public function render()
    {
        return view('livewire.characters.character-index', [
            'characters' => $this->campaign->characters()
                ->withCount('alignmentEvents')
                ->orderBy('name')
                ->get(),
        ])->title(__('Characters') . ' — ' . $this->campaign->name);
    }
}
