<div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl">
    <div class="flex items-center justify-between">
        <flux:heading size="xl">{{ __('My Campaigns') }}</flux:heading>
        <flux:button variant="primary" href="{{ route('campaigns.create') }}" wire:navigate icon="plus">
            {{ __('New Campaign') }}
        </flux:button>
    </div>

    @if ($this->campaigns->isEmpty())
        <div class="flex flex-1 flex-col items-center justify-center rounded-xl border border-dashed border-zinc-300 p-12 dark:border-zinc-600">
            <div class="text-center">
                <flux:icon name="map" class="mx-auto mb-4 size-12 text-zinc-400 dark:text-zinc-500" />
                <flux:heading size="lg" class="mb-2">{{ __('No campaigns yet') }}</flux:heading>
                <flux:text class="mb-6 text-zinc-500 dark:text-zinc-400">
                    {{ __('Create your first campaign to start building your world.') }}
                </flux:text>
                <flux:button variant="primary" href="{{ route('campaigns.create') }}" wire:navigate icon="plus">
                    {{ __('Create Campaign') }}
                </flux:button>
            </div>
        </div>
    @else
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($this->campaigns as $campaign)
                <a href="{{ route('campaigns.show', $campaign) }}" wire:navigate
                   class="group rounded-xl border border-zinc-200 bg-white p-5 transition hover:border-zinc-300 hover:shadow-md dark:border-zinc-700 dark:bg-zinc-800 dark:hover:border-zinc-600">
                    <div class="mb-3 flex items-start justify-between">
                        <flux:heading size="lg" class="group-hover:text-accent truncate">
                            {{ $campaign->name }}
                        </flux:heading>
                        @php
                            $variant = match($campaign->status) {
                                'active' => 'primary',
                                'archived' => 'warning',
                                default => 'outline',
                            };
                        @endphp
                        <flux:badge :variant="$variant" size="sm">
                            {{ ucfirst($campaign->status) }}
                        </flux:badge>
                    </div>

                    @if ($campaign->premise)
                        <flux:text class="mb-4 line-clamp-2 text-sm text-zinc-500 dark:text-zinc-400">
                            {{ $campaign->premise }}
                        </flux:text>
                    @endif

                    <div class="flex items-center gap-4 text-sm text-zinc-500 dark:text-zinc-400">
                        <div class="flex items-center gap-1">
                            <flux:icon name="users" class="size-4" />
                            <span>{{ $campaign->characters_count }} {{ Str::plural('character', $campaign->characters_count) }}</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <flux:icon name="book-open" class="size-4" />
                            <span>{{ $campaign->game_sessions_count }} {{ Str::plural('session', $campaign->game_sessions_count) }}</span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</div>
