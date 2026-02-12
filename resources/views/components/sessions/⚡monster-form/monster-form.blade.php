<flux:modal wire:model="showForm" class="md:w-2xl">
    <flux:heading size="lg">{{ __('Add Monsters') }}</flux:heading>

    <div class="flex flex-col gap-4 mt-4">
        <div class="grid gap-3 sm:grid-cols-2">
            <flux:select wire:model.live="monsterSource" label="{{ __('Source') }}">
                <flux:select.option value="srd">{{ __('SRD Monsters') }}</flux:select.option>
                <flux:select.option value="custom">{{ __('Custom Monsters') }}</flux:select.option>
            </flux:select>

            <flux:input
                wire:model.live="monsterSearchQuery"
                label="{{ __('Search') }}"
                placeholder="{{ __('Search monsters...') }}"
            />
        </div>

        @if (count($this->monsterSearchResults) > 0)
            <div class="max-h-48 overflow-y-auto rounded-lg border border-zinc-200 dark:border-zinc-700">
                @foreach ($this->monsterSearchResults as $result)
                    <button
                        type="button"
                        wire:click="{{ $result['source'] === 'srd' ? 'selectSrdMonster' : 'selectCustomMonster' }}({{ $result['id'] }})"
                        class="w-full px-3 py-2 text-left hover:bg-zinc-100 dark:hover:bg-zinc-800 border-b border-zinc-200 dark:border-zinc-700 last:border-b-0"
                    >
                        <div class="flex items-center justify-between">
                            <span class="font-medium text-zinc-700 dark:text-zinc-200">{{ $result['name'] }}</span>
                            <span class="text-xs text-zinc-500">CR {{ $result['cr'] }} • HP {{ $result['hp'] }} • AC {{ $result['ac'] }}</span>
                        </div>
                    </button>
                @endforeach
            </div>
        @endif

        <div class="grid gap-3 sm:grid-cols-4">
            <flux:input
                wire:model="monsterName"
                label="{{ __('Name') }}"
                placeholder="{{ __('e.g., Goblin') }}"
                required
            />
            <flux:input
                wire:model="monsterHpMax"
                type="number"
                label="{{ __('HP') }}"
                min="1"
                required
            />
            <flux:input
                wire:model="monsterAc"
                type="number"
                label="{{ __('AC') }}"
                min="1"
                required
            />
            <flux:input
                wire:model="monsterCount"
                type="number"
                label="{{ __('Count') }}"
                min="1"
                max="20"
                required
            />
        </div>
    </div>

    <div class="flex justify-end gap-3">
        <flux:button variant="subtle" wire:click="close">{{ __('Cancel') }}</flux:button>
        <flux:button variant="primary" wire:click="save">{{ __('Add Monsters') }}</flux:button>
    </div>
</flux:modal>
