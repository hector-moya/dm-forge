<div class="flex w-full flex-1 flex-col gap-6">
    {{-- Header --}}
    <div class="flex items-center gap-4">
        <flux:button variant="subtle" href="{{ route('campaigns.show', $campaign) }}" wire:navigate icon="arrow-left" size="sm">
            {{ __('Back') }}
        </flux:button>
        <flux:heading size="xl">{{ __('Factions') }} — {{ $campaign->name }}</flux:heading>
    </div>

    {{-- Actions --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div class="flex-1">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="{{ __('Search factions...') }}" icon="magnifying-glass" />
        </div>
        <div class="flex items-center gap-2">
            <flux:button variant="subtle" wire:click="openGenerateModal" icon="sparkles">
                {{ __('Generate Faction') }}
            </flux:button>
            <flux:button variant="primary" wire:click="openForm" icon="plus">
                {{ __('Add Faction') }}
            </flux:button>
        </div>
    </div>

    {{-- Inline Form --}}
    @if ($showForm)
        <div class="rounded-xl border border-zinc-300 bg-zinc-50 p-5 dark:border-zinc-600 dark:bg-zinc-700/50">
            <flux:heading size="base" class="mb-3">
                {{ $editingFactionId ? __('Edit Faction') : __('New Faction') }}
            </flux:heading>
            <div class="flex flex-col gap-3">
                <flux:input wire:model="form.name" label="{{ __('Name') }}" placeholder="{{ __('Faction name...') }}" required />
                <flux:textarea wire:model="form.description" label="{{ __('Description') }}" placeholder="{{ __('Describe this faction...') }}" rows="3" />
                <div class="grid gap-3 sm:grid-cols-3">
                    <flux:input wire:model="form.alignment" label="{{ __('Alignment') }}" placeholder="{{ __('e.g., Lawful Good') }}" />
                    <flux:input wire:model="form.goals" label="{{ __('Goals') }}" placeholder="{{ __('What do they want?') }}" />
                    <flux:input wire:model="form.resources" label="{{ __('Resources') }}" placeholder="{{ __('What do they have?') }}" />
                </div>
                <div class="flex items-center justify-end gap-2">
                    <flux:button variant="subtle" size="sm" wire:click="$set('showForm', false)">{{ __('Cancel') }}</flux:button>
                    <flux:button variant="primary" size="sm" wire:click="save">
                        {{ $editingFactionId ? __('Update Faction') : __('Add Faction') }}
                    </flux:button>
                </div>
            </div>
        </div>
    @endif

    {{-- Faction Table --}}
    @if ($factions->isEmpty())
        <div class="flex flex-1 flex-col items-center justify-center rounded-xl border border-dashed border-zinc-300 p-12 dark:border-zinc-600">
            <div class="text-center">
                <flux:icon name="flag" class="mx-auto mb-4 size-12 text-zinc-400 dark:text-zinc-500" />
                <flux:heading size="lg" class="mb-2">{{ __('No factions found') }}</flux:heading>
                <flux:text class="text-zinc-500 dark:text-zinc-400">
                    {{ __('Create a faction or generate one with AI.') }}
                </flux:text>
            </div>
        </div>
    @else
        <flux:table>
            <flux:table.columns>
                <flux:table.column>{{ __('Name') }}</flux:table.column>
                <flux:table.column>{{ __('Alignment') }}</flux:table.column>
                <flux:table.column>{{ __('Members') }}</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                @foreach ($factions as $faction)
                    <flux:table.row wire:key="faction-{{ $faction->id }}">
                        <flux:table.cell variant="strong">{{ $faction->name }}</flux:table.cell>
                        <flux:table.cell>
                            @if ($faction->alignment)
                                <flux:badge size="sm" variant="outline">{{ $faction->alignment }}</flux:badge>
                            @else
                                —
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>{{ $faction->npcs_count }}</flux:table.cell>
                        <flux:table.cell>
                            <div class="flex items-center justify-end gap-1">
                                <flux:button variant="subtle" size="sm" wire:click="viewFaction({{ $faction->id }})" icon="eye" title="{{ __('View') }}" />
                                <flux:button variant="subtle" size="sm" wire:click="openForm({{ $faction->id }})" icon="pencil" title="{{ __('Edit') }}" />
                                <flux:button variant="subtle" size="sm" wire:click="delete({{ $faction->id }})" wire:confirm="{{ __('Delete this faction?') }}" icon="trash" title="{{ __('Delete') }}" />
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    @endif

    {{-- View Faction Detail --}}
    <flux:modal name="view-faction" class="md:w-xl" variant="flyout">
        @if ($viewingFaction)
            <livewire:factions.faction-card :faction="$viewingFaction" :key="'faction-card-' . $viewingFaction->id" />
        @endif
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
