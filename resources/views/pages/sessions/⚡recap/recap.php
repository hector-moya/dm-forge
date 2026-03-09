<?php

use App\Ai\Agents\NarrativeWriter;
use App\Models\GameSession;
use Laravel\Ai\Streaming\Events\TextDelta;
use Livewire\Component;

new class extends Component
{
    public GameSession $session;

    public bool $generating = false;

    // Log editing
    public ?int $editingLogId = null;

    public string $editLogEntry = '';

    public string $editLogType = 'narrative';

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

    public function startEditLog(int $logId): void
    {
        $log = $this->session->sessionLogs()->findOrFail($logId);
        $this->editingLogId = $log->id;
        $this->editLogEntry = $log->entry;
        $this->editLogType = $log->type;
    }

    public function cancelEditLog(): void
    {
        $this->editingLogId = null;
        $this->editLogEntry = '';
        $this->editLogType = 'narrative';
    }

    public function saveLog(): void
    {
        $this->validate([
            'editLogEntry' => ['required', 'string', 'max:2000'],
            'editLogType' => ['required', 'in:narrative,decision,combat,note'],
        ]);

        $log = $this->session->sessionLogs()->findOrFail($this->editingLogId);
        $log->update([
            'entry' => $this->editLogEntry,
            'type' => $this->editLogType,
        ]);

        $this->cancelEditLog();
    }

    public function deleteLog(int $logId): void
    {
        $this->session->sessionLogs()->findOrFail($logId)->delete();

        if ($this->editingLogId === $logId) {
            $this->cancelEditLog();
        }
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

        return view('pages.sessions.⚡recap.recap', [
            'logs' => $logs,
        ])->title(__('Recap').' — Session #'.$this->session->session_number);
    }
};
