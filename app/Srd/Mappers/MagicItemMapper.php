<?php

namespace App\Srd\Mappers;

use App\Srd\Contracts\SrdMapperContract;

class MagicItemMapper implements SrdMapperContract
{
    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function map(array $data): array
    {
        return [
            'name' => $data['name'],
            'equipment_category' => $data['equipment_category']['name'] ?? 'Other',
            'rarity' => $data['rarity']['name'] ?? 'Common',
            'description' => $this->joinDesc($data['desc'] ?? []),
            'variant' => $data['variant'] ?? false,
            'image_url' => $data['image'] ?? null,
        ];
    }

    /**
     * @param  array<int, string>  $desc
     */
    private function joinDesc(array $desc): ?string
    {
        $joined = implode("\n\n", $desc);

        return $joined !== '' ? $joined : null;
    }
}
