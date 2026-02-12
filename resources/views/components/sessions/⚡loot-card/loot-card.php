<?php

use App\Models\CustomLoot;
use App\Models\SrdEquipment;
use App\Models\SrdMagicItem;
use Flux;
use Livewire\Component;

new class extends Component
{
    public string $lootableType;

    public int $lootableId;

    public bool $showForm = true;

    public string $lootSearchQuery = '';

    public string $lootSource = 'equipment';

    public ?int $selectedLootId = null;

    public string $selectedLootType = '';

    public int $lootQuantity = 1;

    public string $lootNotes = '';

    public function getLootSearchResultsProperty(): array
    {
        if (strlen($this->lootSearchQuery) < 2) {
            return [];
        }

        return match ($this->lootSource) {
            'magic_item' => SrdMagicItem::query()
                ->search($this->lootSearchQuery)
                ->limit(10)->get()
                ->map(fn ($i) => ['id' => $i->id, 'name' => $i->name, 'type' => 'srd_magic_item', 'rarity' => $i->rarity])
                ->toArray(),
            'custom' => CustomLoot::query()
                ->where('user_id', auth()->id())
                ->search($this->lootSearchQuery)
                ->limit(10)->get()
                ->map(fn ($i) => ['id' => $i->id, 'name' => $i->name, 'type' => 'custom_loot', 'rarity' => $i->rarity])
                ->toArray(),
            default => SrdEquipment::query()
                ->search($this->lootSearchQuery)
                ->limit(10)->get()
                ->map(fn ($i) => ['id' => $i->id, 'name' => $i->name, 'type' => 'srd_equipment', 'rarity' => null])
                ->toArray(),
        };
    }

    public function selectLoot(int $id, string $type): void
    {
        $this->selectedLootId = $id;
        $this->selectedLootType = $type;
    }

    public function save(): void
    {
        $this->validate([
            'selectedLootId' => ['required', 'integer'],
            'selectedLootType' => ['required', 'string', 'in:srd_equipment,srd_magic_item,custom_loot'],
            'lootQuantity' => ['required', 'integer', 'min:1'],
        ]);

        $lootableTypeClass = match ($this->selectedLootType) {
            'srd_equipment' => SrdEquipment::class,
            'srd_magic_item' => SrdMagicItem::class,
            'custom_loot' => CustomLoot::class,
        };

        $lootable = $this->lootableType::query()->findOrFail($this->lootableId);
        $lootable->loot()->create([
            'lootable_type' => $lootableTypeClass,
            'lootable_id' => $this->selectedLootId,
            'quantity' => $this->lootQuantity,
            'notes' => $this->lootNotes ?: null,
        ]);

        Flux::toast(__('Loot added successfully'));
        $this->showForm = false;
        $this->dispatch('loot-form-closed');
        $this->dispatch('$refresh');
    }

    public function close(): void
    {
        $this->showForm = false;
        $this->dispatch('loot-form-closed');
    }
};
