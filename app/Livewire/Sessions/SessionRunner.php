<?php

namespace App\Livewire\Sessions;

use App\Ai\Agents\AlignmentAdvisor;
use App\Models\Character;
use App\Models\EncounterMonster;
use App\Models\GameSession;
use Livewire\Component;

class SessionRunner extends Component
{
    public GameSession $session;

    // Initiative tracker
    public array $combatants = [];

    public int $currentTurnIndex = 0;

    public bool $inCombat = false;

    // Add combatant form
    public bool $showAddCombatant = false;

    public string $combatantType = 'character';

    public ?int $selectedCharacterId = null;

    public ?int $selectedEncounterId = null;

    public string $combatantName = '';

    public int $combatantInitiative = 10;

    // Combat panel — selected combatant
    public ?int $selectedCombatantIndex = null;

    // Quick log
    public string $logEntry = '';

    public string $logType = 'narrative';

    // Decision recorder
    public bool $showDecisionModal = false;

    public array $decisionCharacterIds = [];

    public string $decisionAction = '';

    public ?array $aiSuggestion = null;

    public bool $loadingAiSuggestion = false;

    // Puzzle hint tracking (puzzleId => highest revealed tier)
    public array $revealedHints = [];

    // Conditions list
    public array $conditionOptions = [
        'blinded', 'charmed', 'deafened', 'frightened', 'grappled',
        'incapacitated', 'invisible', 'paralyzed', 'petrified',
        'poisoned', 'prone', 'restrained', 'stunned', 'unconscious',
    ];

    public function mount(GameSession $session): void
    {
        abort_unless($session->campaign->user_id === auth()->id(), 403);

        $this->session = $session;

        if ($session->status === 'prepared') {
            $session->update(['status' => 'running', 'started_at' => now()]);
            $this->session->refresh();
        }
    }

    // ── Initiative ────────────────────────────────────────────────────

    public function addCharacterToCombat(int $characterId): void
    {
        $character = $this->session->campaign->characters()->findOrFail($characterId);

        if (collect($this->combatants)->where('source_type', 'character')->where('source_id', $characterId)->isNotEmpty()) {
            return;
        }

        $this->combatants[] = [
            'name' => $character->name,
            'initiative' => 0,
            'hp_max' => $character->hp_max,
            'hp_current' => $character->hp_current,
            'armor_class' => $character->armor_class,
            'conditions' => [],
            'source_type' => 'character',
            'source_id' => $character->id,
            'is_pc' => true,
        ];

        $this->sortCombatants();
    }

    public function addMonstersToCombat(int $encounterId): void
    {
        $encounter = $this->session->encounters()->with('monsters')->findOrFail($encounterId);

        foreach ($encounter->monsters as $monster) {
            if (collect($this->combatants)->where('source_type', 'monster')->where('source_id', $monster->id)->isNotEmpty()) {
                continue;
            }

            $this->combatants[] = [
                'name' => $monster->name,
                'initiative' => $monster->initiative ?? 0,
                'hp_max' => $monster->hp_max,
                'hp_current' => $monster->hp_current,
                'armor_class' => $monster->armor_class,
                'conditions' => $monster->conditions ?? [],
                'source_type' => 'monster',
                'source_id' => $monster->id,
                'is_pc' => false,
            ];
        }

        $this->sortCombatants();
    }

    public function addCustomCombatant(): void
    {
        $this->validate([
            'combatantName' => ['required', 'string', 'max:255'],
            'combatantInitiative' => ['required', 'integer'],
        ]);

        $this->combatants[] = [
            'name' => $this->combatantName,
            'initiative' => $this->combatantInitiative,
            'hp_max' => 10,
            'hp_current' => 10,
            'armor_class' => 10,
            'conditions' => [],
            'source_type' => 'custom',
            'source_id' => null,
            'is_pc' => false,
        ];

        $this->combatantName = '';
        $this->combatantInitiative = 10;
        $this->showAddCombatant = false;
        $this->sortCombatants();
    }

    public function setInitiative(int $index, int $value): void
    {
        if (isset($this->combatants[$index])) {
            $this->combatants[$index]['initiative'] = $value;
            $this->sortCombatants();
        }
    }

