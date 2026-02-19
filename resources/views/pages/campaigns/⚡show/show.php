<?php

use App\Models\Campaign;
use Livewire\Component;

new class extends Component
{
    public Campaign $campaign;

    public function mount(Campaign $campaign): void
    {
        abort_unless($campaign->user_id === auth()->id(), 403);

        $this->campaign = $campaign->load([
            'factions',
            'locations',
            'npcs.faction',
            'npcs.location',
            'characters',
            'gameSessions',
        ]);
    }

    public function render(): \Illuminate\View\View
    {
        return view('pages.campaigns.⚡show.show')
            ->title($this->campaign->name);
    }
};
