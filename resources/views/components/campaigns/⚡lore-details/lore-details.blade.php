<div class="space-y-6">
    {{-- Lore Image --}}
    <div class="flex flex-col items-center gap-3">
        @if ($lore->image_path)
            <x-image-lightbox :src="$lore->image_url" :alt="$lore->name" />
        @else
            <div class="flex h-32 w-full items-center justify-center rounded-lg bg-zinc-100 dark:bg-zinc-700">
                <flux:icon name="flag" class="size-12 text-zinc-400 dark:text-zinc-500" />
            </div>
        @endif
        <flux:button variant="subtle" size="sm" wire:click="generateImage({{ $lore->id }})" icon="sparkles" wire:loading.attr="disabled" wire:target="generateImage({{ $lore->id }})">
            <span wire:loading.remove wire:target="generateImage({{ $lore->id }})">{{ $lore->image_path ? __('Regenerate Image') : __('Generate Image') }}</span>
            <span wire:loading wire:target="generateImage({{ $lore->id }})">{{ __('Generating...') }}</span>
        </flux:button>
        <span wire:stream.replace="imageStatus" class="text-xs italic text-zinc-500"></span>
    </div>

    <div>
        <flux:heading size="lg">{{ $lore->name }}</flux:heading>
    </div>

    <flux:separator />

    @if ($lore->description)
        <div>
            <flux:heading size="sm" class="mb-1">{{ __('Description') }}</flux:heading>
            <flux:text class="text-sm">{{ $lore->description }}</flux:text>
        </div>
    @endif

    @if ($lore->dm_notes)
        <div>
            <flux:heading size="sm" class="mb-1">{{ __('DM Notes') }}</flux:heading>
            <flux:text class="text-sm">{{ $lore->dm_notes }}</flux:text>
        </div>
    @endif
</div>
