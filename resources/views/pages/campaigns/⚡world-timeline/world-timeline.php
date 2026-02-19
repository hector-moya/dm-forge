<?php

use App\Models\Campaign;
use Livewire\Component;

new class extends Component
{
    public Campaign $campaign;

    // Add event form
    public bool $showEventForm = false;

    public ?int $editingEventId = null;

    public string $eventTitle = '';

    public string $eventDescription = '';

    public string $eventType = 'custom';

    public ?int $eventFactionId = null;

    public ?int $eventLocationId = null;

    public ?int $eventSessionId = null;

    public string $eventOccurredAt = '';

    // Filter
    public string $filterType = '';

    public function mount(Campaign $campaign): void
    {
        abort_unless($campaign->user_id === auth()->id(), 403);

        $this->campaign = $campaign;
        $this->eventOccurredAt = now()->format('Y-m-d\TH:i');
    }

    public function openEventForm(?int $eventId = null): void
    {
        $this->resetEventForm();
        $this->showEventForm = true;

        if ($eventId) {
            $event = $this->campaign->worldEvents()->findOrFail($eventId);
            $this->editingEventId = $event->id;
            $this->eventTitle = $event->title;
            $this->eventDescription = $event->description;
            $this->eventType = $event->event_type;
            $this->eventFactionId = $event->faction_id;
            $this->eventLocationId = $event->location_id;
            $this->eventSessionId = $event->game_session_id;
            $this->eventOccurredAt = $event->occurred_at->format('Y-m-d\TH:i');
        }
    }

    public function saveEvent(): void
    {
        $this->validate([
            'eventTitle' => ['required', 'string', 'max:255'],
            'eventDescription' => ['required', 'string', 'max:5000'],
            'eventType' => ['required', 'in:faction_movement,consequence_resolved,npc_change,territory_change,custom'],
            'eventFactionId' => ['nullable', 'exists:factions,id'],
            'eventLocationId' => ['nullable', 'exists:locations,id'],
            'eventSessionId' => ['nullable', 'exists:game_sessions,id'],
            'eventOccurredAt' => ['required', 'date'],
        ]);

        $data = [
            'title' => $this->eventTitle,
            'description' => $this->eventDescription,
            'event_type' => $this->eventType,
            'faction_id' => $this->eventFactionId,
            'location_id' => $this->eventLocationId,
            'game_session_id' => $this->eventSessionId,
            'occurred_at' => $this->eventOccurredAt,
        ];

        if ($this->editingEventId) {
            $this->campaign->worldEvents()->findOrFail($this->editingEventId)->update($data);
            \Flux::toast(__('Event updated successfully'));
        } else {
            $this->campaign->worldEvents()->create($data);
            \Flux::toast(__('Event created successfully'));
        }

        $this->resetEventForm();
    }

    public function deleteEvent(int $eventId): void
    {
        $this->campaign->worldEvents()->findOrFail($eventId)->delete();
        \Flux::toast(__('Event deleted successfully'));
    }

    private function resetEventForm(): void
    {
        $this->showEventForm = false;
        $this->editingEventId = null;
        $this->eventTitle = '';
        $this->eventDescription = '';
        $this->eventType = 'custom';
        $this->eventFactionId = null;
        $this->eventLocationId = null;
        $this->eventSessionId = null;
        $this->eventOccurredAt = now()->format('Y-m-d\TH:i');
        $this->resetValidation();
    }

    public function render(): \Illuminate\View\View
    {
        $query = $this->campaign->worldEvents()
            ->with(['faction', 'location', 'gameSession'])
            ->orderByDesc('occurred_at');

        if ($this->filterType) {
            $query->where('event_type', $this->filterType);
        }

        return view('pages.campaigns.⚡world-timeline.world-timeline', [
            'events' => $query->get(),
            'factions' => $this->campaign->factions()->orderBy('name')->get(),
            'locations' => $this->campaign->locations()->orderBy('name')->get(),
            'sessions' => $this->campaign->gameSessions()->orderBy('session_number')->get(),
        ])->title(__('Timeline').' — '.$this->campaign->name);
    }
};
