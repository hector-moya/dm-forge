{{-- Factions --}}
<flux:card>
    <div class="mb-3 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <flux:heading size="lg">{{ __('Lore') }}</flux:heading>
            <flux:badge size="sm">{{ $this->campaignLores->count() }}</flux:badge>
        </div>
        <div class="flex items-center gap-1">
            <flux:button variant="primary" size="xs" href="{{ route('campaigns.lore', $campaign) }}" wire:navigate icon="pencil">{{ __('Edit') }}</flux:button>
        </div>
    </div>
    @if ($this->campaignLores->isEmpty())
        <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('No lore yet.') }}</flux:text>
    @else
        <ul class="space-y-2">
            @foreach ($this->campaignLores as $lore)
                <flux:card class="flex items-center justify-between py-4!">
                    <div class="space-x-4">
                        <flux:text>{{ $lore->name }}</flux:text>
                        @if ($lore->alignment)
                            <flux:badge size="sm" variant="outline">{{ $lore->alignment }}</flux:badge>
                        @endif
                    </div>
                    <flux:modal.trigger name="view-lore-{{ $lore->id }}">
                        <flux:button variant="ghost" size="xs" icon="eye" />
                    </flux:modal.trigger>

                </flux:card>
                <flux:modal name="view-lore-{{ $lore->id }}" class="md:w-xl" variant="flyout">
                    <livewire:campaigns.lore-details :loreId="$lore->id" :key="'lore-details-' . $lore->id" />
                </flux:modal>
            @endforeach
        </ul>
    @endif
    </flux:card>
