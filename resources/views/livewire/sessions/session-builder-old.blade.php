<div class="mx-auto flex w-full max-w-4xl flex-col gap-6">
    {{-- Header --}}
    <div class="flex items-center gap-4">
        <flux:button variant="subtle" href="{{ route('campaigns.sessions', $campaign) }}" wire:navigate icon="arrow-left" size="sm">
            {{ __('Sessions') }}
        </flux:button>
        <flux:heading size="xl">{{ $session ? __('Edit Session') : __('New Session') }}</flux:heading>
    </div>

{{-- Session Metadata Form --}}
<form wire:submit="saveSession" class="flex flex-col gap-6">
    <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
        <flux:heading size="lg" class="mb-4">{{ __('Session Details') }}</flux:heading>

        <div class="flex flex-col gap-4">
            <div class="grid gap-4 sm:grid-cols-2">
                <flux:input
                    wire:model="title"
                    label="{{ __('Title') }}"
                    placeholder="{{ __('Session title...') }}"
                    required
                />
                <flux:input
                    wire:model="session_number"
                    type="number"
                    label="{{ __('Session Number') }}"
                    min="1"
                    required
                />
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <flux:select wire:model="type" label="{{ __('Type') }}">
                    <flux:select.option value="sequential">{{ __('Sequential') }}</flux:select.option>
                    <flux:select.option value="one_shot">{{ __('One Shot') }}</flux:select.option>
                </flux:select>

                <flux:select wire:model="status" label="{{ __('Status') }}">
                    <flux:select.option value="draft">{{ __('Draft') }}</flux:select.option>
                    <flux:select.option value="prepared">{{ __('Prepared') }}</flux:select.option>
                    <flux:select.option value="running">{{ __('Running') }}</flux:select.option>
                    <flux:select.option value="completed">{{ __('Completed') }}</flux:select.option>
                </flux:select>
            </div>

            <flux:textarea
                wire:model="setup_text"
                label="{{ __('Setup Text') }}"
                placeholder="{{ __('Read-aloud text or scene-setting description for players...') }}"
                rows="4"
            />

            <flux:textarea
                wire:model="dm_notes"
                label="{{ __('DM Notes') }}"
                placeholder="{{ __('Private notes, reminders, and prep details...') }}"
                rows="4"
            />
        </div>
    </div>

    {{-- Save / Delete buttons --}}
    <div class="flex items-center justify-between">
        @if ($session)
            <flux:button variant="danger" type="button" wire:click="deleteSession" wire:confirm="{{ __('Are you sure you want to delete this session? All scenes, encounters, and branches will be lost.') }}" icon="trash">
                {{ __('Delete Session') }}
            </flux:button>
        @else
            <div></div>
        @endif
        <div class="flex items-center gap-3">
            <flux:button variant="subtle" href="{{ route('campaigns.sessions', $campaign) }}" wire:navigate>
                {{ __('Cancel') }}
            </flux:button>
            <flux:button type="submit" variant="primary">
                {{ $session ? __('Save Changes') : __('Create Session') }}
            </flux:button>
        </div>
    </div>
</form>

