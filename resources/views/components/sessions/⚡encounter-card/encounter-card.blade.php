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
            <flux:button variant="subtle" size="sm" href="{{ route('sessions.combat', [$encounter->game_session_id, $encounter]) }}" wire:navigate icon="bolt" title="{{ __('Combat Tracker') }}" />
            <flux:button variant="subtle" size="sm" wire:click="openMonsterForm" icon="plus" title="{{ __('Add Monster') }}" />
            <flux:button variant="subtle" size="sm" wire:click="openNpcForm" icon="user-plus" title="{{ __('Add NPC') }}" />
            <flux:button variant="subtle" size="sm" wire:click="openLootForm" icon="gift" title="{{ __('Add Loot') }}" />
            <flux:button variant="subtle" size="sm" wire:click="openForm" icon="pencil" title="{{ __('Edit Encounter') }}" />
            <flux:button variant="subtle" size="sm" wire:click="delete" wire:confirm="{{ __('Delete this encounter?') }}" icon="trash" title="{{ __('Delete Encounter') }}" />
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

    {{-- NPCs --}}
    @if ($encounter->npcs->isNotEmpty())
        <div class="mt-2 flex flex-wrap gap-2">
            @foreach ($encounter->npcs as $encounterNpc)
                <div class="flex items-center gap-1 rounded-md bg-emerald-50 px-2 py-1 text-xs dark:bg-emerald-900/30">
                    <span class="font-medium text-emerald-700 dark:text-emerald-300">{{ $encounterNpc->name }}</span>
                    <span class="text-emerald-500 dark:text-emerald-400">HP {{ $encounterNpc->hp_max }} AC {{ $encounterNpc->armor_class }}</span>
                    <button type="button" wire:click="deleteNpc({{ $encounterNpc->id }})" wire:confirm="{{ __('Remove this NPC?') }}" class="ml-1 text-emerald-400 hover:text-red-500 dark:text-emerald-500">
                        &times;
                    </button>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Add NPC Form --}}
    @if ($showNpcForm)
        @php $campaignNpcs = $encounter->gameSession->campaign->npcs()->orderBy('name')->get(); @endphp
        <div class="mt-2 rounded-lg border border-emerald-200 bg-emerald-50 p-3 dark:border-emerald-700 dark:bg-emerald-900/20">
            <span class="mb-2 block text-xs font-semibold uppercase text-emerald-600 dark:text-emerald-400">{{ __('Add NPC to Encounter') }}</span>
            <div class="flex flex-col gap-2">
                <flux:select wire:model="selectedNpcId" placeholder="{{ __('Select NPC...') }}" size="sm">
                    @foreach ($campaignNpcs as $npc)
                        <flux:select.option :value="$npc->id">{{ $npc->name }} ({{ $npc->role }})</flux:select.option>
                    @endforeach
                </flux:select>
                <div class="flex items-end gap-2">
                    <flux:input wire:model="npcHpMax" type="number" label="{{ __('HP') }}" size="sm" class="w-20" />
                    <flux:input wire:model="npcArmorClass" type="number" label="{{ __('AC') }}" size="sm" class="w-20" />
                    <flux:button variant="primary" size="sm" wire:click="addNpcToEncounter">{{ __('Add') }}</flux:button>
                    <flux:button variant="subtle" size="sm" wire:click="$set('showNpcForm', false)">{{ __('Cancel') }}</flux:button>
                </div>
            </div>
        </div>
    @endif

    {{-- Edit Encounter Modal --}}
    <flux:modal wire:model="showForm" class="md:w-xl">
        <flux:heading size="lg">{{ __('Edit Encounter') }}</flux:heading>

        <div class="flex flex-col gap-4 mt-4">
            <flux:input
                wire:model="name"
                label="{{ __('Name') }}"
                placeholder="{{ __('Encounter name...') }}"
                required
            />
            <flux:textarea
                wire:model="description"
                label="{{ __('Description') }}"
                placeholder="{{ __('Describe the encounter setup...') }}"
                rows="3"
            />
            <flux:input
                wire:model="environment"
                label="{{ __('Environment') }}"
                placeholder="{{ __('e.g., Dark cave, Open field, Castle dungeon...') }}"
            />
        </div>

        <div class="flex justify-end gap-3">
            <flux:button variant="subtle" wire:click="$set('showForm', false)">{{ __('Cancel') }}</flux:button>
            <flux:button variant="primary" wire:click="save">{{ __('Update Encounter') }}</flux:button>
        </div>
    </flux:modal>

    {{-- Monster Form Modal --}}
    @if ($showMonsterForm)
        <livewire:sessions.monster-form :encounterId="$encounter->id" :key="'monster-form-'.$encounter->id" />
    @endif

    {{-- Loot Form Modal --}}
    @if ($showLootForm)
        <livewire:sessions.loot-card :lootableType="get_class($encounter)" :lootableId="$encounter->id" :key="'loot-form-'.$encounter->id" />
    @endif
</div>
