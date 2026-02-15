<div class="flex w-full flex-1 flex-col gap-6">
    {{-- Header --}}
    <div class="flex items-center gap-4">
        <flux:button variant="subtle" href="{{ route('campaigns.show', $campaign) }}" wire:navigate icon="arrow-left" size="sm">
            {{ __('Back') }}
        </flux:button>
        <flux:heading size="xl">{{ __('NPCs') }} — {{ $campaign->name }}</flux:heading>
    </div>

    {{-- Actions --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
        <div class="flex-1">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="{{ __('Search NPCs...') }}" icon="magnifying-glass" />
        </div>
        <div class="flex items-center gap-2">
            <flux:select wire:model.live="factionFilter" placeholder="{{ __('All Factions') }}">
                <flux:select.option value="">{{ __('All Factions') }}</flux:select.option>
                @foreach ($factions as $faction)
                    <flux:select.option value="{{ $faction->id }}">{{ $faction->name }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:select wire:model.live="aliveFilter" placeholder="{{ __('All') }}">
                <flux:select.option value="all">{{ __('All') }}</flux:select.option>
                <flux:select.option value="alive">{{ __('Alive') }}</flux:select.option>
                <flux:select.option value="dead">{{ __('Dead') }}</flux:select.option>
            </flux:select>
            <flux:button variant="subtle" wire:click="openGenerateModal" icon="sparkles">
                {{ __('Generate NPC') }}
            </flux:button>
            <flux:button variant="primary" wire:click="openForm" icon="plus">
                {{ __('Add NPC') }}
            </flux:button>
        </div>
    </div>

    {{-- Inline Form --}}
    @if ($showForm)
        <div class="rounded-xl border border-zinc-300 bg-zinc-50 p-5 dark:border-zinc-600 dark:bg-zinc-700/50">
            <flux:heading size="base" class="mb-3">
                {{ $editingNpcId ? __('Edit NPC') : __('New NPC') }}
            </flux:heading>
            <div class="flex flex-col gap-3">
                <div class="grid gap-3 sm:grid-cols-2">
                    <flux:input wire:model="npcName" label="{{ __('Name') }}" placeholder="{{ __('NPC name...') }}" required />
                    <flux:input wire:model="npcRole" label="{{ __('Role') }}" placeholder="{{ __('e.g., Blacksmith, Quest Giver') }}" />
                </div>
                <flux:textarea wire:model="npcDescription" label="{{ __('Description') }}" placeholder="{{ __('Physical appearance, background...') }}" rows="3" />
                <div class="grid gap-3 sm:grid-cols-2">
                    <flux:textarea wire:model="npcPersonality" label="{{ __('Personality') }}" placeholder="{{ __('Traits, temperament...') }}" rows="2" />
                    <flux:textarea wire:model="npcMotivation" label="{{ __('Motivation') }}" placeholder="{{ __('Goals, fears, desires...') }}" rows="2" />
                </div>
                <div class="grid gap-3 sm:grid-cols-2">
                    <flux:textarea wire:model="npcVoiceDescription" label="{{ __('Voice Description') }}" placeholder="{{ __('Accent, pitch, cadence...') }}" rows="2" />
                    <flux:textarea wire:model="npcSpeechPatterns" label="{{ __('Speech Patterns') }}" placeholder="{{ __('Formal, slang, poetic...') }}" rows="2" />
                </div>
                <flux:textarea wire:model="npcCatchphrases" label="{{ __('Catchphrases') }}" placeholder="{{ __('One per line...') }}" rows="2" />
                <div class="grid gap-3 sm:grid-cols-3">
                    <flux:select wire:model="npcFactionId" label="{{ __('Faction') }}" placeholder="{{ __('None') }}">
                        <flux:select.option value="">{{ __('None') }}</flux:select.option>
                        @foreach ($factions as $faction)
                            <flux:select.option value="{{ $faction->id }}">{{ $faction->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:select wire:model="npcLocationId" label="{{ __('Location') }}" placeholder="{{ __('None') }}">
                        <flux:select.option value="">{{ __('None') }}</flux:select.option>
                        @foreach ($locations as $location)
                            <flux:select.option value="{{ $location->id }}">{{ $location->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:select wire:model="npcIsAlive" label="{{ __('Status') }}">
                        <flux:select.option :value="true">{{ __('Alive') }}</flux:select.option>
                        <flux:select.option :value="false">{{ __('Dead') }}</flux:select.option>
                    </flux:select>
                </div>
                <div class="flex items-center justify-end gap-2">
                    <flux:button variant="subtle" size="sm" wire:click="$set('showForm', false)">{{ __('Cancel') }}</flux:button>
                    <flux:button variant="primary" size="sm" wire:click="save">
                        {{ $editingNpcId ? __('Update NPC') : __('Add NPC') }}
                    </flux:button>
                </div>
            </div>
        </div>
    @endif

    {{-- NPC Table --}}
    @if ($npcs->isEmpty())
        <div class="flex flex-1 flex-col items-center justify-center rounded-xl border border-dashed border-zinc-300 p-12 dark:border-zinc-600">
            <div class="text-center">
                <flux:icon name="users" class="mx-auto mb-4 size-12 text-zinc-400 dark:text-zinc-500" />
                <flux:heading size="lg" class="mb-2">{{ __('No NPCs found') }}</flux:heading>
                <flux:text class="text-zinc-500 dark:text-zinc-400">
                    {{ __('Create an NPC or generate one with AI.') }}
                </flux:text>
            </div>
        </div>
    @else
        <flux:table>
            <flux:table.columns>
                <flux:table.column>{{ __('Name') }}</flux:table.column>
                <flux:table.column>{{ __('Role') }}</flux:table.column>
                <flux:table.column>{{ __('Faction') }}</flux:table.column>
                <flux:table.column>{{ __('Location') }}</flux:table.column>
                <flux:table.column>{{ __('Status') }}</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                @foreach ($npcs as $npc)
                    <flux:table.row wire:key="npc-{{ $npc->id }}">
                        <flux:table.cell variant="strong">{{ $npc->name }}</flux:table.cell>
                        <flux:table.cell>{{ $npc->role ?? '—' }}</flux:table.cell>
                        <flux:table.cell>{{ $npc->faction?->name ?? '—' }}</flux:table.cell>
                        <flux:table.cell>{{ $npc->location?->name ?? '—' }}</flux:table.cell>
                        <flux:table.cell>
                            @if ($npc->is_alive)
                                <flux:badge size="sm" color="green">{{ __('Alive') }}</flux:badge>
                            @else
                                <flux:badge size="sm" color="red">{{ __('Dead') }}</flux:badge>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>
                            <div class="flex items-center justify-end gap-1">
                                <flux:button variant="subtle" size="sm" wire:click="viewNpc({{ $npc->id }})" icon="eye" title="{{ __('View') }}" />
                                <flux:button variant="subtle" size="sm" wire:click="openForm({{ $npc->id }})" icon="pencil" title="{{ __('Edit') }}" />
                                <flux:button variant="subtle" size="sm" wire:click="delete({{ $npc->id }})" wire:confirm="{{ __('Delete this NPC?') }}" icon="trash" title="{{ __('Delete') }}" />
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    @endif

    {{-- View NPC Detail --}}
    <flux:modal name="view-npc" class="md:w-xl" variant="flyout">
        @if ($viewingNpc)
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">{{ $viewingNpc->name }}</flux:heading>
                    @if ($viewingNpc->role)
                        <flux:text class="mt-1 text-sm text-zinc-500">{{ $viewingNpc->role }}</flux:text>
                    @endif
                    <div class="mt-2 flex flex-wrap gap-2">
                        @if ($viewingNpc->is_alive)
                            <flux:badge size="sm" color="green">{{ __('Alive') }}</flux:badge>
                        @else
                            <flux:badge size="sm" color="red">{{ __('Dead') }}</flux:badge>
                        @endif
                        @if ($viewingNpc->faction)
                            <flux:badge size="sm" variant="outline">{{ $viewingNpc->faction->name }}</flux:badge>
                        @endif
                        @if ($viewingNpc->location)
                            <flux:badge size="sm" variant="outline">{{ $viewingNpc->location->name }}</flux:badge>
                        @endif
                    </div>
                </div>

                <flux:separator />

                @if ($viewingNpc->description)
                    <div>
                        <flux:heading size="sm" class="mb-1">{{ __('Description') }}</flux:heading>
                        <flux:text class="text-sm whitespace-pre-line">{{ $viewingNpc->description }}</flux:text>
                    </div>
                @endif

                @if ($viewingNpc->personality)
                    <div>
                        <flux:heading size="sm" class="mb-1">{{ __('Personality') }}</flux:heading>
                        <flux:text class="text-sm">{{ $viewingNpc->personality }}</flux:text>
                    </div>
                @endif

                @if ($viewingNpc->motivation)
                    <div>
                        <flux:heading size="sm" class="mb-1">{{ __('Motivation') }}</flux:heading>
                        <flux:text class="text-sm">{{ $viewingNpc->motivation }}</flux:text>
                    </div>
                @endif

                @if ($viewingNpc->voice_description || $viewingNpc->speech_patterns)
                    <div>
                        <flux:heading size="sm" class="mb-1">{{ __('Voice & Speech') }}</flux:heading>
                        @if ($viewingNpc->voice_description)
                            <flux:text class="text-sm"><strong>{{ __('Voice:') }}</strong> {{ $viewingNpc->voice_description }}</flux:text>
                        @endif
                        @if ($viewingNpc->speech_patterns)
                            <flux:text class="text-sm"><strong>{{ __('Patterns:') }}</strong> {{ $viewingNpc->speech_patterns }}</flux:text>
                        @endif
                    </div>
                @endif

                @if ($viewingNpc->catchphrases)
                    <div>
                        <flux:heading size="sm" class="mb-1">{{ __('Catchphrases') }}</flux:heading>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($viewingNpc->catchphrases as $phrase)
                                <flux:badge size="sm" variant="outline">"{{ $phrase }}"</flux:badge>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if ($viewingNpc->backstory)
                    <div>
                        <flux:heading size="sm" class="mb-1">{{ __('Backstory') }}</flux:heading>
                        <flux:text class="text-sm whitespace-pre-line">{{ $viewingNpc->backstory }}</flux:text>
                    </div>
                @endif

                <flux:separator />

                @include('livewire.campaigns.partials.entity-history', ['history' => $history])
            </div>
        @endif
    </flux:modal>

    {{-- Generate Modal --}}
    <flux:modal wire:model="showGenerateModal" class="md:w-xl">
        <flux:heading size="lg">{{ __('Generate NPC with AI') }}</flux:heading>
        <flux:text class="mt-1">{{ __('Provide optional context to guide the AI, then review and edit the result before saving.') }}</flux:text>

        <div class="mt-4 flex flex-col gap-4">
            <flux:textarea
                wire:model="generateContext"
                label="{{ __('Context (optional)') }}"
                placeholder="{{ __('e.g., A mysterious merchant with ties to the thieves guild, a grumpy dwarven blacksmith...') }}"
                rows="3"
            />
        </div>

        <div class="mt-4 flex justify-end gap-3">
            <flux:button variant="subtle" wire:click="$set('showGenerateModal', false)">{{ __('Cancel') }}</flux:button>
            <flux:button variant="primary" wire:click="generate" icon="sparkles" wire:loading.attr="disabled" wire:target="generate">
                <span wire:loading.remove wire:target="generate">{{ __('Generate') }}</span>
                <span wire:loading wire:target="generate">{{ __('Generating...') }}</span>
            </flux:button>
        </div>
    </flux:modal>
</div>
