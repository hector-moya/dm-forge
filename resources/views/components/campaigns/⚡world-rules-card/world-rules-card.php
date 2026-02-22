<?php

use App\Models\Campaign;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public Campaign $campaign;

    public function mount(Campaign $campaign): void
    {
        $this->campaign = $campaign;
    }

    #[Computed]
    public function campaignWorldRules(): Collection
    {
        return $this->campaign->worldRules()->get();
    }
};
