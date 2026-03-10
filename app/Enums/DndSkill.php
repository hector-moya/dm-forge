<?php

namespace App\Enums;

enum DndSkill: string
{
    // STR
    case Athletics = 'athletics';

    // DEX
    case Acrobatics = 'acrobatics';
    case SleightOfHand = 'sleight_of_hand';
    case Stealth = 'stealth';

    // CON
    case Constitution = 'constitution';

    // INT
    case Arcana = 'arcana';
    case History = 'history';
    case Investigation = 'investigation';
    case Nature = 'nature';
    case Religion = 'religion';

    // WIS
    case AnimalHandling = 'animal_handling';
    case Insight = 'insight';
    case Medicine = 'medicine';
    case Perception = 'perception';
    case Survival = 'survival';

    // CHA
    case Deception = 'deception';
    case Intimidation = 'intimidation';
    case Performance = 'performance';
    case Persuasion = 'persuasion';

    // Raw ability checks
    case Strength = 'strength';
    case Dexterity = 'dexterity';
    case Intelligence = 'intelligence';
    case Wisdom = 'wisdom';
    case Charisma = 'charisma';

    public function label(): string
    {
        return match ($this) {
            self::Athletics => 'Athletics',
            self::Acrobatics => 'Acrobatics',
            self::SleightOfHand => 'Sleight of Hand',
            self::Stealth => 'Stealth',
            self::Constitution => 'Constitution',
            self::Arcana => 'Arcana',
            self::History => 'History',
            self::Investigation => 'Investigation',
            self::Nature => 'Nature',
            self::Religion => 'Religion',
            self::AnimalHandling => 'Animal Handling',
            self::Insight => 'Insight',
            self::Medicine => 'Medicine',
            self::Perception => 'Perception',
            self::Survival => 'Survival',
            self::Deception => 'Deception',
            self::Intimidation => 'Intimidation',
            self::Performance => 'Performance',
            self::Persuasion => 'Persuasion',
            self::Strength => 'Strength (raw)',
            self::Dexterity => 'Dexterity (raw)',
            self::Intelligence => 'Intelligence (raw)',
            self::Wisdom => 'Wisdom (raw)',
            self::Charisma => 'Charisma (raw)',
        };
    }

    public function ability(): string
    {
        return match ($this) {
            self::Athletics, self::Strength => 'STR',
            self::Acrobatics, self::SleightOfHand, self::Stealth, self::Dexterity => 'DEX',
            self::Constitution => 'CON',
            self::Arcana, self::History, self::Investigation, self::Nature, self::Religion, self::Intelligence => 'INT',
            self::AnimalHandling, self::Insight, self::Medicine, self::Perception, self::Survival, self::Wisdom => 'WIS',
            self::Deception, self::Intimidation, self::Performance, self::Persuasion, self::Charisma => 'CHA',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Athletics => 'Climbing, jumping, swimming, grappling, or any feat of raw physical strength.',
            self::Acrobatics => 'Staying on your feet, tumbling, diving, and feats of balance.',
            self::SleightOfHand => 'Pickpocketing, planting items on someone, concealing objects.',
            self::Stealth => 'Moving silently and hiding from notice.',
            self::Constitution => 'Enduring pain, holding breath, resisting exhaustion or harsh environments.',
            self::Arcana => 'Recalling lore about spells, magic items, eldritch symbols, magical traditions, and the planes of existence.',
            self::History => 'Recalling lore about historical events, legendary people, ancient kingdoms, and past disputes.',
            self::Investigation => 'Looking for clues, deducing from evidence, examining an area for hidden things or traps.',
            self::Nature => 'Recalling lore about terrain, plants, animals, weather, and natural cycles.',
            self::Religion => 'Recalling lore about deities, rites, prayers, religious hierarchies, holy symbols, and the undead.',
            self::AnimalHandling => 'Calming a domesticated animal, keeping a mount steady, or intuiting an animal\'s intentions.',
            self::Insight => 'Reading a person\'s true intentions, detecting lies, sensing emotional state.',
            self::Medicine => 'Stabilizing a dying companion, diagnosing an illness, or recalling medical knowledge.',
            self::Perception => 'Spotting, hearing, or otherwise noticing the presence of something (passive or active).',
            self::Survival => 'Tracking creatures, navigating wilderness, predicting weather, avoiding natural hazards.',
            self::Deception => 'Misleading others, maintaining disguises, bluffing, telling convincing lies.',
            self::Intimidation => 'Influencing others through threats, hostile actions, or shows of force.',
            self::Performance => 'Delighting an audience with music, dance, acting, or storytelling.',
            self::Persuasion => 'Diplomatically influencing someone using tact, good nature, and reasoned arguments.',
            self::Strength => 'A raw test of muscle — forcing open a door, bending bars, or feats not covered by Athletics.',
            self::Dexterity => 'A raw test of agility or reflexes not covered by a specific DEX skill.',
            self::Intelligence => 'A raw mental recall or reasoning check not covered by a specific INT skill.',
            self::Wisdom => 'A raw test of awareness or willpower not covered by a specific WIS skill.',
            self::Charisma => 'A raw test of force of personality not covered by a specific CHA skill.',
        };
    }

    public function abilityColor(): string
    {
        return match ($this->ability()) {
            'STR' => 'red',
            'DEX' => 'green',
            'CON' => 'orange',
            'INT' => 'blue',
            'WIS' => 'cyan',
            'CHA' => 'purple',
            default => 'zinc',
        };
    }

    /** @return self[] */
    public static function byAbility(string $ability): array
    {
        return array_filter(self::cases(), fn (self $case) => $case->ability() === $ability);
    }
}
