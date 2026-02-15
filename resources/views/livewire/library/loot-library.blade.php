<div class="flex w-full flex-1 flex-col gap-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <flux:heading size="xl">{{ __('Loot Library') }}</flux:heading>
        <flux:button variant="primary" wire:click="openCustomForm" icon="plus">
            {{ __('Custom Loot') }}
        </flux:button>
    </div>

    {{-- Search & Filters --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
        <div class="flex-1">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="{{ __('Search loot...') }}" icon="magnifying-glass" />
        </div>
        <div class="flex gap-3">
            <flux:select wire:model.live="categoryFilter" placeholder="{{ __('All Categories') }}">
                <flux:select.option value="">{{ __('All Categories') }}</flux:select.option>
                @foreach ($categories as $category)
                    <flux:select.option value="{{ $category }}">{{ $category }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:select wire:model.live="sourceFilter">
                <flux:select.option value="all">{{ __('All Sources') }}</flux:select.option>
                <flux:select.option value="equipment">{{ __('SRD Equipment') }}</flux:select.option>
                <flux:select.option value="magic_item">{{ __('SRD Magic Items') }}</flux:select.option>
                <flux:select.option value="custom">{{ __('Custom Only') }}</flux:select.option>
            </flux:select>
        </div>
    </div>

    {{-- Loot Table --}}
    @if ($items->isEmpty())
        <div class="flex flex-1 flex-col items-center justify-center rounded-xl border border-dashed border-zinc-300 p-12 dark:border-zinc-600">
            <div class="text-center">
                <flux:icon name="gift" class="mx-auto mb-4 size-12 text-zinc-400 dark:text-zinc-500" />
                <flux:heading size="lg" class="mb-2">{{ __('No loot found') }}</flux:heading>
                <flux:text class="text-zinc-500 dark:text-zinc-400">
                    {{ __('Try adjusting your search or filters.') }}
                </flux:text>
            </div>
        </div>
    @else
        <flux:table>
            <flux:table.columns>
                <flux:table.column>{{ __('Name') }}</flux:table.column>
                <flux:table.column>{{ __('Category') }}</flux:table.column>
                <flux:table.column>{{ __('Rarity') }}</flux:table.column>
                <flux:table.column>{{ __('Value (GP)') }}</flux:table.column>
                <flux:table.column>{{ __('Weight') }}</flux:table.column>
                <flux:table.column>{{ __('Source') }}</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                @foreach ($items as $item)
                    <flux:table.row wire:key="item-{{ $item['source'] }}-{{ $item['id'] }}">
                        <flux:table.cell variant="strong">{{ $item['name'] }}</flux:table.cell>
                        <flux:table.cell>{{ $item['category'] ?? '—' }}</flux:table.cell>
                        <flux:table.cell>
                            @if ($item['rarity'])
                                @php
                                    $rarityColor = match(strtolower($item['rarity'])) {
                                        'common' => 'zinc',
                                        'uncommon' => 'green',
                                        'rare' => 'blue',
                                        'very rare' => 'purple',
                                        'legendary' => 'amber',
                                        'artifact' => 'red',
                                        default => 'zinc',
                                    };
                                @endphp
                                <flux:badge size="sm" :color="$rarityColor">{{ ucfirst($item['rarity']) }}</flux:badge>
                            @else
                                —
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>{{ $item['value_gp'] !== null ? number_format($item['value_gp'], 2) : '—' }}</flux:table.cell>
                        <flux:table.cell>{{ $item['weight'] !== null ? $item['weight'] . ' lb.' : '—' }}</flux:table.cell>
                        <flux:table.cell>
                            @php
                                $sourceColor = match($item['source']) {
                                    'equipment' => 'blue',
                                    'magic_item' => 'purple',
                                    'custom' => 'green',
                                    default => 'zinc',
                                };
                                $sourceLabel = match($item['source']) {
                                    'equipment' => 'Equipment',
                                    'magic_item' => 'Magic Item',
                                    'custom' => 'Custom',
                                    default => $item['source'],
                                };
                            @endphp
                            <flux:badge size="sm" :color="$sourceColor">{{ $sourceLabel }}</flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>
                            <div class="flex items-center justify-end gap-1">
                                <flux:button variant="subtle" size="sm" wire:click="viewItem({{ $item['id'] }}, '{{ $item['source'] }}')" icon="eye" title="{{ __('View') }}" />
                                @if ($item['source'] === 'custom')
                                    <flux:button variant="subtle" size="sm" wire:click="editCustomLoot({{ $item['id'] }})" icon="pencil" title="{{ __('Edit') }}" />
                                    <flux:button variant="subtle" size="sm" wire:click="deleteCustomLoot({{ $item['id'] }})" wire:confirm="{{ __('Delete this custom loot?') }}" icon="trash" title="{{ __('Delete') }}" />
                                @endif
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    @endif

    {{-- View Item Detail Modal --}}
    <flux:modal name="view-item" class="md:w-xl" variant="flyout">
        @if ($viewingItem)
            <div class="space-y-6">
                {{-- Image display --}}
                <div class="flex flex-col items-center gap-3">
                    @if ($viewingItemSource === 'custom' && $viewingItem->image_path)
                        <img src="{{ $viewingItem->image_url }}" alt="{{ $viewingItem->name }}" class="h-48 w-full rounded-lg object-cover" />
                    @elseif (!empty($viewingItem->image_url) && $viewingItem instanceof \App\Models\SrdMagicItem)
                        <img src="{{ $viewingItem->full_image_url }}" alt="{{ $viewingItem->name }}" class="max-h-48 rounded-lg object-contain" loading="lazy" />
                    @elseif ($viewingItemSource === 'custom')
                        <div class="flex h-32 w-full items-center justify-center rounded-lg bg-zinc-100 dark:bg-zinc-700">
                            <flux:icon name="gift" class="size-12 text-zinc-400 dark:text-zinc-500" />
                        </div>
                    @endif
                    @if ($viewingItemSource === 'custom')
                        <flux:button variant="subtle" size="sm" wire:click="generateImage({{ $viewingItem->id }})" icon="sparkles" wire:loading.attr="disabled" wire:target="generateImage({{ $viewingItem->id }})">
                            <span wire:loading.remove wire:target="generateImage({{ $viewingItem->id }})">{{ $viewingItem->image_path ? __('Regenerate Image') : __('Generate Image') }}</span>
                            <span wire:loading wire:target="generateImage({{ $viewingItem->id }})">{{ __('Generating...') }}</span>
                        </flux:button>
                    @endif
                </div>

                <div>
                    <flux:heading size="lg">{{ $viewingItem->name }}</flux:heading>
                    @if (!empty($viewingItem->rarity))
                        @php
                            $rarityColor = match(strtolower($viewingItem->rarity)) {
                                'common' => 'zinc',
                                'uncommon' => 'green',
                                'rare' => 'blue',
                                'very rare' => 'purple',
                                'legendary' => 'amber',
                                'artifact' => 'red',
                                default => 'zinc',
                            };
                        @endphp
                        <flux:badge size="sm" :color="$rarityColor" class="mt-2">{{ ucfirst($viewingItem->rarity) }}</flux:badge>
                    @endif
                </div>

                <flux:separator />

                <div class="grid grid-cols-2 gap-3 text-sm">
                    @if (!empty($viewingItem->equipment_category ?? $viewingItem->category))
                        <div><span class="font-semibold text-zinc-700 dark:text-zinc-300">{{ __('Category') }}:</span> {{ $viewingItem->equipment_category ?? $viewingItem->category }}</div>
                    @endif
                    @if (!empty($viewingItem->weapon_category))
                        <div><span class="font-semibold text-zinc-700 dark:text-zinc-300">{{ __('Weapon Type') }}:</span> {{ $viewingItem->weapon_category }}</div>
                    @endif
                    @if (!empty($viewingItem->armor_category))
                        <div><span class="font-semibold text-zinc-700 dark:text-zinc-300">{{ __('Armor Type') }}:</span> {{ $viewingItem->armor_category }}</div>
                    @endif
                    @if (($viewingItem->cost_gp ?? $viewingItem->value_gp ?? null) !== null)
                        <div><span class="font-semibold text-zinc-700 dark:text-zinc-300">{{ __('Value') }}:</span> {{ number_format($viewingItem->cost_gp ?? $viewingItem->value_gp, 2) }} GP</div>
                    @endif
                    @if (!empty($viewingItem->weight))
                        <div><span class="font-semibold text-zinc-700 dark:text-zinc-300">{{ __('Weight') }}:</span> {{ $viewingItem->weight }} lb.</div>
                    @endif
                </div>

                {{-- Damage (weapons) --}}
                @if (!empty($viewingItem->damage))
                    <div class="text-sm">
                        <span class="font-semibold text-zinc-700 dark:text-zinc-300">{{ __('Damage') }}:</span>
                        {{ $viewingItem->damage['damage_dice'] ?? '' }} {{ $viewingItem->damage['damage_type']['name'] ?? '' }}
                    </div>
                @endif

                {{-- Armor Class --}}
                @if (!empty($viewingItem->armor_class) && is_array($viewingItem->armor_class))
                    <div class="text-sm">
                        <span class="font-semibold text-zinc-700 dark:text-zinc-300">{{ __('Armor Class') }}:</span>
                        {{ $viewingItem->armor_class['base'] ?? '' }}{{ !empty($viewingItem->armor_class['dex_bonus']) ? ' + DEX' : '' }}
                    </div>
                @endif

                {{-- Properties --}}
                @if (!empty($viewingItem->properties))
                    <div class="text-sm">
                        <span class="font-semibold text-zinc-700 dark:text-zinc-300">{{ __('Properties') }}:</span>
                        @if (is_array($viewingItem->properties))
                            {{ collect($viewingItem->properties)->pluck('name')->implode(', ') }}
                        @else
                            {{ $viewingItem->properties }}
                        @endif
                    </div>
                @endif

                {{-- Description --}}
                @if (!empty($viewingItem->description))
                    <div>
                        <flux:heading size="sm" class="mb-2">{{ __('Description') }}</flux:heading>
                        <flux:text class="text-sm">{{ $viewingItem->description }}</flux:text>
                    </div>
                @endif
            </div>
        @endif
    </flux:modal>

    {{-- Custom Loot Create/Edit Modal --}}
    <flux:modal wire:model="showCustomForm" class="md:w-xl">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $editingCustomLootId ? __('Edit Custom Loot') : __('Create Custom Loot') }}</flux:heading>
                <flux:text class="mt-2">{{ __('Define a custom loot item for your encounters and scenes.') }}</flux:text>
            </div>

            <div class="flex flex-col gap-4">
                <flux:input wire:model="customName" label="{{ __('Name') }}" placeholder="{{ __('Item name...') }}" required />

                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:select wire:model="customCategory" label="{{ __('Category') }}">
                        <flux:select.option value="equipment">{{ __('Equipment') }}</flux:select.option>
                        <flux:select.option value="magic_item">{{ __('Magic Item') }}</flux:select.option>
                        <flux:select.option value="currency">{{ __('Currency') }}</flux:select.option>
                        <flux:select.option value="other">{{ __('Other') }}</flux:select.option>
                    </flux:select>
                    <flux:select wire:model="customRarity" label="{{ __('Rarity') }}">
                        <flux:select.option value="">{{ __('None') }}</flux:select.option>
                        <flux:select.option value="Common">{{ __('Common') }}</flux:select.option>
                        <flux:select.option value="Uncommon">{{ __('Uncommon') }}</flux:select.option>
                        <flux:select.option value="Rare">{{ __('Rare') }}</flux:select.option>
                        <flux:select.option value="Very Rare">{{ __('Very Rare') }}</flux:select.option>
                        <flux:select.option value="Legendary">{{ __('Legendary') }}</flux:select.option>
                        <flux:select.option value="Artifact">{{ __('Artifact') }}</flux:select.option>
                    </flux:select>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:input wire:model="customValueGp" type="number" label="{{ __('Value (GP)') }}" step="0.01" min="0" />
                    <flux:input wire:model="customWeight" type="number" label="{{ __('Weight (lb.)') }}" step="0.1" min="0" />
                </div>

                <flux:textarea wire:model="customDescription" label="{{ __('Description') }}" placeholder="{{ __('Describe the item...') }}" rows="3" />
            </div>

            <div class="flex justify-end gap-2">
                <flux:button variant="subtle" wire:click="$set('showCustomForm', false)">{{ __('Cancel') }}</flux:button>
                <flux:button variant="primary" wire:click="saveCustomLoot">
                    {{ $editingCustomLootId ? __('Update Loot') : __('Create Loot') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
