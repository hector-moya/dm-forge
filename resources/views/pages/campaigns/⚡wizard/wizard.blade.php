<div class="mx-auto flex w-full max-w-4xl flex-col gap-6">
    {{-- Header --}}
    <div class="flex items-center gap-4">
        <flux:button variant="subtle" href="{{ route('dashboard') }}" wire:navigate icon="arrow-left" size="sm">
            {{ __('Dashboard') }}
        </flux:button>
        <flux:heading size="xl">{{ __('Campaign Wizard') }}</flux:heading>
    </div>

    {{-- Step Indicator --}}
    <div class="flex items-center justify-between rounded-xl border border-zinc-200 bg-white px-6 py-4 dark:border-zinc-700 dark:bg-zinc-800">
        @php
            $steps = [
                1 => 'Basics',
                2 => 'World',
                3 => 'Factions',
                4 => 'Locations',
                5 => 'NPCs',
                6 => 'Characters',
                7 => 'Review',
            ];
        @endphp
        @foreach ($steps as $num => $label)
            <button
                wire:click="goToStep({{ $num }})"
                class="flex items-center gap-2 text-sm font-medium transition {{ $num === $currentStep ? 'text-accent' : ($num < $currentStep ? 'cursor-pointer text-zinc-600 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-zinc-200' : 'cursor-not-allowed text-zinc-400 dark:text-zinc-600') }}"
                @if ($num > $currentStep) disabled @endif
            >
                <span class="flex h-7 w-7 items-center justify-center rounded-full text-xs font-bold {{ $num === $currentStep ? 'bg-accent text-white' : ($num < $currentStep ? 'bg-zinc-200 text-zinc-600 dark:bg-zinc-600 dark:text-zinc-300' : 'bg-zinc-100 text-zinc-400 dark:bg-zinc-700 dark:text-zinc-600') }}">
                    @if ($num < $currentStep)
                        <flux:icon name="check" variant="mini" class="size-4" />
                    @else
                        {{ $num }}
                    @endif
                </span>
                <span class="hidden sm:inline">{{ __($label) }}</span>
            </button>
            @if (! $loop->last)
                <div class="hidden h-px flex-1 sm:block {{ $num < $currentStep ? 'bg-accent/30' : 'bg-zinc-200 dark:bg-zinc-700' }}"></div>
            @endif
        @endforeach
    </div>

    {{-- Step Content --}}
    <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
        {{-- Step 1: Basics --}}
        @if ($currentStep === 1)
            <flux:heading size="lg" class="mb-4">{{ __('Campaign Basics') }}</flux:heading>
            <flux:text class="mb-4">{{ __('Start with the core identity of your campaign.') }}</flux:text>

            <div class="flex flex-col gap-4">
                <flux:input
                    wire:model="name"
                    label="{{ __('Campaign Name') }}"
                    placeholder="{{ __('e.g., The Curse of Strahd, Tomb of Annihilation...') }}"
                    required
                />
                <flux:textarea
                    wire:model="premise"
                    label="{{ __('Premise') }}"
                    placeholder="{{ __('What is the central hook or premise of this campaign?') }}"
                    rows="3"
                />
                <flux:input
                    wire:model="theme_tone"
                    label="{{ __('Theme & Tone') }}"
                    placeholder="{{ __('e.g., Dark fantasy, Lighthearted adventure, Political intrigue...') }}"
                />
            </div>

        {{-- Step 2: World Building --}}
        @elseif ($currentStep === 2)
            <div class="mb-4 flex items-center justify-between">
                <div>
                    <flux:heading size="lg">{{ __('World Building') }}</flux:heading>
                    <flux:text>{{ __('Define the lore and rules of your world.') }}</flux:text>
                </div>
                <flux:button variant="subtle" size="sm" wire:click="suggestWorld" icon="sparkles" wire:loading.attr="disabled" wire:target="suggestWorld">
                    <span wire:loading.remove wire:target="suggestWorld">{{ __('AI Suggest') }}</span>
                    <span wire:loading wire:target="suggestWorld">{{ __('Generating...') }}</span>
                </flux:button>
            </div>

            <div class="flex flex-col gap-4">
                <flux:textarea
                    wire:model="lore"
                    label="{{ __('Lore') }}"
                    placeholder="{{ __('Background lore, history, and world-building details...') }}"
                    rows="6"
                />
                <flux:textarea
                    wire:model="world_rules"
                    label="{{ __('World Rules') }}"
                    placeholder="{{ __('Special rules, unique aspects, or house rules for this world...') }}"
                    rows="4"
                />
            </div>

        {{-- Step 3: Factions --}}
        @elseif ($currentStep === 3)
            <div class="mb-4 flex items-center justify-between">
                <div>
                    <flux:heading size="lg">{{ __('Factions') }}</flux:heading>
                    <flux:text>{{ __('Define the major factions in your campaign world.') }}</flux:text>
                </div>
                <flux:button variant="subtle" size="sm" wire:click="suggestFactions" icon="sparkles" wire:loading.attr="disabled" wire:target="suggestFactions">
                    <span wire:loading.remove wire:target="suggestFactions">{{ __('AI Suggest') }}</span>
                    <span wire:loading wire:target="suggestFactions">{{ __('Generating...') }}</span>
                </flux:button>
            </div>

            @if (count($factions) > 0)
                <div class="mb-4 space-y-2">
                    @foreach ($factions as $index => $faction)
                        <div class="flex items-center justify-between rounded-lg bg-zinc-50 px-4 py-3 dark:bg-zinc-700/50">
                            <div>
                                <span class="font-medium text-zinc-700 dark:text-zinc-200">{{ $faction['name'] }}</span>
                                @if ($faction['alignment'])
                                    <flux:badge size="sm" variant="outline" class="ml-2">{{ $faction['alignment'] }}</flux:badge>
                                @endif
                                @if ($faction['description'])
                                    <p class="mt-0.5 text-sm text-zinc-500 dark:text-zinc-400">{{ Str::limit($faction['description'], 80) }}</p>
                                @endif
                            </div>
                            <flux:button variant="subtle" size="sm" wire:click="removeFaction({{ $index }})" icon="x-mark" />
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="rounded-lg border border-zinc-300 bg-zinc-50 p-4 dark:border-zinc-600 dark:bg-zinc-700/50">
                <flux:heading size="base" class="mb-3">{{ __('Add Faction') }}</flux:heading>
                <div class="flex flex-col gap-3">
                    <flux:input wire:model="factionName" label="{{ __('Name') }}" placeholder="{{ __('Faction name...') }}" />
                    <flux:textarea wire:model="factionDescription" label="{{ __('Description') }}" placeholder="{{ __('Describe this faction...') }}" rows="2" />
                    <div class="grid gap-3 sm:grid-cols-2">
                        <flux:input wire:model="factionAlignment" label="{{ __('Alignment') }}" placeholder="{{ __('e.g., Lawful Good') }}" />
                        <flux:input wire:model="factionGoals" label="{{ __('Goals') }}" placeholder="{{ __('What do they want?') }}" />
                    </div>
                    <div class="flex justify-end">
                        <flux:button variant="primary" size="sm" wire:click="addFaction" icon="plus">{{ __('Add') }}</flux:button>
                    </div>
                </div>
            </div>

        {{-- Step 4: Locations --}}
        @elseif ($currentStep === 4)
            <div class="mb-4 flex items-center justify-between">
                <div>
                    <flux:heading size="lg">{{ __('Locations') }}</flux:heading>
                    <flux:text>{{ __('Define key locations in your campaign world.') }}</flux:text>
                </div>
                <flux:button variant="subtle" size="sm" wire:click="suggestLocations" icon="sparkles" wire:loading.attr="disabled" wire:target="suggestLocations">
                    <span wire:loading.remove wire:target="suggestLocations">{{ __('AI Suggest') }}</span>
                    <span wire:loading wire:target="suggestLocations">{{ __('Generating...') }}</span>
                </flux:button>
            </div>

            @if (count($locations) > 0)
                <div class="mb-4 space-y-2">
                    @foreach ($locations as $index => $location)
                        <div class="flex items-center justify-between rounded-lg bg-zinc-50 px-4 py-3 dark:bg-zinc-700/50">
                            <div>
                                <span class="font-medium text-zinc-700 dark:text-zinc-200">{{ $location['name'] }}</span>
                                @if ($location['region'])
                                    <span class="ml-2 text-sm text-zinc-500 dark:text-zinc-400">{{ $location['region'] }}</span>
                                @endif
                                @if ($location['description'])
                                    <p class="mt-0.5 text-sm text-zinc-500 dark:text-zinc-400">{{ Str::limit($location['description'], 80) }}</p>
                                @endif
                            </div>
                            <flux:button variant="subtle" size="sm" wire:click="removeLocation({{ $index }})" icon="x-mark" />
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="rounded-lg border border-zinc-300 bg-zinc-50 p-4 dark:border-zinc-600 dark:bg-zinc-700/50">
                <flux:heading size="base" class="mb-3">{{ __('Add Location') }}</flux:heading>
                <div class="flex flex-col gap-3">
                    <flux:input wire:model="locationName" label="{{ __('Name') }}" placeholder="{{ __('Location name...') }}" />
                    <flux:textarea wire:model="locationDescription" label="{{ __('Description') }}" placeholder="{{ __('Describe this location...') }}" rows="2" />
                    <flux:input wire:model="locationRegion" label="{{ __('Region') }}" placeholder="{{ __('e.g., Northern Wastes, Coastal Towns...') }}" />
                    <div class="flex justify-end">
                        <flux:button variant="primary" size="sm" wire:click="addLocation" icon="plus">{{ __('Add') }}</flux:button>
                    </div>
                </div>
            </div>

        {{-- Step 5: NPCs --}}
        @elseif ($currentStep === 5)
            <div class="mb-4 flex items-center justify-between">
                <div>
                    <flux:heading size="lg">{{ __('NPCs') }}</flux:heading>
                    <flux:text>{{ __('Define notable NPCs in your campaign world.') }}</flux:text>
                </div>
                <flux:button variant="subtle" size="sm" wire:click="suggestNpcs" icon="sparkles" wire:loading.attr="disabled" wire:target="suggestNpcs">
                    <span wire:loading.remove wire:target="suggestNpcs">{{ __('AI Suggest') }}</span>
                    <span wire:loading wire:target="suggestNpcs">{{ __('Generating...') }}</span>
                </flux:button>
            </div>

            @if (count($npcs) > 0)
                <div class="mb-4 space-y-2">
                    @foreach ($npcs as $index => $npc)
                        <div class="flex items-center justify-between rounded-lg bg-zinc-50 px-4 py-3 dark:bg-zinc-700/50">
                            <div>
                                <span class="font-medium text-zinc-700 dark:text-zinc-200">{{ $npc['name'] }}</span>
                                @if ($npc['role'])
                                    <flux:badge size="sm" variant="outline" class="ml-2">{{ $npc['role'] }}</flux:badge>
                                @endif
                                @if ($npc['description'])
                                    <p class="mt-0.5 text-sm text-zinc-500 dark:text-zinc-400">{{ Str::limit($npc['description'], 80) }}</p>
                                @endif
                            </div>
                            <flux:button variant="subtle" size="sm" wire:click="removeNpc({{ $index }})" icon="x-mark" />
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="rounded-lg border border-zinc-300 bg-zinc-50 p-4 dark:border-zinc-600 dark:bg-zinc-700/50">
                <flux:heading size="base" class="mb-3">{{ __('Add NPC') }}</flux:heading>
                <div class="flex flex-col gap-3">
                    <div class="grid gap-3 sm:grid-cols-2">
                        <flux:input wire:model="npcName" label="{{ __('Name') }}" placeholder="{{ __('NPC name...') }}" />
                        <flux:input wire:model="npcRole" label="{{ __('Role') }}" placeholder="{{ __('e.g., Blacksmith, Quest Giver') }}" />
                    </div>
                    <flux:textarea wire:model="npcDescription" label="{{ __('Description') }}" placeholder="{{ __('Physical appearance, background...') }}" rows="2" />
                    <flux:textarea wire:model="npcPersonality" label="{{ __('Personality') }}" placeholder="{{ __('Traits, temperament, quirks...') }}" rows="2" />
                    <div class="flex justify-end">
                        <flux:button variant="primary" size="sm" wire:click="addNpc" icon="plus">{{ __('Add') }}</flux:button>
                    </div>
                </div>
            </div>

        {{-- Step 6: Characters --}}
        @elseif ($currentStep === 6)
            <flux:heading size="lg" class="mb-1">{{ __('Party Members') }}</flux:heading>
            <flux:text class="mb-4">{{ __('Add the player characters for this campaign. You can add more later.') }}</flux:text>

            @if (count($characters) > 0)
                <div class="mb-4 space-y-2">
                    @foreach ($characters as $index => $character)
                        <div class="flex items-center justify-between rounded-lg bg-zinc-50 px-4 py-3 dark:bg-zinc-700/50">
                            <div>
                                <span class="font-medium text-zinc-700 dark:text-zinc-200">{{ $character['name'] }}</span>
                                @if ($character['class'])
                                    <span class="ml-2 text-sm text-zinc-500 dark:text-zinc-400">{{ $character['class'] }} {{ __('Lv.') }} {{ $character['level'] }}</span>
                                @endif
                                @if ($character['player_name'])
                                    <span class="ml-2 text-xs text-zinc-400 dark:text-zinc-500">({{ $character['player_name'] }})</span>
                                @endif
                            </div>
                            <flux:button variant="subtle" size="sm" wire:click="removeCharacter({{ $index }})" icon="x-mark" />
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="rounded-lg border border-zinc-300 bg-zinc-50 p-4 dark:border-zinc-600 dark:bg-zinc-700/50">
                <flux:heading size="base" class="mb-3">{{ __('Add Character') }}</flux:heading>
                <div class="flex flex-col gap-3">
                    <div class="grid gap-3 sm:grid-cols-2">
                        <flux:input wire:model="characterName" label="{{ __('Character Name') }}" placeholder="{{ __('e.g., Thorin Ironforge') }}" />
                        <flux:input wire:model="characterPlayerName" label="{{ __('Player Name') }}" placeholder="{{ __('Real name of the player...') }}" />
                    </div>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <flux:input wire:model="characterClass" label="{{ __('Class') }}" placeholder="{{ __('e.g., Fighter, Wizard, Rogue...') }}" />
                        <flux:input wire:model="characterLevel" type="number" label="{{ __('Level') }}" min="1" max="20" />
                    </div>
                    <div class="flex justify-end">
                        <flux:button variant="primary" size="sm" wire:click="addCharacter" icon="plus">{{ __('Add') }}</flux:button>
                    </div>
                </div>
            </div>

        {{-- Step 7: Review --}}
        @elseif ($currentStep === 7)
            <flux:heading size="lg" class="mb-4">{{ __('Review & Create') }}</flux:heading>
            <flux:text class="mb-4">{{ __('Review your campaign details before creating.') }}</flux:text>

            <div class="space-y-4">
                <div class="rounded-lg bg-zinc-50 p-4 dark:bg-zinc-700/50">
                    <flux:heading size="base" class="mb-2">{{ __('Campaign') }}</flux:heading>
                    <dl class="space-y-1 text-sm">
                        <div><dt class="inline font-medium text-zinc-600 dark:text-zinc-400">{{ __('Name:') }}</dt> <dd class="inline text-zinc-800 dark:text-zinc-200">{{ $name }}</dd></div>
                        @if ($premise)
                            <div><dt class="inline font-medium text-zinc-600 dark:text-zinc-400">{{ __('Premise:') }}</dt> <dd class="inline text-zinc-800 dark:text-zinc-200">{{ Str::limit($premise, 120) }}</dd></div>
                        @endif
                        @if ($theme_tone)
                            <div><dt class="inline font-medium text-zinc-600 dark:text-zinc-400">{{ __('Tone:') }}</dt> <dd class="inline text-zinc-800 dark:text-zinc-200">{{ $theme_tone }}</dd></div>
                        @endif
                    </dl>
                </div>

                @if ($lore || $world_rules)
                    <div class="rounded-lg bg-zinc-50 p-4 dark:bg-zinc-700/50">
                        <flux:heading size="base" class="mb-2">{{ __('World') }}</flux:heading>
                        <div class="space-y-1 text-sm text-zinc-800 dark:text-zinc-200">
                            @if ($lore)
                                <p>{{ Str::limit($lore, 200) }}</p>
                            @endif
                            @if ($world_rules)
                                <p class="text-zinc-500 dark:text-zinc-400">{{ Str::limit($world_rules, 150) }}</p>
                            @endif
                        </div>
                    </div>
                @endif

                @if (count($factions) > 0)
                    <div class="rounded-lg bg-zinc-50 p-4 dark:bg-zinc-700/50">
                        <flux:heading size="base" class="mb-2">{{ __('Factions') }} ({{ count($factions) }})</flux:heading>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($factions as $faction)
                                <flux:badge>{{ $faction['name'] }}</flux:badge>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if (count($locations) > 0)
                    <div class="rounded-lg bg-zinc-50 p-4 dark:bg-zinc-700/50">
                        <flux:heading size="base" class="mb-2">{{ __('Locations') }} ({{ count($locations) }})</flux:heading>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($locations as $location)
                                <flux:badge>{{ $location['name'] }}</flux:badge>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if (count($npcs) > 0)
                    <div class="rounded-lg bg-zinc-50 p-4 dark:bg-zinc-700/50">
                        <flux:heading size="base" class="mb-2">{{ __('NPCs') }} ({{ count($npcs) }})</flux:heading>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($npcs as $npc)
                                <flux:badge>{{ $npc['name'] }}{{ $npc['role'] ? " — {$npc['role']}" : '' }}</flux:badge>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if (count($characters) > 0)
                    <div class="rounded-lg bg-zinc-50 p-4 dark:bg-zinc-700/50">
                        <flux:heading size="base" class="mb-2">{{ __('Party Members') }} ({{ count($characters) }})</flux:heading>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($characters as $character)
                                <flux:badge>{{ $character['name'] }} — {{ $character['class'] }} {{ __('Lv.') }} {{ $character['level'] }}</flux:badge>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        @endif
    </div>

    {{-- Navigation Buttons --}}
    <div class="flex items-center justify-between">
        @if ($currentStep > 1)
            <flux:button variant="subtle" wire:click="previousStep" icon="arrow-left">
                {{ __('Back') }}
            </flux:button>
        @else
            <div></div>
        @endif

        @if ($currentStep < $totalSteps)
            <flux:button variant="primary" wire:click="nextStep" icon-trailing="arrow-right">
                {{ __('Next') }}
            </flux:button>
        @else
            <flux:button variant="primary" wire:click="createCampaign" icon="check">
                {{ __('Create Campaign') }}
            </flux:button>
        @endif
    </div>
</div>
