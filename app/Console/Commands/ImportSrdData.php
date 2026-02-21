<?php

namespace App\Console\Commands;

use App\Models\SrdEquipment;
use App\Models\SrdMagicItem;
use App\Models\SrdMonster;
use App\Srd\Contracts\SrdMapperContract;
use App\Srd\Mappers\EquipmentMapper;
use App\Srd\Mappers\MagicItemMapper;
use App\Srd\Mappers\MonsterMapper;
use App\Srd\SrdApiClient;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;

class ImportSrdData extends Command
{
    protected $signature = 'srd:import
        {--monsters-only : Only import monsters}
        {--equipment-only : Only import equipment}
        {--magic-items-only : Only import magic items}
        {--fresh : Truncate tables before importing}';

    protected $description = 'Import D&D 5e SRD data from dnd5eapi.co';

    public function __construct(
        private readonly SrdApiClient $client,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $importAll = ! $this->option('monsters-only')
            && ! $this->option('equipment-only')
            && ! $this->option('magic-items-only');

        if ($importAll || $this->option('monsters-only')) {
            $this->importEntities('monsters', '/monsters', new MonsterMapper, SrdMonster::class);
        }

        if ($importAll || $this->option('equipment-only')) {
            $this->importEntities('equipment', '/equipment', new EquipmentMapper, SrdEquipment::class);
        }

        if ($importAll || $this->option('magic-items-only')) {
            $this->importEntities('magic items', '/magic-items', new MagicItemMapper, SrdMagicItem::class);
        }

        $this->info('SRD import complete!');

        return self::SUCCESS;
    }

    /**
     * @param  class-string<Model>  $modelClass
     */
    private function importEntities(
        string $label,
        string $endpoint,
        SrdMapperContract $mapper,
        string $modelClass,
    ): void {
        $this->info("Importing {$label}...");

        if ($this->option('fresh')) {
            $modelClass::query()->truncate();
        }

        $list = $this->client->fetchList($endpoint);

        $bar = $this->output->createProgressBar(\count($list));
        $bar->start();

        foreach (array_chunk($list, $this->client->batchSize()) as $chunk) {
            $details = $this->client->fetchBatch($chunk, $endpoint);

            foreach ($details as $data) {
                if (! $data) {
                    $bar->advance();

                    continue;
                }

                $modelClass::query()->updateOrCreate(
                    ['index' => $data['index']],
                    $mapper->map($data),
                );

                $bar->advance();
            }
        }

        $bar->finish();
        $this->newLine();
        $this->info('Imported '.$modelClass::query()->count()." {$label}.");
    }
}