    public function removeCombatant(int $index): void
    {
        unset($this->combatants[$index]);
        $this->combatants = array_values($this->combatants);

        if ($this->currentTurnIndex >= count($this->combatants)) {
            $this->currentTurnIndex = 0;
        }

        if ($this->selectedCombatantIndex === $index) {
            $this->selectedCombatantIndex = null;
        }
    }

    public function startCombat(): void
    {
        $this->inCombat = true;
        $this->currentTurnIndex = 0;
        $this->sortCombatants();
    }

    public function nextTurn(): void
    {
        if (empty($this->combatants)) {
            return;
        }

        $this->currentTurnIndex = ($this->currentTurnIndex + 1) % count($this->combatants);
    }

    public function endCombat(): void
    {
        $this->inCombat = false;
        $this->currentTurnIndex = 0;
        $this->syncMonstersToDb();
    }

    private function sortCombatants(): void
    {
        usort($this->combatants, fn ($a, $b) => $b['initiative'] <=> $a['initiative']);
    }

    // ── Combat Panel ──────────────────────────────────────────────────

    public function selectCombatant(int $index): void
    {
        $this->selectedCombatantIndex = $index;
    }

    public function adjustHp(int $index, int $amount): void
    {
        if (! isset($this->combatants[$index])) {
            return;
        }

        $current = $this->combatants[$index]['hp_current'] + $amount;
        $this->combatants[$index]['hp_current'] = max(0, min($current, $this->combatants[$index]['hp_max']));
    }

    public function healFull(int $index): void
    {
        if (isset($this->combatants[$index])) {
            $this->combatants[$index]['hp_current'] = $this->combatants[$index]['hp_max'];
        }
    }

    public function toggleCondition(int $index, string $condition): void
    {
        if (! isset($this->combatants[$index])) {
            return;
        }

        $conditions = $this->combatants[$index]['conditions'];

        if (in_array($condition, $conditions)) {
            $conditions = array_values(array_diff($conditions, [$condition]));
        } else {
            $conditions[] = $condition;
        }

        $this->combatants[$index]['conditions'] = $conditions;
    }

    private function syncMonstersToDb(): void
    {
        foreach ($this->combatants as $combatant) {
            if ($combatant['source_type'] === 'monster' && $combatant['source_id']) {
                EncounterMonster::where('id', $combatant['source_id'])->update([
                    'hp_current' => $combatant['hp_current'],
                    'initiative' => $combatant['initiative'],
                    'conditions' => $combatant['conditions'],
                ]);
            }
        }
    }

    // ── Scene Revealer ────────────────────────────────────────────────

    public function toggleSceneReveal(int $sceneId): void
    {
        $scene = $this->session->scenes()->findOrFail($sceneId);
        $scene->update(['is_revealed' => ! $scene->is_revealed]);
    }

    // ── Branch Options ────────────────────────────────────────────────

    public function chooseBranch(int $branchId): void
    {
        $branch = $this->session->branchOptions()->findOrFail($branchId);
        $branch->update(['chosen' => ! $branch->chosen]);
    }

    // ── Quick Log ─────────────────────────────────────────────────────

    public function addLogEntry(): void
    {
        $this->validate([
            'logEntry' => ['required', 'string', 'max:2000'],
            'logType' => ['required', 'in:narrative,decision,combat,note'],
        ]);

        $this->session->sessionLogs()->create([
            'entry' => $this->logEntry,
            'type' => $this->logType,
            'logged_at' => now(),
        ]);

        $this->logEntry = '';
    }

    // ── Decision Recorder ─────────────────────────────────────────────

    public function openDecisionModal(): void
    {
        $this->showDecisionModal = true;
        $this->decisionCharacterIds = [];
        $this->decisionAction = '';
        $this->aiSuggestion = null;
    }

    public function getAiSuggestion(): void
    {
        $this->validate([
            'decisionAction' => ['required', 'string', 'max:2000'],
        ]);

        $this->loadingAiSuggestion = true;

        try {
            $advisor = new AlignmentAdvisor($this->session->campaign);
            $response = $advisor->prompt($this->decisionAction);

            $this->aiSuggestion = $response->toArray();
        } catch (\Throwable) {
            $this->aiSuggestion = [
                'good_evil_delta' => 0,
                'law_chaos_delta' => 0,
                'reasoning' => 'AI suggestion unavailable. Please set values manually.',
                'tags' => [],
            ];
        }

        $this->loadingAiSuggestion = false;
    }

