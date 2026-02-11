<?php

namespace App\Livewire\Sessions;

use App\Models\Campaign;
use App\Models\GameSession;
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

    // Scene inline form
    public bool $showSceneForm = false;
    public ?int $editingSceneId = null;
    public string $sceneTitle = '';
    public string $sceneDescription = '';
    public string $sceneNotes = '';

    // Encounter inline form
    public bool $showEncounterForm = false;
    public ?int $editingEncounterId = null;
    public ?int $encounterSceneId = null;
    public string $encounterName = '';
    public string $encounterDescription = '';
    public string $encounterEnvironment = '';
    public string $encounterDifficulty = 'medium';

    // Monster inline form
    public bool $showMonsterForm = false;
    public ?int $addingMonsterToEncounterId = null;
    public string $monsterName = '';
    public int $monsterHpMax = 10;
    public int $monsterAc = 10;
    public int $monsterCount = 1;

    // Branch inline form
    public bool $showBranchForm = false;
    public ?int $editingBranchId = null;
    public ?int $branchSceneId = null;
    public string $branchLabel = '';
    public string $branchDescription = '';

    // Consequence inline
    public bool $showConsequenceForm = false;
    public ?int $addingConsequenceToBranchId = null;
    public string $consequenceType = 'immediate';
    public string $consequenceDescription = '';

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
            $this->encounterDifficulty = $encounter->difficulty ?? 'medium';
        }
    }

    public function saveEncounter(): void
    {
        $this->validate([
            'encounterName' => ['required', 'string', 'max:255'],
            'encounterDescription' => ['nullable', 'string', 'max:5000'],
            'encounterEnvironment' => ['nullable', 'string', 'max:255'],
            'encounterDifficulty' => ['required', 'in:easy,medium,hard,deadly'],
        ]);

        $data = [
            'name' => $this->encounterName,
            'description' => $this->encounterDescription ?: null,
            'environment' => $this->encounterEnvironment ?: null,
            'difficulty' => $this->encounterDifficulty,
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
        $this->encounterDifficulty = 'medium';
    }

    // ── Monster CRUD ────────────────────────────────────────────────────

    public function openMonsterForm(int $encounterId): void
    {
        $this->resetMonsterForm();
        $this->showMonsterForm = true;
        $this->addingMonsterToEncounterId = $encounterId;
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
        $this->monsterName = '';
        $this->monsterHpMax = 10;
        $this->monsterAc = 10;
        $this->monsterCount = 1;
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

    public function render()
    {
        $scenes = $this->session
            ? $this->session->scenes()->orderBy('sort_order')->get()
            : collect();

        $encounters = $this->session
            ? $this->session->encounters()->with('monsters')->orderBy('sort_order')->get()
            : collect();

        $branches = $this->session
            ? $this->session->branchOptions()->with('consequences')->orderBy('sort_order')->get()
            : collect();

        return view('livewire.sessions.session-builder', [
            'scenes' => $scenes,
            'encounters' => $encounters,
            'branches' => $branches,
        ])->title($this->session ? __('Edit Session') : __('New Session'));
    }
}
