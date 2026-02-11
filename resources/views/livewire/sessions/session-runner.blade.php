<x-layouts::app :title="__('Session') . ' #' . $session->session_number . ' — ' . $session->title">
    <div class="mx-auto flex w-full max-w-7xl flex-col gap-4" x-data="{ dmNotesOpen: false }">
        {{-- Header --}}
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-4">
                <flux:button variant="subtle" href="{{ route('sessions.edit', $session) }}" wire:navigate icon="arrow-left" size="sm">
                    {{ __('Edit') }}
                </flux:button>
                <div>
                    <div class="flex items-center gap-3">
                        <flux:heading size="xl">{{ $session->title }}</flux:heading>
                        <flux:badge variant="warning" size="sm">{{ ucfirst($session->status) }}</flux:badge>
                    </div>
                    <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                        {{ __('Session') }} #{{ $session->session_number }} &mdash; {{ $session->campaign->name }}
                    </flux:text>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <flux:button variant="subtle" size="sm" x-on:click="dmNotesOpen = !dmNotesOpen" icon="eye">
                    {{ __('DM Notes') }}
                </flux:button>
                @if ($session->status === 'running')
                    <flux:button variant="danger" size="sm" wire:click="endSession" wire:confirm="{{ __('End this session? Status will change to completed.') }}" icon="stop-circle">
                        {{ __('End Session') }}
                    </flux:button>
                @endif
            </div>
        </div>

        {{-- DM Notes Panel --}}
        <div x-show="dmNotesOpen" x-collapse class="rounded-xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800">
            <flux:heading size="base" class="mb-2">{{ __('DM Notes') }}</flux:heading>
            <flux:text class="whitespace-pre-line text-sm text-zinc-600 dark:text-zinc-300">{{ $session->dm_notes ?: __('No DM notes.') }}</flux:text>
            @if ($session->setup_text)
                <flux:heading size="base" class="mb-2 mt-4">{{ __('Setup Text') }}</flux:heading>
                <flux:text class="whitespace-pre-line text-sm text-zinc-600 dark:text-zinc-300">{{ $session->setup_text }}</flux:text>
            @endif
        </div>

        {{-- Two-column layout --}}
        <div class="grid gap-4 lg:grid-cols-2">
            {{-- LEFT COLUMN --}}
            <div class="flex flex-col gap-4">
                {{-- Initiative Tracker --}}
                <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
                    <div class="mb-3 flex items-center justify-between">
                        <flux:heading size="lg">{{ __('Initiative') }}</flux:heading>
                        <div class="flex items-center gap-2">
                            @if (!$inCombat && count($combatants) > 0)
                                <flux:button variant="primary" size="sm" wire:click="startCombat" icon="play">
                                    {{ __('Start') }}
                                </flux:button>
                            @elseif ($inCombat)
                                <flux:button variant="primary" size="sm" wire:click="nextTurn" icon="forward">
                                    {{ __('Next') }}
                                </flux:button>
                                <flux:button variant="subtle" size="sm" wire:click="endCombat" icon="stop-circle">
                                    {{ __('End') }}
                                </flux:button>
                            @endif
                            <flux:button variant="subtle" size="sm" wire:click="$toggle('showAddCombatant')" icon="plus" />
                        </div>
                    </div>

                    {{-- Add combatant form --}}
                    @if ($showAddCombatant)
                        <div class="mb-3 space-y-2 rounded-lg border border-zinc-300 bg-zinc-50 p-3 dark:border-zinc-600 dark:bg-zinc-700/50">
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
                            @if ($encounters->isNotEmpty())
                                <div>
                                    <span class="mb-1 block text-xs font-semibold uppercase text-zinc-400">{{ __('Encounters') }}</span>
                                    <div class="flex flex-wrap gap-1">
                                        @foreach ($encounters as $encounter)
                                            <flux:button variant="outline" size="sm" wire:click="addMonstersToCombat({{ $encounter->id }})">
                                                {{ $encounter->name }} ({{ $encounter->monsters->count() }})
                                            </flux:button>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                            <div class="flex items-end gap-2">
                                <flux:input wire:model="combatantName" label="{{ __('Custom') }}" placeholder="{{ __('Name...') }}" size="sm" class="flex-1" />
                                <flux:input wire:model="combatantInitiative" type="number" label="{{ __('Init') }}" size="sm" class="w-20" />
                                <flux:button variant="primary" size="sm" wire:click="addCustomCombatant">{{ __('Add') }}</flux:button>
                            </div>
                        </div>
                    @endif

                    {{-- Combatant list --}}
                    @if (empty($combatants))
                        <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Add combatants to begin.') }}</flux:text>
                    @else
                        <div class="space-y-1">
                            @foreach ($combatants as $i => $combatant)
                                <div
                                    wire:click="selectCombatant({{ $i }})"
                                    class="flex cursor-pointer items-center justify-between rounded-lg px-3 py-2 transition
                                        {{ $inCombat && $i === $currentTurnIndex ? 'border-2 border-amber-400 bg-amber-50 dark:border-amber-500 dark:bg-amber-900/30' : '' }}
                                        {{ $selectedCombatantIndex === $i ? 'bg-blue-50 dark:bg-blue-900/30' : 'hover:bg-zinc-50 dark:hover:bg-zinc-700/50' }}"
                                >
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 text-center">
                                            <input type="number"
                                                   value="{{ $combatant['initiative'] }}"
                                                   wire:change="setInitiative({{ $i }}, $event.target.value)"
                                                   class="w-8 rounded bg-transparent p-0 text-center text-sm font-bold text-zinc-700 dark:text-zinc-200 focus:ring-1 focus:ring-blue-500"
                                                   onclick="event.stopPropagation()" />
                                        </div>
                                        <div>
                                            <span class="text-sm font-medium {{ $combatant['is_pc'] ? 'text-blue-600 dark:text-blue-400' : 'text-red-600 dark:text-red-400' }}">
                                                {{ $combatant['name'] }}
                                            </span>
                                            @if (!empty($combatant['conditions']))
                                                <div class="flex flex-wrap gap-1 mt-0.5">
                                                    @foreach ($combatant['conditions'] as $cond)
                                                        <span class="rounded bg-purple-100 px-1 text-xs text-purple-700 dark:bg-purple-900/50 dark:text-purple-300">{{ $cond }}</span>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3 text-sm">
                                        <span class="{{ $combatant['hp_current'] <= 0 ? 'text-red-500' : ($combatant['hp_current'] < $combatant['hp_max'] / 2 ? 'text-amber-500' : 'text-green-500') }}">
                                            {{ $combatant['hp_current'] }}/{{ $combatant['hp_max'] }}
                                        </span>
                                        <span class="text-zinc-400 dark:text-zinc-500">AC {{ $combatant['armor_class'] }}</span>
                                        <button type="button" wire:click.stop="removeCombatant({{ $i }})" class="text-zinc-400 hover:text-red-500">&times;</button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Combat Panel (selected combatant) --}}
                @if ($selectedCombatantIndex !== null && isset($combatants[$selectedCombatantIndex]))
                    @php $selected = $combatants[$selectedCombatantIndex]; @endphp
                    <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
                        <div class="mb-3 flex items-center justify-between">
                            <flux:heading size="lg">{{ $selected['name'] }}</flux:heading>
                            <span class="text-sm text-zinc-500 dark:text-zinc-400">AC {{ $selected['armor_class'] }}</span>
                        </div>

                        <div class="mb-3">
                            <div class="mb-1 flex items-center justify-between text-sm">
                                <span class="text-zinc-500 dark:text-zinc-400">{{ __('HP') }}</span>
                                <span class="font-bold {{ $selected['hp_current'] <= 0 ? 'text-red-500' : 'text-zinc-700 dark:text-zinc-200' }}">
                                    {{ $selected['hp_current'] }} / {{ $selected['hp_max'] }}
                                </span>
                            </div>
                            <div class="h-3 w-full overflow-hidden rounded-full bg-zinc-200 dark:bg-zinc-700">
                                <div class="h-full rounded-full transition-all {{ $selected['hp_current'] < $selected['hp_max'] / 2 ? 'bg-amber-500' : 'bg-green-500' }} {{ $selected['hp_current'] <= 0 ? '!bg-red-500' : '' }}"
                                     style="width: {{ $selected['hp_max'] > 0 ? min(100, ($selected['hp_current'] / $selected['hp_max']) * 100) : 0 }}%"></div>
                            </div>
                        </div>

                        <div class="mb-3 flex flex-wrap gap-1">
                            <flux:button variant="danger" size="sm" wire:click="adjustHp({{ $selectedCombatantIndex }}, -10)">-10</flux:button>
                            <flux:button variant="danger" size="sm" wire:click="adjustHp({{ $selectedCombatantIndex }}, -5)">-5</flux:button>
                            <flux:button variant="danger" size="sm" wire:click="adjustHp({{ $selectedCombatantIndex }}, -1)">-1</flux:button>
                            <flux:button variant="primary" size="sm" wire:click="adjustHp({{ $selectedCombatantIndex }}, 1)">+1</flux:button>
                            <flux:button variant="primary" size="sm" wire:click="adjustHp({{ $selectedCombatantIndex }}, 5)">+5</flux:button>
                            <flux:button variant="primary" size="sm" wire:click="adjustHp({{ $selectedCombatantIndex }}, 10)">+10</flux:button>
                            <flux:button variant="subtle" size="sm" wire:click="healFull({{ $selectedCombatantIndex }})">{{ __('Full') }}</flux:button>
                        </div>

                        <div>
                            <span class="mb-1 block text-xs font-semibold uppercase text-zinc-400">{{ __('Conditions') }}</span>
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
                @endif

                {{-- Session Log --}}
                <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
                    <flux:heading size="lg" class="mb-3">{{ __('Session Log') }}</flux:heading>

                    <form wire:submit="addLogEntry" class="mb-3 flex gap-2">
                        <flux:select wire:model="logType" class="w-32" size="sm">
                            <flux:select.option value="narrative">{{ __('Narrative') }}</flux:select.option>
                            <flux:select.option value="combat">{{ __('Combat') }}</flux:select.option>
                            <flux:select.option value="decision">{{ __('Decision') }}</flux:select.option>
                            <flux:select.option value="note">{{ __('Note') }}</flux:select.option>
                        </flux:select>
                        <flux:input wire:model="logEntry" placeholder="{{ __('Log entry...') }}" size="sm" class="flex-1" />
                        <flux:button type="submit" variant="primary" size="sm" icon="plus">{{ __('Log') }}</flux:button>
                    </form>

                    @if ($logs->isEmpty())
                        <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('No log entries yet.') }}</flux:text>
                    @else
                        <div class="max-h-60 space-y-1 overflow-y-auto">
                            @foreach ($logs as $log)
                                <div class="flex items-start gap-2 rounded px-2 py-1 text-sm">
                                    <span class="shrink-0 text-xs text-zinc-400 dark:text-zinc-500">{{ $log->logged_at?->format('H:i') }}</span>
                                    @php
                                        $logColor = match($log->type) {
                                            'combat' => 'text-red-500',
                                            'decision' => 'text-amber-500',
                                            'narrative' => 'text-blue-500',
                                            default => 'text-zinc-400',
                                        };
                                    @endphp
                                    <span class="shrink-0 text-xs font-semibold uppercase {{ $logColor }}">{{ $log->type }}</span>
                                    <span class="text-zinc-700 dark:text-zinc-300">{{ $log->entry }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- RIGHT COLUMN --}}
            <div class="flex flex-col gap-4">
                {{-- Scenes --}}
                <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
                    <flux:heading size="lg" class="mb-3">{{ __('Scenes') }}</flux:heading>
                    @if ($scenes->isEmpty())
                        <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('No scenes planned.') }}</flux:text>
                    @else
                        <div class="space-y-2">
                            @foreach ($scenes as $scene)
                                <div class="flex items-start justify-between rounded-lg px-3 py-2 {{ $scene->is_revealed ? 'bg-green-50 dark:bg-green-900/20' : 'bg-zinc-50 dark:bg-zinc-700/50' }}">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ $scene->title }}</span>
                                            @if ($scene->is_revealed)
                                                <flux:badge size="sm" variant="primary">{{ __('Revealed') }}</flux:badge>
                                            @endif
                                        </div>
                                        @if ($scene->is_revealed && $scene->description)
                                            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ $scene->description }}</p>
                                        @endif
                                    </div>
                                    <flux:button variant="subtle" size="sm" wire:click="toggleSceneReveal({{ $scene->id }})"
                                                 icon="{{ $scene->is_revealed ? 'eye-slash' : 'eye' }}" />
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Quick Actions --}}
                <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
                    <flux:heading size="lg" class="mb-3">{{ __('Quick Actions') }}</flux:heading>
                    <div class="flex flex-wrap gap-2">
                        <flux:button variant="primary" size="sm" wire:click="openDecisionModal" icon="scale">
                            {{ __('Major Decision') }}
                        </flux:button>
                        @if ($session->status === 'completed')
                            <flux:button variant="subtle" size="sm" href="{{ route('sessions.recap', $session) }}" wire:navigate icon="book-open">
                                {{ __('View Recap') }}
                            </flux:button>
                        @endif
                    </div>
                </div>

                {{-- Branch Options --}}
                @if ($branches->isNotEmpty())
                    <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
                        <flux:heading size="lg" class="mb-3">{{ __('Branch Options') }}</flux:heading>
                        <div class="space-y-2">
                            @foreach ($branches as $branch)
                                <div class="flex items-start gap-3 rounded-lg px-3 py-2 {{ $branch->chosen ? 'bg-indigo-50 dark:bg-indigo-900/20' : 'bg-zinc-50 dark:bg-zinc-700/50' }}">
                                    <input type="checkbox"
                                           {{ $branch->chosen ? 'checked' : '' }}
                                           wire:click="chooseBranch({{ $branch->id }})"
                                           class="mt-1 rounded border-zinc-300 text-indigo-600 focus:ring-indigo-500 dark:border-zinc-600 dark:bg-zinc-700" />
                                    <div class="flex-1">
                                        <span class="text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ $branch->label }}</span>
                                        @if ($branch->description)
                                            <p class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">{{ Str::limit($branch->description, 100) }}</p>
                                        @endif
                                        @if ($branch->chosen && $branch->consequences->isNotEmpty())
                                            <div class="mt-2 space-y-1">
                                                @foreach ($branch->consequences as $consequence)
                                                    @php
                                                        $cTypeColor = match($consequence->type) {
                                                            'immediate' => 'text-amber-600 dark:text-amber-400',
                                                            'delayed' => 'text-blue-600 dark:text-blue-400',
                                                            'meta' => 'text-purple-600 dark:text-purple-400',
                                                            default => 'text-zinc-500',
                                                        };
                                                    @endphp
                                                    <div class="text-xs">
                                                        <span class="font-semibold {{ $cTypeColor }}">{{ ucfirst($consequence->type) }}:</span>
                                                        <span class="text-zinc-600 dark:text-zinc-400">{{ $consequence->description }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Decision Recorder Modal --}}
    <flux:modal wire:model="showDecisionModal" variant="dialog" class="max-w-lg">
        <div class="space-y-4">
            <flux:heading size="lg">{{ __('Record Major Decision') }}</flux:heading>

            <flux:textarea
                wire:model="decisionAction"
                label="{{ __('What did the character(s) do?') }}"
                placeholder="{{ __('Describe the action or decision...') }}"
                rows="3"
                required
            />

            <div>
                <span class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Characters involved') }}</span>
                <div class="flex flex-wrap gap-2">
                    @foreach ($characters as $character)
                        <label class="flex items-center gap-1.5 rounded-lg border border-zinc-200 px-2 py-1 text-sm dark:border-zinc-600 cursor-pointer
                            {{ in_array($character->id, $decisionCharacterIds) ? 'bg-blue-50 border-blue-300 dark:bg-blue-900/30 dark:border-blue-600' : '' }}">
                            <input type="checkbox" value="{{ $character->id }}" wire:model="decisionCharacterIds"
                                   class="rounded border-zinc-300 text-blue-600 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700" />
                            {{ $character->name }}
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- AI Suggestion --}}
            <div class="flex items-center gap-2">
                <flux:button variant="subtle" size="sm" wire:click="getAiSuggestion" wire:loading.attr="disabled" icon="sparkles">
                    <span wire:loading.remove wire:target="getAiSuggestion">{{ __('Get AI Suggestion') }}</span>
                    <span wire:loading wire:target="getAiSuggestion">{{ __('Thinking...') }}</span>
                </flux:button>
            </div>

            @if ($aiSuggestion)
                <div class="rounded-lg border border-indigo-200 bg-indigo-50 p-3 dark:border-indigo-700 dark:bg-indigo-900/20">
                    <span class="mb-1 block text-xs font-semibold uppercase text-indigo-500">{{ __('AI Suggestion') }}</span>
                    <p class="mb-2 text-sm text-zinc-600 dark:text-zinc-300">{{ $aiSuggestion['reasoning'] }}</p>
                    <div class="flex items-center gap-4 text-sm">
                        <span>Good/Evil: <strong>{{ $aiSuggestion['good_evil_delta'] >= 0 ? '+' : '' }}{{ $aiSuggestion['good_evil_delta'] }}</strong></span>
                        <span>Law/Chaos: <strong>{{ $aiSuggestion['law_chaos_delta'] >= 0 ? '+' : '' }}{{ $aiSuggestion['law_chaos_delta'] }}</strong></span>
                    </div>
                    @if (!empty($aiSuggestion['tags']))
                        <div class="mt-1 flex flex-wrap gap-1">
                            @foreach ($aiSuggestion['tags'] as $tag)
                                <span class="rounded bg-indigo-100 px-1.5 py-0.5 text-xs text-indigo-700 dark:bg-indigo-800 dark:text-indigo-300">{{ $tag }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="flex justify-end gap-2">
                    <flux:button variant="subtle" wire:click="$set('showDecisionModal', false)">
                        {{ __('Cancel') }}
                    </flux:button>
                    <flux:button variant="primary" wire:click="confirmDecision({{ $aiSuggestion['good_evil_delta'] }}, {{ $aiSuggestion['law_chaos_delta'] }})">
                        {{ __('Accept & Apply') }}
                    </flux:button>
                </div>
            @else
                <div class="flex justify-end gap-2">
                    <flux:button variant="subtle" wire:click="$set('showDecisionModal', false)">
                        {{ __('Cancel') }}
                    </flux:button>
                    <flux:button variant="primary" wire:click="confirmDecision(0, 0)">
                        {{ __('Record (No Alignment Change)') }}
                    </flux:button>
                </div>
            @endif
        </div>
    </flux:modal>
</x-layouts::app>
