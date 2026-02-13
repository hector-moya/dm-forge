<div class="mx-auto flex w-full max-w-7xl flex-col gap-4">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <flux:button variant="subtle" href="{{ route('sessions.run', $session) }}" wire:navigate icon="arrow-left" size="sm">
                {{ __('Back') }}
            </flux:button>
            <div>
                <flux:heading size="xl">{{ $encounter->name }}</flux:heading>
                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('Combat Tracker') }} &mdash; {{ $session->title }}
                </flux:text>
            </div>
        </div>
        <div class="flex items-center gap-2">
            @if (!$inCombat && count($combatants) > 0)
                <flux:button variant="primary" size="sm" wire:click="startCombat" icon="play">
                    {{ __('Start Combat') }}
                </flux:button>
            @elseif ($inCombat)
                <flux:button variant="subtle" size="sm" wire:click="previousTurn" icon="backward" />
                <flux:button variant="primary" size="sm" wire:click="nextTurn" icon="forward">
                    {{ __('Next Turn') }}
                </flux:button>
                <flux:button variant="danger" size="sm" wire:click="endCombat" wire:confirm="{{ __('End combat? All changes will be saved.') }}" icon="stop-circle">
                    {{ __('End') }}
                </flux:button>
            @endif
        </div>
    </div>

    {{-- Two-column layout --}}
    <div class="grid gap-4 lg:grid-cols-3">
        {{-- LEFT COLUMN — Initiative Order --}}
        <div class="flex flex-col gap-4 lg:col-span-1">
            {{-- Add Combatant Toggle --}}
            <flux:button variant="subtle" size="sm" wire:click="$toggle('showAddCombatant')" icon="plus" class="w-full">
                {{ __('Add Combatant') }}
            </flux:button>

            {{-- Add combatant panel --}}
            @if ($showAddCombatant)
                <div class="space-y-3 rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
                    @if ($characters->isNotEmpty())
                        <div>
                            <span class="mb-1 block text-xs font-semibold uppercase text-zinc-400">{{ __('Characters') }}</span>
                            <div class="flex flex-wrap gap-1">
                                @foreach ($characters as $character)
                                    <flux:button variant="outline" size="sm" wire:click="addCharacterToCombat({{ $character->id }})">
                                        {{ $character->name }}
                                    </flux:button>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if ($npcs->isNotEmpty())
                        <div>
                            <span class="mb-1 block text-xs font-semibold uppercase text-zinc-400">{{ __('NPCs') }}</span>
                            <div class="flex flex-wrap gap-1">
                                @foreach ($npcs as $npc)
                                    <flux:button variant="outline" size="sm" wire:click="addNpcToCombat({{ $npc->id }})">
                                        {{ $npc->name }}
                                    </flux:button>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div>
                        <span class="mb-2 block text-xs font-semibold uppercase text-zinc-400">{{ __('Custom') }}</span>
                        <form wire:submit="addCustomCombatant" class="space-y-2">
                            <flux:input wire:model="combatantName" placeholder="{{ __('Name...') }}" size="sm" />
                            <div class="grid grid-cols-3 gap-2">
                                <flux:input wire:model="combatantInitiative" type="number" label="{{ __('Init') }}" size="sm" />
                                <flux:input wire:model="combatantHpMax" type="number" label="{{ __('HP') }}" size="sm" />
                                <flux:input wire:model="combatantAc" type="number" label="{{ __('AC') }}" size="sm" />
                            </div>
                            <flux:button type="submit" variant="primary" size="sm" class="w-full">{{ __('Add') }}</flux:button>
                        </form>
                    </div>
                </div>
            @endif

            {{-- Initiative List --}}
            <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800">
                <div class="border-b border-zinc-200 px-4 py-3 dark:border-zinc-700">
                    <flux:heading size="base">{{ __('Initiative Order') }}</flux:heading>
                </div>

                @if (empty($combatants))
                    <div class="p-4">
                        <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Add combatants to begin.') }}</flux:text>
                    </div>
                @else
                    <div class="divide-y divide-zinc-100 dark:divide-zinc-700/50">
                        @foreach ($combatants as $i => $combatant)
                            <div
                                wire:click="selectCombatant({{ $i }})"
                                wire:key="combatant-{{ $i }}-{{ $combatant['name'] }}"
                                class="flex cursor-pointer items-center gap-3 px-4 py-2.5 transition
                                    {{ $inCombat && $i === $currentTurnIndex ? 'border-l-4 border-l-amber-400 bg-amber-50 dark:bg-amber-900/20' : 'border-l-4 border-l-transparent' }}
                                    {{ $selectedCombatantIndex === $i ? 'bg-blue-50 dark:bg-blue-900/20' : 'hover:bg-zinc-50 dark:hover:bg-zinc-700/30' }}"
                            >
                                {{-- Initiative Input --}}
                                <div class="w-10 shrink-0 text-center">
                                    <input type="number"
                                           value="{{ $combatant['initiative'] }}"
                                           wire:change="setInitiative({{ $i }}, $event.target.value)"
                                           class="w-10 rounded border-0 bg-transparent p-0 text-center text-sm font-bold text-zinc-700 focus:ring-1 focus:ring-blue-500 dark:text-zinc-200"
                                           onclick="event.stopPropagation()" />
                                </div>

                                {{-- Name + Conditions --}}
                                <div class="min-w-0 flex-1">
                                    @php
                                        $nameColor = match(true) {
                                            $combatant['is_pc'] => 'text-blue-600 dark:text-blue-400',
                                            $combatant['source_type'] === 'npc' => 'text-emerald-600 dark:text-emerald-400',
                                            default => 'text-red-600 dark:text-red-400',
                                        };
                                    @endphp
                                    <span class="block truncate text-sm font-medium {{ $nameColor }}">
                                        {{ $combatant['name'] }}
                                    </span>
                                    @if (!empty($combatant['conditions']))
                                        <div class="flex flex-wrap gap-0.5 mt-0.5">
                                            @foreach ($combatant['conditions'] as $cond)
                                                <span class="rounded bg-purple-100 px-1 text-[10px] text-purple-700 dark:bg-purple-900/50 dark:text-purple-300">{{ $cond }}</span>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>

                                {{-- HP + AC --}}
                                <div class="flex shrink-0 items-center gap-2 text-xs">
                                    @php
                                        $hpRatio = $combatant['hp_max'] > 0 ? $combatant['hp_current'] / $combatant['hp_max'] : 0;
                                        $hpColor = match(true) {
                                            $combatant['hp_current'] <= 0 => 'text-red-500',
                                            $hpRatio < 0.5 => 'text-amber-500',
                                            default => 'text-green-600 dark:text-green-400',
                                        };
                                    @endphp
                                    <span class="font-medium {{ $hpColor }}">{{ $combatant['hp_current'] }}/{{ $combatant['hp_max'] }}</span>
                                    <span class="text-zinc-400 dark:text-zinc-500">AC{{ $combatant['armor_class'] }}</span>
                                    <button type="button" wire:click.stop="removeCombatant({{ $i }})" class="text-zinc-300 hover:text-red-500 dark:text-zinc-600">&times;</button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- RIGHT COLUMN — Selected Combatant Details --}}
        <div class="flex flex-col gap-4 lg:col-span-2">
            @if ($selectedCombatantIndex !== null && isset($combatants[$selectedCombatantIndex]))
                @php $selected = $combatants[$selectedCombatantIndex]; @endphp

                {{-- Combat Controls Panel --}}
                <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
                    <div class="mb-3 flex items-center justify-between">
                        <flux:heading size="lg">{{ $selected['name'] }}</flux:heading>
                        <div class="flex items-center gap-3 text-sm text-zinc-500 dark:text-zinc-400">
                            <span>AC {{ $selected['armor_class'] }}</span>
                            @if ($selected['source_type'] === 'monster')
                                <flux:badge size="sm" variant="danger">{{ __('Monster') }}</flux:badge>
                            @elseif ($selected['is_pc'])
                                <flux:badge size="sm" variant="primary">{{ __('PC') }}</flux:badge>
                            @elseif ($selected['source_type'] === 'npc')
                                <flux:badge size="sm" color="emerald">{{ __('NPC') }}</flux:badge>
                            @else
                                <flux:badge size="sm" variant="outline">{{ __('Custom') }}</flux:badge>
                            @endif
                        </div>
                    </div>

                    {{-- HP Bar --}}
                    <div class="mb-3">
                        <div class="mb-1 flex items-center justify-between text-sm">
                            <span class="text-zinc-500 dark:text-zinc-400">{{ __('Hit Points') }}</span>
                            <span class="font-bold {{ $selected['hp_current'] <= 0 ? 'text-red-500' : 'text-zinc-700 dark:text-zinc-200' }}">
                                {{ $selected['hp_current'] }} / {{ $selected['hp_max'] }}
                            </span>
                        </div>
                        <div class="h-3 w-full overflow-hidden rounded-full bg-zinc-200 dark:bg-zinc-700">
                            @php
                                $hpPercent = $selected['hp_max'] > 0 ? min(100, ($selected['hp_current'] / $selected['hp_max']) * 100) : 0;
                                $barColor = match(true) {
                                    $selected['hp_current'] <= 0 => 'bg-red-500',
                                    $hpPercent < 50 => 'bg-amber-500',
                                    default => 'bg-green-500',
                                };
                            @endphp
                            <div class="h-full rounded-full transition-all {{ $barColor }}" style="width: {{ $hpPercent }}%"></div>
                        </div>
                    </div>

                    {{-- HP Adjustment Buttons --}}
                    <div class="mb-3 flex flex-wrap items-center gap-1">
                        <flux:button variant="danger" size="sm" wire:click="adjustHp({{ $selectedCombatantIndex }}, -10)">-10</flux:button>
                        <flux:button variant="danger" size="sm" wire:click="adjustHp({{ $selectedCombatantIndex }}, -5)">-5</flux:button>
                        <flux:button variant="danger" size="sm" wire:click="adjustHp({{ $selectedCombatantIndex }}, -1)">-1</flux:button>

                        <div class="flex items-center gap-1">
                            <flux:input wire:model="customHpAmount" type="number" size="sm" class="w-20" placeholder="0" />
                            <flux:button variant="primary" size="sm" wire:click="applyCustomHp({{ $selectedCombatantIndex }})">{{ __('Apply') }}</flux:button>
                        </div>

                        <flux:button variant="primary" size="sm" wire:click="adjustHp({{ $selectedCombatantIndex }}, 1)">+1</flux:button>
                        <flux:button variant="primary" size="sm" wire:click="adjustHp({{ $selectedCombatantIndex }}, 5)">+5</flux:button>
                        <flux:button variant="primary" size="sm" wire:click="adjustHp({{ $selectedCombatantIndex }}, 10)">+10</flux:button>
                        <flux:button variant="subtle" size="sm" wire:click="healFull({{ $selectedCombatantIndex }})" icon="heart">{{ __('Full') }}</flux:button>
                    </div>

                    {{-- Conditions --}}
                    <div>
                        <span class="mb-1.5 block text-xs font-semibold uppercase text-zinc-400">{{ __('Conditions') }}</span>
                        <div class="flex flex-wrap gap-1">
                            @foreach ($conditionOptions as $condition)
                                <button
                                    type="button"
                                    wire:click="toggleCondition({{ $selectedCombatantIndex }}, '{{ $condition }}')"
                                    class="rounded-full px-2 py-0.5 text-xs transition
                                        {{ in_array($condition, $selected['conditions']) ? 'bg-purple-600 text-white' : 'bg-zinc-100 text-zinc-600 hover:bg-zinc-200 dark:bg-zinc-700 dark:text-zinc-400 dark:hover:bg-zinc-600' }}"
                                >
                                    {{ ucfirst($condition) }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Full Stat Block --}}
                @if ($statBlock)
                    <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800" x-data="{ expanded: true }">
                        <button type="button" x-on:click="expanded = !expanded" class="flex w-full items-center justify-between border-b border-zinc-200 px-4 py-3 dark:border-zinc-700">
                            <flux:heading size="base">{{ __('Stat Block') }}</flux:heading>
                            <flux:icon x-bind:class="expanded ? 'rotate-180' : ''" name="chevron-down" class="size-4 text-zinc-400 transition-transform" />
                        </button>

                        <div x-show="expanded" x-collapse class="p-4">
                            @if ($statBlock['type'] === 'monster')
                                @include('livewire.sessions.partials.stat-block-monster', ['stat' => $statBlock])
                            @elseif ($statBlock['type'] === 'character')
                                @include('livewire.sessions.partials.stat-block-character', ['stat' => $statBlock])
                            @elseif ($statBlock['type'] === 'npc')
                                @include('livewire.sessions.partials.stat-block-npc', ['stat' => $statBlock])
                            @endif
                        </div>
                    </div>
                @endif

            @else
                {{-- No Selection --}}
                <div class="flex flex-1 items-center justify-center rounded-xl border border-dashed border-zinc-300 bg-zinc-50 p-12 dark:border-zinc-600 dark:bg-zinc-800/50">
                    <div class="text-center">
                        <flux:icon name="cursor-arrow-rays" class="mx-auto mb-2 size-8 text-zinc-300 dark:text-zinc-600" />
                        <flux:text class="text-zinc-400 dark:text-zinc-500">{{ __('Select a combatant to view details') }}</flux:text>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
