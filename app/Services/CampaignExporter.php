<?php

namespace App\Services;

use App\Models\Campaign;
use App\Models\Character;
use App\Models\Faction;
use App\Models\GameSession;
use App\Models\Location;
use App\Models\Npc;
use Illuminate\Database\Eloquent\Collection;

class CampaignExporter
{
    public function toMarkdown(Campaign $campaign): string
    {
        $campaign->load(['factions', 'locations', 'npcs', 'characters', 'gameSessions', 'lores', 'worldRules', 'specialMechanics']);

        return implode('', [
            $this->formatCampaignHeader($campaign),
            $this->formatFactions($campaign->factions),
            $this->formatLocations($campaign->locations),
            $this->formatNpcs($campaign->npcs),
            $this->formatCharacters($campaign->characters),
            $this->formatSessions($campaign->gameSessions),
        ]);
    }

    private function formatCampaignHeader(Campaign $campaign): string
    {
        $md = "# {$campaign->name}\n\n";

        if ($campaign->premise) {
            $md .= "## Premise\n\n{$campaign->premise}\n\n";
        }

        if ($campaign->lores->isNotEmpty()) {
            $md .= "## Lore\n\n";
            foreach ($campaign->lores as $lore) {
                /** @var \App\Models\Lore $lore */
                $md .= "### {$lore->name}\n\n";
                if ($lore->description) {
                    $md .= "{$lore->description}\n\n";
                }
            }
        } elseif ($campaign->lore) {
            $md .= "## Lore\n\n{$campaign->lore}\n\n";
        }

        if ($campaign->theme_tone) {
            $md .= "**Theme & Tone:** {$campaign->theme_tone}\n\n";
        }

        if ($campaign->worldRules->isNotEmpty()) {
            $md .= "## World Rules\n\n";
            foreach ($campaign->worldRules as $rule) {
                /** @var \App\Models\WorldRule $rule */
                $md .= "### {$rule->name}\n\n";
                if ($rule->description) {
                    $md .= "{$rule->description}\n\n";
                }
            }
        } elseif ($campaign->world_rules) {
            $md .= "## World Rules\n\n{$campaign->world_rules}\n\n";
        }

        if ($campaign->specialMechanics->isNotEmpty()) {
            $md .= "## Special Mechanics\n\n";
            foreach ($campaign->specialMechanics as $mechanic) {
                /** @var \App\Models\SpecialMechanic $mechanic */
                $md .= "### {$mechanic->name}\n\n";
                if ($mechanic->description) {
                    $md .= "{$mechanic->description}\n\n";
                }
            }
        } elseif ($campaign->special_mechanics) {
            $md .= "## Special Mechanics\n\n";
            foreach ($campaign->special_mechanics as $mechanic) {
                $md .= "- {$mechanic}\n";
            }
            $md .= "\n";
        }

        return $md;
    }

    /**
     * @param  Collection<int, Faction>  $factions
     */
    private function formatFactions(Collection $factions): string
    {
        if ($factions->isEmpty()) {
            return '';
        }

        $md = "## Factions\n\n";

        foreach ($factions as $faction) {
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

        return $md;
    }

    /**
     * @param  Collection<int, Location>  $locations
     */
    private function formatLocations(Collection $locations): string
    {
        if ($locations->isEmpty()) {
            return '';
        }

        $md = "## Locations\n\n";

        foreach ($locations as $location) {
            $md .= "### {$location->name}";
            if ($location->region) {
                $md .= " ({$location->region})";
            }
            $md .= "\n\n";
            if ($location->description) {
                $md .= "{$location->description}\n\n";
            }
        }

        return $md;
    }

    /**
     * @param  Collection<int, Npc>  $npcs
     */
    private function formatNpcs(Collection $npcs): string
    {
        if ($npcs->isEmpty()) {
            return '';
        }

        $md = "## NPCs\n\n";

        foreach ($npcs as $npc) {
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

        return $md;
    }

    /**
     * @param  Collection<int, Character>  $characters
     */
    private function formatCharacters(Collection $characters): string
    {
        if ($characters->isEmpty()) {
            return '';
        }

        $md = "## Characters\n\n";

        foreach ($characters as $character) {
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

        return $md;
    }

    /**
     * @param  Collection<int, GameSession>  $sessions
     */
    private function formatSessions(Collection $sessions): string
    {
        if ($sessions->isEmpty()) {
            return '';
        }

        $md = "## Sessions\n\n";

        foreach ($sessions->sortBy('session_number') as $session) {
            $md .= "### Session #{$session->session_number}: {$session->title}\n\n";
            $md .= '**Status:** '.ucfirst($session->status)."\n\n";
            if ($session->setup_text) {
                $md .= "{$session->setup_text}\n\n";
            }
        }

        return $md;
    }
}
