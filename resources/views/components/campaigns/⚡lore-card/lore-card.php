<?php

use App\Models\Campaign;
use Livewire\Attributes\Computed;
use Illuminate\Support\Collection;
use Livewire\Component;

new class extends Component
{
    public Campaign $campaign;
    public ?Collection $campaignLore = null;

    public function mount(Campaign $campaign): void
    {
        $this->campaign = $campaign;
    }

    #[Computed]
    public function campaignLores(): Collection
    {
        return $this->campaignLore  = $this->campaign->lores()->get();
    }
};
