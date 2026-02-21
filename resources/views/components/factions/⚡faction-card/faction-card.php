<?php

use App\Models\Faction;
use Livewire\Component;

new class extends Component
{
    public Faction $faction;
    public $history;

    public function mount(Faction $faction): void
    {
        abort_unless($faction->campaign->user_id === auth()->id(), 403);

        $this->faction = $faction;
        $this->history = $faction
                ? $faction->worldEvents()->with(['location', 'gameSession'])->orderByDesc('occurred_at')->limit(20)->get()
                : collect();
    }
};
