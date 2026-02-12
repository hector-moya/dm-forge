<div class="rounded-md bg-white px-3 py-2 dark:bg-zinc-800">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
            <flux:icon name="arrows-right-left" class="size-4 text-indigo-500" />
            <span class="text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ $branch->label }}</span>
        </div>
        <div class="flex items-center gap-1">
            <flux:button variant="subtle" size="sm" wire:click="openConsequenceForm" icon="plus" title="{{ __('Add Consequence') }}" />
            <flux:button variant="subtle" size="sm" wire:click="openForm" icon="pencil" title="{{ __('Edit Branch') }}" />
            <flux:button variant="subtle" size="sm" wire:click="delete" wire:confirm="{{ __('Delete this branch option?') }}" icon="trash" title="{{ __('Delete Branch') }}" />
        </div>
    </div>
    @if ($branch->description)
        <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">{{ Str::limit($branch->description, 100) }}</p>
    @endif

    {{-- Consequences --}}
    @if ($branch->consequences->isNotEmpty())
        <div class="mt-2 space-y-1">
            @foreach ($branch->consequences as $consequence)
                <div class="flex items-center justify-between rounded bg-zinc-100 px-2 py-1 text-xs dark:bg-zinc-700">
                    <div class="flex items-center gap-2">
                        @php
                            $typeVariant = match($consequence->type) {
                                'immediate' => 'warning',
                                'delayed' => 'outline',
                                'meta' => 'primary',
                                default => 'outline',
                            };
                        @endphp
                        <flux:badge size="sm" :variant="$typeVariant">{{ ucfirst($consequence->type) }}</flux:badge>
                        <span class="text-zinc-600 dark:text-zinc-400">{{ Str::limit($consequence->description, 80) }}</span>
                    </div>
                    <button type="button" wire:click="deleteConsequence({{ $consequence->id }})" wire:confirm="{{ __('Remove this consequence?') }}" class="text-zinc-400 hover:text-red-500 dark:text-zinc-500">
                        &times;
                    </button>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Edit Branch Modal --}}
    <flux:modal wire:model="showForm" class="md:w-xl">
        <flux:heading size="lg">{{ __('Edit Branch Option') }}</flux:heading>

        <div class="flex flex-col gap-4 mt-4">
            <flux:input
                wire:model="label"
                label="{{ __('Label') }}"
                placeholder="{{ __('e.g., Fight the dragon, Negotiate peace, Flee...') }}"
                required
            />
            <flux:textarea
                wire:model="description"
                label="{{ __('Description') }}"
                placeholder="{{ __('What happens if the party chooses this option?') }}"
                rows="3"
            />
        </div>

        <div class="flex justify-end gap-3">
            <flux:button variant="subtle" wire:click="$set('showForm', false)">{{ __('Cancel') }}</flux:button>
            <flux:button variant="primary" wire:click="save">{{ __('Update Branch') }}</flux:button>
        </div>
    </flux:modal>

    {{-- Consequence Form Modal --}}
    @if ($showConsequenceForm)
        <livewire:sessions.consequence-form :branchId="$branch->id" :key="'consequence-form-'.$branch->id" />
    @endif
</div>
