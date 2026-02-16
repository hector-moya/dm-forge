<?php

namespace App\Services;

use App\Ai\Agents\ImagePromptCrafter;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Laravel\Ai\Image;

class EntityImageGenerator
{
    /**
     * @var array<string, array<string>> Attributes to extract per entity type.
     */
    private const CONTEXT_FIELDS = [
        'monster' => ['name', 'size', 'type', 'subtype', 'alignment', 'notes'],
        'npc' => ['name', 'role', 'description', 'personality'],
        'location' => ['name', 'description', 'region'],
        'faction' => ['name', 'description', 'alignment', 'goals'],
        'scene' => ['title', 'description', 'notes'],
        'loot' => ['name', 'category', 'rarity', 'description'],
    ];

    /**
     * Generate an image for the given entity and persist the path.
     */
    public function generate(Model $entity, string $entityType, ?string $extraContext = null, ?Closure $onProgress = null): ?string
    {
        $onProgress ??= fn () => null;

        try {
            $context = $this->buildContext($entity, $entityType, $extraContext);

            $onProgress('Crafting image prompt...');

            $crafted = (new ImagePromptCrafter)->prompt(
                "Entity type: {$entityType}\n\n{$context}"
            );

            $orientation = $crafted['orientation'] ?? 'square';
            $prompt = $crafted['prompt'];

            $onProgress('Generating image...');

            $image = Image::of($prompt)->{$orientation}()->generate();

            $onProgress('Saving image...');

            $timestamp = time();
            $filename = "images/{$entityType}s/{$entity->getKey()}_{$timestamp}.webp";
            Storage::disk('public')->put($filename, (string) $image);

            $this->deleteOldImage($entity);

            $entity->image_path = $filename;
            $entity->save();

            return $filename;
        } catch (\Throwable $e) {
            Log::error("Image generation failed for {$entityType} #{$entity->getKey()}: {$e->getMessage()}");

            return null;
        }
    }

    private function buildContext(Model $entity, string $entityType, ?string $extraContext): string
    {
        $fields = self::CONTEXT_FIELDS[$entityType] ?? ['name', 'description'];
        $parts = [];

        foreach ($fields as $field) {
            $value = $entity->getAttribute($field);
            if ($value) {
                $label = str_replace('_', ' ', ucfirst($field));
                $parts[] = "{$label}: {$value}";
            }
        }

        $context = implode("\n", $parts);

        if ($extraContext) {
            $context .= "\n\nAdditional context: {$extraContext}";
        }

        return $context;
    }

    private function deleteOldImage(Model $entity): void
    {
        $oldPath = $entity->getOriginal('image_path');

        if ($oldPath && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }
    }
}
