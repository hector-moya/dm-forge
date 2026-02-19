<?php

use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;


new #[Title('Dashboard')] class extends Component
{
    #[Computed]
    public function campaigns(): Collection
    {
        return auth()->user()->campaigns()
            ->withCount(['characters', 'gameSessions'])
            ->latest()
            ->get();
    }
};
