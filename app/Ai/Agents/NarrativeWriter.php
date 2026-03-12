<?php

namespace App\Ai\Agents;

use App\Ai\Concerns\HasCampaignContext;
use App\Models\Campaign;
use App\Models\Character;
use App\Models\Encounter;
use App\Models\EncounterMonster;
use App\Models\EncounterNpc;
use App\Models\GameSession;
use App\Models\Npc;
use App\Models\Scene;
use App\Models\SessionLog;
use Laravel\Ai\Attributes\MaxTokens;
use Laravel\Ai\Attributes\Timeout;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Promptable;
use Stringable;

#[Timeout(120)]
#[MaxTokens(4096)]
class NarrativeWriter implements Agent
{
    use HasCampaignContext, Promptable;

    public function __construct(
        protected Campaign $campaign,
        protected GameSession $session,
    ) {}

    public function instructions(): Stringable|string
    {
        $campaignContext = $this->buildCampaignContext();
        $sessionContext = $this->buildSessionContext();

        return <<<PROMPT
        You are a masterful fantasy narrative writer in the style of J.R.R. Tolkien, creating an epic session recap for a D&D campaign. Write with rich, evocative prose, vivid descriptions of landscapes and atmospheres, a sense of mythic grandeur, and attention to the inner thoughts and motivations of characters.

        {$campaignContext}

        Session: #{$this->session->session_number} — {$this->session->title}

        {$sessionContext}

        Using ALL of the context above — every scene, encounter, monster, NPC, character, and log entry — write a recap in the following structured format:

        ## Narrative Recap
        Write a vivid narrative of approximately 3,000-5,000 characters (500-800 words). Retell the session's events in past tense, third person, weaving together the scenes, encounters, decisions, and character moments into a cohesive tale. Describe the settings, the tension of combat, the weight of decisions, and the bonds between characters.

        ## Key Events
        - Bullet point list of the most important events that occurred
        - Focus on decisions made, battles fought, and discoveries
        - Include who was involved and why it mattered
        - 4-8 bullet points

        ## Plot Hooks
        - Unresolved threads and future plot hooks revealed during this session
        - Questions left unanswered, mysteries deepened
        - 2-4 bullet points

        ## World State Changes
        - How the world has changed as a result of this session
        - NPC relationship changes, territory control, faction shifts
        - 1-3 bullet points

        Write with Tolkien's grandeur and depth. Every detail from the session context matters.
        PROMPT;
    }

    protected function buildSessionContext(): string
    {
        $context = '';

        $scenes = $this->session->scenes()
            ->with(['encounters.monsters', 'encounters.npcs'])
            ->orderBy('sort_order')
            ->get();

        if ($scenes->isNotEmpty()) {
            $context .= "## Session Scenes\n";
            foreach ($scenes as $i => $scene) {
                /** @var Scene $scene */
                $num = $i + 1;
                $context .= "{$num}. {$scene->title}";
                if ($scene->description) {
                    $context .= " — {$scene->description}";
                }
                $context .= "\n";
                if ($scene->notes) {
                    $context .= "   DM Notes: {$scene->notes}\n";
                }

                foreach ($scene->encounters as $encounter) {
                    /** @var Encounter $encounter */
                    $context .= "   Encounter: {$encounter->name}";
                    if ($encounter->difficulty) {
                        $context .= " [{$encounter->difficulty}]";
                    }
                    if ($encounter->environment) {
                        $context .= " — Environment: {$encounter->environment}";
                    }
                    $context .= "\n";
                    if ($encounter->description) {
                        $context .= "     {$encounter->description}\n";
                    }

                    foreach ($encounter->monsters as $monster) {
                        /** @var EncounterMonster $monster */
                        $context .= "     Monster: {$monster->name}";
                        if ($monster->challenge_rating) {
                            $context .= " (CR {$monster->challenge_rating})";
                        }
                        $context .= " — HP {$monster->hp_current}/{$monster->hp_max}, AC {$monster->armor_class}";
                        $context .= "\n";
                    }

                    foreach ($encounter->npcs as $encounterNpc) {
                        /** @var EncounterNpc $encounterNpc */
                        $context .= "     NPC: {$encounterNpc->name}";
                        $context .= " — HP {$encounterNpc->hp_current}/{$encounterNpc->hp_max}, AC {$encounterNpc->armor_class}";
                        if ($encounterNpc->notes) {
                            $context .= " — {$encounterNpc->notes}";
                        }
                        $context .= "\n";
                    }
                }
            }
            $context .= "\n";
        }

        $logs = $this->session->sessionLogs()->orderBy('logged_at')->get();
        if ($logs->isNotEmpty()) {
            $context .= "## Session Logs (chronological)\n";
            foreach ($logs as $log) {
                /** @var SessionLog $log */
                $time = $log->logged_at?->format('H:i:s') ?? 'N/A';
                $type = strtoupper($log->type);
                $context .= "[{$time}] [{$type}] {$log->entry}\n";
            }
            $context .= "\n";
        }

        $characters = $this->campaign->characters()->get();
        if ($characters->isNotEmpty()) {
            $context .= "## Party Characters\n";
            foreach ($characters as $character) {
                /** @var Character $character */
                $line = "- {$character->name}";
                if ($character->player_name) {
                    $line .= " (Player: {$character->player_name})";
                }
                $line .= ' — '.($character->class ?? 'Unknown').' Lv'.$character->level;
                $line .= ", HP {$character->hp_current}/{$character->hp_max}, AC {$character->armor_class}";
                if ($character->alignment_label) {
                    $line .= ", {$character->alignment_label}";
                }
                if ($character->race) {
                    $line .= ", {$character->race}";
                }
                if ($character->background) {
                    $line .= " ({$character->background})";
                }
                $context .= "{$line}\n";
            }
            $context .= "\n";
        }

        $npcs = $this->campaign->npcs()->with(['faction', 'location'])->get();
        if ($npcs->isNotEmpty()) {
            $context .= "## Campaign NPCs\n";
            foreach ($npcs as $npc) {
                /** @var Npc $npc */
                $line = "- {$npc->name}";
                if ($npc->role) {
                    $line .= " ({$npc->role})";
                }
                if ($npc->faction) {
                    $line .= " — Faction: {$npc->faction->name}";
                }
                if ($npc->location) {
                    $line .= " — Location: {$npc->location->name}";
                }
                if ($npc->personality) {
                    $line .= " — {$npc->personality}";
                }
                if ($npc->motivation) {
                    $line .= " — Motivation: {$npc->motivation}";
                }
                $context .= "{$line}\n";
            }
            $context .= "\n";
        }

        return $context;
    }
}
