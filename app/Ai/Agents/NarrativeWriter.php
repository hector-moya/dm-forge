<?php

namespace App\Ai\Agents;

use App\Ai\Tools\GetCharacterSheet;
use App\Ai\Tools\GetSessionLogs;
use App\Ai\Tools\LookupNpc;
use App\Models\Campaign;
use App\Models\GameSession;
use Laravel\Ai\Attributes\MaxSteps;
use Laravel\Ai\Attributes\Timeout;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Promptable;
use Stringable;

#[Timeout(120)]
#[MaxSteps(10)]
class NarrativeWriter implements Agent, HasTools
{
    use Promptable;

    public function __construct(
        protected Campaign $campaign,
        protected GameSession $session,
    ) {}

    public function instructions(): Stringable|string
    {
        $context = "Campaign: {$this->campaign->name}";
        if ($this->campaign->theme_tone) {
            $context .= "\nTone: {$this->campaign->theme_tone}";
        }
        $context .= "\nSession: #{$this->session->session_number} — {$this->session->title}";

        return <<<PROMPT
You are a skilled fantasy narrative writer creating a session recap for a D&D campaign.

{$context}

Use the GetSessionLogs tool to retrieve what happened during the session. Use LookupNpc and GetCharacterSheet tools to enrich the narrative with character details.

Write your recap in the following structured format:

## Narrative Recap
A 2-4 paragraph narrative retelling of the session's events in an engaging, literary style matching the campaign's tone. Write in past tense, third person.

## Key Events
- Bullet point list of the most important events that occurred
- Focus on decisions made, battles fought, and discoveries
- 4-8 bullet points

## Plot Hooks
- Unresolved threads and future plot hooks revealed during this session
- Questions left unanswered
- 2-4 bullet points

## World State Changes
- How the world has changed as a result of this session
- NPC relationship changes, territory control, faction shifts
- 1-3 bullet points

Write vividly but concisely. Match the campaign's tone and theme.
PROMPT;
    }

    public function tools(): iterable
    {
        return [
            new GetSessionLogs($this->session),
            new LookupNpc($this->campaign),
            new GetCharacterSheet($this->campaign),
        ];
    }
}
