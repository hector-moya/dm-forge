<?php

use App\Ai\Agents\PuzzleDesigner;
use App\Models\Scene;
use App\Services\EntityImageGenerator;
use Livewire\Component;

new class extends Component
{
    public Scene $scene;

    public int $sessionId;

    public bool $showForm = false;

    public ?int $editingSceneId = null;

    public string $title = '';

    public string $description = '';

    public string $notes = '';

    // Puzzle form
    public bool $showPuzzleForm = false;

    public ?int $editingPuzzleId = null;

    public string $puzzleName = '';

    public string $puzzleDescription = '';

    public string $puzzleSolution = '';

    public string $puzzleHint1 = '';

    public string $puzzleHint2 = '';

    public string $puzzleHint3 = '';

    public string $puzzleDifficulty = 'medium';

    public string $puzzleType = 'riddle';

    public string $puzzleNotes = '';

    // Puzzle generator
    public bool $showGeneratePuzzleModal = false;

    public string $generatePuzzleContext = '';

    public string $generatePuzzleDifficulty = 'medium';

    public string $generatePuzzleType = 'riddle';

    public bool $generatingPuzzle = false;

    // Encounter form
    public bool $showAddEncounterForm = false;

    public string $newEncounterName = '';

    public string $newEncounterDescription = '';

    public string $newEncounterEnvironment = '';

    // Branch form
    public bool $showAddBranchForm = false;

    public string $newBranchLabel = '';

    public string $newBranchDescription = '';

    public function openForm(?int $sceneId = null): void
    {
        $this->showForm = true;
        $this->editingSceneId = $sceneId;

        if ($sceneId) {
            $scene = Scene::query()->findOrFail($sceneId);
            $this->title = $scene->title;
            $this->description = $scene->description ?? '';
            $this->notes = $scene->notes ?? '';
        } else {
            $this->resetForm();
        }
    }

    public function save(): void
    {
        $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $data = [
            'title' => $this->title,
            'description' => $this->description ?: null,
            'notes' => $this->notes ?: null,
        ];

        if ($this->editingSceneId) {
            Scene::query()->findOrFail($this->editingSceneId)->update($data);
            \Flux::toast(__('Scene updated successfully'));
        } else {
            $maxSort = Scene::query()->where('game_session_id', $this->sessionId)->max('sort_order') ?? 0;
            Scene::query()->create(array_merge($data, [
                'game_session_id' => $this->sessionId,
                'sort_order' => $maxSort + 1,
            ]));
            \Flux::toast(__('Scene created successfully'));
        }

        $this->resetForm();
        $this->dispatch('$refresh');
    }

    public function delete(int $sceneId): void
    {
        Scene::query()->findOrFail($sceneId)->delete();
        \Flux::toast(__('Scene deleted successfully'));
        $this->dispatch('$refresh');
    }

    // ── Puzzle CRUD ─────────────────────────────────────────────────

    public function openPuzzleForm(?int $puzzleId = null): void
    {
        $this->resetPuzzleForm();
        $this->showPuzzleForm = true;

        if ($puzzleId) {
            $puzzle = $this->scene->puzzles()->findOrFail($puzzleId);
            $this->editingPuzzleId = $puzzle->id;
            $this->puzzleName = $puzzle->name;
            $this->puzzleDescription = $puzzle->description ?? '';
            $this->puzzleSolution = $puzzle->solution ?? '';
            $this->puzzleHint1 = $puzzle->hint_tier_1 ?? '';
            $this->puzzleHint2 = $puzzle->hint_tier_2 ?? '';
            $this->puzzleHint3 = $puzzle->hint_tier_3 ?? '';
            $this->puzzleDifficulty = $puzzle->difficulty;
            $this->puzzleType = $puzzle->puzzle_type;
            $this->puzzleNotes = $puzzle->notes ?? '';
        }
    }

    public function savePuzzle(): void
    {
        $this->validate([
            'puzzleName' => ['required', 'string', 'max:255'],
            'puzzleDescription' => ['required', 'string', 'max:10000'],
            'puzzleSolution' => ['required', 'string', 'max:5000'],
            'puzzleHint1' => ['nullable', 'string', 'max:2000'],
            'puzzleHint2' => ['nullable', 'string', 'max:2000'],
            'puzzleHint3' => ['nullable', 'string', 'max:2000'],
            'puzzleDifficulty' => ['required', 'in:easy,medium,hard'],
            'puzzleType' => ['required', 'in:riddle,logic,physical,cipher,pattern'],
            'puzzleNotes' => ['nullable', 'string', 'max:5000'],
        ]);

        $data = [
            'name' => $this->puzzleName,
            'description' => $this->puzzleDescription,
            'solution' => $this->puzzleSolution,
            'hint_tier_1' => $this->puzzleHint1 ?: null,
            'hint_tier_2' => $this->puzzleHint2 ?: null,
            'hint_tier_3' => $this->puzzleHint3 ?: null,
            'difficulty' => $this->puzzleDifficulty,
            'puzzle_type' => $this->puzzleType,
            'notes' => $this->puzzleNotes ?: null,
            'campaign_id' => $this->scene->gameSession->campaign_id,
        ];

        if ($this->editingPuzzleId) {
            $this->scene->puzzles()->findOrFail($this->editingPuzzleId)->update($data);
            \Flux::toast(__('Puzzle updated successfully'));
        } else {
            $this->scene->puzzles()->create($data);
            \Flux::toast(__('Puzzle added to scene'));
        }

        $this->resetPuzzleForm();
        $this->dispatch('$refresh');
    }

    public function deletePuzzle(int $puzzleId): void
    {
        $this->scene->puzzles()->findOrFail($puzzleId)->delete();
        \Flux::toast(__('Puzzle removed'));
        $this->dispatch('$refresh');
    }

    public function togglePuzzleSolved(int $puzzleId): void
    {
        $puzzle = $this->scene->puzzles()->findOrFail($puzzleId);
        $puzzle->update(['is_solved' => ! $puzzle->is_solved]);
        $this->dispatch('$refresh');
    }

    // ── Puzzle Generator ─────────────────────────────────────────────

    public function openGeneratePuzzleModal(): void
    {
        $this->showGeneratePuzzleModal = true;
        $this->generatePuzzleContext = '';
        $this->generatePuzzleDifficulty = 'medium';
        $this->generatePuzzleType = 'riddle';
        $this->generatingPuzzle = false;
    }

    public function generatePuzzle(): void
    {
        $this->generatingPuzzle = true;

        try {
            $campaign = $this->scene->gameSession->campaign;
            $designer = new PuzzleDesigner($campaign);

            $prompt = "Generate a {$this->generatePuzzleDifficulty} {$this->generatePuzzleType} puzzle.";
            if ($this->scene->title) {
                $prompt .= " Scene context: {$this->scene->title}.";
            }
            if ($this->scene->description) {
                $prompt .= " {$this->scene->description}";
            }
            if ($this->generatePuzzleContext) {
                $prompt .= " Additional context: {$this->generatePuzzleContext}";
            }

            $response = $designer->prompt($prompt);

            $this->showGeneratePuzzleModal = false;

            $this->resetPuzzleForm();
            $this->showPuzzleForm = true;
            $this->puzzleName = $response['name'] ?? '';
            $this->puzzleDescription = $response['description'] ?? '';
            $this->puzzleSolution = $response['solution'] ?? '';
            $this->puzzleHint1 = $response['hint_tier_1'] ?? '';
            $this->puzzleHint2 = $response['hint_tier_2'] ?? '';
            $this->puzzleHint3 = $response['hint_tier_3'] ?? '';
            $this->puzzleDifficulty = $this->generatePuzzleDifficulty;
            $this->puzzleType = $this->generatePuzzleType;

            \Flux::toast(__('Puzzle generated! Review and save below.'));
        } catch (\Throwable $e) {
            \Flux::toast(__('Puzzle generation failed: ') . $e->getMessage());
        }

        $this->generatingPuzzle = false;
    }

    // ── Image Generation ──────────────────────────────────────────────

    public function generateSceneImage(): void
    {
        try {
            $path = app(EntityImageGenerator::class)->generate(
                $this->scene, 'scene', null,
                fn (string $status) => $this->stream(to: 'imageStatus', content: $status, replace: true),
            );

            if ($path) {
                $this->scene->refresh();
                \Flux::toast(__('Scene image generated!'));
            } else {
                \Flux::toast(__('Image generation failed.'));
            }
        } catch (\Throwable $e) {
            \Flux::toast(__('Image generation failed: ').$e->getMessage());
        }
    }

    // ── Encounter CRUD ───────────────────────────────────────────────

    public function saveNewEncounter(): void
    {
        $this->validate([
            'newEncounterName' => ['required', 'string', 'max:255'],
            'newEncounterDescription' => ['nullable', 'string', 'max:5000'],
            'newEncounterEnvironment' => ['nullable', 'string', 'max:255'],
        ]);

        $this->scene->encounters()->create([
            'name' => $this->newEncounterName,
            'description' => $this->newEncounterDescription ?: null,
            'environment' => $this->newEncounterEnvironment ?: null,
            'difficulty' => 'medium',
            'game_session_id' => $this->scene->game_session_id,
        ]);

        \Flux::toast(__('Encounter added to scene'));
        $this->resetEncounterForm();
        $this->dispatch('$refresh');
    }

    // ── Branch CRUD ──────────────────────────────────────────────────

    public function saveNewBranch(): void
    {
        $this->validate([
            'newBranchLabel' => ['required', 'string', 'max:255'],
            'newBranchDescription' => ['nullable', 'string', 'max:5000'],
        ]);

        $maxSort = $this->scene->branchOptions()->max('sort_order') ?? 0;
        $this->scene->branchOptions()->create([
            'label' => $this->newBranchLabel,
            'description' => $this->newBranchDescription ?: null,
            'sort_order' => $maxSort + 1,
            'game_session_id' => $this->scene->game_session_id,
        ]);

        \Flux::toast(__('Branch option added'));
        $this->resetBranchForm();
        $this->dispatch('$refresh');
    }

    private function resetEncounterForm(): void
    {
        $this->showAddEncounterForm = false;
        $this->newEncounterName = '';
        $this->newEncounterDescription = '';
        $this->newEncounterEnvironment = '';
    }

    private function resetBranchForm(): void
    {
        $this->showAddBranchForm = false;
        $this->newBranchLabel = '';
        $this->newBranchDescription = '';
    }

    private function resetPuzzleForm(): void
    {
        $this->showPuzzleForm = false;
        $this->editingPuzzleId = null;
        $this->puzzleName = '';
        $this->puzzleDescription = '';
        $this->puzzleSolution = '';
        $this->puzzleHint1 = '';
        $this->puzzleHint2 = '';
        $this->puzzleHint3 = '';
        $this->puzzleDifficulty = 'medium';
        $this->puzzleType = 'riddle';
        $this->puzzleNotes = '';
    }

    private function resetForm(): void
    {
        $this->showForm = false;
        $this->editingSceneId = null;
        $this->title = '';
        $this->description = '';
        $this->notes = '';
    }
};
