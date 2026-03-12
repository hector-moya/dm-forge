<?php

namespace App\Ai\Concerns;

use App\Models\Campaign;
use App\Models\Lore;
use App\Models\WorldRule;

/**
 * Provides a standardised campaign context string for AI agent instructions.
 * Requires the consuming class to have a `protected Campaign $campaign` property.
 */
trait HasCampaignContext
{
    protected function buildCampaignContext(): string
    {
        /** @var Campaign $campaign */
        $campaign = $this->campaign;

        $context = "Campaign: {$campaign->name}";

        if ($campaign->premise) {
            $context .= "\nPremise: {$campaign->premise}";
        }

        if ($campaign->theme_tone) {
            $context .= "\nTone: {$campaign->theme_tone}";
        }

        if ($campaign->lore) {
            $context .= "\nLore: {$campaign->lore}";
        } elseif ($campaign->relationLoaded('lores') && $campaign->lores->isNotEmpty()) {
            $loreText = $campaign->lores->map(fn (Lore $l) => "{$l->name}: {$l->description}")->implode("\n");
            $context .= "\nLore:\n{$loreText}";
        }

        if ($campaign->world_rules) {
            $context .= "\nWorld Rules: {$campaign->world_rules}";
        } elseif ($campaign->relationLoaded('worldRules') && $campaign->worldRules->isNotEmpty()) {
            $rulesText = $campaign->worldRules->map(fn (WorldRule $r) => "{$r->name}: {$r->description}")->implode("\n");
            $context .= "\nWorld Rules:\n{$rulesText}";
        }

        return $context;
    }
}
