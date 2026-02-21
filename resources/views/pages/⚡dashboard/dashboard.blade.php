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
                <flux:card  href="{{ route('campaigns.show', $campaign) }}" wire:navigate class="flex h-full flex-col dark:border-accent/50! dark:hover:border-accent group cursor-pointer border transition-colors hover:bg-zinc-100/50 dark:bg-zinc-800/50 dark:hover:bg-zinc-700/50">
                        {{-- Heading --}}
                        <div class="mb-3 flex items-start justify-between">
                            <flux:heading size="lg" class="group-hover:text-accent truncate">
                                {{ $campaign->name }}
                            </flux:heading>
                            @php
                                $variant = match ($campaign->status) {
                                    'active' => 'primary',
                                    'archived' => 'warning',
                                    default => 'outline',
                                };
                            @endphp
                            <flux:badge :variant="$variant" size="sm">
                                {{ ucfirst($campaign->status) }}
                            </flux:badge>
                        </div>

                        {{-- Premise --}}

                        @if ($campaign->premise)
                            <flux:text class="mb-4 line-clamp-2 dark:group-hover:text-white">
                                {{ $campaign->premise }}
                            </flux:text>
                        @endif

                        {{-- Details --}}
                        <div class="mt-auto flex items-center gap-4 dark:group-hover:text-white"">
                            <div class="flex items-center gap-1">
                                <flux:badge icon="users" size="sm">
                                    {{ $campaign->characters_count }} {{ Str::plural('character', $campaign->characters_count) }}
                                </flux:badge>
                            </div>
                            <div class="flex items-center gap-1">
                                <flux:badge icon="book-open" size="sm">
                                    {{ $campaign->game_sessions_count }} {{ Str::plural('session', $campaign->game_sessions_count) }}
                                </flux:badge>
                            </div>
                        </div>
                </flux:card>
            @endforeach
        </div>
    @endif
</div>
