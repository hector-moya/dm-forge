<?php

use App\Models\CustomMonster;
use App\Models\Encounter;
use App\Models\SrdMonster;
use Flux;
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

        for ($i = 0; $i < $this->monsterCount; $i++) {
            $name = $this->monsterCount > 1
                ? $this->monsterName.' '.($i + 1)
                : $this->monsterName;

            $encounter->monsters()->create([
                'name' => $name,
                'hp_max' => $this->monsterHpMax,
                'hp_current' => $this->monsterHpMax,
                'armor_class' => $this->monsterAc,
                'srd_monster_id' => $this->selectedSrdMonsterId,
                'custom_monster_id' => $this->selectedCustomMonsterId,
                'challenge_rating' => $this->monsterCr,
                'xp' => $this->monsterXp,
            ]);
        }

        Flux::toast(__('Monsters added successfully'));
        $this->showForm = false;
        $this->dispatch('monster-form-closed');
        $this->dispatch('$refresh');
    }

    public function close(): void
    {
        $this->showForm = false;
        $this->dispatch('monster-form-closed');
    }
};
