<?php

namespace App\Srd;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class SrdApiClient
{
    private const BASE_URL = 'https://www.dnd5eapi.co/api/2014';

    private const BATCH_SIZE = 20;

    /**
     * @return array<int, array{index: string, name: string, url: string}>
     */
    public function fetchList(string $endpoint): array
    {
        $response = Http::get(self::BASE_URL.$endpoint);

        if ($response->failed()) {
            return [];
        }

        return $response->json('results', []);
    }

    /**
     * @param  array<int, array{index: string}>  $items
     * @return array<string, array<string, mixed>|null>
     */
    public function fetchBatch(array $items, string $endpoint): array
    {
        $responses = Http::pool(function ($pool) use ($items, $endpoint) {
            foreach ($items as $item) {
                $pool->as($item['index'])->get(self::BASE_URL.$endpoint.'/'.$item['index']);
            }
        });

        $results = [];

        foreach ($items as $item) {
            $response = $responses[$item['index']] ?? null;

            if ($response instanceof Response && $response->successful()) {
                $results[$item['index']] = $response->json();
            } else {
                $results[$item['index']] = null;
            }
        }

        return $results;
    }

    public function batchSize(): int
    {
        return self::BATCH_SIZE;
    }
}
