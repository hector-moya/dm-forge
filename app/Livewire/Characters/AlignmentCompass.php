<?php

namespace App\Livewire\Characters;

use App\Models\Character;
use Livewire\Component;

class AlignmentCompass extends Component
{
    public Character $character;

    public int $goodEvilScore;

    public int $lawChaosScore;

    // Manual adjustment form
    public string $actionDescription = '';

    public int $goodEvilDelta = 0;

    public int $lawChaosDelta = 0;

    public function mount(Character $character): void
    {
        abort_unless($character->campaign->user_id === auth()->id(), 403);

        $this->character = $character;
        $this->goodEvilScore = $character->good_evil_score;
        $this->lawChaosScore = $character->law_chaos_score;
    }

    public function recordEvent(): void
    {
        $this->validate([
            'actionDescription' => ['required', 'string', 'max:1000'],
            'goodEvilDelta' => ['required', 'integer', 'min:-5', 'max:5'],
            'lawChaosDelta' => ['required', 'integer', 'min:-5', 'max:5'],
        ]);

        $this->character->alignmentEvents()->create([
            'action_description' => $this->actionDescription,
            'good_evil_delta' => $this->goodEvilDelta,
            'law_chaos_delta' => $this->lawChaosDelta,
            'dm_overridden' => false,
        ]);

        // Update character scores, clamped to -10..+10
        $newGE = max(-10, min(10, $this->character->good_evil_score + $this->goodEvilDelta));
        $newLC = max(-10, min(10, $this->character->law_chaos_score + $this->lawChaosDelta));

        $this->character->update([
            'good_evil_score' => $newGE,
            'law_chaos_score' => $newLC,
            'alignment_label' => $this->computeAlignmentLabel($newGE, $newLC),
        ]);

        $this->goodEvilScore = $newGE;
        $this->lawChaosScore = $newLC;
        $this->actionDescription = '';
        $this->goodEvilDelta = 0;
        $this->lawChaosDelta = 0;

        $this->character->refresh();
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

        return "$lawChaos $goodEvil";
    }

    public function render()
    {
        return view('livewire.characters.alignment-compass', [
            'events' => $this->character->alignmentEvents()
                ->latest()
                ->limit(20)
                ->get(),
        ])->title(__('Alignment').' — '.$this->character->name);
    }
}
