<?php

namespace App\Ai\Tools;

use App\Models\Campaign;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class LookupLocation implements Tool
{
    public function __construct(
        protected Campaign $campaign,
    ) {}

    public function description(): Stringable|string
    {
        return 'Search for locations in the campaign by name or region. Returns location details including description and sub-locations.';
    }

    public function handle(Request $request): Stringable|string
    {
        $query = $this->campaign->locations()->with('children');

        if ($name = $request->string('name')) {
            $query->where('name', 'like', "%{$name}%");
        }

        if ($region = $request->string('region')) {
            $query->where('region', 'like', "%{$region}%");
        }

        $locations = $query->limit(10)->get();

        if ($locations->isEmpty()) {
            return 'No locations found matching the search criteria.';
        }

        return $locations->map(function ($location) {
            $info = "**{$location->name}**";
            if ($location->region) {
                $info .= " ({$location->region})";
            }
            if ($location->description) {
                $info .= "\n{$location->description}";
            }
            if ($location->children->isNotEmpty()) {
                $info .= "\nSub-locations: ".$location->children->pluck('name')->implode(', ');
            }

            return $info;
        })->implode("\n\n---\n\n");
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()->description('Location name or partial name to search for'),
            'region' => $schema->string()->description('Region name to filter locations by'),
        ];
    }
}
