<?php

namespace App\Livewire\Library;

use App\Models\CustomMonster;
use App\Models\SrdMonster;
use Illuminate\Support\Collection;
use Livewire\Component;

class MonsterLibrary extends Component
{
    public string $search = '';

    public string $typeFilter = '';

    public string $sourceFilter = 'all';

    public ?int $viewingMonsterId = null;

    public string $viewingMonsterSource = '';

    // Custom monster form
    public bool $showCustomForm = false;

    public ?int $editingCustomMonsterId = null;

    public string $customName = '';

    public string $customSize = 'Medium';

    public string $customType = '';

    public string $customAlignment = '';

    public int $customArmorClass = 10;

    public int $customHitPoints = 1;

    public string $customHitDice = '';

    public ?float $customChallengeRating = null;

    public ?int $customXp = null;

    public int $customStrength = 10;

    public int $customDexterity = 10;

    public int $customConstitution = 10;

    public int $customIntelligence = 10;

    public int $customWisdom = 10;

    public int $customCharisma = 10;

    public string $customLanguages = '';

    public string $customNotes = '';

    public function getMonsters(): Collection
    {
        $srdQuery = SrdMonster::query();
        $customQuery = CustomMonster::query()->where('user_id', auth()->id());

        if ($this->search !== '') {
            $srdQuery->search($this->search);
            $customQuery->search($this->search);
        }

        if ($this->typeFilter !== '') {
            $srdQuery->byType($this->typeFilter);
            $customQuery->where('type', $this->typeFilter);
        }

        if ($this->sourceFilter === 'srd') {
            return $srdQuery->orderBy('name')->limit(50)->get()
                ->map(fn ($m) => $this->formatMonster($m, 'srd'));
        }

        if ($this->sourceFilter === 'custom') {
            return $customQuery->orderBy('name')->limit(50)->get()
                ->map(fn ($m) => $this->formatMonster($m, 'custom'));
        }

        $srdResults = $srdQuery->orderBy('name')->limit(40)->get()
            ->map(fn ($m) => $this->formatMonster($m, 'srd'));
        $customResults = $customQuery->orderBy('name')->limit(10)->get()
            ->map(fn ($m) => $this->formatMonster($m, 'custom'));

        return $customResults->concat($srdResults)->sortBy('name')->values();
    }

    public function viewMonster(int $id, string $source): void
    {
        $this->viewingMonsterId = $id;
        $this->viewingMonsterSource = $source;
    }

    public function getViewingMonsterProperty(): ?object
    {
        if (! $this->viewingMonsterId) {
            return null;
        }

        if ($this->viewingMonsterSource === 'srd') {
            return SrdMonster::query()->find($this->viewingMonsterId);
        }

        return CustomMonster::query()
            ->where('user_id', auth()->id())
            ->find($this->viewingMonsterId);
    }

    public function openCustomForm(): void
    {
        $this->resetCustomForm();
        $this->showCustomForm = true;
    }

    public function editCustomMonster(int $id): void
    {
        $monster = CustomMonster::query()
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        $this->editingCustomMonsterId = $monster->id;
        $this->customName = $monster->name;
        $this->customSize = $monster->size ?? 'Medium';
        $this->customType = $monster->type ?? '';
        $this->customAlignment = $monster->alignment ?? '';
        $this->customArmorClass = $monster->armor_class;
        $this->customHitPoints = $monster->hit_points;
        $this->customHitDice = $monster->hit_dice ?? '';
        $this->customChallengeRating = $monster->challenge_rating;
        $this->customXp = $monster->xp;
        $this->customStrength = $monster->strength ?? 10;
        $this->customDexterity = $monster->dexterity ?? 10;
        $this->customConstitution = $monster->constitution ?? 10;
        $this->customIntelligence = $monster->intelligence ?? 10;
        $this->customWisdom = $monster->wisdom ?? 10;
        $this->customCharisma = $monster->charisma ?? 10;
        $this->customLanguages = $monster->languages ?? '';
        $this->customNotes = $monster->notes ?? '';
        $this->showCustomForm = true;
    }

    public function saveCustomMonster(): void
    {
        $this->validate([
            'customName' => 'required|string|max:255',
            'customArmorClass' => 'required|integer|min:1',
            'customHitPoints' => 'required|integer|min:1',
        ]);

        $data = [
            'name' => $this->customName,
            'size' => $this->customSize,
            'type' => $this->customType,
            'alignment' => $this->customAlignment,
            'armor_class' => $this->customArmorClass,
            'hit_points' => $this->customHitPoints,
            'hit_dice' => $this->customHitDice,
            'challenge_rating' => $this->customChallengeRating,
            'xp' => $this->customXp,
            'strength' => $this->customStrength,
            'dexterity' => $this->customDexterity,
            'constitution' => $this->customConstitution,
            'intelligence' => $this->customIntelligence,
            'wisdom' => $this->customWisdom,
            'charisma' => $this->customCharisma,
            'languages' => $this->customLanguages,
            'notes' => $this->customNotes,
        ];

        if ($this->editingCustomMonsterId) {
            CustomMonster::query()
                ->where('user_id', auth()->id())
                ->where('id', $this->editingCustomMonsterId)
                ->update($data);
        } else {
            auth()->user()->customMonsters()->create($data);
        }

        $this->resetCustomForm();
        $this->showCustomForm = false;
    }

    public function deleteCustomMonster(int $id): void
    {
        CustomMonster::query()
            ->where('user_id', auth()->id())
            ->where('id', $id)
            ->delete();

        if ($this->viewingMonsterId === $id) {
            $this->viewingMonsterId = null;
        }
    }

    public function getMonsterTypesProperty(): array
    {
        return SrdMonster::query()
            ->distinct()
            ->whereNotNull('type')
            ->pluck('type')
            ->sort()
            ->values()
            ->toArray();
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.library.monster-library', [
            'monsters' => $this->getMonsters(),
            'viewingMonster' => $this->getViewingMonsterProperty(),
            'monsterTypes' => $this->monsterTypes,
        ])->title('Monster Library');
    }

    private function resetCustomForm(): void
    {
        $this->editingCustomMonsterId = null;
        $this->customName = '';
        $this->customSize = 'Medium';
        $this->customType = '';
        $this->customAlignment = '';
        $this->customArmorClass = 10;
        $this->customHitPoints = 1;
        $this->customHitDice = '';
        $this->customChallengeRating = null;
        $this->customXp = null;
        $this->customStrength = 10;
        $this->customDexterity = 10;
        $this->customConstitution = 10;
        $this->customIntelligence = 10;
        $this->customWisdom = 10;
        $this->customCharisma = 10;
        $this->customLanguages = '';
        $this->customNotes = '';
    }

    /**
     * @return array{id: int, name: string, source: string, type: ?string, cr: ?float, xp: ?int, ac: int, hp: int, size: ?string}
     */
    private function formatMonster(SrdMonster|CustomMonster $monster, string $source): array
    {
        return [
            'id' => $monster->id,
            'name' => $monster->name,
            'source' => $source,
            'type' => $monster->type,
            'cr' => $monster->challenge_rating,
            'xp' => $monster->xp,
            'ac' => $monster->armor_class,
            'hp' => $monster->hit_points ?? $monster->hit_points,
            'size' => $monster->size,
        ];
    }
}
