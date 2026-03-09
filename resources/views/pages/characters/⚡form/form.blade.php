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
        <flux:tab.group>
            <flux:tabs variant="segmented" class="w-full">
                <flux:tab name="info">{{ __('Character Info') }}</flux:tab>
                <flux:tab name="combat">{{ __('Combat') }}</flux:tab>
                <flux:tab name="abilities">{{ __('Abilities & Skills') }}</flux:tab>
                <flux:tab name="proficiencies">{{ __('Equipment & Features') }}</flux:tab>
                <flux:tab name="spells">{{ __('Spells') }}</flux:tab>
            </flux:tabs>

            {{-- Tab 1: Character Info --}}
            <flux:tab.panel name="info">
                <div class="mt-4 flex flex-col gap-4">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <flux:input wire:model="name" label="{{ __('Character Name') }}" required />
                        <flux:input wire:model="player_name" label="{{ __('Player Name') }}" />
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <flux:input wire:model="characterClass" label="{{ __('Class') }}" placeholder="{{ __('e.g., Rogue, Paladin') }}" />
                        <flux:input wire:model="race" label="{{ __('Race') }}" placeholder="{{ __('e.g., Human, Elf, Dwarf') }}" />
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <flux:input wire:model="background" label="{{ __('Background') }}" placeholder="{{ __('e.g., Soldier, Noble') }}" />
                        <flux:input wire:model="alignment_label" label="{{ __('Alignment') }}" placeholder="{{ __('e.g., Chaotic Good') }}" />
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <flux:input wire:model="level" label="{{ __('Level') }}" type="number" min="1" max="30" required />
                        <flux:input wire:model="experience_points" label="{{ __('Experience Points') }}" type="number" min="0" />
                    </div>
                    <flux:textarea wire:model="notes" label="{{ __('Notes') }}" rows="4" placeholder="{{ __('Character backstory, abilities, goals...') }}" />
                </div>
            </flux:tab.panel>

            {{-- Tab 2: Combat Stats --}}
            <flux:tab.panel name="combat">
                <div class="mt-4 flex flex-col gap-4">
                    <div class="grid gap-4 sm:grid-cols-3">
                        <flux:input wire:model="hp_max" label="{{ __('Max HP') }}" type="number" min="1" required />
                        <flux:input wire:model="hp_current" label="{{ __('Current HP') }}" type="number" min="0" />
                        <flux:input wire:model="armor_class" label="{{ __('Armor Class') }}" type="number" min="1" required />
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <flux:input wire:model="speed" label="{{ __('Speed (ft.)') }}" type="number" min="0" />
                        <flux:input wire:model="proficiency_bonus" label="{{ __('Proficiency Bonus') }}" type="number" min="1" max="9" placeholder="{{ __('e.g., 2') }}" />
                    </div>
                </div>
            </flux:tab.panel>

            {{-- Tab 3: Ability Scores & Skills --}}
            <flux:tab.panel name="abilities">
                <div class="mt-4 flex flex-col gap-6">
                    {{-- Ability Scores --}}
                    <div>
                        <flux:heading size="sm" class="mb-3">{{ __('Ability Scores') }}</flux:heading>
                        <div class="grid grid-cols-6 gap-3">
                            @foreach (['str' => 'STR', 'dex' => 'DEX', 'con' => 'CON', 'int' => 'INT', 'wis' => 'WIS', 'cha' => 'CHA'] as $key => $label)
                                <div class="text-center">
                                    <div class="mb-1 text-xs font-bold uppercase text-zinc-500 dark:text-zinc-400">{{ $label }}</div>
                                    <flux:input wire:model="abilityScores.{{ $key }}" type="number" min="1" max="30" class="text-center" />
                                    @php
                                        $score = old("abilityScores.$key", $abilityScores[$key] ?? 10);
                                        $mod = (int) floor(($score - 10) / 2);
                                    @endphp
                                    <div class="mt-1 text-xs text-zinc-500">{{ $mod >= 0 ? '+' : '' }}{{ $mod }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Saving Throws --}}
                    <div>
                        <flux:heading size="sm" class="mb-2">{{ __('Saving Throw Proficiencies') }}</flux:heading>
                        <div class="flex flex-wrap gap-4">
                            @foreach (['str' => 'Strength', 'dex' => 'Dexterity', 'con' => 'Constitution', 'int' => 'Intelligence', 'wis' => 'Wisdom', 'cha' => 'Charisma'] as $key => $label)
                                <flux:checkbox wire:model="savingThrowProficiencies" value="{{ $key }}" label="{{ $label }}" />
                            @endforeach
                        </div>
                    </div>

                    {{-- Skills --}}
                    <div>
                        <flux:input wire:model="skillProficiencies" label="{{ __('Skill Proficiencies') }}" placeholder="{{ __('athletics, perception, stealth') }}" />
                        <flux:text class="mt-1 text-xs text-zinc-500">{{ __('Comma-separated list of proficient skills') }}</flux:text>
                    </div>
                </div>
            </flux:tab.panel>

            {{-- Tab 4: Equipment & Features --}}
            <flux:tab.panel name="proficiencies">
                <div class="mt-4 flex flex-col gap-4">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <flux:textarea wire:model="otherProficiencies" label="{{ __('Other Proficiencies') }}" placeholder="{{ __('Armor types, weapon types, tools...') }}" rows="3" />
                        <flux:textarea wire:model="languages" label="{{ __('Languages') }}" placeholder="{{ __('Common, Elvish, Dwarvish...') }}" rows="3" />
                    </div>
                    <flux:textarea wire:model="equipment" label="{{ __('Equipment') }}" placeholder="{{ __('One item per line') }}" rows="5" />
                    <flux:textarea wire:model="featuresTraits" label="{{ __('Features & Traits') }}" placeholder="{{ __('Second Wind: Bonus action to regain 1d10+Fighter Level HP.') }}" rows="6" />
                    <flux:text class="text-xs text-zinc-500">{{ __('Format: "Name: Description" one per line') }}</flux:text>
                </div>
            </flux:tab.panel>

            {{-- Tab 5: Spells --}}
            <flux:tab.panel name="spells">
                <div class="mt-4 flex flex-col gap-4">
                    <div class="grid gap-4 sm:grid-cols-3">
                        <flux:select wire:model="spellcastingAbility" label="{{ __('Spellcasting Ability') }}" placeholder="{{ __('None') }}">
                            <flux:select.option value="">{{ __('None') }}</flux:select.option>
                            @foreach (['str' => 'Strength', 'dex' => 'Dexterity', 'con' => 'Constitution', 'int' => 'Intelligence', 'wis' => 'Wisdom', 'cha' => 'Charisma'] as $key => $label)
                                <flux:select.option value="{{ $key }}">{{ $label }}</flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:input wire:model="spellSaveDc" type="number" min="1" label="{{ __('Spell Save DC') }}" placeholder="—" />
                        <flux:input wire:model="spellAttackBonus" type="number" label="{{ __('Spell Attack Bonus') }}" placeholder="—" />
                    </div>
                    <flux:textarea wire:model="cantrips" label="{{ __('Cantrips Known') }}" placeholder="{{ __('Fire Bolt') }}" rows="3" />
                    <flux:text class="text-xs text-zinc-500">{{ __('One cantrip per line') }}</flux:text>
                    <flux:textarea wire:model="spellsByLevel" label="{{ __('Spells by Level') }}" placeholder="{{ __("1: Magic Missile, Shield\n2: Misty Step, Invisibility") }}" rows="6" />
                    <flux:text class="text-xs text-zinc-500">{{ __('Format: "Level: Spell 1, Spell 2" one level per line') }}</flux:text>
                </div>
            </flux:tab.panel>
        </flux:tab.group>

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
