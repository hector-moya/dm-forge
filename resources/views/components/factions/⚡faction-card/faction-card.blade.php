<flux:card>
    <div class="space-y-6">
        {{-- Faction Image --}}
        <div class="flex flex-col items-center gap-3">
            @if ($faction->image_path)
                <x-image-lightbox :src="$faction->image_url" :alt="$faction->name" />
            @else
                <div class="flex h-32 w-full items-center justify-center rounded-lg bg-zinc-100 dark:bg-zinc-700">
                    <flux:icon name="flag" class="size-12 text-zinc-400 dark:text-zinc-500" />
                </div>
            @endif
            <flux:button variant="subtle" size="sm" wire:click="generateImage({{ $faction->id }})" icon="sparkles" wire:loading.attr="disabled" wire:target="generateImage({{ $faction->id }})">
                <span wire:loading.remove wire:target="generateImage({{ $faction->id }})">{{ $faction->image_path ? __('Regenerate Image') : __('Generate Image') }}</span>
                <span wire:loading wire:target="generateImage({{ $faction->id }})">{{ __('Generating...') }}</span>
            </flux:button>
            <span wire:stream.replace="imageStatus" class="text-xs italic text-zinc-500"></span>
        </div>

        <div>
            <flux:heading size="lg">{{ $faction->name }}</flux:heading>
            @if ($faction->alignment)
                <flux:badge size="sm" variant="outline" class="mt-2">{{ $faction->alignment }}</flux:badge>
            @endif
        </div>

        <flux:separator />

        @if ($faction->description)
            <div>
                <flux:heading size="sm" class="mb-1">{{ __('Description') }}</flux:heading>
                <flux:text class="text-sm">{{ $faction->description }}</flux:text>
            </div>
        @endif

        @if ($faction->goals)
            <div>
                <flux:heading size="sm" class="mb-1">{{ __('Goals') }}</flux:heading>
                <flux:text class="text-sm">{{ $faction->goals }}</flux:text>
            </div>
        @endif

        @if ($faction->resources)
            <div>
                <flux:heading size="sm" class="mb-1">{{ __('Resources') }}</flux:heading>
                <flux:text class="text-sm">{{ $faction->resources }}</flux:text>
            </div>
        @endif

        @if ($faction->npcs->isNotEmpty())
            <div>
                <flux:heading size="sm" class="mb-1">{{ __('Members') }}</flux:heading>
                <div class="flex flex-wrap gap-2">
                    @foreach ($faction->npcs as $npc)
                        <flux:badge size="sm">{{ $npc->name }}{{ $npc->role ? " ({$npc->role})" : '' }}</flux:badge>
                    @endforeach
                </div>
            </div>
        @endif

        <flux:separator />

        <livewire:campaigns.partials.entity-history: $history />

    </div>
</flux:card>
