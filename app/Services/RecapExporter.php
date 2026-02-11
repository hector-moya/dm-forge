<?php

namespace App\Services;

use App\Models\GameSession;

class RecapExporter
{
    public function toMarkdown(GameSession $session): string
    {
        $session->load(['campaign', 'sessionLogs']);

        $md = "# Session #{$session->session_number}: {$session->title}\n\n";
        $md .= "**Campaign:** {$session->campaign->name}\n";
        $md .= "**Status:** ".ucfirst($session->status)."\n";

        if ($session->started_at) {
            $md .= "**Date:** {$session->started_at->format('F j, Y')}\n";
        }

        $md .= "\n---\n\n";

        if ($session->generated_narrative) {
            $md .= "## Narrative Recap\n\n{$session->generated_narrative}\n\n";
        }

        if ($session->generated_bullets) {
            $md .= "## Key Events\n\n{$session->generated_bullets}\n\n";
        }

        if ($session->generated_hooks) {
            $md .= "## Plot Hooks\n\n{$session->generated_hooks}\n\n";
        }

        if ($session->generated_world_state) {
            $md .= "## World State Changes\n\n{$session->generated_world_state}\n\n";
        }

        if ($session->sessionLogs->isNotEmpty()) {
            $md .= "## Session Log\n\n";
            foreach ($session->sessionLogs->sortBy('logged_at') as $log) {
                $time = $log->logged_at?->format('H:i:s') ?? '';
                $type = strtoupper($log->type);
                $md .= "- [{$time}] **{$type}** — {$log->entry}\n";
            }
            $md .= "\n";
        }

        return $md;
    }
}
