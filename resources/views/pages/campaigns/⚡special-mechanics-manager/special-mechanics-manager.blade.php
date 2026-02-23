<div class="flex w-full flex-1 flex-col gap-6">
    {{-- Header --}}
    <div class="flex items-center gap-4">
        <flux:button variant="subtle" href="{{ route('campaigns.show', $campaign) }}" wire:navigate icon="arrow-left" size="sm">
            {{ __('Back') }}
        </flux:button>
        <flux:heading size="xl">{{ __('Special Mechanics') }} — {{ $campaign->name }}</flux:heading>
    </div>

    {{-- Actions --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div class="flex-1">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="{{ __('Search special mechanics...') }}" icon="magnifying-glass" />
        </div>
        <div class="flex items-center gap-2">
            <flux:button variant="primary" icon="plus" wire:click="openCreateMechanicModal">
                {{ __('Add Mechanic') }}
            </flux:button>
        </div>
    </div>

    {{-- Table --}}
    @if ($this->campaignSpecialMechanics->isEmpty())
        <div class="flex flex-1 flex-col items-center justify-center rounded-xl border border-dashed border-zinc-300 p-12 dark:border-zinc-600">
            <div class="text-center">
                <flux:icon name="cog" class="mx-auto mb-4 size-12 text-zinc-400 dark:text-zinc-500" />
                <flux:heading size="lg" class="mb-2">{{ __('No special mechanics found') }}</flux:heading>
                <flux:text class="text-zinc-500 dark:text-zinc-400">
                    {{ __('Add mechanics that make your campaign unique.') }}
                </flux:text>
            </div>
        </div>
    @else
        <flux:table>
            <flux:table.columns>
                <flux:table.column>{{ __('Name') }}</flux:table.column>
                <flux:table.column>{{ __('Rules') }}</flux:table.column>
                <flux:table.column>{{ __('Created at') }}</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                @foreach ($this->campaignSpecialMechanics as $mechanic)
                    <flux:table.row wire:key="mechanic-{{ $mechanic->id }}">
                        <flux:table.cell variant="strong">{{ $mechanic->name }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:badge size="sm">{{ $mechanic->rules_count }}</flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>{{ $mechanic->created_at->format('M j, Y') }}</flux:table.cell>
                        <flux:table.cell>
                            <div class="flex items-center justify-end gap-1">
                                <flux:modal.trigger name="view-mechanic-{{ $mechanic->id }}">
                                    <flux:button variant="subtle" size="sm" wire:click="openViewMechanicModal({{ $mechanic->id }})" icon="eye" title="{{ __('View') }}" />
                                </flux:modal.trigger>
                                <flux:button variant="subtle" size="sm" wire:click="openRulesPanel({{ $mechanic->id }})" icon="list" title="{{ __('Manage Rules') }}" />
                                <flux:modal.trigger name="create-mechanic">
                                    <flux:button variant="subtle" size="sm" wire:click="setMechanicId({{ $mechanic->id }})" icon="pencil" title="{{ __('Edit') }}" />
                                </flux:modal.trigger>
                                <flux:button variant="subtle" size="sm" wire:click="form.destroy({{ $mechanic }})" wire:confirm="{{ __('Delete this special mechanic?') }}" icon="trash" title="{{ __('Delete') }}" />
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    @endif

    {{-- Rules Panel --}}
    @if ($editingMechanicForRulesId)
        @php $editingMechanic = $this->campaignSpecialMechanics->firstWhere('id', $editingMechanicForRulesId); @endphp
        <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
            <div class="mb-4 flex items-center justify-between">
                <flux:heading size="lg">{{ __('Rules for') }}: {{ $editingMechanic?->name }}</flux:heading>
                <div class="flex items-center gap-2">
                    <flux:button variant="subtle" size="sm" icon="plus" wire:click="openRuleForm()">{{ __('Add Rule') }}</flux:button>
                    <flux:button variant="ghost" size="sm" icon="x-mark" wire:click="closeRulesPanel" />
                </div>
            </div>

            @if ($showRuleForm)
                <div class="mb-4 rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-600 dark:bg-zinc-700/50">
                    <flux:heading size="sm" class="mb-3">{{ $editingRuleId ? __('Edit Rule') : __('New Rule') }}</flux:heading>
                    <div class="flex flex-col gap-3">
                        <flux:input wire:model="ruleName" label="{{ __('Name') }}" placeholder="{{ __('Rule name...') }}" required />
                        <flux:textarea wire:model="ruleDescription" label="{{ __('Description') }}" placeholder="{{ __('Describe this rule...') }}" rows="2" />
                        <flux:textarea wire:model="ruleNotes" label="{{ __('Notes') }}" placeholder="{{ __('Additional notes...') }}" rows="2" />
                        <div class="flex items-center justify-end gap-2">
                            <flux:button variant="subtle" size="sm" wire:click="resetRuleForm">{{ __('Cancel') }}</flux:button>
                            <flux:button variant="primary" size="sm" wire:click="saveRule">
                                {{ $editingRuleId ? __('Update Rule') : __('Add Rule') }}
                            </flux:button>
                        </div>
                    </div>
                </div>
            @endif

            @if ($this->editingMechanicRules->isEmpty())
                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('No rules yet. Add the first rule above.') }}</flux:text>
            @else
                <ul class="space-y-2">
                    @foreach ($this->editingMechanicRules as $rule)
                        <li class="flex items-start justify-between rounded-lg bg-zinc-50 px-4 py-3 dark:bg-zinc-700/50" wire:key="rule-{{ $rule->id }}">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-zinc-800 dark:text-zinc-100">{{ $rule->name }}</p>
                                @if ($rule->description)
                                    <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">{{ $rule->description }}</p>
                                @endif
                                @if ($rule->notes)
                                    <p class="mt-1 text-xs italic text-zinc-500 dark:text-zinc-400">{{ $rule->notes }}</p>
                                @endif
                            </div>
                            <div class="ml-3 flex items-center gap-1">
                                <flux:button variant="ghost" size="xs" icon="pencil" wire:click="openRuleForm({{ $rule->id }})" />
                                <flux:button variant="ghost" size="xs" icon="trash" wire:click="deleteRule({{ $rule->id }})" wire:confirm="{{ __('Delete this rule?') }}" />
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    @endif

    {{-- View Detail --}}
    @if ($selectedMechanicId)
        <flux:modal name="view-mechanic-{{ $selectedMechanicId }}" class="md:w-xl" variant="flyout" @close="$set('selectedMechanicId', null)">
            <livewire:campaigns.special-mechanic-details :specialMechanicId="$selectedMechanicId" :key="'special-mechanic-details-' . $selectedMechanicId" />
        </flux:modal>
    @endif

    {{-- Create / Edit Modal --}}
    <flux:modal name="create-mechanic" class="md:w-xl space-y-6">
        <flux:heading size="xl" class="mb-3">
            {{ $selectedMechanicId ? __('Edit Special Mechanic') : __('New Special Mechanic') }}
        </flux:heading>
        <div class="flex flex-col gap-3">
            <flux:input wire:model="form.name" label="{{ __('Name') }}" placeholder="{{ __('Mechanic name...') }}" required />
            <flux:textarea wire:model="form.description" label="{{ __('Description') }}" placeholder="{{ __('Describe this mechanic...') }}" rows="3" />
            <flux:textarea wire:model="form.dmNotes" label="{{ __('DM Notes') }}" placeholder="{{ __('Notes for the DM...') }}" rows="3" />

            @if (! $selectedMechanicId)
                {{-- Pending Rules (create only) --}}
                <div>
                    <flux:heading size="sm" class="mb-2">{{ __('Rules') }}</flux:heading>

                    @if (count($form->specialMechanicRules) > 0)
                        <ul class="mb-3 space-y-2">
                            @foreach ($form->specialMechanicRules as $index => $rule)
                                <li class="flex items-center justify-between rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-700/50" wire:key="pending-rule-{{ $index }}">
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-zinc-800 dark:text-zinc-100">{{ $rule['name'] }}</p>
                                        @if ($rule['description'])
                                            <p class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">{{ $rule['description'] }}</p>
                                        @endif
                                    </div>
                                    <flux:button variant="ghost" size="xs" icon="trash" wire:click="form.removeRule({{ $index }})" />
                                </li>
                            @endforeach
                        </ul>
                    @endif

                    <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-3 dark:border-zinc-600 dark:bg-zinc-700/50">
                        <div class="flex flex-col gap-2">
                            <flux:input wire:model="form.pendingRuleName" label="{{ __('Rule Name') }}" placeholder="{{ __('Rule name...') }}" />
                            <flux:textarea wire:model="form.pendingRuleDescription" label="{{ __('Description') }}" placeholder="{{ __('Describe this rule...') }}" rows="2" />
                            <flux:textarea wire:model="form.pendingRuleNotes" label="{{ __('Notes') }}" placeholder="{{ __('Additional notes...') }}" rows="2" />
                            <div class="flex justify-end">
                                <flux:button variant="subtle" size="sm" icon="plus" wire:click="form.addRule">{{ __('Add Rule') }}</flux:button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="flex items-center justify-end gap-2">
                <flux:button variant="subtle" size="sm" wire:click="resetSelectedMechanicId">{{ __('Cancel') }}</flux:button>
                <flux:button variant="primary" size="sm" wire:click="save">
                    {{ $selectedMechanicId ? __('Update Mechanic') : __('Add Mechanic') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
