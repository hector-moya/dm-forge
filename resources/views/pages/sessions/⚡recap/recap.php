<?php

use App\Ai\Agents\NarrativeWriter;
use App\Models\GameSession;
use Laravel\Ai\Streaming\Events\TextDelta;
use Livewire\Component;

new class extends Component
{
    public GameSession $session;

    public bool $generating = false;

    // View / Edit log
    public bool $showViewLogModal = false;

    public bool $showEditLogModal = false;

    public ?int $viewingLogId = null;

    public ?int $editingLogId = null;

    public string $editLogType = 'note';

    public array $editLogCharacterIds = [];

    public string $editLogEntry = '';

    public function mount(GameSession $session): void
    {
        abort_unless($session->campaign->user_id === auth()->id(), 403);

        $this->session = $session;
    }

    public function generateRecap(): void
    {
        set_time_limit(120);

        $this->generating = true;
        $fullText = '';

        try {
            $writer = new NarrativeWriter($this->session->campaign, $this->session);
            $stream = $writer->stream(
                "Generate a complete session recap for session #{$this->session->session_number}: {$this->session->title}"
            );

            foreach ($stream as $event) {
                if ($event instanceof TextDelta) {
                    $fullText .= $event->delta;
                    $this->stream(to: 'streamedRecap', content: e($event->delta));
                }
            }

            $this->parseAndSaveRecap($fullText);
        } catch (\Throwable $e) {
            $this->dispatch('recap-error', message: $e->getMessage());
        }

        $this->session->refresh();
        $this->generating = false;
    }

    private function parseAndSaveRecap(string $text): void
    {
        $sections = [
            'generated_narrative' => '',
            'generated_bullets' => '',
            'generated_hooks' => '',
            'generated_world_state' => '',
        ];

        $currentSection = 'generated_narrative';

        foreach (explode("\n", $text) as $line) {
            if (preg_match('/^##\s*Key Events/i', $line)) {
                $currentSection = 'generated_bullets';

                continue;
            }
            if (preg_match('/^##\s*Plot Hooks/i', $line)) {
                $currentSection = 'generated_hooks';

                continue;
            }
            if (preg_match('/^##\s*World State/i', $line)) {
                $currentSection = 'generated_world_state';

                continue;
            }
            if (preg_match('/^##\s*Narrative Recap/i', $line)) {
                $currentSection = 'generated_narrative';

                continue;
            }

            $sections[$currentSection] .= $line."\n";
        }

        $this->session->update(array_map('trim', $sections));
    }

    public function openViewLog(int $logId): void
    {
        $log = $this->session->sessionLogs()->findOrFail($logId);
        $this->viewingLogId = $log->id;
        $this->showViewLogModal = true;
    }

    public function openEditLog(int $logId): void
    {
        $log = $this->session->sessionLogs()->findOrFail($logId);
        $this->editingLogId = $log->id;
        $this->editLogType = $log->type;
        $this->editLogCharacterIds = $log->character_ids ?? [];
        $this->editLogEntry = $log->entry;
        $this->showEditLogModal = true;
    }

    public function toggleAllEditLogCharacters(): void
    {
        $allIds = $this->session->campaign->characters()->pluck('id')->all();
        $this->editLogCharacterIds = count($this->editLogCharacterIds) === count($allIds) ? [] : $allIds;
    }

    public function saveEditLog(): void
    {
        $this->validate([
            'editLogEntry' => ['required', 'string', 'max:2000'],
            'editLogType' => ['required', 'in:narrative,combat,decision,note'],
        ]);

        $log = $this->session->sessionLogs()->findOrFail($this->editingLogId);
        $log->update([
            'entry' => $this->editLogEntry,
            'type' => $this->editLogType,
            'character_ids' => $this->editLogCharacterIds ?: null,
        ]);

        $this->showEditLogModal = false;
        $this->editingLogId = null;
    }

    public function deleteLog(int $logId): void
    {
        $this->session->sessionLogs()->findOrFail($logId)->delete();
    }

    public function clearRecap(): void
    {
        $this->session->update([
            'generated_narrative' => null,
            'generated_bullets' => null,
            'generated_hooks' => null,
            'generated_world_state' => null,
        ]);
        $this->session->refresh();
    }

    public function render(): \Illuminate\View\View
    {
        $logs = $this->session->sessionLogs()
            ->orderBy('logged_at')
            ->get();

        $characters = $this->session->campaign->characters()->get();
        $allScenes = $this->session->scenes()->orderBy('sort_order')->get();

        return view('pages.sessions.⚡recap.recap', [
            'logs' => $logs,
            'characters' => $characters,
            'allScenes' => $allScenes,
        ])->title(__('Recap').' — Session #'.$this->session->session_number);
    }
};
