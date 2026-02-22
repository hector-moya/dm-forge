<div>
    <div class="mb-3 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <flux:heading size="lg">{{ __('Special Mechanics') }}</flux:heading>
            <flux:badge size="sm">{{ $this->campaignSpecialMechanics->count() }}</flux:badge>
        </div>
        <div class="flex items-center gap-1">
            <flux:button variant="primary" size="xs" href="{{ route('campaigns.special-mechanics', $campaign) }}" wire:navigate icon="pencil">{{ __('Edit') }}</flux:button>
        </div>
    </div>
    @if ($this->campaignSpecialMechanics->isEmpty())
        <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('No special mechanics yet.') }}</flux:text>
    @else
        <ul class="space-y-2">
            @foreach ($this->campaignSpecialMechanics as $mechanic)
                <flux:card class="flex items-center justify-between py-4!">
                    <div class="flex items-center gap-2">
                        <flux:text>{{ $mechanic->name }}</flux:text>
                        @if ($mechanic->rules_count > 0)
                            <flux:badge size="sm" variant="outline">{{ $mechanic->rules_count }} {{ __('rules') }}</flux:badge>
                        @endif
                    </div>
                    <flux:modal.trigger name="view-special-mechanic-{{ $mechanic->id }}">
                        <flux:button variant="ghost" size="xs" icon="eye" />
                    </flux:modal.trigger>
                </flux:card>
                <flux:modal name="view-special-mechanic-{{ $mechanic->id }}" class="md:w-xl" variant="flyout">
                    <livewire:campaigns.special-mechanic-details :specialMechanicId="$mechanic->id" :key="'special-mechanic-details-' . $mechanic->id" />
                </flux:modal>
            @endforeach
        </ul>
    @endif
</div>
