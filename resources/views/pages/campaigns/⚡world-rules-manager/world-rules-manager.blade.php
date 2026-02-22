<div class="flex w-full flex-1 flex-col gap-6">
    {{-- Header --}}
    <div class="flex items-center gap-4">
        <flux:button variant="subtle" href="{{ route('campaigns.show', $campaign) }}" wire:navigate icon="arrow-left" size="sm">
            {{ __('Back') }}
        </flux:button>
        <flux:heading size="xl">{{ __('World Rules') }} — {{ $campaign->name }}</flux:heading>
    </div>

    {{-- Actions --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div class="flex-1">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="{{ __('Search world rules...') }}" icon="magnifying-glass" />
        </div>
        <div class="flex items-center gap-2">
            <flux:modal.trigger name="create-world-rule">
                <flux:button variant="primary" icon="plus">
                    {{ __('Add World Rule') }}
                </flux:button>
            </flux:modal.trigger>
        </div>
    </div>

    {{-- Table --}}
    @if ($this->campaignWorldRules->isEmpty())
        <div class="flex flex-1 flex-col items-center justify-center rounded-xl border border-dashed border-zinc-300 p-12 dark:border-zinc-600">
            <div class="text-center">
                <flux:icon name="earth" class="mx-auto mb-4 size-12 text-zinc-400 dark:text-zinc-500" />
                <flux:heading size="lg" class="mb-2">{{ __('No world rules found') }}</flux:heading>
                <flux:text class="text-zinc-500 dark:text-zinc-400">
                    {{ __('Add rules that govern how the world works.') }}
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
                @foreach ($this->campaignWorldRules as $worldRule)
                    <flux:table.row wire:key="world-rule-{{ $worldRule->id }}">
                        <flux:table.cell variant="strong">{{ $worldRule->name }}</flux:table.cell>
                        <flux:table.cell>{{ $worldRule->user->name }}</flux:table.cell>
                        <flux:table.cell>{{ $worldRule->created_at->format('M j, Y') }}</flux:table.cell>
                        <flux:table.cell>{{ $worldRule->updated_at->format('M j, Y') }}</flux:table.cell>
                        <flux:table.cell>
                            <div class="flex items-center justify-end gap-1">
                                <flux:modal.trigger name="view-world-rule-{{ $worldRule->id }}">
                                    <flux:button variant="subtle" size="sm" wire:click="openViewWorldRuleModal({{ $worldRule->id }})" icon="eye" title="{{ __('View') }}" />
                                </flux:modal.trigger>
                                <flux:modal.trigger name="create-world-rule">
                                    <flux:button variant="subtle" size="sm" wire:click="setWorldRuleId({{ $worldRule->id }})" icon="pencil" title="{{ __('Edit') }}" />
                                </flux:modal.trigger>
                                <flux:button variant="subtle" size="sm" wire:click="form.destroy({{ $worldRule }})" wire:confirm="{{ __('Delete this world rule?') }}" icon="trash" title="{{ __('Delete') }}" />
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    @endif

    {{-- View Detail --}}
    @if ($selectedWorldRuleId)
        <flux:modal name="view-world-rule-{{ $selectedWorldRuleId }}" class="md:w-xl" variant="flyout" @close="$set('selectedWorldRuleId', null)">
            <livewire:campaigns.world-rule-details :worldRuleId="$selectedWorldRuleId" :key="'world-rule-details-' . $selectedWorldRuleId" />
        </flux:modal>
    @endif

    {{-- Create / Edit Modal --}}
    <flux:modal name="create-world-rule" class="md:w-xl space-y-6" @close="resetSelectedWorldRuleId">
        <flux:heading size="xl" class="mb-3">
            {{ $selectedWorldRuleId ? __('Edit World Rule') : __('New World Rule') }}
        </flux:heading>
        <div class="flex flex-col gap-3">
            <flux:input wire:model="form.name" label="{{ __('Name') }}" placeholder="{{ __('World rule name...') }}" required />
            <flux:textarea wire:model="form.description" label="{{ __('Description') }}" placeholder="{{ __('Describe this world rule...') }}" rows="3" />
            <flux:textarea wire:model="form.dmNotes" label="{{ __('DM Notes') }}" placeholder="{{ __('Notes for the DM...') }}" rows="3" />
            <div class="flex items-center justify-end gap-2">
                <flux:button variant="subtle" size="sm" wire:click="resetSelectedWorldRuleId">{{ __('Cancel') }}</flux:button>
                <flux:button variant="primary" size="sm" wire:click="save">
                    {{ $selectedWorldRuleId ? __('Update World Rule') : __('Add World Rule') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
