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
            <flux:modal.trigger name="ability-check-cheatsheet">
                <flux:button variant="subtle" size="sm" icon="academic-cap">{{ __('Skills') }}</flux:button>
            </flux:modal.trigger>
            <flux:modal.trigger name="npc-interaction-cheatsheet">
                <flux:button variant="subtle" size="sm" icon="users">{{ __('NPC Rules') }}</flux:button>
            </flux:modal.trigger>
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

    {{-- Scene Navigation Bar --}}
    @if ($allScenes->isNotEmpty())
        <div class="flex items-center gap-2">
            <flux:button variant="subtle" size="sm" wire:click="previousScene" icon="chevron-left" :disabled="!$currentSceneId" />
            <div class="flex flex-1 items-center gap-1.5 overflow-x-auto">
                @foreach ($allScenes as $scene)
                    <button
                        type="button"
                        wire:click="navigateToScene({{ $scene->id }})"
                        class="shrink-0 rounded-full px-3 py-1 text-xs font-medium whitespace-nowrap transition
                            {{ $scene->id === $currentSceneId
                                ? 'bg-blue-600 text-white'
                                : ($scene->is_revealed
                                    ? 'bg-green-100 text-green-700 hover:bg-green-200 dark:bg-green-900/30 dark:text-green-400'
                                    : 'bg-zinc-100 text-zinc-600 hover:bg-zinc-200 dark:bg-zinc-700 dark:text-zinc-400') }}"
                    >
                        {{ $scene->title }}
                    </button>
                @endforeach
            </div>
            <flux:button variant="subtle" size="sm" wire:click="nextScene" icon="chevron-right" :disabled="!$currentSceneId" />
        </div>
    @endif

    {{-- Two-column layout --}}
    <div class="grid gap-4 lg:grid-cols-3">
        {{-- LEFT COLUMN (2 cols) --}}
        <div class="flex flex-col gap-4 lg:col-span-2">
            {{-- Current Scene --}}
            @if ($currentScene)
                <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-800">
                    <div class="mb-3 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <flux:heading size="lg">{{ $currentScene->title }}</flux:heading>
                            @if ($currentScene->is_revealed)
                                <flux:badge size="sm" variant="primary">{{ __('Revealed') }}</flux:badge>
                            @endif
                        </div>
                        <flux:button variant="subtle" size="sm" wire:click="toggleSceneReveal({{ $currentScene->id }})"
                                     icon="{{ $currentScene->is_revealed ? 'eye-slash' : 'eye' }}"
                                     title="{{ $currentScene->is_revealed ? __('Hide') : __('Reveal') }}" />
                    </div>

                    @if ($currentScene->image_url)
                        <x-image-lightbox :src="$currentScene->image_url" :alt="$currentScene->title" class="mb-3 max-h-48 w-full rounded-lg object-cover" />
                    @endif

                    @if ($currentScene->description)
                        <flux:text class="whitespace-pre-line text-sm text-zinc-600 dark:text-zinc-300">{{ $currentScene->description }}</flux:text>
                    @endif

                    @if ($currentScene->notes)
                        <details class="mt-3">
                            <summary class="cursor-pointer text-xs font-semibold uppercase text-zinc-400">{{ __('Scene Notes (DM)') }}</summary>
                            <p class="mt-1 whitespace-pre-line text-sm text-zinc-500 dark:text-zinc-400">{{ $currentScene->notes }}</p>
                        </details>
                    @endif
                </div>

                {{-- Scene Ability Checks --}}
                <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
                    <div class="mb-3 flex items-center justify-between">
                        <flux:heading size="lg">{{ __('Ability Checks') }}</flux:heading>
                        <flux:button variant="subtle" size="sm" wire:click="openAbilityCheckForm()" icon="plus">{{ __('Add') }}</flux:button>
                    </div>

                    {{-- Inline form --}}
                    @if ($showAbilityCheckForm)
                        <div class="mb-4 rounded-lg border border-zinc-300 bg-zinc-50 p-4 dark:border-zinc-600 dark:bg-zinc-700/50">
                            <flux:heading size="base" class="mb-3">
                                {{ $editingCheckId ? __('Edit Check') : __('New Ability Check') }}
                            </flux:heading>
                            <div class="flex flex-col gap-3">
                                <div class="grid grid-cols-2 gap-3">
                                    <flux:select wire:model="abilityCheckForm.skill" label="{{ __('Skill') }}" required>
                                        <flux:select.option value="">{{ __('— Select skill —') }}</flux:select.option>
                                        @foreach (\App\Enums\DndSkill::cases() as $skill)
                                            <flux:select.option value="{{ $skill->value }}">
                                                {{ $skill->label() }} ({{ $skill->ability() }})
                                            </flux:select.option>
                                        @endforeach
                                    </flux:select>
                                    <flux:input wire:model="abilityCheckForm.subject" label="{{ __('Subject (optional)') }}" placeholder="{{ __('e.g. the painting') }}" />
                                </div>
                                <div class="grid grid-cols-2 gap-3">
                                    <flux:input wire:model="abilityCheckForm.dc" type="number" min="1" max="30" label="{{ __('DC (normal success)') }}" required />
                                    <flux:input wire:model="abilityCheckForm.dcSuper" type="number" min="1" max="30" label="{{ __('DC (super success, optional)') }}" />
                                </div>
                                <flux:textarea wire:model="abilityCheckForm.failureText" label="{{ __('Failure (below DC)') }}" placeholder="{{ __('What the players see/learn on failure...') }}" rows="2" required />
                                <flux:textarea wire:model="abilityCheckForm.successText" label="{{ __('Normal success') }}" placeholder="{{ __('What the players see/learn on normal success...') }}" rows="2" required />
                                <flux:textarea wire:model="abilityCheckForm.superSuccessText" label="{{ __('Super success (optional)') }}" placeholder="{{ __('What the players see/learn on exceptional success...') }}" rows="2" />
                                <div class="flex items-center justify-end gap-2">
                                    <flux:button variant="subtle" size="sm" wire:click="$set('showAbilityCheckForm', false)">{{ __('Cancel') }}</flux:button>
                                    <flux:button variant="primary" size="sm" wire:click="saveAbilityCheck">{{ __('Save') }}</flux:button>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Check cards --}}
                    @if ($sceneAbilityChecks->isEmpty() && !$showAbilityCheckForm)
                        <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('No ability checks defined for this scene.') }}</flux:text>
                    @else
                        <div class="space-y-2">
                            @foreach ($sceneAbilityChecks as $check)
                                <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-3 dark:border-zinc-600 dark:bg-zinc-700/30" wire:key="check-{{ $check->id }}">
                                    <div class="mb-2 flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <flux:badge size="sm" :color="$check->skill->abilityColor()">
                                                {{ $check->skill->label() }}
                                            </flux:badge>
                                            <span class="text-xs text-zinc-400 dark:text-zinc-500">{{ $check->skill->ability() }}</span>
                                            @if ($check->subject)
                                                <span class="text-sm font-medium text-zinc-700 dark:text-zinc-200">— {{ $check->subject }}</span>
                                            @endif
                                        </div>
                                        <div class="flex items-center gap-1">
                                            <flux:button variant="subtle" size="sm" wire:click="openAbilityCheckForm({{ $check->id }})" icon="pencil" />
                                            <flux:button variant="subtle" size="sm" wire:click="deleteAbilityCheck({{ $check->id }})" wire:confirm="{{ __('Remove this ability check?') }}" icon="trash" />
                                        </div>
                                    </div>
                                    <div class="space-y-1 text-sm">
                                        <div class="flex gap-2">
                                            <span class="shrink-0 font-medium text-red-500">✗ Below DC {{ $check->dc }}:</span>
                                            <span class="text-zinc-600 dark:text-zinc-300">{{ $check->failure_text }}</span>
                                        </div>
                                        <div class="flex gap-2">
                                            <span class="shrink-0 font-medium text-green-600 dark:text-green-400">✓ DC {{ $check->dc }}+:</span>
                                            <span class="text-zinc-600 dark:text-zinc-300">{{ $check->success_text }}</span>
                                        </div>
                                        @if ($check->dc_super && $check->super_success_text)
                                            <div class="flex gap-2">
                                                <span class="shrink-0 font-medium text-amber-500">✦ DC {{ $check->dc_super }}+:</span>
                                                <span class="text-zinc-600 dark:text-zinc-300">{{ $check->super_success_text }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Scene Encounters --}}
                @if ($sceneEncounters->isNotEmpty())
                    <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
                        <flux:heading size="lg" class="mb-3">{{ __('Encounters') }}</flux:heading>
                        <div class="space-y-3">
                            @foreach ($sceneEncounters as $encounter)
                                <div class="rounded-lg border border-amber-200 bg-amber-50 p-3 dark:border-amber-700 dark:bg-amber-900/20" wire:key="runner-enc-{{ $encounter->id }}">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <flux:icon name="bolt" class="size-4 text-amber-500" />
                                            <span class="text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ $encounter->name }}</span>
                                            @if ($encounter->difficulty)
                                                @php
                                                    $diffColor = match($encounter->difficulty) {
                                                        'easy' => 'green', 'medium' => 'amber', 'hard' => 'red', 'deadly' => 'red', default => 'zinc',
                                                    };
                                                @endphp
                                                <flux:badge size="sm" variant="pill" :color="$diffColor">{{ ucfirst($encounter->difficulty) }}</flux:badge>
                                            @endif
                                        </div>
                                        <div class="flex items-center gap-1">
                                            <flux:button variant="subtle" size="sm" wire:click="addMonstersToCombat({{ $encounter->id }})" icon="plus" title="{{ __('Add to Initiative') }}" />
                                            <flux:button variant="primary" size="sm" href="{{ route('sessions.combat', [$session, $encounter]) }}" wire:navigate icon="bolt" title="{{ __('Launch Combat') }}" />
                                        </div>
                                    </div>
                                    @if ($encounter->description)
                                        <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">{{ $encounter->description }}</p>
                                    @endif

                                    {{-- Monsters --}}
                                    @if ($encounter->monsters->isNotEmpty())
                                        <div class="mt-2 flex flex-wrap gap-2">
                                            @foreach ($encounter->monsters as $monster)
                                                <div class="flex items-center gap-1 rounded-md bg-red-50 px-2 py-1 text-xs dark:bg-red-900/30">
                                                    <span class="font-medium text-red-700 dark:text-red-300">{{ $monster->name }}</span>
                                                    <span class="text-red-400 dark:text-red-500">HP {{ $monster->hp_current ?? $monster->hp_max }}/{{ $monster->hp_max }} AC {{ $monster->armor_class }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif

                                    {{-- NPCs --}}
                                    @if ($encounter->npcs->isNotEmpty())
                                        <div class="mt-2 flex flex-wrap gap-2">
                                            @foreach ($encounter->npcs as $encounterNpc)
                                                <div class="flex items-center gap-1 rounded-md bg-emerald-50 px-2 py-1 text-xs dark:bg-emerald-900/30">
                                                    <span class="font-medium text-emerald-700 dark:text-emerald-300">{{ $encounterNpc->name }}</span>
                                                    <span class="text-emerald-400 dark:text-emerald-500">HP {{ $encounterNpc->hp_current ?? $encounterNpc->hp_max }}/{{ $encounterNpc->hp_max }} AC {{ $encounterNpc->armor_class }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Scene Puzzles --}}
                @if ($scenePuzzles->isNotEmpty())
                    <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
                        <flux:heading size="lg" class="mb-3">{{ __('Puzzles') }}</flux:heading>
                        <div class="space-y-3">
                            @foreach ($scenePuzzles as $puzzle)
                                <div class="rounded-md border border-violet-200 bg-violet-50 p-3 dark:border-violet-700 dark:bg-violet-900/30" wire:key="runner-puzzle-{{ $puzzle->id }}">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <flux:icon.puzzle-piece class="size-4 text-violet-500" />
                                            <span class="text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ $puzzle->name }}</span>
                                            <flux:badge size="sm" variant="pill" color="{{ match($puzzle->difficulty) { 'easy' => 'green', 'medium' => 'amber', 'hard' => 'red' } }}">{{ ucfirst($puzzle->difficulty) }}</flux:badge>
                                            @if ($puzzle->is_solved)
                                                <flux:badge size="sm" variant="pill" color="emerald">{{ __('Solved') }}</flux:badge>
                                            @endif
                                        </div>
                                        <flux:button variant="subtle" size="sm" wire:click="togglePuzzleSolved({{ $puzzle->id }})" icon="{{ $puzzle->is_solved ? 'x-mark' : 'check' }}" title="{{ $puzzle->is_solved ? __('Mark Unsolved') : __('Mark Solved') }}" />
                                    </div>

                                    <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">{{ $puzzle->description }}</p>

                                    {{-- Progressive Hint Buttons --}}
                                    @php $currentTier = $revealedHints[$puzzle->id] ?? 0; @endphp
                                    <div class="mt-2 flex flex-wrap gap-2">
                                        @if ($puzzle->hint_tier_1)
                                            @if ($currentTier >= 1)
                                                <div class="w-full rounded bg-amber-50 p-2 text-sm text-amber-800 dark:bg-amber-900/30 dark:text-amber-300">
                                                    <span class="font-semibold">{{ __('Hint 1:') }}</span> {{ $puzzle->hint_tier_1 }}
                                                </div>
                                            @else
                                                <flux:button variant="subtle" size="sm" wire:click="revealHint({{ $puzzle->id }}, 1)" icon="light-bulb">{{ __('Hint 1') }}</flux:button>
                                            @endif
                                        @endif
                                        @if ($puzzle->hint_tier_2)
                                            @if ($currentTier >= 2)
                                                <div class="w-full rounded bg-orange-50 p-2 text-sm text-orange-800 dark:bg-orange-900/30 dark:text-orange-300">
                                                    <span class="font-semibold">{{ __('Hint 2:') }}</span> {{ $puzzle->hint_tier_2 }}
                                                </div>
                                            @elseif ($currentTier >= 1)
                                                <flux:button variant="subtle" size="sm" wire:click="revealHint({{ $puzzle->id }}, 2)" icon="light-bulb">{{ __('Hint 2') }}</flux:button>
                                            @endif
                                        @endif
                                        @if ($puzzle->hint_tier_3)
                                            @if ($currentTier >= 3)
                                                <div class="w-full rounded bg-red-50 p-2 text-sm text-red-800 dark:bg-red-900/30 dark:text-red-300">
                                                    <span class="font-semibold">{{ __('Hint 3:') }}</span> {{ $puzzle->hint_tier_3 }}
                                                </div>
                                            @elseif ($currentTier >= 2)
                                                <flux:button variant="subtle" size="sm" wire:click="revealHint({{ $puzzle->id }}, 3)" icon="light-bulb">{{ __('Hint 3') }}</flux:button>
                                            @endif
                                        @endif
                                    </div>

                                    {{-- Solution (DM only, collapsible) --}}
                                    <details class="mt-2">
                                        <summary class="cursor-pointer text-xs font-semibold uppercase text-violet-500">{{ __('Solution (DM Eyes Only)') }}</summary>
                                        <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">{{ $puzzle->solution }}</p>
                                    </details>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Scene Branch Options --}}
                @if ($sceneBranches->isNotEmpty())
                    <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
                        <flux:heading size="lg" class="mb-3">{{ __('Branch Options') }}</flux:heading>
                        <div class="space-y-2">
                            @foreach ($sceneBranches as $branch)
                                <button
                                    type="button"
                                    wire:click="chooseBranch({{ $branch->id }})"
                                    @if ($branch->chosen) disabled @endif
                                    class="flex w-full items-start gap-3 rounded-lg border px-4 py-3 text-left transition
                                        {{ $branch->chosen
                                            ? 'border-indigo-300 bg-indigo-50 dark:border-indigo-600 dark:bg-indigo-900/20'
                                            : 'border-zinc-200 bg-zinc-50 hover:border-indigo-300 hover:bg-indigo-50 dark:border-zinc-600 dark:bg-zinc-700/50 dark:hover:border-indigo-600 dark:hover:bg-indigo-900/20' }}"
                                >
                                    <flux:icon name="arrows-right-left" class="mt-0.5 size-4 shrink-0 {{ $branch->chosen ? 'text-indigo-500' : 'text-zinc-400' }}" />
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ $branch->label }}</span>
                                            @if ($branch->chosen)
                                                <flux:badge size="sm" variant="primary">{{ __('Chosen') }}</flux:badge>
                                            @endif
                                            @if ($branch->destinationScene)
                                                <span class="flex items-center gap-1 text-xs text-zinc-400">
                                                    <flux:icon name="arrow-right" class="size-3" />
                                                    {{ $branch->destinationScene->title }}
                                                </span>
                                            @endif
                                        </div>
                                        @if ($branch->description)
                                            <p class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">{{ Str::limit($branch->description, 150) }}</p>
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
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif
            @else
                <div class="rounded-xl border border-zinc-200 bg-white p-8 text-center dark:border-zinc-700 dark:bg-zinc-800">
                    <flux:icon name="map" class="mx-auto mb-2 size-8 text-zinc-300 dark:text-zinc-600" />
                    <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('No scenes planned for this session.') }}</flux:text>
                </div>
            @endif
        </div>

        {{-- RIGHT COLUMN (1 col) --}}
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
                        @if ($sceneEncounters->isNotEmpty())
                            <div>
                                <span class="mb-1 block text-xs font-semibold uppercase text-zinc-400">{{ __('Encounters') }}</span>
                                <div class="flex flex-wrap gap-1">
                                    @foreach ($sceneEncounters as $encounter)
                                        <flux:button variant="outline" size="sm" wire:click="addMonstersToCombat({{ $encounter->id }})">
                                            {{ $encounter->name }} ({{ $encounter->monsters->count() + $encounter->npcs->count() }})
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
                                        @php
                                            $nameColor = match($combatant['source_type']) {
                                                'character' => 'text-blue-600 dark:text-blue-400',
                                                'encounter_npc' => 'text-emerald-600 dark:text-emerald-400',
                                                default => 'text-red-600 dark:text-red-400',
                                            };
                                        @endphp
                                        <span class="text-sm font-medium {{ $nameColor }}">
                                            {{ $combatant['name'] }}
                                        </span>
                                        @if (!empty($combatant['conditions']))
                                            <div class="mt-0.5 flex flex-wrap gap-1">
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
                            <div class="h-full rounded-full transition-all {{ $selected['hp_current'] < $selected['hp_max'] / 2 ? 'bg-amber-500' : 'bg-green-500' }} {{ $selected['hp_current'] <= 0 ? 'bg-red-500!' : '' }}"
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

            {{-- Session Notes --}}
            <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
                <flux:heading size="lg" class="mb-3">{{ __('Session Notes') }}</flux:heading>

                <div class="mb-3 flex flex-wrap gap-2">
                    <flux:button variant="primary" size="sm" wire:click="openNoteModal" icon="pencil-square">
                        {{ __('Write Note') }}
                    </flux:button>
                    @if (in_array($session->status, ['running', 'completed']))
                        <flux:button variant="subtle" size="sm" href="{{ route('sessions.recap', $session) }}" wire:navigate icon="book-open">
                            {{ __('View Recap') }}
                        </flux:button>
                    @endif
                </div>

                @if ($logs->isEmpty())
                    <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('No notes yet.') }}</flux:text>
                @else
                    <div class="max-h-60 space-y-1 overflow-y-auto">
                        @foreach ($logs as $log)
                            <div class="group flex items-center gap-2 rounded px-2 py-1 text-sm">
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
                                <span class="flex-1 text-zinc-700 dark:text-zinc-300">{{ $log->entry }}</span>
                                <flux:dropdown position="bottom" align="end">
                                    <flux:button icon="ellipsis-horizontal" variant="ghost" size="xs" class="shrink-0 opacity-0 group-hover:opacity-100" />
                                    <flux:menu>
                                        <flux:menu.item icon="eye" wire:click="openViewLog({{ $log->id }})">{{ __('View') }}</flux:menu.item>
                                        <flux:menu.item icon="pencil" wire:click="openEditLog({{ $log->id }})">{{ __('Edit') }}</flux:menu.item>
                                        <flux:menu.separator />
                                        <flux:menu.item icon="trash" variant="danger" wire:click="deleteLog({{ $log->id }})">{{ __('Delete') }}</flux:menu.item>
                                    </flux:menu>
                                </flux:dropdown>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Skill & Ability Check Cheatsheet --}}
    <flux:modal name="ability-check-cheatsheet" variant="flyout" class="w-full max-w-md">
        <flux:heading size="lg" class="mb-1">{{ __('Skill & Ability Check Reference') }}</flux:heading>
        <flux:text class="mb-4 text-sm text-zinc-500 dark:text-zinc-400">{{ __('All D&D 5e skills grouped by governing ability score.') }}</flux:text>

        @php
            $skillGroups = [
                'STR' => ['color' => 'red',    'label' => 'Strength'],
                'DEX' => ['color' => 'green',   'label' => 'Dexterity'],
                'CON' => ['color' => 'orange',  'label' => 'Constitution'],
                'INT' => ['color' => 'blue',    'label' => 'Intelligence'],
                'WIS' => ['color' => 'cyan',    'label' => 'Wisdom'],
                'CHA' => ['color' => 'purple',  'label' => 'Charisma'],
            ];
        @endphp

        <div class="flex flex-col gap-4">
            @foreach ($skillGroups as $abilityKey => $abilityMeta)
                <div>
                    <div class="mb-2 flex items-center gap-2">
                        <flux:badge :color="$abilityMeta['color']" size="sm">{{ $abilityKey }}</flux:badge>
                        <span class="text-sm font-semibold text-zinc-700 dark:text-zinc-200">{{ $abilityMeta['label'] }}</span>
                    </div>
                    <div class="space-y-1.5 pl-2">
                        @foreach (\App\Enums\DndSkill::byAbility($abilityKey) as $skill)
                            <div>
                                <span class="text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ $skill->label() }}</span>
                                <span class="ml-1 text-sm text-zinc-500 dark:text-zinc-400">— {{ $skill->description() }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </flux:modal>

    {{-- NPC Interaction Rules Cheatsheet --}}
    <flux:modal name="npc-interaction-cheatsheet" variant="flyout" class="w-full max-w-md">
        <flux:heading size="lg" class="mb-1">{{ __('NPC Interaction Rules') }}</flux:heading>
        <flux:text class="mb-4 text-sm text-zinc-500 dark:text-zinc-400">{{ __('What your NPC rolls in response to player actions.') }}</flux:text>

        <div class="flex flex-col gap-5">
            {{-- Social Interactions --}}
            <div>
                <flux:heading size="base" class="mb-2">{{ __('Social Interactions') }}</flux:heading>
                <div class="overflow-hidden rounded-lg border border-zinc-200 dark:border-zinc-600">
                    <table class="w-full text-sm">
                        <thead class="bg-zinc-50 dark:bg-zinc-700/50">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-zinc-500">{{ __('Player rolls') }}</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-zinc-500">{{ __('NPC rolls') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-700">
                            <tr>
                                <td class="px-3 py-2">
                                    <flux:badge color="purple" size="sm">Persuasion</flux:badge>
                                    <span class="ml-1 text-zinc-500">(CHA)</span>
                                </td>
                                <td class="px-3 py-2 text-zinc-600 dark:text-zinc-300">Insight (WIS) — contested, to detect ulterior motive</td>
                            </tr>
                            <tr>
                                <td class="px-3 py-2">
                                    <flux:badge color="purple" size="sm">Deception</flux:badge>
                                    <span class="ml-1 text-zinc-500">(CHA)</span>
                                </td>
                                <td class="px-3 py-2 text-zinc-600 dark:text-zinc-300">Insight (WIS) — contested roll to see through the lie</td>
                            </tr>
                            <tr>
                                <td class="px-3 py-2">
                                    <flux:badge color="purple" size="sm">Intimidation</flux:badge>
                                    <span class="ml-1 text-zinc-500">(CHA)</span>
                                </td>
                                <td class="px-3 py-2 text-zinc-600 dark:text-zinc-300">WIS saving throw <em>or</em> Insight (WIS) contested — DM's choice</td>
                            </tr>
                            <tr>
                                <td class="px-3 py-2">
                                    <flux:badge color="purple" size="sm">Performance</flux:badge>
                                    <span class="ml-1 text-zinc-500">(CHA)</span>
                                </td>
                                <td class="px-3 py-2 text-zinc-600 dark:text-zinc-300">Passive Perception or Insight — NPC reacts based on result</td>
                            </tr>
                            <tr>
                                <td class="px-3 py-2">
                                    <flux:badge color="cyan" size="sm">Insight</flux:badge>
                                    <span class="ml-1 text-zinc-500">(WIS)</span>
                                </td>
                                <td class="px-3 py-2 text-zinc-600 dark:text-zinc-300">No counter roll — DM decides what the NPC reveals</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Combat Interactions --}}
            <div>
                <flux:heading size="base" class="mb-2">{{ __('Combat Interactions') }}</flux:heading>
                <div class="overflow-hidden rounded-lg border border-zinc-200 dark:border-zinc-600">
                    <table class="w-full text-sm">
                        <thead class="bg-zinc-50 dark:bg-zinc-700/50">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-zinc-500">{{ __('Player action') }}</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-zinc-500">{{ __('NPC counter') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-700">
                            <tr>
                                <td class="px-3 py-2">
                                    <flux:badge color="red" size="sm">Grapple</flux:badge>
                                    <span class="ml-1 text-zinc-500">Athletics (STR)</span>
                                </td>
                                <td class="px-3 py-2 text-zinc-600 dark:text-zinc-300">Athletics (STR) <em>or</em> Acrobatics (DEX) — NPC's choice</td>
                            </tr>
                            <tr>
                                <td class="px-3 py-2">
                                    <flux:badge color="red" size="sm">Shove</flux:badge>
                                    <span class="ml-1 text-zinc-500">Athletics (STR)</span>
                                </td>
                                <td class="px-3 py-2 text-zinc-600 dark:text-zinc-300">Athletics (STR) <em>or</em> Acrobatics (DEX) — NPC's choice</td>
                            </tr>
                            <tr>
                                <td class="px-3 py-2">
                                    <flux:badge color="purple" size="sm">Feint/Distract</flux:badge>
                                    <span class="ml-1 text-zinc-500">Deception (CHA)</span>
                                </td>
                                <td class="px-3 py-2 text-zinc-600 dark:text-zinc-300">Insight (WIS) — contested roll</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- General Rules --}}
            <div class="rounded-lg border border-amber-200 bg-amber-50 p-3 dark:border-amber-700 dark:bg-amber-900/20">
                <flux:heading size="base" class="mb-1 text-amber-800 dark:text-amber-300">{{ __('General Rule') }}</flux:heading>
                <flux:text class="text-sm text-amber-700 dark:text-amber-400">
                    {{ __('Contested rolls: both sides roll d20 + modifier. Higher total wins. On a tie, the action being resisted fails (tie goes to the defender).') }}
                </flux:text>
            </div>

            {{-- Common DCs --}}
            <div>
                <flux:heading size="base" class="mb-2">{{ __('Common DCs') }}</flux:heading>
                <div class="grid grid-cols-2 gap-2 text-sm">
                    <div class="flex items-center justify-between rounded-lg border border-zinc-200 px-3 py-2 dark:border-zinc-600">
                        <span class="text-zinc-600 dark:text-zinc-300">{{ __('Very Easy') }}</span>
                        <flux:badge color="zinc" size="sm">5</flux:badge>
                    </div>
                    <div class="flex items-center justify-between rounded-lg border border-zinc-200 px-3 py-2 dark:border-zinc-600">
                        <span class="text-zinc-600 dark:text-zinc-300">{{ __('Easy') }}</span>
                        <flux:badge color="green" size="sm">10</flux:badge>
                    </div>
                    <div class="flex items-center justify-between rounded-lg border border-zinc-200 px-3 py-2 dark:border-zinc-600">
                        <span class="text-zinc-600 dark:text-zinc-300">{{ __('Medium') }}</span>
                        <flux:badge color="amber" size="sm">15</flux:badge>
                    </div>
                    <div class="flex items-center justify-between rounded-lg border border-zinc-200 px-3 py-2 dark:border-zinc-600">
                        <span class="text-zinc-600 dark:text-zinc-300">{{ __('Hard') }}</span>
                        <flux:badge color="orange" size="sm">20</flux:badge>
                    </div>
                    <div class="flex items-center justify-between rounded-lg border border-zinc-200 px-3 py-2 dark:border-zinc-600">
                        <span class="text-zinc-600 dark:text-zinc-300">{{ __('Very Hard') }}</span>
                        <flux:badge color="red" size="sm">25</flux:badge>
                    </div>
                    <div class="flex items-center justify-between rounded-lg border border-zinc-200 px-3 py-2 dark:border-zinc-600">
                        <span class="text-zinc-600 dark:text-zinc-300">{{ __('Nearly Impossible') }}</span>
                        <flux:badge color="red" size="sm">30</flux:badge>
                    </div>
                </div>
            </div>
        </div>
    </flux:modal>

    {{-- Write Note Modal --}}
    <flux:modal wire:model="showNoteModal" variant="dialog" class="max-w-lg">
        <div class="space-y-4">
            <flux:heading size="lg">{{ __('Write Note') }}</flux:heading>

            <flux:select wire:model="noteType" label="{{ __('Note type') }}">
                <flux:select.option value="combat">{{ __('Combat') }}</flux:select.option>
                <flux:select.option value="narrative">{{ __('Narrative') }}</flux:select.option>
                <flux:select.option value="decision">{{ __('Decision') }}</flux:select.option>
                <flux:select.option value="note">{{ __('Note') }}</flux:select.option>
            </flux:select>

            <div>
                <span class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Characters involved') }}</span>
                <div class="flex flex-wrap gap-2">
                    @foreach ($characters as $character)
                        <label class="flex cursor-pointer items-center gap-1.5 rounded-lg border border-zinc-200 px-2 py-1 text-sm dark:border-zinc-600
                            {{ in_array($character->id, $noteCharacterIds) ? 'bg-blue-50 border-blue-300 dark:bg-blue-900/30 dark:border-blue-600' : '' }}">
                            <input type="checkbox" value="{{ $character->id }}" wire:model="noteCharacterIds"
                                   class="rounded border-zinc-300 text-blue-600 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700" />
                            {{ $character->name }}
                        </label>
                    @endforeach
                </div>
            </div>

            <flux:textarea
                wire:model="noteEntry"
                label="{{ __('Description') }}"
                placeholder="{{ __('Describe what happened...') }}"
                rows="3"
                required
            />

            @if ($noteType === 'decision')
                {{-- AI Alignment Suggestion --}}
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
                        @if (! empty($aiSuggestion['tags']))
                            <div class="mt-1 flex flex-wrap gap-1">
                                @foreach ($aiSuggestion['tags'] as $tag)
                                    <span class="rounded bg-indigo-100 px-1.5 py-0.5 text-xs text-indigo-700 dark:bg-indigo-800 dark:text-indigo-300">{{ $tag }}</span>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div class="flex justify-end gap-2">
                        <flux:button variant="subtle" wire:click="$set('showNoteModal', false)">
                            {{ __('Cancel') }}
                        </flux:button>
                        <flux:button variant="subtle" wire:click="confirmDecision(0, 0)">
                            {{ __('Save Without Alignment Change') }}
                        </flux:button>
                        <flux:button variant="primary" wire:click="confirmDecision({{ $aiSuggestion['good_evil_delta'] }}, {{ $aiSuggestion['law_chaos_delta'] }})">
                            {{ __('Accept & Apply') }}
                        </flux:button>
                    </div>
                @else
                    <div class="flex justify-end gap-2">
                        <flux:button variant="subtle" wire:click="$set('showNoteModal', false)">
                            {{ __('Cancel') }}
                        </flux:button>
                        <flux:button variant="primary" wire:click="confirmDecision(0, 0)">
                            {{ __('Save Without Alignment Change') }}
                        </flux:button>
                    </div>
                @endif
            @else
                <div class="flex justify-end gap-2">
                    <flux:button variant="subtle" wire:click="$set('showNoteModal', false)">
                        {{ __('Cancel') }}
                    </flux:button>
                    <flux:button variant="primary" wire:click="saveNote">
                        {{ __('Save Note') }}
                    </flux:button>
                </div>
            @endif
        </div>
    </flux:modal>

    {{-- View Log Modal --}}
    <flux:modal wire:model="showViewLogModal" variant="dialog" class="max-w-lg">
        @php
            $viewingLog = $viewingLogId ? $logs->firstWhere('id', $viewingLogId) : null;
        @endphp
        @if ($viewingLog)
            <div class="space-y-4">
                <flux:heading size="lg">{{ __('View Note') }}</flux:heading>

                <div>
                    <span class="mb-1 block text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Type') }}</span>
                    @php
                        $viewColor = match($viewingLog->type) {
                            'combat' => 'red',
                            'decision' => 'amber',
                            'narrative' => 'blue',
                            default => 'zinc',
                        };
                    @endphp
                    <flux:badge :color="$viewColor" size="sm">{{ ucfirst($viewingLog->type) }}</flux:badge>
                </div>

                @if (! empty($viewingLog->character_ids))
                    <div>
                        <span class="mb-1 block text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Characters involved') }}</span>
                        <div class="flex flex-wrap gap-1">
                            @foreach ($characters->whereIn('id', $viewingLog->character_ids) as $character)
                                <flux:badge color="blue" size="sm">{{ $character->name }}</flux:badge>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div>
                    <span class="mb-1 block text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Description') }}</span>
                    <p class="text-sm text-zinc-700 dark:text-zinc-300">{{ $viewingLog->entry }}</p>
                </div>

                <div class="flex justify-end">
                    <flux:button variant="subtle" wire:click="$set('showViewLogModal', false)">
                        {{ __('Close') }}
                    </flux:button>
                </div>
            </div>
        @endif
    </flux:modal>

    {{-- Edit Log Modal --}}
    <flux:modal wire:model="showEditLogModal" variant="dialog" class="max-w-lg">
        <div class="space-y-4">
            <flux:heading size="lg">{{ __('Edit Note') }}</flux:heading>

            <flux:select wire:model="editLogType" label="{{ __('Note type') }}">
                <flux:select.option value="combat">{{ __('Combat') }}</flux:select.option>
                <flux:select.option value="narrative">{{ __('Narrative') }}</flux:select.option>
                <flux:select.option value="decision">{{ __('Decision') }}</flux:select.option>
                <flux:select.option value="note">{{ __('Note') }}</flux:select.option>
            </flux:select>

            <div>
                <span class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Characters involved') }}</span>
                <div class="flex flex-wrap gap-2">
                    @foreach ($characters as $character)
                        <label class="flex cursor-pointer items-center gap-1.5 rounded-lg border border-zinc-200 px-2 py-1 text-sm dark:border-zinc-600
                            {{ in_array($character->id, $editLogCharacterIds) ? 'bg-blue-50 border-blue-300 dark:bg-blue-900/30 dark:border-blue-600' : '' }}">
                            <input type="checkbox" value="{{ $character->id }}" wire:model="editLogCharacterIds"
                                   class="rounded border-zinc-300 text-blue-600 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700" />
                            {{ $character->name }}
                        </label>
                    @endforeach
                </div>
            </div>

            <flux:textarea
                wire:model="editLogEntry"
                label="{{ __('Description') }}"
                rows="3"
                required
            />

            <div class="flex justify-end gap-2">
                <flux:button variant="subtle" wire:click="$set('showEditLogModal', false)">
                    {{ __('Cancel') }}
                </flux:button>
                <flux:button variant="primary" wire:click="saveEditLog">
                    {{ __('Save Changes') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
