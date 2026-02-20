<div class="mx-auto flex w-full max-w-4xl flex-col gap-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-4">
            <flux:button variant="subtle" href="{{ route('campaigns.show', $campaign) }}" wire:navigate icon="arrow-left" size="sm">
                {{ __('Campaign') }}
            </flux:button>
            <flux:heading size="xl">{{ __('Sessions') }}</flux:heading>
        </div>
        <flux:button variant="primary" href="{{ route('sessions.create', $campaign) }}" wire:navigate icon="plus">
            {{ __('New Session') }}
        </flux:button>
    </div>

    @if ($sessions->isEmpty())
        <div class="flex flex-col items-center justify-center rounded-xl border border-dashed border-zinc-300 p-12 dark:border-zinc-600">
            <flux:icon name="book-open" class="mx-auto mb-4 size-12 text-zinc-400 dark:text-zinc-500" />
            <flux:heading size="lg" class="mb-2">{{ __('No sessions yet') }}</flux:heading>
            <flux:text class="mb-6 text-zinc-500 dark:text-zinc-400">
                {{ __('Create your first session to start planning.') }}
            </flux:text>
            <flux:button variant="primary" href="{{ route('sessions.create', $campaign) }}" wire:navigate icon="plus">
                {{ __('Create Session') }}
            </flux:button>
        </div>
    @else
        <div class="space-y-3">
            @foreach ($sessions as $session)
                <a href="{{ route('sessions.edit', $session) }}" wire:navigate
                   class="group flex items-center justify-between rounded-xl border border-zinc-200 bg-white px-5 py-4 transition hover:border-zinc-300 hover:shadow-md dark:border-zinc-700 dark:bg-zinc-800 dark:hover:border-zinc-600">
                    <div>
                        <div class="flex items-center gap-3">
                            <span class="text-sm font-semibold text-zinc-500 dark:text-zinc-400">#{{ $session->session_number }}</span>
                            <flux:heading size="lg" class="group-hover:text-accent">{{ $session->title }}</flux:heading>
                            @php
                                $variant = match($session->status) {
                                    'completed' => 'primary',
                                    'running' => 'warning',
                                    'prepared' => 'outline',
                                    default => 'outline',
                                };
                            @endphp
                            <flux:badge :variant="$variant" size="sm">{{ ucfirst($session->status) }}</flux:badge>
                        </div>
                        <div class="mt-1 flex items-center gap-4 text-sm text-zinc-500 dark:text-zinc-400">
                            <span>{{ $session->scenes_count }} {{ Str::plural('scene', $session->scenes_count) }}</span>
                            <span>{{ $session->encounters_count }} {{ Str::plural('encounter', $session->encounters_count) }}</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        @if ($session->status === 'prepared')
                            <flux:button variant="primary" size="sm" href="{{ route('sessions.run', $session) }}" wire:navigate icon="play"
                                         onclick="event.stopPropagation(); event.preventDefault(); window.Livewire.navigate('{{ route('sessions.run', $session) }}')">
                                {{ __('Run') }}
                            </flux:button>
                        @elseif ($session->status === 'completed')
                            <flux:button variant="subtle" size="sm" href="{{ route('sessions.recap', $session) }}" wire:navigate icon="book-open">
                                {{ __('Recap') }}
                            </flux:button>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</div>
