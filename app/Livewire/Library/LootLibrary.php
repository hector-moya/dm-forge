<?php

namespace App\Livewire\Library;

use App\Models\CustomLoot;
use App\Models\SrdEquipment;
use App\Models\SrdMagicItem;
use Illuminate\Support\Collection;
use Livewire\Component;

class LootLibrary extends Component
{
    public string $search = '';

    public string $categoryFilter = '';

    public string $sourceFilter = 'all';

    public ?int $viewingItemId = null;

    public string $viewingItemSource = '';

    // Custom loot form
    public bool $showCustomForm = false;

    public ?int $editingCustomLootId = null;

    public string $customName = '';

    public string $customCategory = 'equipment';

    public string $customRarity = '';

    public string $customDescription = '';

    public ?float $customValueGp = null;

    public ?float $customWeight = null;

    public function getItems(): Collection
    {
        $equipmentQuery = SrdEquipment::query();
        $magicItemQuery = SrdMagicItem::query();
        $customQuery = CustomLoot::query()->where('user_id', auth()->id());

        if ($this->search !== '') {
            $equipmentQuery->search($this->search);
            $magicItemQuery->search($this->search);
            $customQuery->search($this->search);
        }

        if ($this->categoryFilter !== '') {
            $equipmentQuery->byCategory($this->categoryFilter);
            $magicItemQuery->where('equipment_category', $this->categoryFilter);
            $customQuery->where('category', $this->categoryFilter);
        }

        if ($this->sourceFilter === 'equipment') {
            return $equipmentQuery->orderBy('name')->limit(50)->get()
                ->map(fn ($item) => $this->formatItem($item, 'equipment'));
        }

        if ($this->sourceFilter === 'magic_item') {
            return $magicItemQuery->orderBy('name')->limit(50)->get()
                ->map(fn ($item) => $this->formatItem($item, 'magic_item'));
        }

        if ($this->sourceFilter === 'custom') {
            return $customQuery->orderBy('name')->limit(50)->get()
                ->map(fn ($item) => $this->formatItem($item, 'custom'));
        }

        $equipment = $equipmentQuery->orderBy('name')->limit(20)->get()
            ->map(fn ($item) => $this->formatItem($item, 'equipment'));
        $magicItems = $magicItemQuery->orderBy('name')->limit(20)->get()
            ->map(fn ($item) => $this->formatItem($item, 'magic_item'));
        $custom = $customQuery->orderBy('name')->limit(10)->get()
            ->map(fn ($item) => $this->formatItem($item, 'custom'));

        return $custom->concat($equipment)->concat($magicItems)->sortBy('name')->values();
    }

    public function viewItem(int $id, string $source): void
    {
        $this->viewingItemId = $id;
        $this->viewingItemSource = $source;

        $this->modal('view-item')->show();
    }

    public function getViewingItemProperty(): ?object
    {
        if (! $this->viewingItemId) {
            return null;
        }

        return match ($this->viewingItemSource) {
            'equipment' => SrdEquipment::query()->find($this->viewingItemId),
            'magic_item' => SrdMagicItem::query()->find($this->viewingItemId),
            'custom' => CustomLoot::query()->where('user_id', auth()->id())->find($this->viewingItemId),
            default => null,
        };
    }

    public function openCustomForm(): void
    {
        $this->resetCustomForm();
        $this->showCustomForm = true;
    }

    public function editCustomLoot(int $id): void
    {
        $loot = CustomLoot::query()
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        $this->editingCustomLootId = $loot->id;
        $this->customName = $loot->name;
        $this->customCategory = $loot->category ?? 'equipment';
        $this->customRarity = $loot->rarity ?? '';
        $this->customDescription = $loot->description ?? '';
        $this->customValueGp = $loot->value_gp;
        $this->customWeight = $loot->weight;
        $this->showCustomForm = true;
    }

    public function saveCustomLoot(): void
    {
        $this->validate([
            'customName' => 'required|string|max:255',
            'customCategory' => 'required|string|in:equipment,magic_item,currency,other',
        ]);

        $data = [
            'name' => $this->customName,
            'category' => $this->customCategory,
            'rarity' => $this->customRarity ?: null,
            'description' => $this->customDescription ?: null,
            'value_gp' => $this->customValueGp,
            'weight' => $this->customWeight,
        ];

        if ($this->editingCustomLootId) {
            CustomLoot::query()
                ->where('user_id', auth()->id())
                ->where('id', $this->editingCustomLootId)
                ->update($data);
        } else {
            auth()->user()->customLoot()->create($data);
        }

        $this->resetCustomForm();
        $this->showCustomForm = false;
    }

    public function deleteCustomLoot(int $id): void
    {
        CustomLoot::query()
            ->where('user_id', auth()->id())
            ->where('id', $id)
            ->delete();

        if ($this->viewingItemId === $id) {
            $this->viewingItemId = null;
        }
    }

    public function getCategoriesProperty(): array
    {
        return SrdEquipment::query()
            ->distinct()
            ->whereNotNull('equipment_category')
            ->pluck('equipment_category')
            ->sort()
            ->values()
            ->toArray();
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.library.loot-library', [
            'items' => $this->getItems(),
            'viewingItem' => $this->getViewingItemProperty(),
            'categories' => $this->categories,
        ])->title('Loot Library');
    }

    private function resetCustomForm(): void
    {
        $this->editingCustomLootId = null;
        $this->customName = '';
        $this->customCategory = 'equipment';
        $this->customRarity = '';
        $this->customDescription = '';
        $this->customValueGp = null;
        $this->customWeight = null;
    }

    /**
     * @return array{id: int, name: string, source: string, category: ?string, rarity: ?string, value_gp: ?float, weight: ?float}
     */
    private function formatItem(SrdEquipment|SrdMagicItem|CustomLoot $item, string $source): array
    {
        return [
            'id' => $item->id,
            'name' => $item->name,
            'source' => $source,
            'category' => $item->equipment_category ?? $item->category ?? null,
            'rarity' => $item->rarity ?? null,
            'value_gp' => $item->cost_gp ?? $item->value_gp ?? null,
            'weight' => $item->weight ?? null,
        ];
    }
}
