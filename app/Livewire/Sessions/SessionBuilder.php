<?php

namespace App\Livewire\Sessions;

use App\Models\Campaign;
use App\Models\CustomLoot;
use App\Models\CustomMonster;
use App\Models\GameSession;
use App\Models\SrdEquipment;
use App\Models\SrdMagicItem;
use App\Models\SrdMonster;
use App\Services\EncounterBalancer;
use Livewire\Component;

class SessionBuilder extends Component
{
    public Campaign $campaign;

    public ?GameSession $session = null;

    // Session fields
    public string $title = '';

    public int $session_number = 1;

    public string $type = 'sequential';

    public string $status = 'draft';

    public string $setup_text = '';

    public string $dm_notes = '';

    // Scene modal form
    public bool $showSceneForm = false;

    public ?int $editingSceneId = null;

    public string $sceneTitle = '';

    public string $sceneDescription = '';

    public string $sceneNotes = '';

    // Encounter modal form
    public bool $showEncounterForm = false;

    public ?int $editingEncounterId = null;

    public ?int $encounterSceneId = null;

    public string $encounterName = '';

    public string $encounterDescription = '';

    public string $encounterEnvironment = '';

    // Monster modal form
    public bool $showMonsterForm = false;

    public ?int $addingMonsterToEncounterId = null;

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

    // Branch modal form
    public bool $showBranchForm = false;

    public ?int $editingBranchId = null;

    public ?int $branchSceneId = null;

    public string $branchLabel = '';

    public string $branchDescription = '';

    // Consequence modal form
    public bool $showConsequenceForm = false;

    public ?int $addingConsequenceToBranchId = null;

    public string $consequenceType = 'immediate';

    public string $consequenceDescription = '';

    // Loot modal form
    public bool $showLootForm = false;

    public ?int $addingLootToEncounterId = null;

    public ?int $addingLootToSceneId = null;

    public string $lootSearchQuery = '';

    public string $lootSource = 'equipment';

    public ?int $selectedLootId = null;

    public string $selectedLootType = '';

    public int $lootQuantity = 1;

    public string $lootNotes = '';

    public function mount(?Campaign $campaign = null, ?GameSession $session = null): void
    {
        if ($session && $session->exists) {
            $this->session = $session;
            $this->campaign = $session->campaign;

            abort_unless($this->campaign->user_id === auth()->id(), 403);

            $this->title = $session->title;
            $this->session_number = $session->session_number;
            $this->type = $session->type;
            $this->status = $session->status;
            $this->setup_text = $session->setup_text ?? '';
            $this->dm_notes = $session->dm_notes ?? '';
        } else {
            abort_unless($campaign && $campaign->user_id === auth()->id(), 403);

            $this->campaign = $campaign;
            $this->session_number = $campaign->gameSessions()->max('session_number') + 1 ?: 1;
        }
    }

    // ── Session CRUD ────────────────────────────────────────────────────

