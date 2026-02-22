<?php

use App\Models\WorldRule;
use Livewire\Component;

new class extends Component
{
    public WorldRule $worldRule;

    public function mount(?int $worldRuleId): void
    {
        $worldRule = WorldRule::findOrFail($worldRuleId);
        abort_unless($worldRule->user_id === auth()->id(), 403);

        $this->worldRule = $worldRule;
    }
};
