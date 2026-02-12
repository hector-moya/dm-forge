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
                <flux:button variant="primary" size="sm" wire:click="openAddSceneForm" icon="plus">
                    {{ __('Add Scene') }}
                </flux:button>
            </div>

            @if ($scenes->isEmpty())
                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('No scenes yet. Add scenes to structure your session.') }}
                </flux:text>
            @else
                <div class="space-y-3">
                    @foreach ($scenes as $index => $scene)
                        <div class="space-y-2">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-semibold text-zinc-400 dark:text-zinc-500">{{ $index + 1 }}</span>
                                <livewire:sessions.scene-card :scene="$scene" :sessionId="$session->id" :key="'scene-'.$scene->id" />
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Unscoped encounters (not tied to a scene) --}}
        @if ($unscopedEncounters->isNotEmpty())
            <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                <div class="mb-4 flex items-center justify-between">
                    <flux:heading size="lg">{{ __('Standalone Encounters') }}</flux:heading>
                    <flux:button variant="primary" size="sm" wire:click="openAddEncounterForm" icon="plus">
                        {{ __('Add Encounter') }}
                    </flux:button>
                </div>
                <div class="space-y-2">
                    @foreach ($unscopedEncounters as $encounter)
                        <livewire:sessions.encounter-card :encounter="$encounter" :sceneId="null" :key="'encounter-'.$encounter->id" />
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Unscoped branches (not tied to a scene) --}}
        @if ($unscopedBranches->isNotEmpty())
            <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                <div class="mb-4 flex items-center justify-between">
                    <flux:heading size="lg">{{ __('Standalone Branch Options') }}</flux:heading>
                    <flux:button variant="primary" size="sm" wire:click="openAddBranchForm" icon="plus">
                        {{ __('Add Branch') }}
                    </flux:button>
                </div>
                <div class="space-y-2">
                    @foreach ($unscopedBranches as $branch)
                        <livewire:sessions.branch-card :branch="$branch" :sceneId="null" :key="'branch-'.$branch->id" />
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Add Scene Modal --}}
        <flux:modal wire:model="showAddSceneForm" class="md:w-xl">
            <flux:heading size="lg">{{ __('Add Scene') }}</flux:heading>

            <div class="flex flex-col gap-4 mt-4">
                <flux:input
                    wire:model="newSceneTitle"
                    label="{{ __('Title') }}"
                    placeholder="{{ __('Scene title...') }}"
                    required
                />
                <flux:textarea
                    wire:model="newSceneDescription"
                    label="{{ __('Description') }}"
                    placeholder="{{ __('What happens in this scene?') }}"
                    rows="3"
                />
                <flux:textarea
                    wire:model="newSceneNotes"
                    label="{{ __('DM Notes') }}"
                    placeholder="{{ __('Private notes for this scene...') }}"
                    rows="2"
                />
            </div>

            <div class="flex justify-end gap-3">
                <flux:button variant="subtle" wire:click="$set('showAddSceneForm', false)">{{ __('Cancel') }}</flux:button>
                <flux:button variant="primary" wire:click="saveNewScene">{{ __('Add Scene') }}</flux:button>
            </div>
        </flux:modal>

        {{-- Add Encounter Modal --}}
        <flux:modal wire:model="showAddEncounterForm" class="md:w-xl">
            <flux:heading size="lg">{{ __('Add Encounter') }}</flux:heading>

            <div class="flex flex-col gap-4 mt-4">
                <flux:input
                    wire:model="newEncounterName"
                    label="{{ __('Name') }}"
                    placeholder="{{ __('Encounter name...') }}"
                    required
                />
                <flux:textarea
                    wire:model="newEncounterDescription"
                    label="{{ __('Description') }}"
                    placeholder="{{ __('Describe the encounter setup...') }}"
                    rows="3"
                />
                <flux:input
                    wire:model="newEncounterEnvironment"
                    label="{{ __('Environment') }}"
                    placeholder="{{ __('e.g., Dark cave, Open field, Castle dungeon...') }}"
                />
            </div>

            <div class="flex justify-end gap-3">
                <flux:button variant="subtle" wire:click="$set('showAddEncounterForm', false)">{{ __('Cancel') }}</flux:button>
                <flux:button variant="primary" wire:click="saveNewEncounter">{{ __('Add Encounter') }}</flux:button>
            </div>
        </flux:modal>

        {{-- Add Branch Modal --}}
        <flux:modal wire:model="showAddBranchForm" class="md:w-xl">
            <flux:heading size="lg">{{ __('Add Branch Option') }}</flux:heading>

            <div class="flex flex-col gap-4 mt-4">
                <flux:input
                    wire:model="newBranchLabel"
                    label="{{ __('Label') }}"
                    placeholder="{{ __('e.g., Fight the dragon, Negotiate peace, Flee...') }}"
                    required
                />
                <flux:textarea
                    wire:model="newBranchDescription"
                    label="{{ __('Description') }}"
                    placeholder="{{ __('What happens if the party chooses this option?') }}"
                    rows="3"
                />
            </div>

            <div class="flex justify-end gap-3">
                <flux:button variant="subtle" wire:click="$set('showAddBranchForm', false)">{{ __('Cancel') }}</flux:button>
                <flux:button variant="primary" wire:click="saveNewBranch">{{ __('Add Branch') }}</flux:button>
            </div>
        </flux:modal>
    @endif
</div>
