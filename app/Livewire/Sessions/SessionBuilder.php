<?php

namespace App\Livewire\Sessions;

use App\Ai\Agents\SessionGenerator;
use App\Models\Campaign;
use App\Models\GameSession;
use App\Models\SrdMonster;
use Flux\Flux;
use Illuminate\Support\Facades\DB;
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

    // Add scene form
    public bool $showAddSceneForm = false;

    public string $newSceneTitle = '';

    public string $newSceneDescription = '';

    public string $newSceneNotes = '';

    // Add encounter form (for standalone encounters)
    public bool $showAddEncounterForm = false;

    public string $newEncounterName = '';

    public string $newEncounterDescription = '';

    public string $newEncounterEnvironment = '';

    // Add branch form (for standalone branches)
    public bool $showAddBranchForm = false;

    public string $newBranchLabel = '';

    public string $newBranchDescription = '';

    // Generate session with AI
    public bool $showGenerateModal = false;

    public string $generateContext = '';

    public bool $generating = false;

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
            Flux::toast(__('Session updated successfully'));
        } else {
            $this->session = $this->campaign->gameSessions()->create($data);
            Flux::toast(__('Session created successfully'));
        }

        $this->redirect(route('sessions.edit', $this->session), navigate: true);
    }

    public function deleteSession(): void
    {
        if ($this->session) {
            $campaignId = $this->session->campaign_id;
            $this->session->delete();
            Flux::toast(__('Session deleted successfully'));
            $this->redirect(route('campaigns.sessions', $campaignId), navigate: true);
        }
    }

    // ── Add Scene ────────────────────────────────────────────────────

    public function openAddSceneForm(): void
    {
        $this->showAddSceneForm = true;
        $this->newSceneTitle = '';
        $this->newSceneDescription = '';
        $this->newSceneNotes = '';
    }

    public function saveNewScene(): void
    {
        $this->validate([
            'newSceneTitle' => ['required', 'string', 'max:255'],
            'newSceneDescription' => ['nullable', 'string', 'max:5000'],
            'newSceneNotes' => ['nullable', 'string', 'max:5000'],
        ]);

        $maxSort = $this->session->scenes()->max('sort_order') ?? 0;
        $this->session->scenes()->create([
            'title' => $this->newSceneTitle,
            'description' => $this->newSceneDescription ?: null,
            'notes' => $this->newSceneNotes ?: null,
            'sort_order' => $maxSort + 1,
        ]);

        Flux::toast(__('Scene created successfully'));
        $this->showAddSceneForm = false;
    }

    // ── Add Standalone Encounter ────────────────────────────────────────

    public function openAddEncounterForm(): void
    {
        $this->showAddEncounterForm = true;
        $this->newEncounterName = '';
        $this->newEncounterDescription = '';
        $this->newEncounterEnvironment = '';
    }

    public function saveNewEncounter(): void
    {
        $this->validate([
            'newEncounterName' => ['required', 'string', 'max:255'],
            'newEncounterDescription' => ['nullable', 'string', 'max:5000'],
            'newEncounterEnvironment' => ['nullable', 'string', 'max:255'],
        ]);

        $this->session->encounters()->create([
            'name' => $this->newEncounterName,
            'description' => $this->newEncounterDescription ?: null,
            'environment' => $this->newEncounterEnvironment ?: null,
            'difficulty' => 'medium',
            'scene_id' => null,
        ]);

        Flux::toast(__('Encounter created successfully'));
        $this->showAddEncounterForm = false;
    }

    // ── Add Standalone Branch ────────────────────────────────────────

    public function openAddBranchForm(): void
    {
        $this->showAddBranchForm = true;
        $this->newBranchLabel = '';
        $this->newBranchDescription = '';
    }

    public function saveNewBranch(): void
    {
        $this->validate([
            'newBranchLabel' => ['required', 'string', 'max:255'],
            'newBranchDescription' => ['nullable', 'string', 'max:5000'],
        ]);

        $this->session->branchOptions()->create([
            'label' => $this->newBranchLabel,
            'description' => $this->newBranchDescription ?: null,
            'scene_id' => null,
        ]);

        Flux::toast(__('Branch option created successfully'));
        $this->showAddBranchForm = false;
    }

    // ── Generate Session with AI ────────────────────────────────────────

    public function openGenerateModal(): void
    {
        $this->generateContext = '';
        $this->showGenerateModal = true;
    }

    public function generateSession(): void
    {
        $this->generating = true;

        try {
            $generator = new SessionGenerator($this->campaign);
            $response = $generator->prompt(
                $this->generateContext
                    ? "Generate a session based on this concept: {$this->generateContext}"
                    : 'Generate a creative and engaging session for this campaign.'
            );

            DB::transaction(function () use ($response) {
                // Create session if it doesn't exist
                if (! $this->session) {
                    $this->session = $this->campaign->gameSessions()->create([
                        'title' => $response['title'] ?? 'Untitled Session',
                        'session_number' => $this->session_number,
                        'type' => $this->type,
                        'status' => 'draft',
                        'setup_text' => $response['setup_text'] ?? null,
                        'dm_notes' => $response['dm_notes'] ?? null,
                    ]);
                } else {
                    // Update existing session metadata
                    $this->session->update([
                        'title' => $response['title'] ?? $this->session->title,
                        'setup_text' => $response['setup_text'] ?? $this->session->setup_text,
                        'dm_notes' => $response['dm_notes'] ?? $this->session->dm_notes,
                    ]);
                }

                $maxSort = $this->session->scenes()->max('sort_order') ?? 0;

                foreach ($response['scenes'] ?? [] as $sceneData) {
                    $maxSort++;

                    // Append NPC names to scene notes
                    $notes = $sceneData['notes'] ?? '';
                    if (! empty($sceneData['npcs_involved'])) {
                        $npcList = implode(', ', $sceneData['npcs_involved']);
                        $notes .= "\n\nNPCs in this scene: {$npcList}";
                    }

                    $scene = $this->session->scenes()->create([
                        'title' => $sceneData['title'],
                        'description' => $sceneData['description'] ?? null,
                        'notes' => trim($notes) ?: null,
                        'sort_order' => $maxSort,
                    ]);

                    // Create encounters with monsters
                    foreach ($sceneData['encounters'] ?? [] as $encounterIndex => $encounterData) {
                        $encounter = $this->session->encounters()->create([
                            'scene_id' => $scene->id,
                            'name' => $encounterData['name'],
                            'description' => $encounterData['description'] ?? null,
                            'environment' => $encounterData['environment'] ?? null,
                            'difficulty' => $encounterData['difficulty'] ?? 'medium',
                            'sort_order' => $encounterIndex,
                        ]);

                        foreach ($encounterData['monsters'] ?? [] as $monsterData) {
                            $srdMonster = SrdMonster::where('name', $monsterData['name'])->first();
                            $quantity = $monsterData['quantity'] ?? 1;

                            for ($i = 0; $i < $quantity; $i++) {
                                $encounter->monsters()->create([
                                    'name' => $monsterData['name'],
                                    'srd_monster_id' => $srdMonster?->id,
                                    'hp_max' => $srdMonster?->hit_points ?? 1,
                                    'armor_class' => $srdMonster?->armor_class ?? 10,
                                    'challenge_rating' => $srdMonster?->challenge_rating,
                                    'xp' => $srdMonster?->xp,
                                ]);
                            }
                        }
                    }

                    // Create branch options
                    foreach ($sceneData['branch_options'] ?? [] as $branchIndex => $branchData) {
                        $this->session->branchOptions()->create([
                            'scene_id' => $scene->id,
                            'label' => $branchData['label'],
                            'description' => $branchData['description'] ?? null,
                            'sort_order' => $branchIndex,
                        ]);
                    }

                    // Create puzzle if present
                    if (! empty($sceneData['puzzle']) && ! empty($sceneData['puzzle']['name'])) {
                        $this->campaign->puzzles()->create([
                            'scene_id' => $scene->id,
                            'name' => $sceneData['puzzle']['name'],
                            'description' => $sceneData['puzzle']['description'],
                            'solution' => $sceneData['puzzle']['solution'],
                            'hint_tier_1' => $sceneData['puzzle']['hint_tier_1'] ?? null,
                            'hint_tier_2' => $sceneData['puzzle']['hint_tier_2'] ?? null,
                            'hint_tier_3' => $sceneData['puzzle']['hint_tier_3'] ?? null,
                            'difficulty' => $sceneData['puzzle']['difficulty'] ?? 'medium',
                            'puzzle_type' => $sceneData['puzzle']['puzzle_type'] ?? 'riddle',
                        ]);
                    }
                }
            });

            $this->showGenerateModal = false;
            Flux::toast(__('Session generated successfully!'));
            $this->redirect(route('sessions.edit', $this->session), navigate: true);
        } catch (\Throwable $e) {
            Flux::toast(__('Generation failed: ').$e->getMessage());
        }

        $this->generating = false;
    }

    public function render(): \Illuminate\View\View
    {
        $scenes = $this->session
            ? $this->session->scenes()->with(['encounters.monsters', 'branchOptions.consequences', 'puzzles'])->orderBy('sort_order')->get()
            : collect();

        $unscopedEncounters = $this->session
            ? $this->session->encounters()->whereNull('scene_id')->with('monsters')->orderBy('sort_order')->get()
            : collect();

        $unscopedBranches = $this->session
            ? $this->session->branchOptions()->whereNull('scene_id')->with('consequences')->orderBy('sort_order')->get()
            : collect();

        return view('livewire.sessions.session-builder', [
            'scenes' => $scenes,
            'unscopedEncounters' => $unscopedEncounters,
            'unscopedBranches' => $unscopedBranches,
        ])->title($this->session ? __('Edit Session') : __('New Session'));
    }
}
