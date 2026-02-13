<?php

namespace App\Console\Commands;

use App\Models\SrdEquipment;
use App\Models\SrdMagicItem;
use App\Models\SrdMonster;
use Illuminate\Console\Command;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class ImportSrdData extends Command
{
    protected $signature = 'srd:import
        {--monsters-only : Only import monsters}
        {--equipment-only : Only import equipment}
        {--magic-items-only : Only import magic items}
        {--fresh : Truncate tables before importing}';

    protected $description = 'Import D&D 5e SRD data from dnd5eapi.co';

    private const BASE_URL = 'https://www.dnd5eapi.co/api/2014';

    private const BATCH_SIZE = 20;

    /** @var array<string, float> */
    private const GP_CONVERSION = [
        'cp' => 0.01,
        'sp' => 0.1,
        'ep' => 0.5,
        'gp' => 1.0,
        'pp' => 10.0,
    ];

    public function handle(): int
    {
        $importAll = ! $this->option('monsters-only')
            && ! $this->option('equipment-only')
            && ! $this->option('magic-items-only');

        if ($importAll || $this->option('monsters-only')) {
            $this->importMonsters();
        }

        if ($importAll || $this->option('equipment-only')) {
            $this->importEquipment();
        }

        if ($importAll || $this->option('magic-items-only')) {
            $this->importMagicItems();
        }

        $this->info('SRD import complete!');

        return self::SUCCESS;
    }

    private function importMonsters(): void
    {
        $this->info('Importing monsters...');

        if ($this->option('fresh')) {
            SrdMonster::query()->truncate();
        }

        $list = $this->fetchList('/monsters');

        $bar = $this->output->createProgressBar(count($list));
        $bar->start();

        foreach (array_chunk($list, self::BATCH_SIZE) as $chunk) {
            $details = $this->fetchBatch($chunk, '/monsters');

            foreach ($details as $data) {
                if (! $data) {
                    $bar->advance();

                    continue;
                }

                SrdMonster::query()->updateOrCreate(
                    ['index' => $data['index']],
                    $this->mapMonsterData($data),
                );

                $bar->advance();
            }
        }

        $bar->finish();
        $this->newLine();
        $this->info('Imported '.SrdMonster::query()->count().' monsters.');
    }

    private function importEquipment(): void
    {
        $this->info('Importing equipment...');

        if ($this->option('fresh')) {
            SrdEquipment::query()->truncate();
        }

        $list = $this->fetchList('/equipment');

        $bar = $this->output->createProgressBar(count($list));
        $bar->start();

        foreach (array_chunk($list, self::BATCH_SIZE) as $chunk) {
            $details = $this->fetchBatch($chunk, '/equipment');

            foreach ($details as $data) {
                if (! $data) {
                    $bar->advance();

                    continue;
                }

                SrdEquipment::query()->updateOrCreate(
                    ['index' => $data['index']],
                    $this->mapEquipmentData($data),
                );

                $bar->advance();
            }
        }

        $bar->finish();
        $this->newLine();
        $this->info('Imported '.SrdEquipment::query()->count().' equipment items.');
    }

    private function importMagicItems(): void
    {
        $this->info('Importing magic items...');

        if ($this->option('fresh')) {
            SrdMagicItem::query()->truncate();
        }

        $list = $this->fetchList('/magic-items');

        $bar = $this->output->createProgressBar(count($list));
        $bar->start();

        foreach (array_chunk($list, self::BATCH_SIZE) as $chunk) {
            $details = $this->fetchBatch($chunk, '/magic-items');

            foreach ($details as $data) {
                if (! $data) {
                    $bar->advance();

                    continue;
                }

                SrdMagicItem::query()->updateOrCreate(
                    ['index' => $data['index']],
                    $this->mapMagicItemData($data),
                );

                $bar->advance();
            }
        }

        $bar->finish();
        $this->newLine();
        $this->info('Imported '.SrdMagicItem::query()->count().' magic items.');
    }

    /**
     * @return array<int, array{index: string, name: string, url: string}>
     */
    private function fetchList(string $endpoint): array
    {
        $response = Http::get(self::BASE_URL.$endpoint);

        if ($response->failed()) {
            $this->error("Failed to fetch list from {$endpoint}");

            return [];
        }

        return $response->json('results', []);
    }

    /**
     * @param  array<int, array{index: string}>  $items
     * @return array<string, array<string, mixed>|null>
     */
    private function fetchBatch(array $items, string $endpoint): array
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
                $this->warn("Failed to fetch {$endpoint}/{$item['index']}");
                $results[$item['index']] = null;
            }
        }

        return $results;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function mapMonsterData(array $data): array
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

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function mapEquipmentData(array $data): array
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
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function mapMagicItemData(array $data): array
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
