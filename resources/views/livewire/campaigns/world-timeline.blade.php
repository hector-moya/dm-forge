<div class="mx-auto flex w-full max-w-4xl flex-col gap-6">
    {{-- Header --}}
    <div class="flex items-center gap-4">
        <flux:button variant="subtle" href="{{ route('campaigns.show', $campaign) }}" wire:navigate icon="arrow-left" size="sm">
            {{ __('Campaign') }}
        </flux:button>
        <flux:heading size="xl">{{ __('World Timeline') }}</flux:heading>
    </div>

    {{-- Controls --}}
    <div class="flex items-center justify-between">
        <flux:select wire:model.live="filterType" class="w-48" placeholder="{{ __('All Events') }}">
            <flux:select.option value="">{{ __('All Events') }}</flux:select.option>
            <flux:select.option value="faction_movement">{{ __('Faction Movement') }}</flux:select.option>
            <flux:select.option value="consequence_resolved">{{ __('Consequence') }}</flux:select.option>
            <flux:select.option value="npc_change">{{ __('NPC Change') }}</flux:select.option>
            <flux:select.option value="territory_change">{{ __('Territory Change') }}</flux:select.option>
            <flux:select.option value="custom">{{ __('Custom') }}</flux:select.option>
        </flux:select>

        <flux:button variant="primary" size="sm" wire:click="openEventForm" icon="plus">
            {{ __('Add Event') }}
        </flux:button>
    </div>

    {{-- Add/Edit Event Form --}}
    @if ($showEventForm)
        <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
            <flux:heading size="lg" class="mb-4">
                {{ $editingEventId ? __('Edit Event') : __('New Event') }}
            </flux:heading>

            <div class="flex flex-col gap-4">
                <flux:input
                    wire:model="eventTitle"
                    label="{{ __('Title') }}"
                    placeholder="{{ __('What happened?') }}"
                    required
                />
                <flux:textarea
                    wire:model="eventDescription"
                    label="{{ __('Description') }}"
                    placeholder="{{ __('Describe the event and its significance...') }}"
                    rows="3"
                    required
                />
                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:select wire:model="eventType" label="{{ __('Event Type') }}">
                        <flux:select.option value="faction_movement">{{ __('Faction Movement') }}</flux:select.option>
                        <flux:select.option value="consequence_resolved">{{ __('Consequence Resolved') }}</flux:select.option>
                        <flux:select.option value="npc_change">{{ __('NPC Change') }}</flux:select.option>
                        <flux:select.option value="territory_change">{{ __('Territory Change') }}</flux:select.option>
                        <flux:select.option value="custom">{{ __('Custom') }}</flux:select.option>
                    </flux:select>
                    <flux:input
                        wire:model="eventOccurredAt"
                        type="datetime-local"
                        label="{{ __('When') }}"
                        required
                    />
                </div>
                <div class="grid gap-4 sm:grid-cols-3">
                    <flux:select wire:model="eventFactionId" label="{{ __('Faction') }}" placeholder="{{ __('None') }}">
                        <flux:select.option value="">{{ __('None') }}</flux:select.option>
                        @foreach ($factions as $faction)
                            <flux:select.option value="{{ $faction->id }}">{{ $faction->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:select wire:model="eventLocationId" label="{{ __('Location') }}" placeholder="{{ __('None') }}">
                        <flux:select.option value="">{{ __('None') }}</flux:select.option>
                        @foreach ($locations as $location)
                            <flux:select.option value="{{ $location->id }}">{{ $location->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:select wire:model="eventSessionId" label="{{ __('Session') }}" placeholder="{{ __('None') }}">
                        <flux:select.option value="">{{ __('None') }}</flux:select.option>
                        @foreach ($sessions as $session)
                            <flux:select.option value="{{ $session->id }}">#{{ $session->session_number }} — {{ $session->title }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </div>
                <div class="flex items-center justify-end gap-3">
                    <flux:button variant="subtle" wire:click="$set('showEventForm', false)">{{ __('Cancel') }}</flux:button>
                    <flux:button variant="primary" wire:click="saveEvent">
                        {{ $editingEventId ? __('Update Event') : __('Add Event') }}
                    </flux:button>
                </div>
            </div>
        </div>
    @endif

    {{-- Timeline --}}
    @if ($events->isEmpty())
        <div class="rounded-xl border border-zinc-200 bg-white p-6 text-center dark:border-zinc-700 dark:bg-zinc-800">
            <flux:text class="text-zinc-500 dark:text-zinc-400">
                {{ __('No events yet. Add events to build your world timeline.') }}
            </flux:text>
        </div>
    @else
        <div class="relative">
            {{-- Timeline line --}}
            <div class="absolute left-5 top-0 bottom-0 w-px bg-zinc-300 dark:bg-zinc-600"></div>

            <div class="space-y-4">
                @foreach ($events as $event)
                    @php
                        $typeColors = [
                            'faction_movement' => 'bg-blue-500',
                            'consequence_resolved' => 'bg-amber-500',
                            'npc_change' => 'bg-purple-500',
                            'territory_change' => 'bg-green-500',
                            'custom' => 'bg-zinc-500',
                        ];
                        $typeLabels = [
                            'faction_movement' => __('Faction'),
                            'consequence_resolved' => __('Consequence'),
                            'npc_change' => __('NPC'),
                            'territory_change' => __('Territory'),
                            'custom' => __('Custom'),
                        ];
                        $dotColor = $typeColors[$event->event_type] ?? 'bg-zinc-500';
                    @endphp

                    <div class="relative flex gap-4 pl-12">
                        {{-- Timeline dot --}}
                        <div class="absolute left-3.5 top-2 h-3 w-3 rounded-full ring-2 ring-white dark:ring-zinc-800 {{ $dotColor }}"></div>

                        {{-- Event card --}}
                        <div class="flex-1 rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
                            <div class="flex items-start justify-between">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="font-semibold text-zinc-800 dark:text-zinc-100">{{ $event->title }}</span>
                                        <flux:badge size="sm" class="{{ str_replace('bg-', 'bg-opacity-20 text-', $dotColor) }}">
                                            {{ $typeLabels[$event->event_type] ?? $event->event_type }}
                                        </flux:badge>
                                    </div>
                                    <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">{{ $event->description }}</p>
                                    <div class="mt-2 flex flex-wrap items-center gap-2 text-xs text-zinc-500 dark:text-zinc-500">
                                        <span>{{ $event->occurred_at->format('M j, Y g:ia') }}</span>
                                        @if ($event->faction)
                                            <flux:badge size="sm" variant="outline">{{ $event->faction->name }}</flux:badge>
                                        @endif
                                        @if ($event->location)
                                            <flux:badge size="sm" variant="outline">{{ $event->location->name }}</flux:badge>
                                        @endif
                                        @if ($event->gameSession)
                                            <flux:badge size="sm" variant="outline">{{ __('Session') }} #{{ $event->gameSession->session_number }}</flux:badge>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex items-center gap-1">
                                    <flux:button variant="subtle" size="sm" wire:click="openEventForm({{ $event->id }})" icon="pencil" />
                                    <flux:button variant="subtle" size="sm" wire:click="deleteEvent({{ $event->id }})" wire:confirm="{{ __('Delete this event?') }}" icon="trash" />
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
