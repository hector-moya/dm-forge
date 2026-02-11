<div class="mx-auto flex w-full max-w-4xl flex-col gap-6">
    <div class="flex items-center gap-4">
        <flux:button variant="subtle" href="{{ route('campaigns.characters', $character->campaign) }}" wire:navigate icon="arrow-left" size="sm">
            {{ __('Characters') }}
        </flux:button>
        <div>
            <flux:heading size="xl">{{ $character->name }}</flux:heading>
            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                {{ $character->class ?? '' }} {{ __('Lvl') }} {{ $character->level }}
                @if ($character->alignment_label)
                    — {{ $character->alignment_label }}
                @endif
            </flux:text>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        {{-- Compass Visualization --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
            <flux:heading size="lg" class="mb-4">{{ __('Alignment Compass') }}</flux:heading>

            <div class="flex justify-center" x-data="{
                ge: @entangle('goodEvilScore'),
                lc: @entangle('lawChaosScore'),
                get dotX() { return 150 + (this.lc * -13) },
                get dotY() { return 150 + (this.ge * -13) }
            }">
                <svg viewBox="0 0 300 300" class="h-72 w-72">
                    {{-- Background grid --}}
                    <rect x="20" y="20" width="260" height="260" fill="none" stroke="currentColor" class="text-zinc-300 dark:text-zinc-600" stroke-width="1"/>
                    <line x1="150" y1="20" x2="150" y2="280" stroke="currentColor" class="text-zinc-300 dark:text-zinc-600" stroke-width="1"/>
                    <line x1="20" y1="150" x2="280" y2="150" stroke="currentColor" class="text-zinc-300 dark:text-zinc-600" stroke-width="1"/>

                    {{-- Quadrant labels --}}
                    <text x="85" y="85" text-anchor="middle" class="fill-zinc-400 dark:fill-zinc-500 text-[11px]">Lawful Good</text>
                    <text x="215" y="85" text-anchor="middle" class="fill-zinc-400 dark:fill-zinc-500 text-[11px]">Chaotic Good</text>
                    <text x="85" y="225" text-anchor="middle" class="fill-zinc-400 dark:fill-zinc-500 text-[11px]">Lawful Evil</text>
                    <text x="215" y="225" text-anchor="middle" class="fill-zinc-400 dark:fill-zinc-500 text-[11px]">Chaotic Evil</text>

                    {{-- Axis labels --}}
                    <text x="150" y="15" text-anchor="middle" class="fill-emerald-500 text-[10px] font-semibold">Good</text>
                    <text x="150" y="295" text-anchor="middle" class="fill-red-500 text-[10px] font-semibold">Evil</text>
                    <text x="10" y="153" text-anchor="middle" class="fill-blue-500 text-[10px] font-semibold" transform="rotate(-90, 10, 153)">Lawful</text>
                    <text x="290" y="153" text-anchor="middle" class="fill-amber-500 text-[10px] font-semibold" transform="rotate(90, 290, 153)">Chaotic</text>

                    {{-- Character dot --}}
                    <circle :cx="dotX" :cy="dotY" r="8" class="fill-indigo-500" opacity="0.9"/>
                    <circle :cx="dotX" :cy="dotY" r="4" class="fill-white"/>
                </svg>
            </div>

            <div class="mt-4 grid grid-cols-2 gap-4 text-center">
                <div class="rounded-lg bg-zinc-50 p-3 dark:bg-zinc-700/50">
                    <div class="text-lg font-bold text-zinc-700 dark:text-zinc-200">{{ $goodEvilScore > 0 ? '+' : '' }}{{ $goodEvilScore }}</div>
                    <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Good / Evil') }}</div>
                </div>
                <div class="rounded-lg bg-zinc-50 p-3 dark:bg-zinc-700/50">
                    <div class="text-lg font-bold text-zinc-700 dark:text-zinc-200">{{ $lawChaosScore > 0 ? '+' : '' }}{{ $lawChaosScore }}</div>
                    <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Law / Chaos') }}</div>
                </div>
            </div>
        </div>

        {{-- Record Event Form --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
            <flux:heading size="lg" class="mb-4">{{ __('Record Alignment Event') }}</flux:heading>

            <form wire:submit="recordEvent" class="flex flex-col gap-4">
                <flux:textarea
                    wire:model="actionDescription"
                    label="{{ __('What did they do?') }}"
                    placeholder="{{ __('Describe the action or decision...') }}"
                    rows="3"
                    required
                />

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                            {{ __('Good/Evil') }} ({{ $goodEvilDelta > 0 ? '+' : '' }}{{ $goodEvilDelta }})
                        </label>
                        <input type="range" wire:model.live="goodEvilDelta" min="-5" max="5" step="1"
                               class="w-full accent-indigo-500" />
                        <div class="mt-1 flex justify-between text-xs text-zinc-500 dark:text-zinc-400">
                            <span>{{ __('Evil') }} -5</span>
                            <span>0</span>
                            <span>+5 {{ __('Good') }}</span>
                        </div>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                            {{ __('Law/Chaos') }} ({{ $lawChaosDelta > 0 ? '+' : '' }}{{ $lawChaosDelta }})
                        </label>
                        <input type="range" wire:model.live="lawChaosDelta" min="-5" max="5" step="1"
                               class="w-full accent-indigo-500" />
                        <div class="mt-1 flex justify-between text-xs text-zinc-500 dark:text-zinc-400">
                            <span>{{ __('Chaos') }} -5</span>
                            <span>0</span>
                            <span>+5 {{ __('Law') }}</span>
                        </div>
                    </div>
                </div>

                <flux:button type="submit" variant="primary" class="w-full">
                    {{ __('Record Event') }}
                </flux:button>
            </form>
        </div>
    </div>

    {{-- Event History --}}
    <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
        <flux:heading size="lg" class="mb-4">{{ __('Alignment History') }}</flux:heading>

        @if ($events->isEmpty())
            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                {{ __('No alignment events recorded yet.') }}
            </flux:text>
        @else
            <div class="space-y-3">
                @foreach ($events as $event)
                    <div class="flex items-start justify-between rounded-lg bg-zinc-50 px-4 py-3 dark:bg-zinc-700/50">
                        <div class="flex-1">
                            <p class="text-sm text-zinc-700 dark:text-zinc-200">{{ $event->action_description }}</p>
                            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                                {{ $event->created_at->diffForHumans() }}
                                @if ($event->dm_overridden)
                                    <flux:badge size="sm" variant="outline" class="ml-1">{{ __('DM Override') }}</flux:badge>
                                @endif
                            </p>
                        </div>
                        <div class="ml-4 flex items-center gap-3 text-sm">
                            <span class="{{ $event->good_evil_delta > 0 ? 'text-emerald-500' : ($event->good_evil_delta < 0 ? 'text-red-500' : 'text-zinc-400') }}">
                                GE: {{ $event->good_evil_delta > 0 ? '+' : '' }}{{ $event->good_evil_delta }}
                            </span>
                            <span class="{{ $event->law_chaos_delta > 0 ? 'text-blue-500' : ($event->law_chaos_delta < 0 ? 'text-amber-500' : 'text-zinc-400') }}">
                                LC: {{ $event->law_chaos_delta > 0 ? '+' : '' }}{{ $event->law_chaos_delta }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
