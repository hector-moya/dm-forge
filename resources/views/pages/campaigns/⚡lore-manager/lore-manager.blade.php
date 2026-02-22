<div class="flex w-full flex-1 flex-col gap-6">
    {{-- Header --}}
    <div class="flex items-center gap-4">
        <flux:button variant="subtle" href="{{ route('campaigns.show', $campaign) }}" wire:navigate icon="arrow-left" size="sm">
            {{ __('Back') }}
        </flux:button>
        <flux:heading size="xl">{{ __('Lore') }} — {{ $campaign->name }}</flux:heading>
    </div>

    {{-- Actions --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div class="flex-1">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="{{ __('Search lore...') }}" icon="magnifying-glass" />
        </div>
        <div class="flex items-center gap-2">
            <flux:button variant="subtle" wire:click="openGenerateModal" icon="sparkles">
                {{ __('Generate Lore') }}
            </flux:button>
            <flux:modal.trigger name="create-lore">
                <flux:button variant="primary" icon="plus">
                    {{ __('Add Lore') }}
                </flux:button>
            </flux:modal.trigger>
        </div>
    </div>

    {{-- Faction Table --}}
    @if ($this->campaignLore->isEmpty())
        <div class="flex flex-1 flex-col items-center justify-center rounded-xl border border-dashed border-zinc-300 p-12 dark:border-zinc-600">
            <div class="text-center">
                <flux:icon name="flag" class="mx-auto mb-4 size-12 text-zinc-400 dark:text-zinc-500" />
                <flux:heading size="lg" class="mb-2">{{ __('No lore found') }}</flux:heading>
                <flux:text class="text-zinc-500 dark:text-zinc-400">
                    {{ __('Create lore or generate one with AI.') }}
                </flux:text>
            </div>
        </div>
    @else
        <flux:table>
            <flux:table.columns>
                <flux:table.column>{{ __('Name') }}</flux:table.column>
                <flux:table.column>{{ __('Created by') }}</flux:table.column>
                <flux:table.column>{{ __('Created at') }}</flux:table.column>
                <flux:table.column>{{ __('Updated at') }}</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                @foreach ($this->campaignLore as $lore)
                    <flux:table.row wire:key="lore-{{ $lore->id }}">
                        <flux:table.cell variant="strong">{{ $lore->name }}</flux:table.cell>
                        <flux:table.cell>{{ $lore->user->name }} </flux:table.cell>
                        <flux:table.cell>{{ $lore->created_at->format('M j, Y') }}</flux:table.cell>
                        <flux:table.cell>{{ $lore->updated_at->format('M j, Y') }}</flux:table.cell>
                        <flux:table.cell>
                            <div class="flex items-center justify-end gap-1">
                                <flux:modal.trigger name="view-lore-{{ $lore->id }}">
                                    <flux:button variant="subtle" size="sm" wire:click="openViewLoreModal({{ $lore->id }})" icon="eye" title="{{ __('View') }}" />
                                </flux:modal.trigger>
                                <flux:modal.trigger name="create-lore">
                                    <flux:button variant="subtle" size="sm" wire:click="setLoreId({{ $lore->id }})" icon="pencil" title="{{ __('Edit') }}" />
                                </flux:modal.trigger>
                                <flux:button variant="subtle" size="sm" wire:click="form.destroy({{ $lore }})" wire:confirm="{{ __('Delete this lore?') }}" icon="trash" title="{{ __('Delete') }}" />
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    @endif

    {{-- View Faction Detail --}}
    @if ($selectedLoreId)
    <flux:modal name="view-lore-{{ $selectedLoreId }}" class="md:w-xl" variant="flyout" @close="$set('selectedLoreId', null)">
            <livewire:campaigns.lore-details :loreId="$selectedLoreId" :key="'lore-details-' . $selectedLoreId" />
    </flux:modal>
    @endif

    {{-- Inline Form --}}
    <flux:modal name="create-lore" class="md:w-xl space-y-6" @close="resetSelectedLoreId">
        <flux:heading size="xl" class="mb-3">
            {{ $selectedLoreId ? __('Edit Lore') : __('New Lore') }}
        </flux:heading>
        <div class="flex flex-col gap-3">
            <flux:input wire:model="form.name" label="{{ __('Name') }}" placeholder="{{ __('Lore name...') }}" required />
            <flux:textarea wire:model="form.description" label="{{ __('Description') }}" placeholder="{{ __('Describe this lore...') }}" rows="3" />
            <flux:textarea wire:model="form.dmNotes" label="{{ __('DM Notes') }}" placeholder="{{ __('Describe this lore for the DM...') }}" rows="3" />
            <div class="flex items-center justify-end gap-2">
                <flux:button variant="subtle" size="sm" wire:click="resetSelectedLoreId">{{ __('Cancel') }}</flux:button>
                <flux:button variant="primary" size="sm" wire:click="save">
                    {{ $selectedLoreId ? __('Update Lore') : __('Add Lore') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Generate Modal --}}
    <flux:modal wire:model="showGenerateModal" class="md:w-xl">
        <flux:heading size="lg">{{ __('Generate Faction with AI') }}</flux:heading>
        <flux:text class="mt-1">{{ __('Provide optional context to guide the AI, then review and edit the result before saving.') }}</flux:text>

        <div class="mt-4 flex flex-col gap-4">
            <flux:textarea wire:model="generateContext" label="{{ __('Context (optional)') }}" placeholder="{{ __('e.g., A secret guild of assassins, an order of paladins protecting the realm...') }}" rows="3" />
            <flux:checkbox wire:model="generateImageOnCreate" label="{{ __('Also generate image') }}" />
        </div>

        <div class="mt-4 flex justify-end gap-3">
            <flux:button variant="subtle" wire:click="$set('showGenerateModal', false)">{{ __('Cancel') }}</flux:button>
            <flux:button variant="primary" wire:click="generate" icon="sparkles" wire:loading.attr="disabled" wire:target="generate">
                <span wire:loading.remove wire:target="generate">{{ __('Generate') }}</span>
                <span wire:loading wire:target="generate">{{ __('Generating...') }}</span>
            </flux:button>
        </div>
    </flux:modal>
</div>
