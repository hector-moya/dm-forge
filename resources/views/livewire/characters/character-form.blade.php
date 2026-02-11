<div class="mx-auto flex w-full max-w-3xl flex-col gap-6">
    <div class="flex items-center gap-4">
        <flux:button variant="subtle" href="{{ route('campaigns.characters', $campaign) }}" wire:navigate icon="arrow-left" size="sm">
            {{ __('Characters') }}
        </flux:button>
        <flux:heading size="xl">
            {{ $character?->exists ? __('Edit Character') : __('New Character') }}
        </flux:heading>
    </div>

    <form wire:submit="save" class="flex flex-col gap-6">
        <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
            <flux:heading size="lg" class="mb-4">{{ __('Character Info') }}</flux:heading>

            <div class="flex flex-col gap-4">
                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:input wire:model="name" label="{{ __('Character Name') }}" required />
                    <flux:input wire:model="player_name" label="{{ __('Player Name') }}" />
                </div>

                <div class="grid gap-4 sm:grid-cols-3">
                    <flux:input wire:model="characterClass" label="{{ __('Class') }}" placeholder="{{ __('e.g., Rogue, Paladin') }}" />
                    <flux:input wire:model="level" label="{{ __('Level') }}" type="number" min="1" max="30" required />
                    <flux:input wire:model="alignment_label" label="{{ __('Alignment') }}" placeholder="{{ __('e.g., Chaotic Good') }}" />
                </div>

                <div class="grid gap-4 sm:grid-cols-3">
                    <flux:input wire:model="hp_max" label="{{ __('Max HP') }}" type="number" min="1" required />
                    <flux:input wire:model="hp_current" label="{{ __('Current HP') }}" type="number" min="0" />
                    <flux:input wire:model="armor_class" label="{{ __('Armor Class') }}" type="number" min="1" required />
                </div>

                <flux:textarea wire:model="notes" label="{{ __('Notes') }}" rows="3" placeholder="{{ __('Character backstory, abilities, inventory notes...') }}" />
            </div>
        </div>

        <div class="flex items-center justify-between">
            @if ($character?->exists)
                <flux:button variant="danger" type="button" wire:click="deleteCharacter" wire:confirm="{{ __('Are you sure you want to delete this character?') }}" icon="trash">
                    {{ __('Delete') }}
                </flux:button>
            @else
                <div></div>
            @endif
            <div class="flex items-center gap-3">
                <flux:button variant="subtle" href="{{ route('campaigns.characters', $campaign) }}" wire:navigate>
                    {{ __('Cancel') }}
                </flux:button>
                <flux:button type="submit" variant="primary">
                    {{ $character?->exists ? __('Save Changes') : __('Create Character') }}
                </flux:button>
            </div>
        </div>
    </form>
</div>
