<div class="space-y-6">
    <div>
        <flux:heading size="lg">{{ $specialMechanic->name }}</flux:heading>
    </div>

    <flux:separator />

    @if ($specialMechanic->description)
        <div>
            <flux:heading size="sm" class="mb-1">{{ __('Description') }}</flux:heading>
            <flux:text class="text-sm">{{ $specialMechanic->description }}</flux:text>
        </div>
    @endif

    @if ($specialMechanic->dm_notes)
        <div>
            <flux:heading size="sm" class="mb-1">{{ __('DM Notes') }}</flux:heading>
            <flux:text class="text-sm">{{ $specialMechanic->dm_notes }}</flux:text>
        </div>
    @endif

    @if ($specialMechanic->rules->isNotEmpty())
        <div>
            <flux:heading size="sm" class="mb-3">{{ __('Rules') }}</flux:heading>
            <ul class="space-y-3">
                @foreach ($specialMechanic->rules as $rule)
                    <li class="rounded-lg bg-zinc-50 p-3 dark:bg-zinc-700/50">
                        <p class="text-sm font-medium text-zinc-800 dark:text-zinc-100">{{ $rule->name }}</p>
                        @if ($rule->description)
                            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">{{ $rule->description }}</p>
                        @endif
                        @if ($rule->notes)
                            <p class="mt-1 text-xs italic text-zinc-500 dark:text-zinc-400">{{ $rule->notes }}</p>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
