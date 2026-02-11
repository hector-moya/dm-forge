<?php

namespace App\Services;

use App\Models\Campaign;

class CampaignExporter
{
    public function toMarkdown(Campaign $campaign): string
    {
        $campaign->load(['factions', 'locations', 'npcs', 'characters', 'gameSessions']);

        $md = "# {$campaign->name}\n\n";

        if ($campaign->premise) {
            $md .= "## Premise\n\n{$campaign->premise}\n\n";
        }

        if ($campaign->lore) {
            $md .= "## Lore\n\n{$campaign->lore}\n\n";
        }

        if ($campaign->theme_tone) {
            $md .= "**Theme & Tone:** {$campaign->theme_tone}\n\n";
        }

        if ($campaign->world_rules) {
            $md .= "## World Rules\n\n{$campaign->world_rules}\n\n";
        }

        if ($campaign->special_mechanics) {
            $md .= "## Special Mechanics\n\n";
            foreach ($campaign->special_mechanics as $mechanic) {
                $md .= "- {$mechanic}\n";
            }
            $md .= "\n";
        }

        if ($campaign->factions->isNotEmpty()) {
            $md .= "## Factions\n\n";
            foreach ($campaign->factions as $faction) {
                $md .= "### {$faction->name}\n\n";
                if ($faction->alignment) {
                    $md .= "**Alignment:** {$faction->alignment}\n\n";
                }
                if ($faction->description) {
                    $md .= "{$faction->description}\n\n";
                }
                if ($faction->goals) {
                    $md .= "**Goals:** {$faction->goals}\n\n";
                }
            }
        }

        if ($campaign->locations->isNotEmpty()) {
            $md .= "## Locations\n\n";
            foreach ($campaign->locations as $location) {
                $md .= "### {$location->name}";
                if ($location->region) {
                    $md .= " ({$location->region})";
                }
                $md .= "\n\n";
                if ($location->description) {
                    $md .= "{$location->description}\n\n";
                }
            }
        }

        if ($campaign->npcs->isNotEmpty()) {
            $md .= "## NPCs\n\n";
            foreach ($campaign->npcs as $npc) {
                $md .= "### {$npc->name}";
                if ($npc->role) {
                    $md .= " — {$npc->role}";
                }
                $md .= $npc->is_alive ? '' : ' [Dead]';
                $md .= "\n\n";
                if ($npc->description) {
                    $md .= "{$npc->description}\n\n";
                }
                if ($npc->personality) {
                    $md .= "**Personality:** {$npc->personality}\n\n";
                }
                if ($npc->motivation) {
                    $md .= "**Motivation:** {$npc->motivation}\n\n";
                }
            }
        }

        if ($campaign->characters->isNotEmpty()) {
            $md .= "## Characters\n\n";
            foreach ($campaign->characters as $character) {
                $md .= "### {$character->name}";
                if ($character->player_name) {
                    $md .= " (Player: {$character->player_name})";
                }
                $md .= "\n\n";
                $md .= '- **Class:** '.($character->class ?? 'Unknown')."\n";
                $md .= '- **Level:** '.($character->level ?? '?')."\n";
                $md .= "- **HP:** {$character->hp_current}/{$character->hp_max}\n";
                $md .= "- **AC:** {$character->armor_class}\n";
                $md .= "- **Alignment:** {$character->alignment_label}\n\n";
            }
        }

        if ($campaign->gameSessions->isNotEmpty()) {
            $md .= "## Sessions\n\n";
            foreach ($campaign->gameSessions->sortBy('session_number') as $session) {
                $md .= "### Session #{$session->session_number}: {$session->title}\n\n";
                $md .= '**Status:** '.ucfirst($session->status)."\n\n";
                if ($session->setup_text) {
                    $md .= "{$session->setup_text}\n\n";
                }
            }
        }

        return $md;
    }
}
