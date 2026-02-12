<flux:modal wire:model="showForm" class="md:w-2xl">
    <flux:heading size="lg">{{ __('Add Loot') }}</flux:heading>

    <div class="flex flex-col gap-4 mt-4">
        <div class="grid gap-3 sm:grid-cols-2">
            <flux:select wire:model.live="lootSource" label="{{ __('Source') }}">
                <flux:select.option value="equipment">{{ __('SRD Equipment') }}</flux:select.option>
                <flux:select.option value="magic_item">{{ __('SRD Magic Items') }}</flux:select.option>
                <flux:select.option value="custom">{{ __('Custom Loot') }}</flux:select.option>
            </flux:select>

            <flux:input
                wire:model.live="lootSearchQuery"
                label="{{ __('Search') }}"
                placeholder="{{ __('Search loot...') }}"
            />
        </div>

        @if (count($this->lootSearchResults) > 0)
            <div class="max-h-48 overflow-y-auto rounded-lg border border-zinc-200 dark:border-zinc-700">
                @foreach ($this->lootSearchResults as $result)
                    <button
                        type="button"
                        wire:click="selectLoot({{ $result['id'] }}, '{{ $result['type'] }}')"
                        class="w-full px-3 py-2 text-left hover:bg-zinc-100 dark:hover:bg-zinc-800 border-b border-zinc-200 dark:border-zinc-700 last:border-b-0"
                    >
                        <div class="flex items-center justify-between">
                            <span class="font-medium text-zinc-700 dark:text-zinc-200">{{ $result['name'] }}</span>
                            @if ($result['rarity'])
                                <span class="text-xs text-zinc-500">{{ $result['rarity'] }}</span>
                            @endif
                        </div>
                    </button>
                @endforeach
            </div>
        @endif

        @if ($selectedLootId)
            <div class="rounded-lg bg-zinc-100 dark:bg-zinc-800 p-3">
                <span class="text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ __('Selected item ID') }}: {{ $selectedLootId }}</span>
            </div>
        @endif

        <div class="grid gap-3 sm:grid-cols-2">
            <flux:input
                wire:model="lootQuantity"
                type="number"
                label="{{ __('Quantity') }}"
                min="1"
                required
            />
            <flux:input
                wire:model="lootNotes"
                label="{{ __('Notes') }}"
                placeholder="{{ __('Optional notes...') }}"
            />
        </div>
    </div>

    <div class="flex justify-end gap-3">
        <flux:button variant="subtle" wire:click="close">{{ __('Cancel') }}</flux:button>
        <flux:button variant="primary" wire:click="save">{{ __('Add Loot') }}</flux:button>
    </div>
</flux:modal>
