<div class="rounded-lg border border-zinc-200 bg-zinc-50 px-4 py-3 dark:border-zinc-600 dark:bg-zinc-700/50">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <span class="font-medium text-zinc-700 dark:text-zinc-200">{{ $scene->title }}</span>
        </div>
        <div class="flex items-center gap-1">
            <flux:button variant="subtle" size="sm" wire:click="openForm({{ $scene->id }})" icon="pencil" title="{{ __('Edit Scene') }}" />
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
                <livewire:sessions.encounter-card :encounter="$encounter" :sceneId="$scene->id" :key="'encounter-'.$encounter->id" />
            @endforeach
        </div>
    @endif

    {{-- Nested Branches --}}
    @if ($scene->branchOptions->isNotEmpty())
        <div class="mt-3 space-y-2 border-t border-zinc-200 pt-3 dark:border-zinc-600">
            <span class="text-xs font-semibold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">{{ __('Branch Options') }}</span>
            @foreach ($scene->branchOptions as $branch)
                <livewire:sessions.branch-card :branch="$branch" :sceneId="$scene->id" :key="'branch-'.$branch->id" />
            @endforeach
        </div>
    @endif

    {{-- Edit Scene Modal --}}
    <flux:modal wire:model="showForm" class="md:w-xl">
        <flux:heading size="lg">{{ $editingSceneId ? __('Edit Scene') : __('New Scene') }}</flux:heading>

        <div class="flex flex-col gap-4 mt-4">
            <flux:input
                wire:model="title"
                label="{{ __('Title') }}"
                placeholder="{{ __('Scene title...') }}"
                required
            />
            <flux:textarea
                wire:model="description"
                label="{{ __('Description') }}"
                placeholder="{{ __('What happens in this scene?') }}"
                rows="3"
            />
            <flux:textarea
                wire:model="notes"
                label="{{ __('DM Notes') }}"
                placeholder="{{ __('Private notes for this scene...') }}"
                rows="2"
            />
        </div>

        <div class="flex justify-end gap-3">
            <flux:button variant="subtle" wire:click="$set('showForm', false)">{{ __('Cancel') }}</flux:button>
            <flux:button variant="primary" wire:click="save">{{ $editingSceneId ? __('Update Scene') : __('Add Scene') }}</flux:button>
        </div>
    </flux:modal>
</div>
