<?php

namespace App\Livewire;

use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $campaigns = auth()->user()->campaigns()
            ->withCount(['characters', 'gameSessions'])
            ->latest()
            ->get();

        return view('livewire.dashboard', [
            'campaigns' => $campaigns,
        ]);
    }
}
