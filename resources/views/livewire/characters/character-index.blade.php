<div class="mx-auto flex w-full max-w-4xl flex-col gap-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-4">
            <flux:button variant="subtle" href="{{ route('campaigns.show', $campaign) }}" wire:navigate icon="arrow-left" size="sm">
                {{ __('Campaign') }}
            </flux:button>
            <flux:heading size="xl">{{ __('Characters') }}</flux:heading>
        </div>
        <flux:button variant="primary" href="{{ route('characters.create', $campaign) }}" wire:navigate icon="plus">
            {{ __('Add Character') }}
        </flux:button>
    </div>

    @if ($characters->isEmpty())
        <div class="flex flex-col items-center justify-center rounded-xl border border-dashed border-zinc-300 p-12 dark:border-zinc-600">
            <flux:icon name="users" class="mx-auto mb-4 size-12 text-zinc-400 dark:text-zinc-500" />
            <flux:heading size="lg" class="mb-2">{{ __('No characters yet') }}</flux:heading>
            <flux:text class="mb-6 text-zinc-500 dark:text-zinc-400">
                {{ __('Add player characters to track their journey.') }}
            </flux:text>
            <flux:button variant="primary" href="{{ route('characters.create', $campaign) }}" wire:navigate icon="plus">
                {{ __('Add Character') }}
            </flux:button>
        </div>
    @else
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($characters as $character)
                <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-800">
                    <div class="mb-3 flex items-start justify-between">
                        <div>
                            <flux:heading size="lg">{{ $character->name }}</flux:heading>
                            @if ($character->player_name)
                                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ __('Player:') }} {{ $character->player_name }}
                                </flux:text>
                            @endif
                        </div>
                        @if ($character->alignment_label)
                            <flux:badge size="sm" variant="outline">{{ $character->alignment_label }}</flux:badge>
                        @endif
                    </div>

                    <div class="mb-4 grid grid-cols-3 gap-2 text-center text-sm">
                        <div class="rounded-lg bg-zinc-50 p-2 dark:bg-zinc-700/50">
                            <div class="font-semibold text-zinc-700 dark:text-zinc-200">{{ $character->class ?? '—' }}</div>
                            <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Class') }}</div>
                        </div>
                        <div class="rounded-lg bg-zinc-50 p-2 dark:bg-zinc-700/50">
                            <div class="font-semibold text-zinc-700 dark:text-zinc-200">{{ $character->level }}</div>
                            <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Level') }}</div>
                        </div>
                        <div class="rounded-lg bg-zinc-50 p-2 dark:bg-zinc-700/50">
                            <div class="font-semibold text-zinc-700 dark:text-zinc-200">{{ $character->armor_class }}</div>
                            <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('AC') }}</div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <flux:button variant="subtle" size="sm" href="{{ route('characters.edit', $character) }}" wire:navigate icon="pencil">
                            {{ __('Edit') }}
                        </flux:button>
                        <flux:button variant="subtle" size="sm" href="{{ route('characters.alignment', $character) }}" wire:navigate icon="compass">
                            {{ __('Alignment') }}
                        </flux:button>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
