<?php

namespace App\Livewire\Sessions;

use App\Ai\Agents\NarrativeWriter;
use App\Models\GameSession;
use Livewire\Component;

class SessionRecap extends Component
{
    public GameSession $session;

    public bool $generating = false;

    public string $streamedText = '';

    public function mount(GameSession $session): void
    {
        abort_unless($session->campaign->user_id === auth()->id(), 403);

        $this->session = $session;
    }

    public function generateRecap(): void
    {
        $this->generating = true;

        try {
            $writer = new NarrativeWriter($this->session->campaign, $this->session);
            $response = $writer->prompt(
                "Generate a complete session recap for session #{$this->session->session_number}: {$this->session->title}"
            );

            $fullText = $response->text;
            $this->parseAndSaveRecap($fullText);
        } catch (\Throwable $e) {
            $this->session->update([
                'generated_narrative' => 'Recap generation failed: '.$e->getMessage(),
            ]);
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

    public function render()
    {
        $logs = $this->session->sessionLogs()
            ->orderBy('logged_at')
            ->get();

        return view('livewire.sessions.session-recap', [
            'logs' => $logs,
        ])->title(__('Recap').' — Session #'.$this->session->session_number);
    }
}
