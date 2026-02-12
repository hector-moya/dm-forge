<div class="flex w-full flex-1 flex-col gap-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <flux:heading size="xl">{{ __('Monster Library') }}</flux:heading>
        <flux:button variant="primary" wire:click="openCustomForm" icon="plus">
            {{ __('Custom Monster') }}
        </flux:button>
    </div>

    {{-- Search & Filters --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
        <div class="flex-1">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="{{ __('Search monsters...') }}" icon="magnifying-glass" />
        </div>
        <div class="flex gap-3">
            <flux:select wire:model.live="typeFilter" placeholder="{{ __('All Types') }}">
                <flux:select.option value="">{{ __('All Types') }}</flux:select.option>
                @foreach ($monsterTypes as $type)
                    <flux:select.option value="{{ $type }}">{{ $type }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:select wire:model.live="sourceFilter">
                <flux:select.option value="all">{{ __('All Sources') }}</flux:select.option>
                <flux:select.option value="srd">{{ __('SRD Only') }}</flux:select.option>
                <flux:select.option value="custom">{{ __('Custom Only') }}</flux:select.option>
            </flux:select>
        </div>
    </div>

    {{-- Monster Table --}}
    @if ($monsters->isEmpty())
        <div class="flex flex-1 flex-col items-center justify-center rounded-xl border border-dashed border-zinc-300 p-12 dark:border-zinc-600">
            <div class="text-center">
                <flux:icon name="bug-ant" class="mx-auto mb-4 size-12 text-zinc-400 dark:text-zinc-500" />
                <flux:heading size="lg" class="mb-2">{{ __('No monsters found') }}</flux:heading>
                <flux:text class="text-zinc-500 dark:text-zinc-400">
                    {{ __('Try adjusting your search or filters.') }}
                </flux:text>
            </div>
        </div>
    @else
        <flux:table>
            <flux:table.columns>
                <flux:table.column>{{ __('Name') }}</flux:table.column>
                <flux:table.column>{{ __('Type') }}</flux:table.column>
                <flux:table.column>{{ __('CR') }}</flux:table.column>
                <flux:table.column>{{ __('AC') }}</flux:table.column>
                <flux:table.column>{{ __('HP') }}</flux:table.column>
                <flux:table.column>{{ __('Source') }}</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                @foreach ($monsters as $monster)
                    <flux:table.row wire:key="monster-{{ $monster['source'] }}-{{ $monster['id'] }}">
                        <flux:table.cell variant="strong">{{ $monster['name'] }}</flux:table.cell>
                        <flux:table.cell>{{ $monster['type'] ?? '—' }}</flux:table.cell>
                        <flux:table.cell>{{ $monster['cr'] !== null ? $monster['cr'] : '—' }}</flux:table.cell>
                        <flux:table.cell>{{ $monster['ac'] }}</flux:table.cell>
                        <flux:table.cell>{{ $monster['hp'] }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:badge size="sm" :color="$monster['source'] === 'srd' ? 'blue' : 'green'">
                                {{ $monster['source'] === 'srd' ? 'SRD' : 'Custom' }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>
                            <div class="flex items-center justify-end gap-1">
                                <flux:button variant="subtle" size="sm" wire:click="viewMonster({{ $monster['id'] }}, '{{ $monster['source'] }}')" icon="eye" title="{{ __('View') }}" />
                                @if ($monster['source'] === 'custom')
                                    <flux:button variant="subtle" size="sm" wire:click="editCustomMonster({{ $monster['id'] }})" icon="pencil" title="{{ __('Edit') }}" />
                                    <flux:button variant="subtle" size="sm" wire:click="deleteCustomMonster({{ $monster['id'] }})" wire:confirm="{{ __('Delete this custom monster?') }}" icon="trash" title="{{ __('Delete') }}" />
                                @endif
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    @endif

    {{-- View Monster Detail Modal --}}
    <flux:modal name="view-monster" class="md:w-xl" variant="flyout">
        @if ($viewingMonster)
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">{{ $viewingMonster->name }}</flux:heading>
                    <flux:text class="mt-1 italic">
                        {{ $viewingMonster->size ?? '' }} {{ $viewingMonster->type ?? '' }}{{ !empty($viewingMonster->subtype) ? " ({$viewingMonster->subtype})" : '' }}{{ !empty($viewingMonster->alignment) ? ", {$viewingMonster->alignment}" : '' }}
                    </flux:text>
                </div>

                <flux:separator />

                {{-- Core Stats --}}
                <div class="grid grid-cols-2 gap-3 text-sm">
                    <div><span class="font-semibold text-zinc-700 dark:text-zinc-300">{{ __('Armor Class') }}:</span> {{ $viewingMonster->armor_class }}{{ !empty($viewingMonster->armor_class_type) ? " ({$viewingMonster->armor_class_type})" : '' }}</div>
                    <div><span class="font-semibold text-zinc-700 dark:text-zinc-300">{{ __('Hit Points') }}:</span> {{ $viewingMonster->hit_points }}{{ !empty($viewingMonster->hit_dice) ? " ({$viewingMonster->hit_dice})" : '' }}</div>
                    @if ($viewingMonster->challenge_rating !== null)
                        <div><span class="font-semibold text-zinc-700 dark:text-zinc-300">{{ __('Challenge') }}:</span> {{ $viewingMonster->challenge_rating }} ({{ number_format($viewingMonster->xp ?? 0) }} XP)</div>
                    @endif
                    @if (!empty($viewingMonster->speed))
                        <div><span class="font-semibold text-zinc-700 dark:text-zinc-300">{{ __('Speed') }}:</span>
                            @if (is_array($viewingMonster->speed))
                                {{ collect($viewingMonster->speed)->map(fn($v, $k) => "$k $v")->implode(', ') }}
                            @else
                                {{ $viewingMonster->speed }}
                            @endif
                        </div>
                    @endif
                </div>

                <flux:separator />

                {{-- Ability Scores --}}
                <div class="grid grid-cols-6 gap-2 text-center text-sm">
                    @foreach (['strength' => 'STR', 'dexterity' => 'DEX', 'constitution' => 'CON', 'intelligence' => 'INT', 'wisdom' => 'WIS', 'charisma' => 'CHA'] as $attr => $label)
                        <div class="rounded-lg border border-zinc-200 px-2 py-2 dark:border-zinc-600">
                            <div class="text-xs font-bold text-zinc-500 dark:text-zinc-400">{{ $label }}</div>
                            <div class="text-lg font-semibold text-zinc-700 dark:text-zinc-200">{{ $viewingMonster->$attr ?? 10 }}</div>
                            @php $mod = floor(((int)($viewingMonster->$attr ?? 10) - 10) / 2); @endphp
                            <div class="text-xs text-zinc-500 dark:text-zinc-400">({{ $mod >= 0 ? "+{$mod}" : $mod }})</div>
                        </div>
                    @endforeach
                </div>

                {{-- Defenses & Senses --}}
                @if (!empty($viewingMonster->damage_vulnerabilities))
                    <div class="text-sm"><span class="font-semibold text-zinc-700 dark:text-zinc-300">{{ __('Vulnerabilities') }}:</span> {{ is_array($viewingMonster->damage_vulnerabilities) ? implode(', ', $viewingMonster->damage_vulnerabilities) : $viewingMonster->damage_vulnerabilities }}</div>
                @endif
                @if (!empty($viewingMonster->damage_resistances))
                    <div class="text-sm"><span class="font-semibold text-zinc-700 dark:text-zinc-300">{{ __('Resistances') }}:</span> {{ is_array($viewingMonster->damage_resistances) ? implode(', ', $viewingMonster->damage_resistances) : $viewingMonster->damage_resistances }}</div>
                @endif
                @if (!empty($viewingMonster->damage_immunities))
                    <div class="text-sm"><span class="font-semibold text-zinc-700 dark:text-zinc-300">{{ __('Immunities') }}:</span> {{ is_array($viewingMonster->damage_immunities) ? implode(', ', $viewingMonster->damage_immunities) : $viewingMonster->damage_immunities }}</div>
                @endif
                @if (!empty($viewingMonster->condition_immunities))
                    <div class="text-sm"><span class="font-semibold text-zinc-700 dark:text-zinc-300">{{ __('Condition Immunities') }}:</span> {{ is_array($viewingMonster->condition_immunities) ? implode(', ', $viewingMonster->condition_immunities) : $viewingMonster->condition_immunities }}</div>
                @endif
                @if (!empty($viewingMonster->senses))
                    <div class="text-sm"><span class="font-semibold text-zinc-700 dark:text-zinc-300">{{ __('Senses') }}:</span>
                        @if (is_array($viewingMonster->senses))
                            {{ collect($viewingMonster->senses)->map(fn($v, $k) => "$k $v")->implode(', ') }}
                        @else
                            {{ $viewingMonster->senses }}
                        @endif
                    </div>
                @endif
                @if (!empty($viewingMonster->languages))
                    <div class="text-sm"><span class="font-semibold text-zinc-700 dark:text-zinc-300">{{ __('Languages') }}:</span> {{ $viewingMonster->languages }}</div>
                @endif

                {{-- Special Abilities --}}
                @if (!empty($viewingMonster->special_abilities))
                    <div>
                        <flux:heading size="sm" class="mb-2">{{ __('Special Abilities') }}</flux:heading>
                        <div class="space-y-2 text-sm">
                            @foreach ($viewingMonster->special_abilities as $ability)
                                <div>
                                    <span class="font-semibold text-zinc-700 dark:text-zinc-300">{{ $ability['name'] ?? '' }}.</span>
                                    {{ $ability['desc'] ?? '' }}
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Actions --}}
                @if (!empty($viewingMonster->actions))
                    <div>
                        <flux:heading size="sm" class="mb-2">{{ __('Actions') }}</flux:heading>
                        <div class="space-y-2 text-sm">
                            @foreach ($viewingMonster->actions as $action)
                                <div>
                                    <span class="font-semibold text-zinc-700 dark:text-zinc-300">{{ $action['name'] ?? '' }}.</span>
                                    {{ $action['desc'] ?? '' }}
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Legendary Actions --}}
                @if (!empty($viewingMonster->legendary_actions))
                    <div>
                        <flux:heading size="sm" class="mb-2">{{ __('Legendary Actions') }}</flux:heading>
                        <div class="space-y-2 text-sm">
                            @foreach ($viewingMonster->legendary_actions as $action)
                                <div>
                                    <span class="font-semibold text-zinc-700 dark:text-zinc-300">{{ $action['name'] ?? '' }}.</span>
                                    {{ $action['desc'] ?? '' }}
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Reactions --}}
                @if (!empty($viewingMonster->reactions))
                    <div>
                        <flux:heading size="sm" class="mb-2">{{ __('Reactions') }}</flux:heading>
                        <div class="space-y-2 text-sm">
                            @foreach ($viewingMonster->reactions as $reaction)
                                <div>
                                    <span class="font-semibold text-zinc-700 dark:text-zinc-300">{{ $reaction['name'] ?? '' }}.</span>
                                    {{ $reaction['desc'] ?? '' }}
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Notes (custom monsters) --}}
                @if (!empty($viewingMonster->notes))
                    <div>
                        <flux:heading size="sm" class="mb-2">{{ __('Notes') }}</flux:heading>
                        <flux:text class="text-sm">{{ $viewingMonster->notes }}</flux:text>
                    </div>
                @endif
            </div>
        @endif
    </flux:modal>

    {{-- Custom Monster Create/Edit Modal --}}
    <flux:modal wire:model="showCustomForm" class="md:w-xl">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $editingCustomMonsterId ? __('Edit Custom Monster') : __('Create Custom Monster') }}</flux:heading>
                <flux:text class="mt-2">{{ __('Define a custom monster for your encounters.') }}</flux:text>
            </div>

            <div class="flex flex-col gap-4">
                <flux:input wire:model="customName" label="{{ __('Name') }}" placeholder="{{ __('Monster name...') }}" required />

                <div class="grid gap-4 sm:grid-cols-3">
                    <flux:select wire:model="customSize" label="{{ __('Size') }}">
                        @foreach (['Tiny', 'Small', 'Medium', 'Large', 'Huge', 'Gargantuan'] as $size)
                            <flux:select.option value="{{ $size }}">{{ $size }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:input wire:model="customType" label="{{ __('Type') }}" placeholder="{{ __('e.g., Fiend') }}" />
                    <flux:input wire:model="customAlignment" label="{{ __('Alignment') }}" placeholder="{{ __('e.g., Chaotic Evil') }}" />
                </div>

                <div class="grid gap-4 sm:grid-cols-4">
                    <flux:input wire:model="customArmorClass" type="number" label="{{ __('Armor Class') }}" min="1" required />
                    <flux:input wire:model="customHitPoints" type="number" label="{{ __('Hit Points') }}" min="1" required />
                    <flux:input wire:model="customHitDice" label="{{ __('Hit Dice') }}" placeholder="{{ __('e.g., 8d8+16') }}" />
                    <flux:input wire:model="customChallengeRating" type="number" label="{{ __('CR') }}" step="0.125" min="0" />
                </div>

                <flux:input wire:model="customXp" type="number" label="{{ __('XP') }}" min="0" />

                {{-- Ability Scores --}}
                <flux:heading size="sm">{{ __('Ability Scores') }}</flux:heading>
                <div class="grid grid-cols-6 gap-3">
                    @foreach (['STR' => 'customStrength', 'DEX' => 'customDexterity', 'CON' => 'customConstitution', 'INT' => 'customIntelligence', 'WIS' => 'customWisdom', 'CHA' => 'customCharisma'] as $label => $prop)
                        <flux:input wire:model="{{ $prop }}" type="number" label="{{ $label }}" min="1" max="30" />
                    @endforeach
                </div>

                <flux:input wire:model="customLanguages" label="{{ __('Languages') }}" placeholder="{{ __('e.g., Common, Infernal') }}" />
                <flux:textarea wire:model="customNotes" label="{{ __('Notes') }}" placeholder="{{ __('Special abilities, lore, tactics...') }}" rows="3" />
            </div>

            <div class="flex justify-end gap-2">
                <flux:button variant="subtle" wire:click="$set('showCustomForm', false)">{{ __('Cancel') }}</flux:button>
                <flux:button variant="primary" wire:click="saveCustomMonster">
                    {{ $editingCustomMonsterId ? __('Update Monster') : __('Create Monster') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
