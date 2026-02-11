<?php

namespace App\Ai\Tools;

use App\Models\Campaign;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class LookupNpc implements Tool
{
    public function __construct(
        protected Campaign $campaign,
    ) {}

    public function description(): Stringable|string
    {
        return 'Search for NPCs in the campaign by name or faction. Returns NPC details including personality, motivation, and current status.';
    }

    public function handle(Request $request): Stringable|string
    {
        $query = $this->campaign->npcs()->with(['faction', 'location']);

        if ($name = $request->string('name')) {
            $query->where('name', 'like', "%{$name}%");
        }

        if ($factionName = $request->string('faction')) {
            $query->whereHas('faction', fn ($q) => $q->where('name', 'like', "%{$factionName}%"));
        }

        $npcs = $query->limit(10)->get();

        if ($npcs->isEmpty()) {
            return 'No NPCs found matching the search criteria.';
        }

        return $npcs->map(function ($npc) {
            $info = "**{$npc->name}**";
            if ($npc->role) {
                $info .= " ({$npc->role})";
            }
            $info .= $npc->is_alive ? ' [Alive]' : ' [Dead]';
            if ($npc->faction) {
                $info .= "\nFaction: {$npc->faction->name}";
            }
            if ($npc->location) {
                $info .= "\nLocation: {$npc->location->name}";
            }
            if ($npc->personality) {
                $info .= "\nPersonality: {$npc->personality}";
            }
            if ($npc->motivation) {
                $info .= "\nMotivation: {$npc->motivation}";
            }

            return $info;
        })->implode("\n\n---\n\n");
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()->description('NPC name or partial name to search for'),
            'faction' => $schema->string()->description('Faction name to filter NPCs by'),
        ];
    }
}
