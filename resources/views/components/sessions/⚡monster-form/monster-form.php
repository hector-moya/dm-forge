<?php

use App\Models\CustomMonster;
use App\Models\Encounter;
use App\Models\SrdMonster;
use Livewire\Component;

new class extends Component
{
    public int $encounterId;

    public bool $showForm = true;

    public string $monsterSource = 'srd';

    public string $monsterSearchQuery = '';

    public ?int $selectedSrdMonsterId = null;

    public ?int $selectedCustomMonsterId = null;

    public string $monsterName = '';

    public int $monsterHpMax = 10;

    public int $monsterAc = 10;

    public int $monsterCount = 1;

    public ?float $monsterCr = null;

    public ?int $monsterXp = null;

    public function selectSrdMonster(int $id): void
    {
        $monster = SrdMonster::query()->findOrFail($id);
        $this->selectedSrdMonsterId = $monster->id;
        $this->selectedCustomMonsterId = null;
        $this->monsterName = $monster->name;
        $this->monsterHpMax = $monster->hit_points;
        $this->monsterAc = $monster->armor_class;
        $this->monsterCr = $monster->challenge_rating;
        $this->monsterXp = $monster->xp;
    }

    public function selectCustomMonster(int $id): void
    {
        $monster = CustomMonster::query()
            ->where('user_id', auth()->id())
            ->findOrFail($id);
        $this->selectedCustomMonsterId = $monster->id;
        $this->selectedSrdMonsterId = null;
        $this->monsterName = $monster->name;
        $this->monsterHpMax = $monster->hit_points;
        $this->monsterAc = $monster->armor_class;
        $this->monsterCr = $monster->challenge_rating;
        $this->monsterXp = $monster->xp;
    }

    public function getMonsterSearchResultsProperty(): array
    {
        if (strlen($this->monsterSearchQuery) < 2) {
            return [];
        }

        if ($this->monsterSource === 'custom') {
            return CustomMonster::query()
                ->where('user_id', auth()->id())
                ->search($this->monsterSearchQuery)
                ->limit(10)
                ->get()
                ->map(fn ($m) => ['id' => $m->id, 'name' => $m->name, 'cr' => $m->challenge_rating, 'hp' => $m->hit_points, 'ac' => $m->armor_class, 'source' => 'custom'])
                ->toArray();
        }

        return SrdMonster::query()
            ->search($this->monsterSearchQuery)
            ->limit(10)
            ->get()
            ->map(fn ($m) => ['id' => $m->id, 'name' => $m->name, 'cr' => $m->challenge_rating, 'hp' => $m->hit_points, 'ac' => $m->armor_class, 'source' => 'srd'])
            ->toArray();
    }

    public function save(): void
    {
        $this->validate([
            'monsterName' => ['required', 'string', 'max:255'],
            'monsterHpMax' => ['required', 'integer', 'min:1'],
            'monsterAc' => ['required', 'integer', 'min:1'],
            'monsterCount' => ['required', 'integer', 'min:1', 'max:20'],
        ]);

        $encounter = Encounter::query()->findOrFail($this->encounterId);

        $srdMonsterId = $this->selectedSrdMonsterId;
        $customMonsterId = $this->selectedCustomMonsterId;
        $stats = null;

        // Auto-persist manually-entered monsters to custom_monsters
        if (! $srdMonsterId && ! $customMonsterId) {
            $customMonster = auth()->user()->customMonsters()->create([
                'name' => $this->monsterName,
                'armor_class' => $this->monsterAc,
                'hit_points' => $this->monsterHpMax,
                'challenge_rating' => $this->monsterCr,
                'xp' => $this->monsterXp,
            ]);
            $customMonsterId = $customMonster->id;
        }

        // Build stats from source model
        if ($srdMonsterId) {
            $source = SrdMonster::query()->find($srdMonsterId);
            if ($source) {
                $stats = $this->extractStats($source);
            }
        } elseif ($customMonsterId) {
            $source = CustomMonster::query()->find($customMonsterId);
            if ($source) {
                $stats = $this->extractStats($source);
            }
        }

        for ($i = 0; $i < $this->monsterCount; $i++) {
            $name = $this->monsterCount > 1
                ? $this->monsterName.' '.($i + 1)
                : $this->monsterName;

            $encounter->monsters()->create([
                'name' => $name,
                'hp_max' => $this->monsterHpMax,
                'hp_current' => $this->monsterHpMax,
                'armor_class' => $this->monsterAc,
                'srd_monster_id' => $srdMonsterId,
                'custom_monster_id' => $customMonsterId,
                'challenge_rating' => $this->monsterCr,
                'xp' => $this->monsterXp,
                'stats' => $stats,
            ]);
        }

        \Flux::toast(__('Monsters added successfully'));
        $this->showForm = false;
        $this->dispatch('monster-form-closed');
        $this->dispatch('$refresh');
    }

    /**
     * @return array<string, mixed>
     */
    private function extractStats(SrdMonster|CustomMonster $source): array
    {
        return [
            'strength' => $source->strength ?? 10,
            'dexterity' => $source->dexterity ?? 10,
            'constitution' => $source->constitution ?? 10,
            'intelligence' => $source->intelligence ?? 10,
            'wisdom' => $source->wisdom ?? 10,
            'charisma' => $source->charisma ?? 10,
        ];
    }

    public function close(): void
    {
        $this->showForm = false;
        $this->dispatch('monster-form-closed');
    }
};
