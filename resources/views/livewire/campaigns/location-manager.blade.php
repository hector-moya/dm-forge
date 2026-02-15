<div class="flex w-full flex-1 flex-col gap-6">
    {{-- Header --}}
    <div class="flex items-center gap-4">
        <flux:button variant="subtle" href="{{ route('campaigns.show', $campaign) }}" wire:navigate icon="arrow-left" size="sm">
            {{ __('Back') }}
        </flux:button>
        <flux:heading size="xl">{{ __('Locations') }} — {{ $campaign->name }}</flux:heading>
    </div>

    {{-- Actions --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
        <div class="flex-1">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="{{ __('Search locations...') }}" icon="magnifying-glass" />
        </div>
        <div class="flex items-center gap-2">
            <flux:select wire:model.live="regionFilter" placeholder="{{ __('All Regions') }}">
                <flux:select.option value="">{{ __('All Regions') }}</flux:select.option>
                @foreach ($regions as $region)
                    <flux:select.option value="{{ $region }}">{{ $region }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:button variant="subtle" wire:click="openGenerateModal" icon="sparkles">
                {{ __('Generate Location') }}
            </flux:button>
            <flux:button variant="primary" wire:click="openForm" icon="plus">
                {{ __('Add Location') }}
            </flux:button>
        </div>
    </div>

    {{-- Inline Form --}}
    @if ($showForm)
        <div class="rounded-xl border border-zinc-300 bg-zinc-50 p-5 dark:border-zinc-600 dark:bg-zinc-700/50">
            <flux:heading size="base" class="mb-3">
                {{ $editingLocationId ? __('Edit Location') : __('New Location') }}
            </flux:heading>
            <div class="flex flex-col gap-3">
                <flux:input wire:model="locationName" label="{{ __('Name') }}" placeholder="{{ __('Location name...') }}" required />
                <flux:textarea wire:model="locationDescription" label="{{ __('Description') }}" placeholder="{{ __('Describe this location...') }}" rows="3" />
                <div class="grid gap-3 sm:grid-cols-2">
                    <flux:input wire:model="locationRegion" label="{{ __('Region') }}" placeholder="{{ __('e.g., Northern Wastes') }}" />
                    <flux:select wire:model="locationParentId" label="{{ __('Parent Location') }}" placeholder="{{ __('None') }}">
                        <flux:select.option value="">{{ __('None') }}</flux:select.option>
                        @foreach ($allLocations->where('id', '!=', $editingLocationId) as $loc)
                            <flux:select.option value="{{ $loc->id }}">{{ $loc->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </div>
                <div class="flex items-center justify-end gap-2">
                    <flux:button variant="subtle" size="sm" wire:click="$set('showForm', false)">{{ __('Cancel') }}</flux:button>
                    <flux:button variant="primary" size="sm" wire:click="save">
                        {{ $editingLocationId ? __('Update Location') : __('Add Location') }}
                    </flux:button>
                </div>
            </div>
        </div>
    @endif

    {{-- Location Table --}}
    @if ($locations->isEmpty())
        <div class="flex flex-1 flex-col items-center justify-center rounded-xl border border-dashed border-zinc-300 p-12 dark:border-zinc-600">
            <div class="text-center">
                <flux:icon name="map-pin" class="mx-auto mb-4 size-12 text-zinc-400 dark:text-zinc-500" />
                <flux:heading size="lg" class="mb-2">{{ __('No locations found') }}</flux:heading>
                <flux:text class="text-zinc-500 dark:text-zinc-400">
                    {{ __('Create a location or generate one with AI.') }}
                </flux:text>
            </div>
        </div>
    @else
        <flux:table>
            <flux:table.columns>
                <flux:table.column>{{ __('Name') }}</flux:table.column>
                <flux:table.column>{{ __('Region') }}</flux:table.column>
                <flux:table.column>{{ __('Parent') }}</flux:table.column>
                <flux:table.column>{{ __('Sub-locations') }}</flux:table.column>
                <flux:table.column>{{ __('NPCs') }}</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                @foreach ($locations as $location)
                    <flux:table.row wire:key="location-{{ $location->id }}">
                        <flux:table.cell variant="strong">{{ $location->name }}</flux:table.cell>
                        <flux:table.cell>{{ $location->region ?? '—' }}</flux:table.cell>
                        <flux:table.cell>{{ $location->parent?->name ?? '—' }}</flux:table.cell>
                        <flux:table.cell>{{ $location->children_count }}</flux:table.cell>
                        <flux:table.cell>{{ $location->npcs_count }}</flux:table.cell>
                        <flux:table.cell>
                            <div class="flex items-center justify-end gap-1">
                                <flux:button variant="subtle" size="sm" wire:click="viewLocation({{ $location->id }})" icon="eye" title="{{ __('View') }}" />
                                <flux:button variant="subtle" size="sm" wire:click="openForm({{ $location->id }})" icon="pencil" title="{{ __('Edit') }}" />
                                <flux:button variant="subtle" size="sm" wire:click="delete({{ $location->id }})" wire:confirm="{{ __('Delete this location?') }}" icon="trash" title="{{ __('Delete') }}" />
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    @endif

    {{-- View Location Detail --}}
    <flux:modal name="view-location" class="md:w-xl" variant="flyout">
        @if ($viewingLocation)
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">{{ $viewingLocation->name }}</flux:heading>
                    @if ($viewingLocation->region)
                        <flux:text class="mt-1 text-sm text-zinc-500">{{ $viewingLocation->region }}</flux:text>
                    @endif
                    @if ($viewingLocation->parent)
                        <flux:text class="text-sm text-zinc-500">{{ __('Part of') }}: {{ $viewingLocation->parent->name }}</flux:text>
                    @endif
                </div>

                <flux:separator />

                @if ($viewingLocation->description)
                    <div>
                        <flux:heading size="sm" class="mb-1">{{ __('Description') }}</flux:heading>
                        <flux:text class="text-sm whitespace-pre-line">{{ $viewingLocation->description }}</flux:text>
                    </div>
                @endif

                @if ($viewingLocation->children->isNotEmpty())
                    <div>
                        <flux:heading size="sm" class="mb-1">{{ __('Sub-locations') }}</flux:heading>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($viewingLocation->children as $child)
                                <flux:badge size="sm">{{ $child->name }}</flux:badge>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if ($viewingLocation->npcs->isNotEmpty())
                    <div>
                        <flux:heading size="sm" class="mb-1">{{ __('NPCs Here') }}</flux:heading>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($viewingLocation->npcs as $npc)
                                <flux:badge size="sm">{{ $npc->name }}{{ $npc->role ? " ({$npc->role})" : '' }}</flux:badge>
                            @endforeach
                        </div>
                    </div>
                @endif

                <flux:separator />

                @include('livewire.campaigns.partials.entity-history', ['history' => $history])
            </div>
        @endif
    </flux:modal>

    {{-- Generate Modal --}}
    <flux:modal wire:model="showGenerateModal" class="md:w-xl">
        <flux:heading size="lg">{{ __('Generate Location with AI') }}</flux:heading>
        <flux:text class="mt-1">{{ __('Provide optional context to guide the AI, then review and edit the result before saving.') }}</flux:text>

        <div class="mt-4 flex flex-col gap-4">
            <flux:textarea
                wire:model="generateContext"
                label="{{ __('Context (optional)') }}"
                placeholder="{{ __('e.g., A haunted forest at the border of two kingdoms, an underground dwarven forge...') }}"
                rows="3"
            />
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
