<?php

use App\Ai\Agents\NarrativeWriter;
use App\Models\GameSession;
use Livewire\Component;

new class extends Component
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

        $maxAttempts = 2;

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            try {
                $writer = new NarrativeWriter($this->session->campaign, $this->session);
                $response = $writer->prompt(
                    "Generate a complete session recap for session #{$this->session->session_number}: {$this->session->title}"
                );

                $this->parseAndSaveRecap($response->text);

                break;
            } catch (\Throwable $e) {
                if ($attempt === $maxAttempts) {
                    $this->session->update([
                        'generated_narrative' => null,
                    ]);

                    $this->dispatch('recap-error', message: $e->getMessage());
                }
            }
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