{{-- Everything below only shows after session is created --}}
@if ($session)
    {{-- ── Scenes Section ─────────────────────────────────────────── --}}
    <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
        <div class="mb-4 flex items-center justify-between">
            <flux:heading size="lg">{{ __('Scenes') }}</flux:heading>
            <flux:button variant="primary" size="sm" wire:click="openSceneForm" icon="plus">
                {{ __('Add Scene') }}
            </flux:button>
        </div>

        @if ($showSceneForm)
            <div class="mb-4 rounded-lg border border-zinc-300 bg-zinc-50 p-4 dark:border-zinc-600 dark:bg-zinc-700/50">
                <flux:heading size="base" class="mb-3">
                    {{ $editingSceneId ? __('Edit Scene') : __('New Scene') }}
                </flux:heading>
                <div class="flex flex-col gap-3">
                    <flux:input
                        wire:model="sceneTitle"
                        label="{{ __('Title') }}"
                        placeholder="{{ __('Scene title...') }}"
                        required
                    />
                    <flux:textarea
                        wire:model="sceneDescription"
                        label="{{ __('Description') }}"
                        placeholder="{{ __('What happens in this scene?') }}"
                        rows="3"
                    />
                    <flux:textarea
                        wire:model="sceneNotes"
                        label="{{ __('DM Notes') }}"
                        placeholder="{{ __('Private notes for this scene...') }}"
                        rows="2"
                    />
                    <div class="flex items-center justify-end gap-2">
                        <flux:button variant="subtle" size="sm" wire:click="$set('showSceneForm', false)">
                            {{ __('Cancel') }}
                        </flux:button>
                        <flux:button variant="primary" size="sm" wire:click="saveScene">
                            {{ $editingSceneId ? __('Update Scene') : __('Add Scene') }}
                        </flux:button>
                    </div>
                </div>
            </div>
        @endif

        @if ($scenes->isEmpty())
            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                {{ __('No scenes yet. Add scenes to structure your session.') }}
            </flux:text>
        @else
            <div class="space-y-3">
                @foreach ($scenes as $scene)
                    <div class="rounded-lg border border-zinc-200 bg-zinc-50 px-4 py-3 dark:border-zinc-600 dark:bg-zinc-700/50">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <span class="text-sm font-semibold text-zinc-400 dark:text-zinc-500">{{ $loop->iteration }}</span>
                                <span class="font-medium text-zinc-700 dark:text-zinc-200">{{ $scene->title }}</span>
                            </div>
                            <div class="flex items-center gap-1">
                                <flux:button variant="subtle" size="sm" wire:click="openEncounterForm({{ $scene->id }})" icon="bolt" title="{{ __('Add Encounter') }}" />
                                <flux:button variant="subtle" size="sm" wire:click="openBranchForm({{ $scene->id }})" icon="arrows-right-left" title="{{ __('Add Branch') }}" />
                                <flux:button variant="subtle" size="sm" wire:click="openSceneForm({{ $scene->id }})" icon="pencil" />
                                <flux:button variant="subtle" size="sm" wire:click="deleteScene({{ $scene->id }})" wire:confirm="{{ __('Delete this scene?') }}" icon="trash" />
                            </div>
                        </div>
                        @if ($scene->description)
                            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ Str::limit($scene->description, 120) }}</p>
                        @endif

                        {{-- Encounters for this scene --}}
                        @php $sceneEncounters = $encounters->where('scene_id', $scene->id); @endphp
                        @if ($sceneEncounters->isNotEmpty())
                            <div class="mt-3 space-y-2 border-t border-zinc-200 pt-3 dark:border-zinc-600">
                                <span class="text-xs font-semibold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">{{ __('Encounters') }}</span>
                                @foreach ($sceneEncounters as $encounter)
                                    <div class="rounded-md bg-white px-3 py-2 dark:bg-zinc-800">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-2">
                                                <flux:icon name="bolt" class="size-4 text-amber-500" />
                                                <span class="text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ $encounter->name }}</span>
                                                @php
                                                    $diffVariant = match($encounter->difficulty) {
                                                        'easy' => 'outline',
                                                        'medium' => 'warning',
                                                        'hard' => 'danger',
                                                        'deadly' => 'danger',
                                                        default => 'outline',
                                                    };
                                                @endphp
                                                <flux:badge size="sm" :variant="$diffVariant">{{ ucfirst($encounter->difficulty) }}</flux:badge>
                                            </div>
                                            <div class="flex items-center gap-1">
                                                <flux:button variant="subtle" size="sm" wire:click="openMonsterForm({{ $encounter->id }})" icon="plus" title="{{ __('Add Monster') }}" />
                                                <flux:button variant="subtle" size="sm" wire:click="openEncounterForm({{ $scene->id }}, {{ $encounter->id }})" icon="pencil" />
                                                <flux:button variant="subtle" size="sm" wire:click="deleteEncounter({{ $encounter->id }})" wire:confirm="{{ __('Delete this encounter?') }}" icon="trash" />
                                            </div>
                                        </div>
                                        @if ($encounter->environment)
                                            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">{{ $encounter->environment }}</p>
                                        @endif

                                        {{-- Monsters --}}
                                        @if ($encounter->monsters->isNotEmpty())
                                            <div class="mt-2 flex flex-wrap gap-2">
                                                @foreach ($encounter->monsters as $monster)
                                                    <div class="flex items-center gap-1 rounded-md bg-zinc-100 px-2 py-1 text-xs dark:bg-zinc-700">
                                                        <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $monster->name }}</span>
                                                        <span class="text-zinc-400 dark:text-zinc-500">HP {{ $monster->hp_max }} AC {{ $monster->armor_class }}</span>
                                                        <button type="button" wire:click="deleteMonster({{ $monster->id }})" wire:confirm="{{ __('Remove this monster?') }}" class="ml-1 text-zinc-400 hover:text-red-500 dark:text-zinc-500">
                                                            &times;
                                                        </button>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        {{-- Branches for this scene --}}
                        @php $sceneBranches = $branches->where('scene_id', $scene->id); @endphp
                        @if ($sceneBranches->isNotEmpty())
                            <div class="mt-3 space-y-2 border-t border-zinc-200 pt-3 dark:border-zinc-600">
                                <span class="text-xs font-semibold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">{{ __('Branch Options') }}</span>
                                @foreach ($sceneBranches as $branch)
                                    <div class="rounded-md bg-white px-3 py-2 dark:bg-zinc-800">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-2">
                                                <flux:icon name="arrows-right-left" class="size-4 text-indigo-500" />
                                                <span class="text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ $branch->label }}</span>
                                            </div>
                                            <div class="flex items-center gap-1">
                                                <flux:button variant="subtle" size="sm" wire:click="openConsequenceForm({{ $branch->id }})" icon="plus" title="{{ __('Add Consequence') }}" />
                                                <flux:button variant="subtle" size="sm" wire:click="openBranchForm({{ $scene->id }}, {{ $branch->id }})" icon="pencil" />
                                                <flux:button variant="subtle" size="sm" wire:click="deleteBranch({{ $branch->id }})" wire:confirm="{{ __('Delete this branch option?') }}" icon="trash" />
                                            </div>
                                        </div>
                                        @if ($branch->description)
                                            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">{{ Str::limit($branch->description, 100) }}</p>
                                        @endif

                                        {{-- Consequences --}}
                                        @if ($branch->consequences->isNotEmpty())
                                            <div class="mt-2 space-y-1">
                                                @foreach ($branch->consequences as $consequence)
                                                    <div class="flex items-center justify-between rounded bg-zinc-100 px-2 py-1 text-xs dark:bg-zinc-700">
                                                        <div class="flex items-center gap-2">
                                                            @php
                                                                $typeVariant = match($consequence->type) {
                                                                    'immediate' => 'warning',
                                                                    'delayed' => 'outline',
                                                                    'meta' => 'primary',
                                                                    default => 'outline',
                                                                };
                                                            @endphp
                                                            <flux:badge size="sm" :variant="$typeVariant">{{ ucfirst($consequence->type) }}</flux:badge>
                                                            <span class="text-zinc-600 dark:text-zinc-400">{{ Str::limit($consequence->description, 80) }}</span>
                                                        </div>
                                                        <button type="button" wire:click="deleteConsequence({{ $consequence->id }})" wire:confirm="{{ __('Remove this consequence?') }}" class="text-zinc-400 hover:text-red-500 dark:text-zinc-500">
                                                            &times;
                                                        </button>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Unscoped encounters (not tied to a scene) --}}
    @php $unscopedEncounters = $encounters->whereNull('scene_id'); @endphp
    @if ($unscopedEncounters->isNotEmpty())
        <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
            <div class="mb-4 flex items-center justify-between">
                <flux:heading size="lg">{{ __('Standalone Encounters') }}</flux:heading>
                <flux:button variant="primary" size="sm" wire:click="openEncounterForm" icon="plus">
                    {{ __('Add Encounter') }}
                </flux:button>
            </div>
            <div class="space-y-2">
                @foreach ($unscopedEncounters as $encounter)
                    <div class="rounded-md bg-zinc-50 px-3 py-2 dark:bg-zinc-700/50">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <flux:icon name="bolt" class="size-4 text-amber-500" />
                                <span class="text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ $encounter->name }}</span>
                                @php
                                    $diffVariant = match($encounter->difficulty) {
                                        'easy' => 'outline',
                                        'medium' => 'warning',
                                        'hard' => 'danger',
                                        'deadly' => 'danger',
                                        default => 'outline',
                                    };
                                @endphp
                                <flux:badge size="sm" :variant="$diffVariant">{{ ucfirst($encounter->difficulty) }}</flux:badge>
                            </div>
                            <div class="flex items-center gap-1">
                                <flux:button variant="subtle" size="sm" wire:click="openMonsterForm({{ $encounter->id }})" icon="plus" title="{{ __('Add Monster') }}" />
                                <flux:button variant="subtle" size="sm" wire:click="openEncounterForm(null, {{ $encounter->id }})" icon="pencil" />
                                <flux:button variant="subtle" size="sm" wire:click="deleteEncounter({{ $encounter->id }})" wire:confirm="{{ __('Delete this encounter?') }}" icon="trash" />
                            </div>
                        </div>
                        @if ($encounter->monsters->isNotEmpty())
                            <div class="mt-2 flex flex-wrap gap-2">
                                @foreach ($encounter->monsters as $monster)
                                    <div class="flex items-center gap-1 rounded-md bg-zinc-100 px-2 py-1 text-xs dark:bg-zinc-700">
                                        <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $monster->name }}</span>
                                        <span class="text-zinc-400 dark:text-zinc-500">HP {{ $monster->hp_max }} AC {{ $monster->armor_class }}</span>
                                        <button type="button" wire:click="deleteMonster({{ $monster->id }})" wire:confirm="{{ __('Remove this monster?') }}" class="ml-1 text-zinc-400 hover:text-red-500 dark:text-zinc-500">
                                            &times;
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Unscoped branches (not tied to a scene) --}}
    @php $unscopedBranches = $branches->whereNull('scene_id'); @endphp
    @if ($unscopedBranches->isNotEmpty())
        <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
            <div class="mb-4 flex items-center justify-between">
                <flux:heading size="lg">{{ __('Standalone Branch Options') }}</flux:heading>
                <flux:button variant="primary" size="sm" wire:click="openBranchForm" icon="plus">
                    {{ __('Add Branch') }}
                </flux:button>
            </div>
            <div class="space-y-2">
                @foreach ($unscopedBranches as $branch)
                    <div class="rounded-md bg-zinc-50 px-3 py-2 dark:bg-zinc-700/50">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <flux:icon name="arrows-right-left" class="size-4 text-indigo-500" />
                                <span class="text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ $branch->label }}</span>
                            </div>
                            <div class="flex items-center gap-1">
                                <flux:button variant="subtle" size="sm" wire:click="openConsequenceForm({{ $branch->id }})" icon="plus" title="{{ __('Add Consequence') }}" />
                                <flux:button variant="subtle" size="sm" wire:click="openBranchForm(null, {{ $branch->id }})" icon="pencil" />
                                <flux:button variant="subtle" size="sm" wire:click="deleteBranch({{ $branch->id }})" wire:confirm="{{ __('Delete this branch option?') }}" icon="trash" />
                            </div>
                        </div>
                        @if ($branch->consequences->isNotEmpty())
                            <div class="mt-2 space-y-1">
                                @foreach ($branch->consequences as $consequence)
                                    <div class="flex items-center justify-between rounded bg-zinc-100 px-2 py-1 text-xs dark:bg-zinc-700">
                                        <div class="flex items-center gap-2">
                                            @php
                                                $typeVariant = match($consequence->type) {
                                                    'immediate' => 'warning',
                                                    'delayed' => 'outline',
                                                    'meta' => 'primary',
                                                    default => 'outline',
                                                };
                                            @endphp
                                            <flux:badge size="sm" :variant="$typeVariant">{{ ucfirst($consequence->type) }}</flux:badge>
                                            <span class="text-zinc-600 dark:text-zinc-400">{{ Str::limit($consequence->description, 80) }}</span>
                                        </div>
                                        <button type="button" wire:click="deleteConsequence({{ $consequence->id }})" wire:confirm="{{ __('Remove this consequence?') }}" class="text-zinc-400 hover:text-red-500 dark:text-zinc-500">
                                            &times;
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ── Encounter Inline Form ──────────────────────────────────── --}}
    @if ($showEncounterForm)
        <div class="rounded-xl border border-amber-300 bg-amber-50 p-6 dark:border-amber-700 dark:bg-amber-900/20">
            <flux:heading size="lg" class="mb-4">
                {{ $editingEncounterId ? __('Edit Encounter') : __('New Encounter') }}
            </flux:heading>
            <div class="flex flex-col gap-3">
                <flux:input
                    wire:model="encounterName"
                    label="{{ __('Name') }}"
                    placeholder="{{ __('Encounter name...') }}"
                    required
                />
                <flux:textarea
                    wire:model="encounterDescription"
                    label="{{ __('Description') }}"
                    placeholder="{{ __('Describe the encounter setup...') }}"
                    rows="3"
                />
                <div class="grid gap-3 sm:grid-cols-2">
                    <flux:input
                        wire:model="encounterEnvironment"
                        label="{{ __('Environment') }}"
                        placeholder="{{ __('e.g., Dark cave, Open field, Castle dungeon...') }}"
                    />
                    <flux:select wire:model="encounterDifficulty" label="{{ __('Difficulty') }}">
                        <flux:select.option value="easy">{{ __('Easy') }}</flux:select.option>
                        <flux:select.option value="medium">{{ __('Medium') }}</flux:select.option>
                        <flux:select.option value="hard">{{ __('Hard') }}</flux:select.option>
                        <flux:select.option value="deadly">{{ __('Deadly') }}</flux:select.option>
                    </flux:select>
                </div>
                <div class="flex items-center justify-end gap-2">
                    <flux:button variant="subtle" size="sm" wire:click="$set('showEncounterForm', false)">
                        {{ __('Cancel') }}
                    </flux:button>
                    <flux:button variant="primary" size="sm" wire:click="saveEncounter">
                        {{ $editingEncounterId ? __('Update Encounter') : __('Add Encounter') }}
                    </flux:button>
                </div>
            </div>
        </div>
    @endif

    {{-- ── Monster Inline Form ────────────────────────────────────── --}}
    @if ($showMonsterForm)
        <div class="rounded-xl border border-red-300 bg-red-50 p-6 dark:border-red-700 dark:bg-red-900/20">
            <flux:heading size="lg" class="mb-4">{{ __('Add Monsters') }}</flux:heading>
            <div class="flex flex-col gap-3">
                <div class="grid gap-3 sm:grid-cols-4">
                    <flux:input
                        wire:model="monsterName"
                        label="{{ __('Name') }}"
                        placeholder="{{ __('e.g., Goblin') }}"
                        required
                    />
                    <flux:input
                        wire:model="monsterHpMax"
                        type="number"
                        label="{{ __('HP') }}"
                        min="1"
                        required
                    />
                    <flux:input
                        wire:model="monsterAc"
                        type="number"
                        label="{{ __('AC') }}"
                        min="1"
                        required
                    />
                    <flux:input
                        wire:model="monsterCount"
                        type="number"
                        label="{{ __('Count') }}"
                        min="1"
                        max="20"
                        required
                    />
                </div>
                <div class="flex items-center justify-end gap-2">
                    <flux:button variant="subtle" size="sm" wire:click="$set('showMonsterForm', false)">
                        {{ __('Cancel') }}
                    </flux:button>
                    <flux:button variant="primary" size="sm" wire:click="saveMonster">
                        {{ __('Add Monsters') }}
                    </flux:button>
                </div>
            </div>
        </div>
    @endif

    {{-- ── Branch Inline Form ─────────────────────────────────────── --}}
    @if ($showBranchForm)
        <div class="rounded-xl border border-indigo-300 bg-indigo-50 p-6 dark:border-indigo-700 dark:bg-indigo-900/20">
            <flux:heading size="lg" class="mb-4">
                {{ $editingBranchId ? __('Edit Branch Option') : __('New Branch Option') }}
            </flux:heading>
            <div class="flex flex-col gap-3">
                <flux:input
                    wire:model="branchLabel"
                    label="{{ __('Label') }}"
                    placeholder="{{ __('e.g., Fight the dragon, Negotiate peace, Flee...') }}"
                    required
                />
                <flux:textarea
                    wire:model="branchDescription"
                    label="{{ __('Description') }}"
                    placeholder="{{ __('What happens if the party chooses this option?') }}"
                    rows="3"
                />
                <div class="flex items-center justify-end gap-2">
                    <flux:button variant="subtle" size="sm" wire:click="$set('showBranchForm', false)">
                        {{ __('Cancel') }}
                    </flux:button>
                    <flux:button variant="primary" size="sm" wire:click="saveBranch">
                        {{ $editingBranchId ? __('Update Branch') : __('Add Branch') }}
                    </flux:button>
                </div>
            </div>
        </div>
    @endif

    {{-- ── Consequence Inline Form ────────────────────────────────── --}}
    @if ($showConsequenceForm)
        <div class="rounded-xl border border-purple-300 bg-purple-50 p-6 dark:border-purple-700 dark:bg-purple-900/20">
            <flux:heading size="lg" class="mb-4">{{ __('Add Consequence') }}</flux:heading>
            <div class="flex flex-col gap-3">
                <flux:select wire:model="consequenceType" label="{{ __('Type') }}">
                    <flux:select.option value="immediate">{{ __('Immediate') }}</flux:select.option>
                    <flux:select.option value="delayed">{{ __('Delayed') }}</flux:select.option>
                    <flux:select.option value="meta">{{ __('Meta') }}</flux:select.option>
                </flux:select>
                <flux:textarea
                    wire:model="consequenceDescription"
                    label="{{ __('Description') }}"
                    placeholder="{{ __('What is the consequence of this choice?') }}"
                    rows="3"
                    required
                />
                <div class="flex items-center justify-end gap-2">
                    <flux:button variant="subtle" size="sm" wire:click="$set('showConsequenceForm', false)">
                        {{ __('Cancel') }}
                    </flux:button>
                    <flux:button variant="primary" size="sm" wire:click="saveConsequence">
                        {{ __('Add Consequence') }}
                    </flux:button>
                </div>
            </div>
        </div>
    @endif
@endif
</div>
