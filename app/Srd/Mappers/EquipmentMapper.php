<?php

namespace App\Srd\Mappers;

use App\Srd\Contracts\SrdMapperContract;

class EquipmentMapper implements SrdMapperContract
{
    /** @var array<string, float> */
    private const GP_CONVERSION = [
        'cp' => 0.01,
        'sp' => 0.1,
        'ep' => 0.5,
        'gp' => 1.0,
        'pp' => 10.0,
    ];

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function map(array $data): array
    {
        $cost = $data['cost'] ?? null;
        $costGp = null;

        if ($cost) {
            $unit = $cost['unit'] ?? 'gp';
            $quantity = $cost['quantity'] ?? 0;
            $costGp = $quantity * (self::GP_CONVERSION[$unit] ?? 1.0);
        }

        return [
            'name' => $data['name'],
            'equipment_category' => $data['equipment_category']['name'] ?? 'Other',
            'weapon_category' => $data['weapon_category'] ?? null,
            'weapon_range' => $data['weapon_range'] ?? null,
            'armor_category' => $data['armor_category'] ?? null,
            'cost_gp' => $costGp,
            'weight' => $data['weight'] ?? null,
            'description' => $this->joinDesc($data['desc'] ?? []),
            'damage' => isset($data['damage']) ? [
                'damage_dice' => $data['damage']['damage_dice'] ?? null,
                'damage_type' => $data['damage']['damage_type']['name'] ?? null,
            ] : null,
            'two_handed_damage' => isset($data['two_handed_damage']) ? [
                'damage_dice' => $data['two_handed_damage']['damage_dice'] ?? null,
                'damage_type' => $data['two_handed_damage']['damage_type']['name'] ?? null,
            ] : null,
            'range' => $data['range'] ?? null,
            'armor_class' => $data['armor_class'] ?? null,
            'properties' => $this->mapReferenceList($data['properties'] ?? []),
            'special' => $data['special'] ?? null,
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, string>
     */
    private function mapReferenceList(array $items): array
    {
        return array_map(fn (array $item) => $item['name'] ?? $item['index'] ?? '', $items);
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
