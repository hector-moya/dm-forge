<?php

use App\Models\Campaign;
use Livewire\Component;

new class extends Component
{
    public Campaign $campaign;

    public function mount(Campaign $campaign): void
    {
        abort_unless($campaign->user_id === auth()->id(), 403);

        $this->campaign = $campaign;
    }

    public function render(): \Illuminate\View\View
    {
        return view('pages.sessions.⚡index.index', [
            'sessions' => $this->campaign->gameSessions()
                ->withCount(['scenes', 'encounters'])
                ->orderByDesc('session_number')
                ->get(),
        ])->title(__('Sessions').' — '.$this->campaign->name);
    }
};