    public function saveSession(): void
    {
        $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'session_number' => ['required', 'integer', 'min:1'],
            'type' => ['required', 'in:one_shot,sequential'],
            'status' => ['required', 'in:draft,prepared,running,completed'],
            'setup_text' => ['nullable', 'string', 'max:10000'],
            'dm_notes' => ['nullable', 'string', 'max:10000'],
        ]);

        $data = [
            'title' => $this->title,
            'session_number' => $this->session_number,
            'type' => $this->type,
            'status' => $this->status,
            'setup_text' => $this->setup_text ?: null,
            'dm_notes' => $this->dm_notes ?: null,
        ];

        if ($this->session) {
            $this->session->update($data);
            session()->flash('message', 'Session updated.');
        } else {
            $this->session = $this->campaign->gameSessions()->create($data);
            session()->flash('message', 'Session created.');
        }

        $this->redirect(route('sessions.edit', $this->session), navigate: true);
    }

    public function deleteSession(): void
    {
        if ($this->session) {
            $campaignId = $this->session->campaign_id;
            $this->session->delete();
            session()->flash('message', 'Session deleted.');
            $this->redirect(route('campaigns.sessions', $campaignId), navigate: true);
        }
    }

    // ── Scene CRUD ──────────────────────────────────────────────────────

    public function openSceneForm(?int $sceneId = null): void
    {
        $this->resetSceneForm();
        $this->showSceneForm = true;

        if ($sceneId) {
            $scene = $this->session->scenes()->findOrFail($sceneId);
            $this->editingSceneId = $scene->id;
            $this->sceneTitle = $scene->title;
            $this->sceneDescription = $scene->description ?? '';
            $this->sceneNotes = $scene->notes ?? '';
        }
    }

    public function saveScene(): void
    {
        $this->validate([
            'sceneTitle' => ['required', 'string', 'max:255'],
            'sceneDescription' => ['nullable', 'string', 'max:5000'],
            'sceneNotes' => ['nullable', 'string', 'max:5000'],
        ]);

        $data = [
            'title' => $this->sceneTitle,
            'description' => $this->sceneDescription ?: null,
            'notes' => $this->sceneNotes ?: null,
        ];

        if ($this->editingSceneId) {
            $this->session->scenes()->findOrFail($this->editingSceneId)->update($data);
        } else {
            $maxSort = $this->session->scenes()->max('sort_order') ?? 0;
            $this->session->scenes()->create(array_merge($data, ['sort_order' => $maxSort + 1]));
        }

        $this->resetSceneForm();
    }

    public function deleteScene(int $sceneId): void
    {
        $this->session->scenes()->findOrFail($sceneId)->delete();
    }

    private function resetSceneForm(): void
    {
        $this->showSceneForm = false;
        $this->editingSceneId = null;
        $this->sceneTitle = '';
        $this->sceneDescription = '';
        $this->sceneNotes = '';
    }

    // ── Encounter CRUD ──────────────────────────────────────────────────

    public function openEncounterForm(?int $sceneId = null, ?int $encounterId = null): void
    {
        $this->resetEncounterForm();
        $this->showEncounterForm = true;
        $this->encounterSceneId = $sceneId;

        if ($encounterId) {
            $encounter = $this->session->encounters()->findOrFail($encounterId);
            $this->editingEncounterId = $encounter->id;
            $this->encounterSceneId = $encounter->scene_id;
            $this->encounterName = $encounter->name;
            $this->encounterDescription = $encounter->description ?? '';
            $this->encounterEnvironment = $encounter->environment ?? '';
        }
    }

    public function saveEncounter(): void
    {
        $this->validate([
            'encounterName' => ['required', 'string', 'max:255'],
            'encounterDescription' => ['nullable', 'string', 'max:5000'],
            'encounterEnvironment' => ['nullable', 'string', 'max:255'],
        ]);

        $data = [
            'name' => $this->encounterName,
            'description' => $this->encounterDescription ?: null,
            'environment' => $this->encounterEnvironment ?: null,
            'difficulty' => 'medium',
            'scene_id' => $this->encounterSceneId,
        ];

        if ($this->editingEncounterId) {
            $this->session->encounters()->findOrFail($this->editingEncounterId)->update($data);
        } else {
            $this->session->encounters()->create($data);
        }

        $this->resetEncounterForm();
    }

    public function deleteEncounter(int $encounterId): void
    {
        $this->session->encounters()->findOrFail($encounterId)->delete();
    }

    private function resetEncounterForm(): void
    {
        $this->showEncounterForm = false;
        $this->editingEncounterId = null;
        $this->encounterSceneId = null;
        $this->encounterName = '';
        $this->encounterDescription = '';
        $this->encounterEnvironment = '';
    }

    // ── Monster CRUD ────────────────────────────────────────────────────

    public function openMonsterForm(int $encounterId): void
    {
        $this->resetMonsterForm();
        $this->showMonsterForm = true;
        $this->addingMonsterToEncounterId = $encounterId;
    }

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

    public function saveMonster(): void
    {
        $this->validate([
            'monsterName' => ['required', 'string', 'max:255'],
            'monsterHpMax' => ['required', 'integer', 'min:1'],
            'monsterAc' => ['required', 'integer', 'min:1'],
            'monsterCount' => ['required', 'integer', 'min:1', 'max:20'],
        ]);

        $encounter = $this->session->encounters()->findOrFail($this->addingMonsterToEncounterId);

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

        $this->resetMonsterForm();
    }

    public function deleteMonster(int $monsterId): void
    {
        $this->session->encounters->each(function ($enc) use ($monsterId) {
            $enc->monsters()->where('id', $monsterId)->delete();
        });
    }

    private function resetMonsterForm(): void
    {
        $this->showMonsterForm = false;
        $this->addingMonsterToEncounterId = null;
        $this->monsterSource = 'srd';
        $this->monsterSearchQuery = '';
        $this->selectedSrdMonsterId = null;
        $this->selectedCustomMonsterId = null;
        $this->monsterName = '';
        $this->monsterHpMax = 10;
        $this->monsterAc = 10;
        $this->monsterCount = 1;
        $this->monsterCr = null;
        $this->monsterXp = null;
    }

    // ── Branch Option CRUD ──────────────────────────────────────────────

    public function openBranchForm(?int $sceneId = null, ?int $branchId = null): void
    {
        $this->resetBranchForm();
        $this->showBranchForm = true;
        $this->branchSceneId = $sceneId;

        if ($branchId) {
            $branch = $this->session->branchOptions()->findOrFail($branchId);
            $this->editingBranchId = $branch->id;
            $this->branchSceneId = $branch->scene_id;
            $this->branchLabel = $branch->label;
            $this->branchDescription = $branch->description ?? '';
        }
    }

    public function saveBranch(): void
    {
        $this->validate([
            'branchLabel' => ['required', 'string', 'max:255'],
            'branchDescription' => ['nullable', 'string', 'max:5000'],
        ]);

        $data = [
            'label' => $this->branchLabel,
            'description' => $this->branchDescription ?: null,
            'scene_id' => $this->branchSceneId,
        ];

        if ($this->editingBranchId) {
            $this->session->branchOptions()->findOrFail($this->editingBranchId)->update($data);
        } else {
            $this->session->branchOptions()->create($data);
        }

        $this->resetBranchForm();
    }

    public function deleteBranch(int $branchId): void
    {
        $this->session->branchOptions()->findOrFail($branchId)->delete();
    }

    private function resetBranchForm(): void
    {
        $this->showBranchForm = false;
        $this->editingBranchId = null;
        $this->branchSceneId = null;
        $this->branchLabel = '';
        $this->branchDescription = '';
    }

    // ── Consequence CRUD ────────────────────────────────────────────────

    public function openConsequenceForm(int $branchId): void
    {
        $this->resetConsequenceForm();
        $this->showConsequenceForm = true;
        $this->addingConsequenceToBranchId = $branchId;
    }

    public function saveConsequence(): void
    {
        $this->validate([
            'consequenceType' => ['required', 'in:immediate,delayed,meta'],
            'consequenceDescription' => ['required', 'string', 'max:2000'],
        ]);

        $branch = $this->session->branchOptions()->findOrFail($this->addingConsequenceToBranchId);
        $branch->consequences()->create([
            'type' => $this->consequenceType,
            'description' => $this->consequenceDescription,
        ]);

        $this->resetConsequenceForm();
    }

    public function deleteConsequence(int $consequenceId): void
    {
        $this->session->branchOptions->each(function ($branch) use ($consequenceId) {
            $branch->consequences()->where('id', $consequenceId)->delete();
        });
    }

    private function resetConsequenceForm(): void
    {
        $this->showConsequenceForm = false;
        $this->addingConsequenceToBranchId = null;
        $this->consequenceType = 'immediate';
        $this->consequenceDescription = '';
    }

    // ── Loot CRUD ───────────────────────────────────────────────────────

    public function openLootForm(?int $encounterId = null, ?int $sceneId = null): void
    {
        $this->resetLootForm();
        $this->showLootForm = true;
        $this->addingLootToEncounterId = $encounterId;
        $this->addingLootToSceneId = $sceneId;
    }

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

    public function saveLoot(): void
    {
        $this->validate([
            'selectedLootId' => ['required', 'integer'],
            'selectedLootType' => ['required', 'string', 'in:srd_equipment,srd_magic_item,custom_loot'],
            'lootQuantity' => ['required', 'integer', 'min:1'],
        ]);

        $lootableType = match ($this->selectedLootType) {
            'srd_equipment' => SrdEquipment::class,
            'srd_magic_item' => SrdMagicItem::class,
            'custom_loot' => CustomLoot::class,
        };

        $data = [
            'lootable_type' => $lootableType,
            'lootable_id' => $this->selectedLootId,
            'quantity' => $this->lootQuantity,
            'notes' => $this->lootNotes ?: null,
        ];

        if ($this->addingLootToEncounterId) {
            $encounter = $this->session->encounters()->findOrFail($this->addingLootToEncounterId);
            $encounter->loot()->create($data);
        } elseif ($this->addingLootToSceneId) {
            $scene = $this->session->scenes()->findOrFail($this->addingLootToSceneId);
            $scene->loot()->create($data);
        }

        $this->resetLootForm();
    }

    public function deleteLoot(string $context, int $lootId): void
    {
        if ($context === 'encounter') {
            $this->session->encounters->each(fn ($enc) => $enc->loot()->where('id', $lootId)->delete());
        } else {
            $this->session->scenes->each(fn ($scene) => $scene->loot()->where('id', $lootId)->delete());
        }
    }

    private function resetLootForm(): void
    {
        $this->showLootForm = false;
        $this->addingLootToEncounterId = null;
        $this->addingLootToSceneId = null;
        $this->lootSearchQuery = '';
        $this->lootSource = 'equipment';
        $this->selectedLootId = null;
        $this->selectedLootType = '';
        $this->lootQuantity = 1;
        $this->lootNotes = '';
    }

    // ── Encounter Balancing ─────────────────────────────────────────────

    /**
     * @return array<int, \App\DataTransferObjects\EncounterDifficulty>
     */
    public function getEncounterDifficultiesProperty(): array
    {
        if (! $this->session) {
            return [];
        }

        $characters = $this->campaign->characters;
        if ($characters->isEmpty()) {
            return [];
        }

        $balancer = app(EncounterBalancer::class);
        $difficulties = [];

        foreach ($this->session->encounters()->with('monsters')->get() as $encounter) {
            $difficulties[$encounter->id] = $balancer->calculate($encounter, $characters);
        }

        return $difficulties;
    }

    public function render(): \Illuminate\View\View
    {
        $scenes = $this->session
            ? $this->session->scenes()->orderBy('sort_order')->get()
            : collect();

        $encounters = $this->session
            ? $this->session->encounters()->with(['monsters', 'loot.lootable'])->orderBy('sort_order')->get()
            : collect();

        $branches = $this->session
            ? $this->session->branchOptions()->with('consequences')->orderBy('sort_order')->get()
            : collect();

        $scenesWithLoot = $this->session
            ? $this->session->scenes()->with('loot.lootable')->get()->keyBy('id')
            : collect();

        return view('livewire.sessions.session-builder', [
            'scenes' => $scenes,
            'encounters' => $encounters,
            'branches' => $branches,
            'scenesWithLoot' => $scenesWithLoot,
            'encounterDifficulties' => $this->encounterDifficulties,
        ])->title($this->session ? __('Edit Session') : __('New Session'));
    }
}
