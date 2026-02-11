<div class="mx-auto flex w-full max-w-4xl flex-col gap-6">
    {{-- Header --}}
    <div class="flex items-center gap-4">
        <flux:button variant="subtle" href="{{ route('campaigns.show', $campaign) }}" wire:navigate icon="arrow-left" size="sm">
            {{ __('Back') }}
        </flux:button>
        <flux:heading size="xl">{{ __('Edit Campaign') }}</flux:heading>
    </div>

    {{-- Campaign Form --}}
    <form wire:submit="save" class="flex flex-col gap-6">
        <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
            <flux:heading size="lg" class="mb-4">{{ __('Campaign Details') }}</flux:heading>

            <div class="flex flex-col gap-4">
                <flux:input
                    wire:model="name"
                    label="{{ __('Campaign Name') }}"
                    placeholder="{{ __('Enter campaign name...') }}"
                    required
                />

                <flux:select wire:model="status" label="{{ __('Status') }}">
                    <flux:select.option value="draft">{{ __('Draft') }}</flux:select.option>
                    <flux:select.option value="active">{{ __('Active') }}</flux:select.option>
                    <flux:select.option value="archived">{{ __('Archived') }}</flux:select.option>
                </flux:select>

                <flux:textarea
                    wire:model="premise"
                    label="{{ __('Premise') }}"
                    placeholder="{{ __('What is the central premise or hook of this campaign?') }}"
                    rows="3"
                />

                <flux:textarea
                    wire:model="lore"
                    label="{{ __('Lore') }}"
                    placeholder="{{ __('Background lore, history, and world-building details...') }}"
                    rows="5"
                />

                <flux:input
                    wire:model="theme_tone"
                    label="{{ __('Theme & Tone') }}"
                    placeholder="{{ __('e.g., Dark fantasy, Lighthearted adventure, Political intrigue...') }}"
                />

                <flux:textarea
                    wire:model="world_rules"
                    label="{{ __('World Rules') }}"
                    placeholder="{{ __('Special rules, house rules, or world-specific mechanics...') }}"
                    rows="4"
                />

                <flux:textarea
                    wire:model="special_mechanics"
                    label="{{ __('Special Mechanics') }}"
                    placeholder="{{ __('Any special mechanics as JSON or plain text...') }}"
                    rows="3"
                    description="{{ __('Enter as JSON array or plain text.') }}"
                />
            </div>
        </div>

        {{-- Save / Delete buttons --}}
        <div class="flex items-center justify-between">
            <flux:button variant="danger" type="button" wire:click="confirmDelete" icon="trash">
                {{ __('Delete Campaign') }}
            </flux:button>
            <div class="flex items-center gap-3">
                <flux:button variant="subtle" href="{{ route('campaigns.show', $campaign) }}" wire:navigate>
                    {{ __('Cancel') }}
                </flux:button>
                <flux:button type="submit" variant="primary">
                    {{ __('Save Changes') }}
                </flux:button>
            </div>
        </div>
    </form>

    {{-- ── Factions Section ──────────────────────────────────────── --}}
    <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
        <div class="mb-4 flex items-center justify-between">
            <flux:heading size="lg">{{ __('Factions') }}</flux:heading>
            <flux:button variant="primary" size="sm" wire:click="openFactionForm" icon="plus">
                {{ __('Add Faction') }}
            </flux:button>
        </div>

        @if ($showFactionForm)
            <div class="mb-4 rounded-lg border border-zinc-300 bg-zinc-50 p-4 dark:border-zinc-600 dark:bg-zinc-700/50">
                <flux:heading size="base" class="mb-3">
                    {{ $editingFactionId ? __('Edit Faction') : __('New Faction') }}
                </flux:heading>
                <div class="flex flex-col gap-3">
                    <flux:input
                        wire:model="factionName"
                        label="{{ __('Name') }}"
                        placeholder="{{ __('Faction name...') }}"
                        required
                    />
                    <flux:textarea
                        wire:model="factionDescription"
                        label="{{ __('Description') }}"
                        placeholder="{{ __('Describe this faction...') }}"
                        rows="2"
                    />
                    <div class="grid gap-3 sm:grid-cols-3">
                        <flux:input
                            wire:model="factionAlignment"
                            label="{{ __('Alignment') }}"
                            placeholder="{{ __('e.g., Lawful Good') }}"
                        />
                        <flux:input
                            wire:model="factionGoals"
                            label="{{ __('Goals') }}"
                            placeholder="{{ __('What do they want?') }}"
                        />
                        <flux:input
                            wire:model="factionResources"
                            label="{{ __('Resources') }}"
                            placeholder="{{ __('What do they have?') }}"
                        />
                    </div>
                    <div class="flex items-center justify-end gap-2">
                        <flux:button variant="subtle" size="sm" wire:click="$set('showFactionForm', false)">
                            {{ __('Cancel') }}
                        </flux:button>
                        <flux:button variant="primary" size="sm" wire:click="saveFaction">
                            {{ $editingFactionId ? __('Update Faction') : __('Add Faction') }}
                        </flux:button>
                    </div>
                </div>
            </div>
        @endif

        @if ($factions->isEmpty())
            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                {{ __('No factions yet. Add one to get started.') }}
            </flux:text>
        @else
            <div class="space-y-2">
                @foreach ($factions as $faction)
                    <div class="flex items-center justify-between rounded-lg bg-zinc-50 px-4 py-3 dark:bg-zinc-700/50">
                        <div>
                            <span class="font-medium text-zinc-700 dark:text-zinc-200">{{ $faction->name }}</span>
                            @if ($faction->alignment)
                                <flux:badge size="sm" variant="outline" class="ml-2">{{ $faction->alignment }}</flux:badge>
                            @endif
                            @if ($faction->description)
                                <p class="mt-0.5 text-sm text-zinc-500 dark:text-zinc-400">{{ Str::limit($faction->description, 80) }}</p>
                            @endif
                        </div>
                        <div class="flex items-center gap-1">
                            <flux:button variant="subtle" size="sm" wire:click="openFactionForm({{ $faction->id }})" icon="pencil" />
                            <flux:button variant="subtle" size="sm" wire:click="deleteFaction({{ $faction->id }})" wire:confirm="{{ __('Are you sure you want to delete this faction?') }}" icon="trash" />
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- ── Locations Section ─────────────────────────────────────── --}}
    <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
        <div class="mb-4 flex items-center justify-between">
            <flux:heading size="lg">{{ __('Locations') }}</flux:heading>
            <flux:button variant="primary" size="sm" wire:click="openLocationForm" icon="plus">
                {{ __('Add Location') }}
            </flux:button>
        </div>

        @if ($showLocationForm)
            <div class="mb-4 rounded-lg border border-zinc-300 bg-zinc-50 p-4 dark:border-zinc-600 dark:bg-zinc-700/50">
                <flux:heading size="base" class="mb-3">
                    {{ $editingLocationId ? __('Edit Location') : __('New Location') }}
                </flux:heading>
                <div class="flex flex-col gap-3">
                    <flux:input
                        wire:model="locationName"
                        label="{{ __('Name') }}"
                        placeholder="{{ __('Location name...') }}"
                        required
                    />
                    <flux:textarea
                        wire:model="locationDescription"
                        label="{{ __('Description') }}"
                        placeholder="{{ __('Describe this location...') }}"
                        rows="2"
                    />
                    <div class="grid gap-3 sm:grid-cols-2">
                        <flux:input
                            wire:model="locationRegion"
                            label="{{ __('Region') }}"
                            placeholder="{{ __('e.g., Northern Wastes') }}"
                        />
                        <flux:select wire:model="locationParentId" label="{{ __('Parent Location') }}" placeholder="{{ __('None') }}">
                            <flux:select.option value="">{{ __('None') }}</flux:select.option>
                            @foreach ($locations->where('id', '!=', $editingLocationId) as $loc)
                                <flux:select.option value="{{ $loc->id }}">{{ $loc->name }}</flux:select.option>
                            @endforeach
                        </flux:select>
                    </div>
                    <div class="flex items-center justify-end gap-2">
                        <flux:button variant="subtle" size="sm" wire:click="$set('showLocationForm', false)">
                            {{ __('Cancel') }}
                        </flux:button>
                        <flux:button variant="primary" size="sm" wire:click="saveLocation">
                            {{ $editingLocationId ? __('Update Location') : __('Add Location') }}
                        </flux:button>
                    </div>
                </div>
            </div>
        @endif

        @if ($locations->isEmpty())
            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                {{ __('No locations yet. Add one to get started.') }}
            </flux:text>
        @else
            <div class="space-y-2">
                @foreach ($locations as $location)
                    <div class="flex items-center justify-between rounded-lg bg-zinc-50 px-4 py-3 dark:bg-zinc-700/50">
                        <div>
                            <span class="font-medium text-zinc-700 dark:text-zinc-200">{{ $location->name }}</span>
                            @if ($location->region)
                                <span class="ml-2 text-sm text-zinc-500 dark:text-zinc-400">{{ $location->region }}</span>
                            @endif
                            @if ($location->description)
                                <p class="mt-0.5 text-sm text-zinc-500 dark:text-zinc-400">{{ Str::limit($location->description, 80) }}</p>
                            @endif
                        </div>
                        <div class="flex items-center gap-1">
                            <flux:button variant="subtle" size="sm" wire:click="openLocationForm({{ $location->id }})" icon="pencil" />
                            <flux:button variant="subtle" size="sm" wire:click="deleteLocation({{ $location->id }})" wire:confirm="{{ __('Are you sure you want to delete this location?') }}" icon="trash" />
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- ── NPCs Section ──────────────────────────────────────────── --}}
    <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
        <div class="mb-4 flex items-center justify-between">
            <flux:heading size="lg">{{ __('NPCs') }}</flux:heading>
            <flux:button variant="primary" size="sm" wire:click="openNpcForm" icon="plus">
                {{ __('Add NPC') }}
            </flux:button>
        </div>

        @if ($showNpcForm)
            <div class="mb-4 rounded-lg border border-zinc-300 bg-zinc-50 p-4 dark:border-zinc-600 dark:bg-zinc-700/50">
                <flux:heading size="base" class="mb-3">
                    {{ $editingNpcId ? __('Edit NPC') : __('New NPC') }}
                </flux:heading>
                <div class="flex flex-col gap-3">
                    <div class="grid gap-3 sm:grid-cols-2">
                        <flux:input
                            wire:model="npcName"
                            label="{{ __('Name') }}"
                            placeholder="{{ __('NPC name...') }}"
                            required
                        />
                        <flux:input
                            wire:model="npcRole"
                            label="{{ __('Role') }}"
                            placeholder="{{ __('e.g., Blacksmith, Quest Giver, Villain...') }}"
                        />
                    </div>
                    <flux:textarea
                        wire:model="npcDescription"
                        label="{{ __('Description') }}"
                        placeholder="{{ __('Physical appearance and background...') }}"
                        rows="2"
                    />
                    <div class="grid gap-3 sm:grid-cols-2">
                        <flux:textarea
                            wire:model="npcPersonality"
                            label="{{ __('Personality') }}"
                            placeholder="{{ __('Personality traits...') }}"
                            rows="2"
                        />
                        <flux:textarea
                            wire:model="npcMotivation"
                            label="{{ __('Motivation') }}"
                            placeholder="{{ __('What drives this NPC?') }}"
                            rows="2"
                        />
                    </div>
                    <div class="grid gap-3 sm:grid-cols-3">
                        <flux:select wire:model="npcFactionId" label="{{ __('Faction') }}" placeholder="{{ __('None') }}">
                            <flux:select.option value="">{{ __('None') }}</flux:select.option>
                            @foreach ($factions as $faction)
                                <flux:select.option value="{{ $faction->id }}">{{ $faction->name }}</flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:select wire:model="npcLocationId" label="{{ __('Location') }}" placeholder="{{ __('None') }}">
                            <flux:select.option value="">{{ __('None') }}</flux:select.option>
                            @foreach ($locations as $location)
                                <flux:select.option value="{{ $location->id }}">{{ $location->name }}</flux:select.option>
                            @endforeach
                        </flux:select>
                        <div class="flex items-end pb-1">
                            <label class="flex items-center gap-2 text-sm text-zinc-700 dark:text-zinc-300">
                                <input type="checkbox" wire:model="npcIsAlive"
                                       class="rounded border-zinc-300 text-accent focus:ring-accent dark:border-zinc-600 dark:bg-zinc-700" />
                                {{ __('Alive') }}
                            </label>
                        </div>
                    </div>
                    <div class="flex items-center justify-end gap-2">
                        <flux:button variant="subtle" size="sm" wire:click="$set('showNpcForm', false)">
                            {{ __('Cancel') }}
                        </flux:button>
                        <flux:button variant="primary" size="sm" wire:click="saveNpc">
                            {{ $editingNpcId ? __('Update NPC') : __('Add NPC') }}
                        </flux:button>
                    </div>
                </div>
            </div>
        @endif

        @if ($npcs->isEmpty())
            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                {{ __('No NPCs yet. Add one to get started.') }}
            </flux:text>
        @else
            <div class="space-y-2">
                @foreach ($npcs as $npc)
                    <div class="flex items-center justify-between rounded-lg bg-zinc-50 px-4 py-3 dark:bg-zinc-700/50">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-medium text-zinc-700 dark:text-zinc-200">{{ $npc->name }}</span>
                                @if ($npc->role)
                                    <span class="text-sm text-zinc-500 dark:text-zinc-400">{{ $npc->role }}</span>
                                @endif
                                @if (! $npc->is_alive)
                                    <flux:badge size="sm" variant="danger">{{ __('Dead') }}</flux:badge>
                                @endif
                            </div>
                            <div class="mt-0.5 flex items-center gap-3 text-xs text-zinc-500 dark:text-zinc-400">
                                @if ($npc->faction)
                                    <span>{{ $npc->faction->name }}</span>
                                @endif
                                @if ($npc->location)
                                    <span>{{ $npc->location->name }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="flex items-center gap-1">
                            <flux:button variant="subtle" size="sm" wire:click="openNpcForm({{ $npc->id }})" icon="pencil" />
                            <flux:button variant="subtle" size="sm" wire:click="deleteNpc({{ $npc->id }})" wire:confirm="{{ __('Are you sure you want to delete this NPC?') }}" icon="trash" />
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Delete Campaign Modal --}}
    <flux:modal wire:model="showDeleteModal" variant="dialog">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Delete Campaign') }}</flux:heading>
                <flux:text class="mt-2">
                    {{ __('Are you sure you want to delete ":name"? This action cannot be undone and will remove all associated factions, locations, NPCs, characters, and sessions.', ['name' => $campaign->name]) }}
                </flux:text>
            </div>
            <div class="flex justify-end gap-3">
                <flux:button variant="subtle" wire:click="$set('showDeleteModal', false)">
                    {{ __('Cancel') }}
                </flux:button>
                <flux:button variant="danger" wire:click="deleteCampaign">
                    {{ __('Delete Campaign') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
