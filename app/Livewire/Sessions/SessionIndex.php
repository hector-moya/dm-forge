<?php

namespace App\Livewire\Sessions;

use App\Models\Campaign;
use Livewire\Component;

class SessionIndex extends Component
{
    public Campaign $campaign;

    public function mount(Campaign $campaign): void
    {
        abort_unless($campaign->user_id === auth()->id(), 403);

        $this->campaign = $campaign;
    }

    public function render()
    {
        return view('livewire.sessions.session-index', [
            'sessions' => $this->campaign->gameSessions()
                ->withCount(['scenes', 'encounters'])
                ->orderByDesc('session_number')
                ->get(),
        ])->title(__('Sessions').' — '.$this->campaign->name);
    }
}
