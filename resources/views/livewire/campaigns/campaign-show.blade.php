<x-layouts::app :title="$campaign->name">
    <div class="mx-auto flex w-full max-w-5xl flex-col gap-6">
        {{-- Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-4">
                <flux:button variant="subtle" href="{{ route('dashboard') }}" wire:navigate icon="arrow-left" size="sm">
                    {{ __('Back') }}
                </flux:button>
                <div>
                    <div class="flex items-center gap-3">
                        <flux:heading size="xl">{{ $campaign->name }}</flux:heading>
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
                    @if ($campaign->theme_tone)
                        <flux:text class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                            {{ $campaign->theme_tone }}
                        </flux:text>
                    @endif
                </div>
            </div>
            <div class="flex items-center gap-2">
                <flux:button variant="primary" href="{{ route('campaigns.edit', $campaign) }}" wire:navigate icon="pencil" size="sm">
                    {{ __('Edit Campaign') }}
                </flux:button>
            </div>
        </div>

        {{-- Campaign Bible Sections --}}
        <div class="grid gap-6 lg:grid-cols-2">
            {{-- Premise --}}
            @if ($campaign->premise)
                <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-800 lg:col-span-2">
                    <flux:heading size="lg" class="mb-3">{{ __('Premise') }}</flux:heading>
                    <flux:text class="whitespace-pre-line text-zinc-600 dark:text-zinc-300">{{ $campaign->premise }}</flux:text>
                </div>
            @endif

            {{-- Lore --}}
            @if ($campaign->lore)
                <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-800 lg:col-span-2">
                    <flux:heading size="lg" class="mb-3">{{ __('Lore') }}</flux:heading>
                    <flux:text class="whitespace-pre-line text-zinc-600 dark:text-zinc-300">{{ $campaign->lore }}</flux:text>
                </div>
            @endif

            {{-- World Rules --}}
            @if ($campaign->world_rules)
                <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-800">
                    <flux:heading size="lg" class="mb-3">{{ __('World Rules') }}</flux:heading>
                    <flux:text class="whitespace-pre-line text-zinc-600 dark:text-zinc-300">{{ $campaign->world_rules }}</flux:text>
                </div>
            @endif

            {{-- Special Mechanics --}}
            @if ($campaign->special_mechanics)
                <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-800">
                    <flux:heading size="lg" class="mb-3">{{ __('Special Mechanics') }}</flux:heading>
                    <ul class="list-inside list-disc space-y-1 text-zinc-600 dark:text-zinc-300">
                        @foreach ($campaign->special_mechanics as $mechanic)
                            <li>{{ $mechanic }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        {{-- Entity Cards --}}
        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            {{-- Factions --}}
            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-800">
                <div class="mb-3 flex items-center justify-between">
                    <flux:heading size="lg">{{ __('Factions') }}</flux:heading>
                    <flux:badge size="sm">{{ $campaign->factions->count() }}</flux:badge>
                </div>
                @if ($campaign->factions->isEmpty())
                    <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('No factions yet.') }}</flux:text>
                @else
                    <ul class="space-y-2">
                        @foreach ($campaign->factions as $faction)
                            <li class="flex items-center justify-between rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-700/50">
                                <span class="text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ $faction->name }}</span>
                                @if ($faction->alignment)
                                    <flux:badge size="sm" variant="outline">{{ $faction->alignment }}</flux:badge>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            {{-- Locations --}}
            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-800">
                <div class="mb-3 flex items-center justify-between">
                    <flux:heading size="lg">{{ __('Locations') }}</flux:heading>
                    <flux:badge size="sm">{{ $campaign->locations->count() }}</flux:badge>
                </div>
                @if ($campaign->locations->isEmpty())
                    <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('No locations yet.') }}</flux:text>
                @else
                    <ul class="space-y-2">
                        @foreach ($campaign->locations as $location)
                            <li class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-700/50">
                                <span class="text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ $location->name }}</span>
                                @if ($location->region)
                                    <span class="ml-2 text-xs text-zinc-500 dark:text-zinc-400">{{ $location->region }}</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            {{-- NPCs --}}
            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-800">
                <div class="mb-3 flex items-center justify-between">
                    <flux:heading size="lg">{{ __('NPCs') }}</flux:heading>
                    <flux:badge size="sm">{{ $campaign->npcs->count() }}</flux:badge>
                </div>
                @if ($campaign->npcs->isEmpty())
                    <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('No NPCs yet.') }}</flux:text>
                @else
                    <ul class="space-y-2">
                        @foreach ($campaign->npcs as $npc)
                            <li class="flex items-center justify-between rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-700/50">
                                <div>
                                    <span class="text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ $npc->name }}</span>
                                    @if ($npc->role)
                                        <span class="ml-2 text-xs text-zinc-500 dark:text-zinc-400">{{ $npc->role }}</span>
                                    @endif
                                </div>
                                @if (! $npc->is_alive)
                                    <flux:badge size="sm" variant="danger">{{ __('Dead') }}</flux:badge>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            {{-- Characters --}}
            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-800">
                <div class="mb-3 flex items-center justify-between">
                    <flux:heading size="lg">{{ __('Characters') }}</flux:heading>
                    <flux:badge size="sm">{{ $campaign->characters->count() }}</flux:badge>
                </div>
                @if ($campaign->characters->isEmpty())
                    <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('No characters yet.') }}</flux:text>
                @else
                    <ul class="space-y-2">
                        @foreach ($campaign->characters as $character)
                            <li class="flex items-center justify-between rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-700/50">
                                <div>
                                    <span class="text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ $character->name }}</span>
                                    @if ($character->player_name)
                                        <span class="ml-2 text-xs text-zinc-500 dark:text-zinc-400">({{ $character->player_name }})</span>
                                    @endif
                                </div>
                                <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                    @if ($character->class)
                                        {{ $character->class }}
                                    @endif
                                    @if ($character->level)
                                        {{ __('Lvl') }} {{ $character->level }}
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            {{-- Sessions --}}
            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-800 md:col-span-2 lg:col-span-2">
                <div class="mb-3 flex items-center justify-between">
                    <flux:heading size="lg">{{ __('Sessions') }}</flux:heading>
                    <flux:badge size="sm">{{ $campaign->gameSessions->count() }}</flux:badge>
                </div>
                @if ($campaign->gameSessions->isEmpty())
                    <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('No sessions yet.') }}</flux:text>
                @else
                    <ul class="space-y-2">
                        @foreach ($campaign->gameSessions->sortByDesc('session_number') as $session)
                            <li class="flex items-center justify-between rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-700/50">
                                <div>
                                    <span class="text-sm font-medium text-zinc-700 dark:text-zinc-200">
                                        #{{ $session->session_number }}: {{ $session->title }}
                                    </span>
                                </div>
                                @if ($session->status)
                                    @php
                                        $sessionVariant = match($session->status) {
                                            'completed' => 'primary',
                                            'in_progress' => 'warning',
                                            default => 'outline',
                                        };
                                    @endphp
                                    <flux:badge size="sm" :variant="$sessionVariant">{{ ucfirst(str_replace('_', ' ', $session->status)) }}</flux:badge>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</x-layouts::app>
