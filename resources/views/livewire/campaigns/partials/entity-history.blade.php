@props(['history'])

@if ($history->isNotEmpty())
    <div>
        <flux:heading size="sm" class="mb-2">{{ __('History') }}</flux:heading>
        <div class="space-y-3">
            @foreach ($history as $event)
                <div class="rounded-lg border border-zinc-200 p-3 dark:border-zinc-600">
                    <div class="flex items-start justify-between gap-2">
                        <div class="text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ $event->title }}</div>
                        <flux:badge size="sm" variant="outline">{{ str_replace('_', ' ', $event->event_type) }}</flux:badge>
                    </div>
                    <flux:text class="mt-1 text-sm">{{ $event->description }}</flux:text>
                    <div class="mt-2 flex items-center gap-3 text-xs text-zinc-500 dark:text-zinc-400">
                        @if ($event->occurred_at)
                            <span>{{ $event->occurred_at->format('M j, Y') }}</span>
                        @endif
                        @if ($event->faction)
                            <span>{{ $event->faction->name }}</span>
                        @endif
                        @if ($event->location)
                            <span>{{ $event->location->name }}</span>
                        @endif
                        @if ($event->gameSession)
                            <span>Session #{{ $event->gameSession->session_number }}</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@else
    <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
        {{ __('No history recorded yet.') }}
    </flux:text>
@endif
