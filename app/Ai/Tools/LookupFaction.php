<?php

namespace App\Ai\Tools;

use App\Models\Campaign;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class LookupFaction implements Tool
{
    public function __construct(
        protected Campaign $campaign,
    ) {}

    public function description(): Stringable|string
    {
        return 'Search for factions in the campaign by name or alignment. Returns faction details including goals, resources, and member count.';
    }

    public function handle(Request $request): Stringable|string
    {
        $query = $this->campaign->factions()->withCount('npcs');

        if ($name = $request->string('name')) {
            $query->where('name', 'like', "%{$name}%");
        }

        if ($alignment = $request->string('alignment')) {
            $query->where('alignment', 'like', "%{$alignment}%");
        }

        $factions = $query->limit(10)->get();

        if ($factions->isEmpty()) {
            return 'No factions found matching the search criteria.';
        }

        return $factions->map(function ($faction) {
            $info = "**{$faction->name}**";
            if ($faction->alignment) {
                $info .= " ({$faction->alignment})";
            }
            if ($faction->description) {
                $info .= "\n{$faction->description}";
            }
            if ($faction->goals) {
                $info .= "\nGoals: {$faction->goals}";
            }
            if ($faction->resources) {
                $info .= "\nResources: {$faction->resources}";
            }
            $info .= "\nMembers: {$faction->npcs_count}";

            return $info;
        })->implode("\n\n---\n\n");
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()->description('Faction name or partial name to search for'),
            'alignment' => $schema->string()->description('Alignment to filter factions by (e.g., Lawful Good, Chaotic Evil)'),
        ];
    }
}
