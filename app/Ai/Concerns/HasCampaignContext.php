<?php

namespace App\Ai\Concerns;

use App\Models\Campaign;

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
        }

        if ($campaign->world_rules) {
            $context .= "\nWorld Rules: {$campaign->world_rules}";
        }

        return $context;
    }
}
