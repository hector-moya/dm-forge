<?php

namespace App\Livewire\Campaigns;

use App\Models\Campaign;
use Livewire\Component;

class CampaignShow extends Component
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

    public function render()
    {
        return view('livewire.campaigns.campaign-show');
    }
}
