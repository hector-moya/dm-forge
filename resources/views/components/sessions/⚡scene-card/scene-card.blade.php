<div class="rounded-lg border border-zinc-200 bg-zinc-50 px-4 py-3 dark:border-zinc-600 dark:bg-zinc-700/50">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <span class="font-medium text-zinc-700 dark:text-zinc-200">{{ $scene->title }}</span>
        </div>
        <div class="flex items-center gap-1">
            <flux:modal.trigger name="add-puzzle-{{ $scene->id }}">
                <flux:button variant="subtle" size="sm" icon="puzzle-piece" title="{{ __('Add Puzzle') }}" />
            </flux:modal.trigger>
            <flux:modal.trigger name="add-branch-{{ $scene->id }}">
                <flux:button variant="subtle" size="sm" icon="plus" title="{{ __('Add Branch') }}" />
            </flux:modal.trigger>
            <flux:modal.trigger name="add-encounter-{{ $scene->id }}">
                <flux:button variant="subtle" size="sm" icon="plus" title="{{ __('Add Encounter') }}" />
            </flux:modal.trigger>
            <flux:modal.trigger name="edit-scene-{{ $scene->id }}">
                <flux:button variant="subtle" size="sm" wire:click="openForm({{ $scene->id }})" icon="pencil" title="{{ __('Edit Scene') }}" />
            </flux:modal.trigger>
            <flux:button variant="subtle" size="sm" wire:click="delete({{ $scene->id }})" wire:confirm="{{ __('Delete this scene?') }}" icon="trash" title="{{ __('Delete Scene') }}" />
        </div>
    </div>
    @if ($scene->description)
        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ Str::limit($scene->description, 120) }}</p>
    @endif

    {{-- Nested Encounters --}}
    @if ($scene->encounters->isNotEmpty())
        <div class="mt-3 space-y-2 border-t border-zinc-200 pt-3 dark:border-zinc-600">
            <span class="text-xs font-semibold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">{{ __('Encounters') }}</span>
            @foreach ($scene->encounters as $encounter)
                <livewire:sessions.encounter-card :encounter="$encounter" :sceneId="$scene->id" :key="'encounter-' . $encounter->id" />
            @endforeach
        </div>
    @endif

    {{-- Nested Branches --}}
    @if ($scene->branchOptions->isNotEmpty())
        <div class="mt-3 space-y-2 border-t border-zinc-200 pt-3 dark:border-zinc-600">
            <span class="text-xs font-semibold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">{{ __('Branch Options') }}</span>
            @foreach ($scene->branchOptions as $branch)
                <livewire:sessions.branch-card :branch="$branch" :sceneId="$scene->id" :key="'branch-' . $branch->id" />
            @endforeach
        </div>
    @endif

    {{-- Nested Puzzles --}}
    @if ($scene->puzzles->isNotEmpty())
        <div class="mt-3 space-y-2 border-t border-zinc-200 pt-3 dark:border-zinc-600">
            <span class="text-xs font-semibold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">{{ __('Puzzles') }}</span>
            @foreach ($scene->puzzles as $puzzle)
                <div class="flex items-center justify-between rounded-md border border-violet-200 bg-violet-50 px-3 py-2 dark:border-violet-700 dark:bg-violet-900/30" wire:key="puzzle-{{ $puzzle->id }}">
                    <div class="flex items-center gap-2">
                        <flux:icon.puzzle-piece class="size-4 text-violet-500" />
                        <span class="text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ $puzzle->name }}</span>
                        <flux:badge size="sm" variant="pill" color="{{ match($puzzle->difficulty) { 'easy' => 'green', 'medium' => 'amber', 'hard' => 'red' } }}">{{ ucfirst($puzzle->difficulty) }}</flux:badge>
                        <flux:badge size="sm" variant="pill" color="violet">{{ ucfirst($puzzle->puzzle_type) }}</flux:badge>
                        @if ($puzzle->is_solved)
                            <flux:badge size="sm" variant="pill" color="emerald">{{ __('Solved') }}</flux:badge>
                        @endif
                    </div>
                    <div class="flex items-center gap-1">
                        <flux:button variant="subtle" size="sm" wire:click="togglePuzzleSolved({{ $puzzle->id }})" icon="{{ $puzzle->is_solved ? 'x-mark' : 'check' }}" title="{{ $puzzle->is_solved ? __('Mark Unsolved') : __('Mark Solved') }}" />
                        <flux:modal.trigger name="edit-puzzle-{{ $puzzle->id }}">
                            <flux:button variant="subtle" size="sm" wire:click="openPuzzleForm({{ $puzzle->id }})" icon="pencil" title="{{ __('Edit Puzzle') }}" />
                        </flux:modal.trigger>
                        <flux:button variant="subtle" size="sm" wire:click="deletePuzzle({{ $puzzle->id }})" wire:confirm="{{ __('Delete this puzzle?') }}" icon="trash" title="{{ __('Delete Puzzle') }}" />
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Edit Scene Modal --}}
    <flux:modal name="edit-scene-{{ $scene->id }}" class="md:w-xl">
        <flux:heading size="lg">{{ $editingSceneId ? __('Edit Scene') : __('New Scene') }}</flux:heading>

        <div class="mt-4 flex flex-col gap-4">
            <flux:input wire:model="title" label="{{ __('Title') }}" placeholder="{{ __('Scene title...') }}" required />
            <flux:textarea wire:model="description" label="{{ __('Description') }}" placeholder="{{ __('What happens in this scene?') }}" rows="3" />
            <flux:textarea wire:model="notes" label="{{ __('DM Notes') }}" placeholder="{{ __('Private notes for this scene...') }}" rows="2" />
        </div>

        <div class="flex justify-end gap-3">
            <flux:button variant="subtle" wire:click="$set('showForm', false)">{{ __('Cancel') }}</flux:button>
            <flux:button variant="primary" wire:click="save">{{ $editingSceneId ? __('Update Scene') : __('Add Scene') }}</flux:button>
        </div>
    </flux:modal>

    {{-- Add Encounter Modal --}}
    <flux:modal name="add-encounter-{{ $scene->id }}" class="md:w-xl">
        <flux:heading size="lg">{{ __('Add Encounter') }}</flux:heading>

        <div class="mt-4 flex flex-col gap-4">
            <flux:input wire:model="newEncounterName" label="{{ __('Name') }}" placeholder="{{ __('Encounter name...') }}" required />
            <flux:textarea wire:model="newEncounterDescription" label="{{ __('Description') }}" placeholder="{{ __('Describe the encounter setup...') }}" rows="3" />
            <flux:input wire:model="newEncounterEnvironment" label="{{ __('Environment') }}" placeholder="{{ __('e.g., Dark cave, Open field, Castle dungeon...') }}" />
        </div>

        <div class="flex justify-end gap-3">
            <flux:button variant="subtle" wire:click="$set('showAddEncounterForm', false)">{{ __('Cancel') }}</flux:button>
            <flux:button variant="primary" wire:click="saveNewEncounter">{{ __('Add Encounter') }}</flux:button>
        </div>
    </flux:modal>

    {{-- Add Branch Modal --}}
    <flux:modal name="add-branch-{{ $scene->id }}" class="md:w-xl">
        <flux:heading size="lg">{{ __('Add Branch Option') }}</flux:heading>

        <div class="mt-4 flex flex-col gap-4">
            <flux:input wire:model="newBranchLabel" label="{{ __('Label') }}" placeholder="{{ __('e.g., Fight the dragon, Negotiate peace, Flee...') }}" required />
            <flux:textarea wire:model="newBranchDescription" label="{{ __('Description') }}" placeholder="{{ __('What happens if the party chooses this option?') }}" rows="3" />
        </div>

        <div class="flex justify-end gap-3">
            <flux:button variant="subtle" wire:click="$set('showAddBranchForm', false)">{{ __('Cancel') }}</flux:button>
            <flux:button variant="primary" wire:click="saveNewBranch">{{ __('Add Branch') }}</flux:button>
        </div>
    </flux:modal>

    {{-- Add / Edit Puzzle Modal --}}
    <flux:modal name="add-puzzle-{{ $scene->id }}" class="md:w-2xl">
        <flux:heading size="lg">{{ $editingPuzzleId ? __('Edit Puzzle') : __('Add Puzzle') }}</flux:heading>

        <div class="mt-4 flex flex-col gap-4">
            <div class="flex items-center justify-between">
                <div></div>
                <flux:modal.trigger name="generate-puzzle-{{ $scene->id }}">
                    <flux:button variant="subtle" size="sm" icon="sparkles" wire:click="openGeneratePuzzleModal">
                        {{ __('Generate with AI') }}
                    </flux:button>
                </flux:modal.trigger>
            </div>

            <flux:input wire:model="puzzleName" label="{{ __('Name') }}" placeholder="{{ __('Puzzle name...') }}" required />

            <div class="grid gap-4 sm:grid-cols-2">
                <flux:select wire:model="puzzleDifficulty" label="{{ __('Difficulty') }}">
                    <flux:select.option value="easy">{{ __('Easy') }}</flux:select.option>
                    <flux:select.option value="medium">{{ __('Medium') }}</flux:select.option>
                    <flux:select.option value="hard">{{ __('Hard') }}</flux:select.option>
                </flux:select>

                <flux:select wire:model="puzzleType" label="{{ __('Type') }}">
                    <flux:select.option value="riddle">{{ __('Riddle') }}</flux:select.option>
                    <flux:select.option value="logic">{{ __('Logic') }}</flux:select.option>
                    <flux:select.option value="physical">{{ __('Physical') }}</flux:select.option>
                    <flux:select.option value="cipher">{{ __('Cipher') }}</flux:select.option>
                    <flux:select.option value="pattern">{{ __('Pattern') }}</flux:select.option>
                </flux:select>
            </div>

            <flux:textarea wire:model="puzzleDescription" label="{{ __('Description') }}" placeholder="{{ __('The puzzle as presented to players...') }}" rows="4" required />

            <flux:textarea wire:model="puzzleSolution" label="{{ __('Solution') }}" placeholder="{{ __('The answer and how it works...') }}" rows="3" required />

            <flux:separator />
            <flux:heading size="sm">{{ __('Progressive Hints') }}</flux:heading>

            <flux:textarea wire:model="puzzleHint1" label="{{ __('Hint Tier 1 — Subtle Nudge') }}" placeholder="{{ __('A gentle hint...') }}" rows="2" />
            <flux:textarea wire:model="puzzleHint2" label="{{ __('Hint Tier 2 — Clearer Clue') }}" placeholder="{{ __('A more direct hint...') }}" rows="2" />
            <flux:textarea wire:model="puzzleHint3" label="{{ __('Hint Tier 3 — Strong Hint') }}" placeholder="{{ __('Nearly gives it away...') }}" rows="2" />

            <flux:textarea wire:model="puzzleNotes" label="{{ __('DM Notes') }}" placeholder="{{ __('Private notes about this puzzle...') }}" rows="2" />
        </div>

        <div class="flex justify-end gap-3">
            <flux:button variant="subtle" wire:click="$set('showPuzzleForm', false)">{{ __('Cancel') }}</flux:button>
            <flux:button variant="primary" wire:click="savePuzzle">{{ $editingPuzzleId ? __('Update Puzzle') : __('Add Puzzle') }}</flux:button>
        </div>
    </flux:modal>

    {{-- Edit Puzzle Modal (for existing puzzles) --}}
    @foreach ($scene->puzzles as $puzzle)
        <flux:modal name="edit-puzzle-{{ $puzzle->id }}" class="md:w-2xl">
            <flux:heading size="lg">{{ __('Edit Puzzle') }}</flux:heading>

            <div class="mt-4 flex flex-col gap-4">
                <flux:input wire:model="puzzleName" label="{{ __('Name') }}" placeholder="{{ __('Puzzle name...') }}" required />

                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:select wire:model="puzzleDifficulty" label="{{ __('Difficulty') }}">
                        <flux:select.option value="easy">{{ __('Easy') }}</flux:select.option>
                        <flux:select.option value="medium">{{ __('Medium') }}</flux:select.option>
                        <flux:select.option value="hard">{{ __('Hard') }}</flux:select.option>
                    </flux:select>

                    <flux:select wire:model="puzzleType" label="{{ __('Type') }}">
                        <flux:select.option value="riddle">{{ __('Riddle') }}</flux:select.option>
                        <flux:select.option value="logic">{{ __('Logic') }}</flux:select.option>
                        <flux:select.option value="physical">{{ __('Physical') }}</flux:select.option>
                        <flux:select.option value="cipher">{{ __('Cipher') }}</flux:select.option>
                        <flux:select.option value="pattern">{{ __('Pattern') }}</flux:select.option>
                    </flux:select>
                </div>

                <flux:textarea wire:model="puzzleDescription" label="{{ __('Description') }}" placeholder="{{ __('The puzzle as presented to players...') }}" rows="4" required />
                <flux:textarea wire:model="puzzleSolution" label="{{ __('Solution') }}" placeholder="{{ __('The answer and how it works...') }}" rows="3" required />

                <flux:separator />
                <flux:heading size="sm">{{ __('Progressive Hints') }}</flux:heading>

                <flux:textarea wire:model="puzzleHint1" label="{{ __('Hint Tier 1 — Subtle Nudge') }}" placeholder="{{ __('A gentle hint...') }}" rows="2" />
                <flux:textarea wire:model="puzzleHint2" label="{{ __('Hint Tier 2 — Clearer Clue') }}" placeholder="{{ __('A more direct hint...') }}" rows="2" />
                <flux:textarea wire:model="puzzleHint3" label="{{ __('Hint Tier 3 — Strong Hint') }}" placeholder="{{ __('Nearly gives it away...') }}" rows="2" />

                <flux:textarea wire:model="puzzleNotes" label="{{ __('DM Notes') }}" placeholder="{{ __('Private notes about this puzzle...') }}" rows="2" />
            </div>

            <div class="flex justify-end gap-3">
                <flux:button variant="subtle" wire:click="$set('showPuzzleForm', false)">{{ __('Cancel') }}</flux:button>
                <flux:button variant="primary" wire:click="savePuzzle">{{ __('Update Puzzle') }}</flux:button>
            </div>
        </flux:modal>
    @endforeach

    {{-- Generate Puzzle Modal --}}
    <flux:modal name="generate-puzzle-{{ $scene->id }}" class="md:w-lg">
        <flux:heading size="lg">{{ __('Generate Puzzle with AI') }}</flux:heading>

        <div class="mt-4 flex flex-col gap-4">
            <div class="grid gap-4 sm:grid-cols-2">
                <flux:select wire:model="generatePuzzleDifficulty" label="{{ __('Difficulty') }}">
                    <flux:select.option value="easy">{{ __('Easy') }}</flux:select.option>
                    <flux:select.option value="medium">{{ __('Medium') }}</flux:select.option>
                    <flux:select.option value="hard">{{ __('Hard') }}</flux:select.option>
                </flux:select>

                <flux:select wire:model="generatePuzzleType" label="{{ __('Type') }}">
                    <flux:select.option value="riddle">{{ __('Riddle') }}</flux:select.option>
                    <flux:select.option value="logic">{{ __('Logic') }}</flux:select.option>
                    <flux:select.option value="physical">{{ __('Physical') }}</flux:select.option>
                    <flux:select.option value="cipher">{{ __('Cipher') }}</flux:select.option>
                    <flux:select.option value="pattern">{{ __('Pattern') }}</flux:select.option>
                </flux:select>
            </div>

            <flux:textarea wire:model="generatePuzzleContext" label="{{ __('Additional Context') }}" placeholder="{{ __('e.g., The puzzle guards a treasure chest in the dragon\'s lair...') }}" rows="3" />
        </div>

        <div class="flex justify-end gap-3">
            <flux:button variant="subtle" wire:click="$set('showGeneratePuzzleModal', false)">{{ __('Cancel') }}</flux:button>
            <flux:button variant="primary" wire:click="generatePuzzle" wire:loading.attr="disabled" wire:target="generatePuzzle">
                <span wire:loading.remove wire:target="generatePuzzle">{{ __('Generate') }}</span>
                <span wire:loading wire:target="generatePuzzle">{{ __('Generating...') }}</span>
            </flux:button>
        </div>
    </flux:modal>
</div>
