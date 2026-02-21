<?php

use App\Srd\Mappers\MagicItemMapper;

test('magic item mapper maps standard fields', function () {
    $mapper = new MagicItemMapper;

    $result = $mapper->map([
        'name' => 'Bag of Holding',
        'equipment_category' => ['name' => 'Wondrous Items'],
        'rarity' => ['name' => 'Uncommon'],
        'desc' => [
            'This bag has an interior space considerably larger than its outside dimensions.',
            'The bag can hold up to 500 pounds.',
        ],
        'variant' => false,
        'image' => '/api/images/magic-items/bag-of-holding.png',
    ]);

    expect($result['name'])->toBe('Bag of Holding')
        ->and($result['equipment_category'])->toBe('Wondrous Items')
        ->and($result['rarity'])->toBe('Uncommon')
        ->and($result['description'])->toContain('500 pounds')
        ->and($result['variant'])->toBeFalse()
        ->and($result['image_url'])->toBe('/api/images/magic-items/bag-of-holding.png');
});

test('magic item mapper applies defaults for missing rarity and category', function () {
    $mapper = new MagicItemMapper;

    $result = $mapper->map(['name' => 'Mystery Item', 'desc' => []]);

    expect($result['equipment_category'])->toBe('Other')
        ->and($result['rarity'])->toBe('Common')
        ->and($result['description'])->toBeNull();
});

test('magic item mapper joins description paragraphs', function () {
    $mapper = new MagicItemMapper;

    $result = $mapper->map([
        'name' => 'Wand of Magic Missiles',
        'desc' => ['First line.', 'Second line.'],
    ]);

    expect($result['description'])->toBe("First line.\n\nSecond line.");
});
