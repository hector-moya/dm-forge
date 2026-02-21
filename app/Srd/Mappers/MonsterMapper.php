<?php

namespace App\Srd\Mappers;

use App\Srd\Contracts\SrdMapperContract;

class MonsterMapper implements SrdMapperContract
{
    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function map(array $data): array
    {
        $ac = $data['armor_class'][0] ?? [];

        return [
            'name' => $data['name'],
            'size' => $data['size'] ?? null,
            'type' => $data['type'] ?? null,
            'subtype' => $data['subtype'] ?? null,
            'alignment' => $data['alignment'] ?? null,
            'armor_class' => $ac['value'] ?? 10,
            'armor_class_type' => $ac['type'] ?? null,
            'hit_points' => $data['hit_points'] ?? 1,
            'hit_dice' => $data['hit_dice'] ?? null,
            'speed' => $data['speed'] ?? null,
            'strength' => $data['strength'] ?? 10,
            'dexterity' => $data['dexterity'] ?? 10,
            'constitution' => $data['constitution'] ?? 10,
            'intelligence' => $data['intelligence'] ?? 10,
            'wisdom' => $data['wisdom'] ?? 10,
            'charisma' => $data['charisma'] ?? 10,
            'proficiencies' => $this->mapProficiencies($data['proficiencies'] ?? []),
            'damage_vulnerabilities' => $data['damage_vulnerabilities'] ?? [],
            'damage_resistances' => $data['damage_resistances'] ?? [],
            'damage_immunities' => $data['damage_immunities'] ?? [],
            'condition_immunities' => $this->mapReferenceList($data['condition_immunities'] ?? []),
            'senses' => $data['senses'] ?? null,
            'languages' => $data['languages'] ?? null,
            'challenge_rating' => $data['challenge_rating'] ?? 0,
            'xp' => $data['xp'] ?? 0,
            'special_abilities' => $this->mapAbilities($data['special_abilities'] ?? []),
            'actions' => $this->mapAbilities($data['actions'] ?? []),
            'legendary_actions' => $this->mapAbilities($data['legendary_actions'] ?? []),
            'reactions' => $this->mapAbilities($data['reactions'] ?? []),
            'image_url' => $data['image'] ?? null,
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $proficiencies
     * @return array<int, array{name: string, value: int}>
     */
    private function mapProficiencies(array $proficiencies): array
    {
        return array_map(fn (array $p) => [
            'name' => $p['proficiency']['name'] ?? '',
            'value' => $p['value'] ?? 0,
        ], $proficiencies);
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
     * @param  array<int, array<string, mixed>>  $abilities
     * @return array<int, array{name: string, desc: string}>
     */
    private function mapAbilities(array $abilities): array
    {
        return array_map(fn (array $ability) => [
            'name' => $ability['name'] ?? '',
            'desc' => $ability['desc'] ?? '',
        ], $abilities);
    }
}
