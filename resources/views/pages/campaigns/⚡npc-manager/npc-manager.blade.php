<div class="flex w-full flex-1 flex-col gap-6">
    {{-- Header --}}
    <div class="flex items-center gap-4">
        <flux:button variant="subtle" href="{{ route('campaigns.show', $campaign) }}" wire:navigate icon="arrow-left" size="sm">
            {{ __('Back') }}
        </flux:button>
        <flux:heading size="xl">{{ __('NPCs') }} — {{ $campaign->name }}</flux:heading>
    </div>

    {{-- Actions --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
        <div class="flex-1">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="{{ __('Search NPCs...') }}" icon="magnifying-glass" />
        </div>
        <div class="flex items-center gap-2">
            <flux:select wire:model.live="factionFilter" placeholder="{{ __('All Factions') }}">
                <flux:select.option value="">{{ __('All Factions') }}</flux:select.option>
                @foreach ($factions as $faction)
                    <flux:select.option value="{{ $faction->id }}">{{ $faction->name }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:select wire:model.live="aliveFilter" placeholder="{{ __('All') }}">
                <flux:select.option value="all">{{ __('All') }}</flux:select.option>
                <flux:select.option value="alive">{{ __('Alive') }}</flux:select.option>
                <flux:select.option value="dead">{{ __('Dead') }}</flux:select.option>
            </flux:select>
            <flux:button variant="subtle" wire:click="openGenerateModal" icon="sparkles">
                {{ __('Generate NPC') }}
            </flux:button>
            <flux:button variant="primary" wire:click="openForm" icon="plus">
                {{ __('Add NPC') }}
            </flux:button>
        </div>
    </div>

    {{-- Inline Form --}}
    @if ($showForm)
        <div class="rounded-xl border border-zinc-300 bg-zinc-50 p-5 dark:border-zinc-600 dark:bg-zinc-700/50">
            <flux:heading size="base" class="mb-3">
                {{ $editingNpcId ? __('Edit NPC') : __('New NPC') }}
            </flux:heading>
            <div class="flex flex-col gap-4">

                {{-- Narrative Fields --}}
                <div class="grid gap-3 sm:grid-cols-2">
                    <flux:input wire:model="npcName" label="{{ __('Name') }}" placeholder="{{ __('NPC name...') }}" required />
                    <flux:input wire:model="npcRole" label="{{ __('Role') }}" placeholder="{{ __('e.g., Blacksmith, Quest Giver') }}" />
                </div>
                <flux:textarea wire:model="npcDescription" label="{{ __('Description') }}" placeholder="{{ __('Physical appearance, background...') }}" rows="3" />
                <div class="grid gap-3 sm:grid-cols-2">
                    <flux:textarea wire:model="npcPersonality" label="{{ __('Personality') }}" placeholder="{{ __('Traits, temperament...') }}" rows="2" />
                    <flux:textarea wire:model="npcMotivation" label="{{ __('Motivation') }}" placeholder="{{ __('Goals, fears, desires...') }}" rows="2" />
                </div>
                <flux:textarea wire:model="npcBackstory" label="{{ __('Backstory') }}" placeholder="{{ __('Key events that shaped this NPC...') }}" rows="2" />
                <div class="grid gap-3 sm:grid-cols-2">
                    <flux:textarea wire:model="npcVoiceDescription" label="{{ __('Voice Description') }}" placeholder="{{ __('Accent, pitch, cadence...') }}" rows="2" />
                    <flux:textarea wire:model="npcSpeechPatterns" label="{{ __('Speech Patterns') }}" placeholder="{{ __('Formal, slang, poetic...') }}" rows="2" />
                </div>
                <flux:textarea wire:model="npcCatchphrases" label="{{ __('Catchphrases') }}" placeholder="{{ __('One per line...') }}" rows="2" />
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
                    <flux:select wire:model="npcIsAlive" label="{{ __('Status') }}">
                        <flux:select.option :value="true">{{ __('Alive') }}</flux:select.option>
                        <flux:select.option :value="false">{{ __('Dead') }}</flux:select.option>
                    </flux:select>
                </div>

                <flux:separator />

                {{-- Stat Block --}}
                <flux:heading size="sm">{{ __('Stat Block') }}</flux:heading>

                {{-- Identity --}}
                <div class="grid gap-3 sm:grid-cols-3">
                    <flux:input wire:model="npcRace" label="{{ __('Race') }}" placeholder="{{ __('e.g., Human, Elf') }}" />
                    <flux:select wire:model="npcSize" label="{{ __('Size') }}" placeholder="{{ __('Medium') }}">
                        <flux:select.option value="">{{ __('—') }}</flux:select.option>
                        @foreach (['Tiny', 'Small', 'Medium', 'Large', 'Huge', 'Gargantuan'] as $size)
                            <flux:select.option value="{{ $size }}">{{ __($size) }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:input wire:model="npcAlignment" label="{{ __('Alignment') }}" placeholder="{{ __('e.g., Neutral Evil') }}" />
                </div>

                {{-- Combat Stats --}}
                <div class="grid gap-3 sm:grid-cols-3 lg:grid-cols-6">
                    <flux:input wire:model="npcArmorClass" type="number" min="0" max="30" label="{{ __('AC') }}" placeholder="10" />
                    <flux:input wire:model="npcArmorType" label="{{ __('Armor Type') }}" placeholder="{{ __('Natural armor') }}" />
                    <flux:input wire:model="npcHpMax" type="number" min="1" label="{{ __('Max HP') }}" placeholder="10" />
                    <flux:input wire:model="npcHitDice" label="{{ __('Hit Dice') }}" placeholder="{{ __('e.g., 3d8+9') }}" />
                    <flux:input wire:model="npcSpeed" label="{{ __('Speed') }}" placeholder="{{ __('30 ft.') }}" />
                    <flux:input wire:model="npcChallengeRating" label="{{ __('CR') }}" placeholder="{{ __('e.g., 1/4') }}" />
                </div>

                {{-- Ability Scores --}}
                <div>
                    <flux:label class="mb-2">{{ __('Ability Scores') }}</flux:label>
                    <div class="grid grid-cols-6 gap-2">
                        @foreach (['str' => 'STR', 'dex' => 'DEX', 'con' => 'CON', 'int' => 'INT', 'wis' => 'WIS', 'cha' => 'CHA'] as $key => $label)
                            <div class="text-center">
                                <div class="mb-1 text-xs font-semibold text-zinc-500 dark:text-zinc-400">{{ $label }}</div>
                                <flux:input wire:model="npcAbilityScores.{{ $key }}" type="number" min="1" max="30" class="text-center" />
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Proficiencies --}}
                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <flux:label class="mb-2">{{ __('Saving Throw Proficiencies') }}</flux:label>
                        <div class="flex flex-wrap gap-3">
                            @foreach (['str' => 'STR', 'dex' => 'DEX', 'con' => 'CON', 'int' => 'INT', 'wis' => 'WIS', 'cha' => 'CHA'] as $key => $label)
                                <flux:checkbox wire:model="npcSavingThrowProficiencies" value="{{ $key }}" label="{{ $label }}" />
                            @endforeach
                        </div>
                    </div>
                    <flux:input wire:model="npcSkillProficiencies" label="{{ __('Skill Proficiencies') }}" placeholder="{{ __('athletics, perception, stealth') }}" />
                </div>

                {{-- Defenses --}}
                <div class="grid gap-3 sm:grid-cols-3">
                    <flux:input wire:model="npcDamageResistances" label="{{ __('Damage Resistances') }}" placeholder="{{ __('fire, cold') }}" />
                    <flux:input wire:model="npcDamageImmunities" label="{{ __('Damage Immunities') }}" placeholder="{{ __('poison') }}" />
                    <flux:input wire:model="npcConditionImmunities" label="{{ __('Condition Immunities') }}" placeholder="{{ __('charmed, frightened') }}" />
                </div>

                {{-- Senses and Languages --}}
                <div class="grid gap-3 sm:grid-cols-2">
                    <flux:input wire:model="npcSenses" label="{{ __('Senses') }}" placeholder="{{ __('Darkvision 60 ft., passive Perception 12') }}" />
                    <flux:input wire:model="npcLanguages" label="{{ __('Languages') }}" placeholder="{{ __('Common, Elvish') }}" />
                </div>

                {{-- Traits and Actions --}}
                <div class="grid gap-3 sm:grid-cols-2">
                    <flux:textarea wire:model="npcSpecialTraits" label="{{ __('Special Traits') }}" placeholder="{{ __('Name: Description (one per line)') }}" rows="3" />
                    <flux:textarea wire:model="npcActions" label="{{ __('Actions') }}" placeholder="{{ __('Longsword: Melee Weapon Attack: +5 to hit...') }}" rows="3" />
                </div>
                <div class="grid gap-3 sm:grid-cols-3">
                    <flux:textarea wire:model="npcBonusActions" label="{{ __('Bonus Actions') }}" placeholder="{{ __('Name: Description') }}" rows="2" />
                    <flux:textarea wire:model="npcReactions" label="{{ __('Reactions') }}" placeholder="{{ __('Name: Description') }}" rows="2" />
                    <flux:textarea wire:model="npcLegendaryActions" label="{{ __('Legendary Actions') }}" placeholder="{{ __('Name: Description') }}" rows="2" />
                </div>

                {{-- Spellcasting --}}
                <div class="grid gap-3 sm:grid-cols-4">
                    <flux:select wire:model="npcSpellcastingAbility" label="{{ __('Spellcasting Ability') }}" placeholder="{{ __('None') }}">
                        <flux:select.option value="">{{ __('None') }}</flux:select.option>
                        @foreach (['str' => 'STR', 'dex' => 'DEX', 'con' => 'CON', 'int' => 'INT', 'wis' => 'WIS', 'cha' => 'CHA'] as $key => $label)
                            <flux:select.option value="{{ $key }}">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:input wire:model="npcSpellSaveDc" type="number" min="1" label="{{ __('Spell Save DC') }}" placeholder="—" />
                    <flux:input wire:model="npcSpellAttackBonus" type="number" label="{{ __('Spell Attack Bonus') }}" placeholder="—" />
                    <flux:textarea wire:model="npcCantrips" label="{{ __('Cantrips') }}" placeholder="{{ __('One per line') }}" rows="2" />
                </div>

                <div class="flex items-center justify-end gap-2">
                    <flux:button variant="subtle" size="sm" wire:click="$set('showForm', false)">{{ __('Cancel') }}</flux:button>
                    <flux:button variant="primary" size="sm" wire:click="save">
                        {{ $editingNpcId ? __('Update NPC') : __('Add NPC') }}
                    </flux:button>
                </div>
            </div>
        </div>
    @endif

    {{-- NPC Table --}}
    @if ($npcs->isEmpty())
        <div class="flex flex-1 flex-col items-center justify-center rounded-xl border border-dashed border-zinc-300 p-12 dark:border-zinc-600">
            <div class="text-center">
                <flux:icon name="users" class="mx-auto mb-4 size-12 text-zinc-400 dark:text-zinc-500" />
                <flux:heading size="lg" class="mb-2">{{ __('No NPCs found') }}</flux:heading>
                <flux:text class="text-zinc-500 dark:text-zinc-400">
                    {{ __('Create an NPC or generate one with AI.') }}
                </flux:text>
            </div>
        </div>
    @else
        <flux:table>
            <flux:table.columns>
                <flux:table.column>{{ __('Name') }}</flux:table.column>
                <flux:table.column>{{ __('Role') }}</flux:table.column>
                <flux:table.column>{{ __('Race') }}</flux:table.column>
                <flux:table.column>{{ __('CR') }}</flux:table.column>
                <flux:table.column>{{ __('Faction') }}</flux:table.column>
                <flux:table.column>{{ __('Status') }}</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                @foreach ($npcs as $npc)
                    <flux:table.row wire:key="npc-{{ $npc->id }}">
                        <flux:table.cell variant="strong">{{ $npc->name }}</flux:table.cell>
                        <flux:table.cell>{{ $npc->role ?? '—' }}</flux:table.cell>
                        <flux:table.cell>{{ $npc->race ?? '—' }}</flux:table.cell>
                        <flux:table.cell>{{ $npc->challenge_rating ? 'CR '.$npc->challenge_rating : '—' }}</flux:table.cell>
                        <flux:table.cell>{{ $npc->faction?->name ?? '—' }}</flux:table.cell>
                        <flux:table.cell>
                            @if ($npc->is_alive)
                                <flux:badge size="sm" color="green">{{ __('Alive') }}</flux:badge>
                            @else
                                <flux:badge size="sm" color="red">{{ __('Dead') }}</flux:badge>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>
                            <div class="flex items-center justify-end gap-1">
                                <flux:button variant="subtle" size="sm" wire:click="viewNpc({{ $npc->id }})" icon="eye" title="{{ __('View') }}" />
                                <flux:button variant="subtle" size="sm" wire:click="openForm({{ $npc->id }})" icon="pencil" title="{{ __('Edit') }}" />
                                <flux:button variant="subtle" size="sm" wire:click="delete({{ $npc->id }})" wire:confirm="{{ __('Delete this NPC?') }}" icon="trash" title="{{ __('Delete') }}" />
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    @endif

    {{-- View NPC Detail (4-tab modal) --}}
    <flux:modal name="view-npc" class="md:w-2xl" variant="flyout">
        @if ($viewingNpc)
            @php
                $stats = $viewingNpc->stats ?? [];
                $abilityScores = $stats['ability_scores'] ?? [];
                $savingProfs = $stats['saving_throw_proficiencies'] ?? [];
                $skillProfs = $stats['skill_proficiencies'] ?? [];
                $abilities = ['str' => 'STR', 'dex' => 'DEX', 'con' => 'CON', 'int' => 'INT', 'wis' => 'WIS', 'cha' => 'CHA'];
                $allSkills = [
                    'acrobatics' => 'Acrobatics (DEX)', 'animal_handling' => 'Animal Handling (WIS)',
                    'arcana' => 'Arcana (INT)', 'athletics' => 'Athletics (STR)',
                    'deception' => 'Deception (CHA)', 'history' => 'History (INT)',
                    'insight' => 'Insight (WIS)', 'intimidation' => 'Intimidation (CHA)',
                    'investigation' => 'Investigation (INT)', 'medicine' => 'Medicine (WIS)',
                    'nature' => 'Nature (INT)', 'perception' => 'Perception (WIS)',
                    'performance' => 'Performance (CHA)', 'persuasion' => 'Persuasion (CHA)',
                    'religion' => 'Religion (INT)', 'sleight_of_hand' => 'Sleight of Hand (DEX)',
                    'stealth' => 'Stealth (DEX)', 'survival' => 'Survival (WIS)',
                ];
                $spellcasting = $stats['spellcasting'] ?? null;
            @endphp

            <flux:tabs variant="segmented" class="w-full">
                <flux:tab name="details">{{ __('Details') }}</flux:tab>
                <flux:tab name="character-sheet">{{ __('Stat Block') }}</flux:tab>
                <flux:tab name="actions">{{ __('Actions') }}</flux:tab>
                <flux:tab name="spells">{{ __('Spells') }}</flux:tab>

                {{-- Tab 1: Details --}}
                <flux:tab.panel name="details">
                    <div class="space-y-6 py-2">
                        {{-- NPC Image --}}
                        <div class="flex flex-col items-center gap-3">
                            @if ($viewingNpc->image_path)
                                <x-image-lightbox :src="$viewingNpc->image_url" :alt="$viewingNpc->name" />
                            @else
                                <div class="flex h-32 w-full items-center justify-center rounded-lg bg-zinc-100 dark:bg-zinc-700">
                                    <flux:icon name="user" class="size-12 text-zinc-400 dark:text-zinc-500" />
                                </div>
                            @endif
                            <flux:button variant="subtle" size="sm" wire:click="generateImage({{ $viewingNpc->id }})" icon="sparkles" wire:loading.attr="disabled" wire:target="generateImage({{ $viewingNpc->id }})">
                                <span wire:loading.remove wire:target="generateImage({{ $viewingNpc->id }})">{{ $viewingNpc->image_path ? __('Regenerate Image') : __('Generate Image') }}</span>
                                <span wire:loading wire:target="generateImage({{ $viewingNpc->id }})">{{ __('Generating...') }}</span>
                            </flux:button>
                            <span wire:stream.replace="imageStatus" class="text-xs text-zinc-500 italic"></span>
                        </div>

                        <div>
                            <flux:heading size="lg">{{ $viewingNpc->name }}</flux:heading>
                            @if ($viewingNpc->role)
                                <flux:text class="mt-1 text-sm text-zinc-500">{{ $viewingNpc->role }}</flux:text>
                            @endif
                            <div class="mt-2 flex flex-wrap gap-2">
                                @if ($viewingNpc->is_alive)
                                    <flux:badge size="sm" color="green">{{ __('Alive') }}</flux:badge>
                                @else
                                    <flux:badge size="sm" color="red">{{ __('Dead') }}</flux:badge>
                                @endif
                                @if ($viewingNpc->race)
                                    <flux:badge size="sm" variant="outline">{{ $viewingNpc->race }}</flux:badge>
                                @endif
                                @if ($viewingNpc->alignment)
                                    <flux:badge size="sm" variant="outline">{{ $viewingNpc->alignment }}</flux:badge>
                                @endif
                                @if ($viewingNpc->faction)
                                    <flux:badge size="sm" variant="outline">{{ $viewingNpc->faction->name }}</flux:badge>
                                @endif
                                @if ($viewingNpc->location)
                                    <flux:badge size="sm" variant="outline">{{ $viewingNpc->location->name }}</flux:badge>
                                @endif
                            </div>
                        </div>

                        <flux:separator />

                        @if ($viewingNpc->description)
                            <div>
                                <flux:heading size="sm" class="mb-1">{{ __('Description') }}</flux:heading>
                                <flux:text class="text-sm whitespace-pre-line">{{ $viewingNpc->description }}</flux:text>
                            </div>
                        @endif

                        @if ($viewingNpc->personality)
                            <div>
                                <flux:heading size="sm" class="mb-1">{{ __('Personality') }}</flux:heading>
                                <flux:text class="text-sm">{{ $viewingNpc->personality }}</flux:text>
                            </div>
                        @endif

                        @if ($viewingNpc->motivation)
                            <div>
                                <flux:heading size="sm" class="mb-1">{{ __('Motivation') }}</flux:heading>
                                <flux:text class="text-sm">{{ $viewingNpc->motivation }}</flux:text>
                            </div>
                        @endif

                        @if ($viewingNpc->backstory)
                            <div>
                                <flux:heading size="sm" class="mb-1">{{ __('Backstory') }}</flux:heading>
                                <flux:text class="text-sm whitespace-pre-line">{{ $viewingNpc->backstory }}</flux:text>
                            </div>
                        @endif

                        @if ($viewingNpc->voice_description || $viewingNpc->speech_patterns)
                            <div>
                                <flux:heading size="sm" class="mb-1">{{ __('Voice & Speech') }}</flux:heading>
                                @if ($viewingNpc->voice_description)
                                    <flux:text class="text-sm"><strong>{{ __('Voice:') }}</strong> {{ $viewingNpc->voice_description }}</flux:text>
                                @endif
                                @if ($viewingNpc->speech_patterns)
                                    <flux:text class="text-sm"><strong>{{ __('Patterns:') }}</strong> {{ $viewingNpc->speech_patterns }}</flux:text>
                                @endif
                            </div>
                        @endif

                        @if ($viewingNpc->catchphrases)
                            <div>
                                <flux:heading size="sm" class="mb-1">{{ __('Catchphrases') }}</flux:heading>
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($viewingNpc->catchphrases as $phrase)
                                        <flux:badge size="sm" variant="outline">"{{ $phrase }}"</flux:badge>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <flux:separator />

                        @include('livewire.campaigns.partials.entity-history', ['history' => $history])
                    </div>
                </flux:tab.panel>

                {{-- Tab 2: Character Sheet (Stat Block) --}}
                <flux:tab.panel name="character-sheet">
                    <div class="space-y-5 py-2">
                        {{-- Combat header bar --}}
                        @if ($viewingNpc->armor_class || $viewingNpc->hp_max || $viewingNpc->speed || $viewingNpc->challenge_rating)
                            <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                                @if ($viewingNpc->armor_class)
                                    <div class="rounded-lg border border-zinc-200 bg-white p-3 text-center dark:border-zinc-700 dark:bg-zinc-800">
                                        <div class="text-xs font-semibold uppercase text-zinc-500 dark:text-zinc-400">{{ __('Armor Class') }}</div>
                                        <div class="text-xl font-bold">{{ $viewingNpc->armor_class }}</div>
                                        @if ($viewingNpc->armor_type)
                                            <div class="text-xs text-zinc-500">{{ $viewingNpc->armor_type }}</div>
                                        @endif
                                    </div>
                                @endif
                                @if ($viewingNpc->hp_max)
                                    <div class="rounded-lg border border-zinc-200 bg-white p-3 text-center dark:border-zinc-700 dark:bg-zinc-800">
                                        <div class="text-xs font-semibold uppercase text-zinc-500 dark:text-zinc-400">{{ __('Hit Points') }}</div>
                                        <div class="text-xl font-bold">{{ $viewingNpc->hp_max }}</div>
                                        @if ($viewingNpc->hit_dice)
                                            <div class="text-xs text-zinc-500">{{ $viewingNpc->hit_dice }}</div>
                                        @endif
                                    </div>
                                @endif
                                @if ($viewingNpc->speed)
                                    <div class="rounded-lg border border-zinc-200 bg-white p-3 text-center dark:border-zinc-700 dark:bg-zinc-800">
                                        <div class="text-xs font-semibold uppercase text-zinc-500 dark:text-zinc-400">{{ __('Speed') }}</div>
                                        <div class="text-xl font-bold">{{ $viewingNpc->speed }}</div>
                                    </div>
                                @endif
                                @if ($viewingNpc->challenge_rating)
                                    <div class="rounded-lg border border-zinc-200 bg-white p-3 text-center dark:border-zinc-700 dark:bg-zinc-800">
                                        <div class="text-xs font-semibold uppercase text-zinc-500 dark:text-zinc-400">{{ __('Challenge') }}</div>
                                        <div class="text-xl font-bold">{{ $viewingNpc->challenge_rating }}</div>
                                    </div>
                                @endif
                            </div>
                        @endif

                        {{-- Ability Scores --}}
                        @if (!empty($abilityScores))
                            <div>
                                <flux:heading size="sm" class="mb-3">{{ __('Ability Scores') }}</flux:heading>
                                <div class="grid grid-cols-6 gap-2">
                                    @foreach ($abilities as $key => $label)
                                        @php $score = $abilityScores[$key] ?? 10; $mod = $viewingNpc->abilityModifier($key); @endphp
                                        <div class="rounded-lg border border-zinc-200 bg-white p-2 text-center dark:border-zinc-700 dark:bg-zinc-800">
                                            <div class="text-xs font-bold uppercase text-zinc-500 dark:text-zinc-400">{{ $label }}</div>
                                            <div class="text-lg font-bold">{{ $score }}</div>
                                            <div class="text-xs text-zinc-600 dark:text-zinc-300">{{ $mod >= 0 ? '+' : '' }}{{ $mod }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Saving Throws --}}
                        @if (!empty($savingProfs))
                            <div>
                                <flux:heading size="sm" class="mb-2">{{ __('Saving Throws') }}</flux:heading>
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($abilities as $key => $label)
                                        @if (in_array($key, $savingProfs))
                                            @php $mod = $viewingNpc->abilityModifier($key); @endphp
                                            <flux:badge size="sm" color="blue">{{ $label }} {{ $mod >= 0 ? '+' : '' }}{{ $mod }}</flux:badge>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Skills --}}
                        @if (!empty($skillProfs))
                            <div>
                                <flux:heading size="sm" class="mb-2">{{ __('Skills') }}</flux:heading>
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($skillProfs as $skill)
                                        <flux:badge size="sm" variant="outline">{{ ucwords(str_replace('_', ' ', $skill)) }}</flux:badge>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Defenses --}}
                        @if (!empty($stats['damage_resistances']) || !empty($stats['damage_immunities']) || !empty($stats['condition_immunities']))
                            <div class="space-y-2">
                                @if (!empty($stats['damage_resistances']))
                                    <div>
                                        <span class="text-sm font-semibold">{{ __('Damage Resistances:') }}</span>
                                        <flux:text class="text-sm"> {{ implode(', ', $stats['damage_resistances']) }}</flux:text>
                                    </div>
                                @endif
                                @if (!empty($stats['damage_immunities']))
                                    <div>
                                        <span class="text-sm font-semibold">{{ __('Damage Immunities:') }}</span>
                                        <flux:text class="text-sm"> {{ implode(', ', $stats['damage_immunities']) }}</flux:text>
                                    </div>
                                @endif
                                @if (!empty($stats['condition_immunities']))
                                    <div>
                                        <span class="text-sm font-semibold">{{ __('Condition Immunities:') }}</span>
                                        <flux:text class="text-sm"> {{ implode(', ', $stats['condition_immunities']) }}</flux:text>
                                    </div>
                                @endif
                            </div>
                        @endif

                        {{-- Senses and Languages --}}
                        @if (!empty($stats['senses']) || !empty($stats['languages']))
                            <div class="space-y-1">
                                @if (!empty($stats['senses']))
                                    <div>
                                        <span class="text-sm font-semibold">{{ __('Senses:') }}</span>
                                        <flux:text class="text-sm"> {{ $stats['senses'] }}</flux:text>
                                    </div>
                                @endif
                                @if (!empty($stats['languages']))
                                    <div>
                                        <span class="text-sm font-semibold">{{ __('Languages:') }}</span>
                                        <flux:text class="text-sm"> {{ $stats['languages'] }}</flux:text>
                                    </div>
                                @endif
                            </div>
                        @endif

                        @if (empty($abilityScores) && empty($viewingNpc->armor_class) && empty($viewingNpc->hp_max))
                            <div class="rounded-lg border border-dashed border-zinc-300 p-8 text-center dark:border-zinc-600">
                                <flux:text class="text-zinc-500">{{ __('No stat block generated yet. Edit this NPC or regenerate with AI to add combat statistics.') }}</flux:text>
                            </div>
                        @endif
                    </div>
                </flux:tab.panel>

                {{-- Tab 3: Actions & Abilities --}}
                <flux:tab.panel name="actions">
                    <div class="space-y-5 py-2">
                        @php
                            $specialTraits = $stats['special_traits'] ?? [];
                            $actions = $stats['actions'] ?? [];
                            $bonusActions = $stats['bonus_actions'] ?? [];
                            $reactions = $stats['reactions'] ?? [];
                            $legendaryActions = $stats['legendary_actions'] ?? [];
                            $hasAny = !empty($specialTraits) || !empty($actions) || !empty($bonusActions) || !empty($reactions) || !empty($legendaryActions);
                        @endphp

                        @if (!$hasAny)
                            <div class="rounded-lg border border-dashed border-zinc-300 p-8 text-center dark:border-zinc-600">
                                <flux:text class="text-zinc-500">{{ __('No actions or abilities defined. Edit this NPC or generate with AI to add combat abilities.') }}</flux:text>
                            </div>
                        @else
                            @if (!empty($specialTraits))
                                <div>
                                    <flux:heading size="sm" class="mb-3">{{ __('Special Traits') }}</flux:heading>
                                    <div class="space-y-3">
                                        @foreach ($specialTraits as $trait)
                                            <div>
                                                <span class="font-semibold italic text-sm">{{ $trait['name'] }}.</span>
                                                <flux:text class="text-sm"> {{ $trait['description'] }}</flux:text>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if (!empty($actions))
                                @if (!empty($specialTraits))<flux:separator />@endif
                                <div>
                                    <flux:heading size="sm" class="mb-3">{{ __('Actions') }}</flux:heading>
                                    <div class="space-y-3">
                                        @foreach ($actions as $action)
                                            <div>
                                                <span class="font-semibold italic text-sm">{{ $action['name'] }}.</span>
                                                <flux:text class="text-sm"> {{ $action['description'] }}</flux:text>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if (!empty($bonusActions))
                                <flux:separator />
                                <div>
                                    <flux:heading size="sm" class="mb-3">{{ __('Bonus Actions') }}</flux:heading>
                                    <div class="space-y-3">
                                        @foreach ($bonusActions as $action)
                                            <div>
                                                <span class="font-semibold italic text-sm">{{ $action['name'] }}.</span>
                                                <flux:text class="text-sm"> {{ $action['description'] }}</flux:text>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if (!empty($reactions))
                                <flux:separator />
                                <div>
                                    <flux:heading size="sm" class="mb-3">{{ __('Reactions') }}</flux:heading>
                                    <div class="space-y-3">
                                        @foreach ($reactions as $reaction)
                                            <div>
                                                <span class="font-semibold italic text-sm">{{ $reaction['name'] }}.</span>
                                                <flux:text class="text-sm"> {{ $reaction['description'] }}</flux:text>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if (!empty($legendaryActions))
                                <flux:separator />
                                <div>
                                    <flux:heading size="sm" class="mb-3">{{ __('Legendary Actions') }}</flux:heading>
                                    <div class="space-y-3">
                                        @foreach ($legendaryActions as $action)
                                            <div>
                                                <span class="font-semibold italic text-sm">{{ $action['name'] }}.</span>
                                                <flux:text class="text-sm"> {{ $action['description'] }}</flux:text>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>
                </flux:tab.panel>

                {{-- Tab 4: Spells --}}
                <flux:tab.panel name="spells">
                    <div class="space-y-5 py-2">
                        @if (!$spellcasting)
                            <div class="rounded-lg border border-dashed border-zinc-300 p-8 text-center dark:border-zinc-600">
                                <flux:text class="text-zinc-500">{{ __('This NPC does not have spellcasting. Edit or regenerate with AI to add spells.') }}</flux:text>
                            </div>
                        @else
                            {{-- Spellcasting header --}}
                            <div class="grid grid-cols-3 gap-3">
                                <div class="rounded-lg border border-zinc-200 bg-white p-3 text-center dark:border-zinc-700 dark:bg-zinc-800">
                                    <div class="text-xs font-semibold uppercase text-zinc-500">{{ __('Ability') }}</div>
                                    <div class="text-lg font-bold uppercase">{{ $spellcasting['ability'] ?? '—' }}</div>
                                </div>
                                <div class="rounded-lg border border-zinc-200 bg-white p-3 text-center dark:border-zinc-700 dark:bg-zinc-800">
                                    <div class="text-xs font-semibold uppercase text-zinc-500">{{ __('Save DC') }}</div>
                                    <div class="text-lg font-bold">{{ $spellcasting['spell_save_dc'] ?? '—' }}</div>
                                </div>
                                <div class="rounded-lg border border-zinc-200 bg-white p-3 text-center dark:border-zinc-700 dark:bg-zinc-800">
                                    <div class="text-xs font-semibold uppercase text-zinc-500">{{ __('Attack Bonus') }}</div>
                                    @php $atk = $spellcasting['attack_bonus'] ?? null; @endphp
                                    <div class="text-lg font-bold">{{ $atk !== null ? ($atk >= 0 ? '+'.$atk : $atk) : '—' }}</div>
                                </div>
                            </div>

                            {{-- Cantrips --}}
                            @if (!empty($spellcasting['cantrips']))
                                <div>
                                    <flux:heading size="sm" class="mb-2">{{ __('Cantrips') }}</flux:heading>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach ($spellcasting['cantrips'] as $cantrip)
                                            <flux:badge size="sm" variant="outline">{{ $cantrip }}</flux:badge>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            {{-- Spells by level --}}
                            @if (!empty($spellcasting['spells_by_level']))
                                @php
                                    $ordinals = ['1' => '1st', '2' => '2nd', '3' => '3rd', '4' => '4th', '5' => '5th', '6' => '6th', '7' => '7th', '8' => '8th', '9' => '9th'];
                                @endphp
                                @foreach ($spellcasting['spells_by_level'] as $level => $spellData)
                                    <div>
                                        <flux:heading size="sm" class="mb-2">
                                            {{ $ordinals[$level] ?? $level }}-{{ __('Level Spells') }}
                                            @if (isset($spellData['slots']))
                                                <flux:text class="font-normal text-zinc-500"> ({{ $spellData['slots'] }} {{ __('slots') }})</flux:text>
                                            @endif
                                        </flux:heading>
                                        <div class="flex flex-wrap gap-2">
                                            @foreach (is_array($spellData) ? ($spellData['prepared'] ?? $spellData) : [] as $spell)
                                                @if (is_string($spell))
                                                    <flux:badge size="sm" variant="outline">{{ $spell }}</flux:badge>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        @endif
                    </div>
                </flux:tab.panel>
            </flux:tabs>
        @endif
    </flux:modal>

    {{-- Generate Modal --}}
    <flux:modal wire:model="showGenerateModal" class="md:w-xl">
        <flux:heading size="lg">{{ __('Generate NPC with AI') }}</flux:heading>
        <flux:text class="mt-1">{{ __('Provide optional context to guide the AI, then review and edit the result before saving.') }}</flux:text>

        <div class="mt-4 flex flex-col gap-4">
            <flux:textarea
                wire:model="generateContext"
                label="{{ __('Context (optional)') }}"
                placeholder="{{ __('e.g., A mysterious merchant with ties to the thieves guild, a grumpy dwarven blacksmith...') }}"
                rows="3"
            />
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
