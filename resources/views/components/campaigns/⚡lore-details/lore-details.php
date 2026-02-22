<?php

use App\Models\Lore;
use Livewire\Component;

new class extends Component
{
    public Lore $lore;

    public function mount(?int $loreId): void
    {
        $lore = Lore::findOrFail($loreId);
        abort_unless($lore->user_id === auth()->id(), 403);

        $this->lore = $lore;
    }
};
