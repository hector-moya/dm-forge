<div class="rounded-lg border border-zinc-200 bg-zinc-50 px-4 py-3 dark:border-zinc-600 dark:bg-zinc-700/50">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <span class="font-medium text-zinc-700 dark:text-zinc-200">{{ $scene->title }}</span>
        </div>
        <div class="flex items-center gap-1">
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
            <flux:button variant="primary" wire:click="$parent.saveNewBranch">{{ __('Add Branch') }}</flux:button>
        </div>
    </flux:modal>
</div>
