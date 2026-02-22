<?php

use App\Models\SpecialMechanic;
use Livewire\Component;

new class extends Component
{
    public SpecialMechanic $specialMechanic;

    public function mount(?int $specialMechanicId): void
    {
        $specialMechanic = SpecialMechanic::with('rules')->findOrFail($specialMechanicId);
        abort_unless($specialMechanic->user_id === auth()->id(), 403);

        $this->specialMechanic = $specialMechanic;
    }
};
