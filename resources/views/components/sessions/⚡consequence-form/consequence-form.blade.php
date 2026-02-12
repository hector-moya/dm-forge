<flux:modal wire:model="showForm" class="md:w-xl">
    <flux:heading size="lg">{{ __('Add Consequence') }}</flux:heading>

    <div class="flex flex-col gap-4 mt-4">
        <flux:select wire:model="type" label="{{ __('Type') }}">
            <flux:select.option value="immediate">{{ __('Immediate') }}</flux:select.option>
            <flux:select.option value="delayed">{{ __('Delayed') }}</flux:select.option>
            <flux:select.option value="meta">{{ __('Meta') }}</flux:select.option>
        </flux:select>
        <flux:textarea
            wire:model="description"
            label="{{ __('Description') }}"
            placeholder="{{ __('What is the consequence of this choice?') }}"
            rows="3"
            required
        />
    </div>

    <div class="flex justify-end gap-3">
        <flux:button variant="subtle" wire:click="$set('showForm', false)">{{ __('Cancel') }}</flux:button>
        <flux:button variant="primary" wire:click="save">{{ __('Add Consequence') }}</flux:button>
    </div>
</flux:modal>
