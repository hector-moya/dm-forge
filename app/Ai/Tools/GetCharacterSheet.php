<?php

namespace App\Ai\Tools;

use App\Models\Campaign;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class GetCharacterSheet implements Tool
{
    public function __construct(
        protected Campaign $campaign,
    ) {}

    public function description(): Stringable|string
    {
        return 'Look up a character by name and return their full stat sheet, alignment scores, and recent alignment history.';
    }

    public function handle(Request $request): Stringable|string
    {
        $name = $request->string('name');

        $character = $this->campaign->characters()
            ->where('name', 'like', "%{$name}%")
            ->first();

        if (! $character) {
            return "No character found matching '{$name}'.";
        }

        $info = "**{$character->name}**";
        if ($character->player_name) {
            $info .= " (Player: {$character->player_name})";
        }
        $info .= "\nClass: ".($character->class ?? 'Unknown').' | Level: '.($character->level ?? '?');
        $info .= "\nHP: {$character->hp_current}/{$character->hp_max} | AC: {$character->armor_class}";
        $info .= "\nAlignment: {$character->alignment_label} (GE: {$character->good_evil_score}, LC: {$character->law_chaos_score})";

        if ($character->stats) {
            $stats = collect($character->stats)
                ->map(fn ($v, $k) => strtoupper($k).": {$v}")
                ->implode(' | ');
            $info .= "\nStats: {$stats}";
        }

        $recentEvents = $character->alignmentEvents()
            ->latest()
            ->limit(5)
            ->get();

        if ($recentEvents->isNotEmpty()) {
            $info .= "\n\nRecent Alignment Events:";
            foreach ($recentEvents as $event) {
                $ge = $event->good_evil_delta >= 0 ? "+{$event->good_evil_delta}" : $event->good_evil_delta;
                $lc = $event->law_chaos_delta >= 0 ? "+{$event->law_chaos_delta}" : $event->law_chaos_delta;
                $info .= "\n- {$event->action_description} (GE: {$ge}, LC: {$lc})";
            }
        }

        return $info;
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()->required()->description('Character name or partial name to search for'),
        ];
    }
}
