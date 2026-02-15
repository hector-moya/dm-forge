<?php

namespace App\Ai\Tools;

use App\Models\SrdMonster;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class LookupSrdMonster implements Tool
{
    public function description(): Stringable|string
    {
        return 'Search for D&D 5e SRD monsters by name, type, or challenge rating. Returns monster stats including name, type, CR, HP, and AC.';
    }

    public function handle(Request $request): Stringable|string
    {
        $query = SrdMonster::query();

        if ($name = $request->string('name')) {
            $query->search($name);
        }

        if ($type = $request->string('type')) {
            $query->byType($type);
        }

        if ($maxCr = $request->float('max_challenge_rating')) {
            $query->where('challenge_rating', '<=', $maxCr);
        }

        $monsters = $query->orderBy('challenge_rating')->limit(15)->get();

        if ($monsters->isEmpty()) {
            return 'No SRD monsters found matching the search criteria.';
        }

        return $monsters->map(function (SrdMonster $monster) {
            $info = "**{$monster->name}** ({$monster->type})";
            $info .= "\nCR: {$monster->challenge_rating} | HP: {$monster->hit_points} | AC: {$monster->armor_class}";
            $info .= " | XP: {$monster->xp}";
            if ($monster->size) {
                $info .= "\nSize: {$monster->size}";
            }
            if ($monster->alignment) {
                $info .= " | Alignment: {$monster->alignment}";
            }

            return $info;
        })->implode("\n\n---\n\n");
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()->description('Monster name or partial name to search for'),
            'type' => $schema->string()->description('Monster type to filter by (e.g., beast, undead, dragon, fiend, aberration)'),
            'max_challenge_rating' => $schema->number()->description('Maximum challenge rating to filter by'),
        ];
    }
}