    public function confirmDecision(int $goodEvilDelta, int $lawChaosDelta): void
    {
        if (empty($this->decisionAction)) {
            return;
        }

        foreach ($this->decisionCharacterIds as $characterId) {
            $character = Character::find($characterId);
            if (! $character) {
                continue;
            }

            $character->alignmentEvents()->create([
                'game_session_id' => $this->session->id,
                'action_description' => $this->decisionAction,
                'good_evil_delta' => $goodEvilDelta,
                'law_chaos_delta' => $lawChaosDelta,
                'ai_suggested_ge' => $this->aiSuggestion['good_evil_delta'] ?? null,
                'ai_suggested_lc' => $this->aiSuggestion['law_chaos_delta'] ?? null,
                'dm_overridden' => ($this->aiSuggestion && (
                    $goodEvilDelta !== $this->aiSuggestion['good_evil_delta'] ||
                    $lawChaosDelta !== $this->aiSuggestion['law_chaos_delta']
                )),
                'tags' => $this->aiSuggestion['tags'] ?? [],
            ]);

            $newGE = max(-10, min(10, $character->good_evil_score + $goodEvilDelta));
            $newLC = max(-10, min(10, $character->law_chaos_score + $lawChaosDelta));

            $character->update([
                'good_evil_score' => $newGE,
                'law_chaos_score' => $newLC,
                'alignment_label' => $this->computeAlignmentLabel($newGE, $newLC),
            ]);
        }

        $this->session->sessionLogs()->create([
            'entry' => "Decision: {$this->decisionAction}",
            'type' => 'decision',
            'tags' => $this->aiSuggestion['tags'] ?? [],
            'logged_at' => now(),
        ]);

        $this->showDecisionModal = false;
        $this->aiSuggestion = null;
    }

    private function computeAlignmentLabel(int $ge, int $lc): string
    {
        $lawChaos = match (true) {
            $lc >= 4 => 'Lawful',
            $lc <= -4 => 'Chaotic',
            default => 'Neutral',
        };

        $goodEvil = match (true) {
            $ge >= 4 => 'Good',
            $ge <= -4 => 'Evil',
            default => 'Neutral',
        };

        if ($lawChaos === 'Neutral' && $goodEvil === 'Neutral') {
            return 'True Neutral';
        }

        return "{$lawChaos} {$goodEvil}";
    }

    // ── Puzzles ───────────────────────────────────────────────────────

    public function revealHint(int $puzzleId, int $tier): void
    {
        $currentTier = $this->revealedHints[$puzzleId] ?? 0;
        if ($tier > $currentTier && $tier <= 3) {
            $this->revealedHints[$puzzleId] = $tier;
        }
    }

    public function togglePuzzleSolved(int $puzzleId): void
    {
        $puzzle = $this->session->campaign->puzzles()->findOrFail($puzzleId);
        $puzzle->update(['is_solved' => ! $puzzle->is_solved]);
    }

    // ── Session End ───────────────────────────────────────────────────

    public function endSession(): void
    {
        $this->syncMonstersToDb();
        $this->session->update(['status' => 'completed', 'ended_at' => now()]);
        $this->session->refresh();
    }

    public function render()
    {
        $scenes = $this->session->scenes()->with('puzzles')->orderBy('sort_order')->get();

        $encounters = $this->session->encounters()
            ->with('monsters')
            ->orderBy('sort_order')
            ->get();

        $branches = $this->session->branchOptions()
            ->with('consequences')
            ->orderBy('sort_order')
            ->get();

        $logs = $this->session->sessionLogs()
            ->orderByDesc('logged_at')
            ->limit(50)
            ->get();

        $characters = $this->session->campaign->characters()->get();

        return view('livewire.sessions.session-runner', [
            'scenes' => $scenes,
            'encounters' => $encounters,
            'branches' => $branches,
            'logs' => $logs,
            'characters' => $characters,
        ])->title(__('Session').' #'.$this->session->session_number.' — '.$this->session->title);
    }
}
