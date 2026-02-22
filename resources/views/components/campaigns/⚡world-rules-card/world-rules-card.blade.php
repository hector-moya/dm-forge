<div>
    <div class="mb-3 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <flux:heading size="lg">{{ __('World Rules') }}</flux:heading>
            <flux:badge size="sm">{{ $this->campaignWorldRules->count() }}</flux:badge>
        </div>
        <div class="flex items-center gap-1">
            <flux:button variant="primary" size="xs" href="{{ route('campaigns.world-rules', $campaign) }}" wire:navigate icon="pencil">{{ __('Edit') }}</flux:button>
        </div>
    </div>
    @if ($this->campaignWorldRules->isEmpty())
        <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('No world rules yet.') }}</flux:text>
    @else
        <ul class="space-y-2">
            @foreach ($this->campaignWorldRules as $worldRule)
                <flux:card class="flex items-center justify-between py-4!">
                    <flux:text>{{ $worldRule->name }}</flux:text>
                    <flux:modal.trigger name="view-world-rule-{{ $worldRule->id }}">
                        <flux:button variant="ghost" size="xs" icon="eye" />
                    </flux:modal.trigger>
                </flux:card>
                <flux:modal name="view-world-rule-{{ $worldRule->id }}" class="md:w-xl" variant="flyout">
                    <livewire:campaigns.world-rule-details :worldRuleId="$worldRule->id" :key="'world-rule-details-' . $worldRule->id" />
                </flux:modal>
            @endforeach
        </ul>
    @endif
</div>
