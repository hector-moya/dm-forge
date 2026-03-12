<?php

namespace App\Livewire\Forms;

use App\Models\Campaign;
use App\Models\Npc;
use Livewire\Attributes\Validate;
use Livewire\Form;

class NpcForm extends Form
{
    // Narrative fields
    #[Validate(['required', 'string', 'max:255'])]
    public string $npcName = '';

    #[Validate(['nullable', 'string', 'max:255'])]
    public string $npcRole = '';

    #[Validate(['nullable', 'string', 'max:5000'])]
    public string $npcDescription = '';

    #[Validate(['nullable', 'string', 'max:2000'])]
    public string $npcPersonality = '';

    #[Validate(['nullable', 'string', 'max:2000'])]
    public string $npcMotivation = '';

    #[Validate(['nullable', 'string', 'max:5000'])]
    public string $npcBackstory = '';

    #[Validate(['nullable', 'string', 'max:2000'])]
    public string $npcVoiceDescription = '';

    #[Validate(['nullable', 'string', 'max:2000'])]
    public string $npcSpeechPatterns = '';

    #[Validate(['nullable', 'string', 'max:2000'])]
    public string $npcCatchphrases = '';

    #[Validate(['nullable', 'exists:factions,id'])]
    public ?int $npcFactionId = null;

    #[Validate(['nullable', 'exists:locations,id'])]
    public ?int $npcLocationId = null;

    #[Validate(['boolean'])]
    public bool $npcIsAlive = true;

    // Stat block identity
    #[Validate(['nullable', 'string', 'max:100'])]
    public string $npcRace = '';

    #[Validate(['nullable', 'string', 'max:50'])]
    public string $npcSize = '';

    #[Validate(['nullable', 'string', 'max:100'])]
    public string $npcAlignment = '';

    // Combat stats
    #[Validate(['nullable', 'integer', 'min:0', 'max:30'])]
    public ?int $npcArmorClass = null;

    #[Validate(['nullable', 'string', 'max:100'])]
    public string $npcArmorType = '';

    #[Validate(['nullable', 'integer', 'min:1', 'max:9999'])]
    public ?int $npcHpMax = null;

    #[Validate(['nullable', 'string', 'max:50'])]
    public string $npcHitDice = '';

    #[Validate(['nullable', 'string', 'max:100'])]
    public string $npcSpeed = '';

    #[Validate(['nullable', 'string', 'max:10'])]
    public string $npcChallengeRating = '';

    // Ability scores — per-key validation handled in buildData(); array-level #[Validate] would apply to the whole array
    public array $npcAbilityScores = [
        'str' => 10, 'dex' => 10, 'con' => 10,
        'int' => 10, 'wis' => 10, 'cha' => 10,
    ];

    // Proficiencies
    public array $npcSavingThrowProficiencies = [];

    #[Validate(['nullable', 'string'])]
    public string $npcSkillProficiencies = '';

    // Defenses
    #[Validate(['nullable', 'string'])]
    public string $npcDamageResistances = '';

    #[Validate(['nullable', 'string'])]
    public string $npcDamageImmunities = '';

    #[Validate(['nullable', 'string'])]
    public string $npcConditionImmunities = '';

    // Senses and languages
    #[Validate(['nullable', 'string'])]
    public string $npcSenses = '';

    #[Validate(['nullable', 'string'])]
    public string $npcLanguages = '';

    // Actions and traits (text area format: "Name: Description" per line)
    #[Validate(['nullable', 'string'])]
    public string $npcSpecialTraits = '';

    #[Validate(['nullable', 'string'])]
    public string $npcActions = '';

    #[Validate(['nullable', 'string'])]
    public string $npcBonusActions = '';

    #[Validate(['nullable', 'string'])]
    public string $npcReactions = '';

    #[Validate(['nullable', 'string'])]
    public string $npcLegendaryActions = '';

    // Spellcasting
    #[Validate(['nullable', 'string'])]
    public string $npcSpellcastingAbility = '';

    #[Validate(['nullable', 'integer'])]
    public ?int $npcSpellSaveDc = null;

    #[Validate(['nullable', 'integer'])]
    public ?int $npcSpellAttackBonus = null;

    #[Validate(['nullable', 'string'])]
    public string $npcCantrips = '';

    public function setNpc(Npc $npc): void
    {
        // Narrative
        $this->npcName = $npc->name;
        $this->npcRole = $npc->role ?? '';
        $this->npcDescription = $npc->description ?? '';
        $this->npcPersonality = $npc->personality ?? '';
        $this->npcMotivation = $npc->motivation ?? '';
        $this->npcBackstory = $npc->backstory ?? '';
        $this->npcVoiceDescription = $npc->voice_description ?? '';
        $this->npcSpeechPatterns = $npc->speech_patterns ?? '';
        $this->npcCatchphrases = $npc->catchphrases ? implode("\n", $npc->catchphrases) : '';
        $this->npcFactionId = $npc->faction_id;
        $this->npcLocationId = $npc->location_id;
        $this->npcIsAlive = $npc->is_alive;

        // Stat block identity
        $this->npcRace = $npc->race ?? '';
        $this->npcSize = $npc->size ?? '';
        $this->npcAlignment = $npc->alignment ?? '';

        // Combat stats
        $this->npcArmorClass = $npc->armor_class;
        $this->npcArmorType = $npc->armor_type ?? '';
        $this->npcHpMax = $npc->hp_max;
        $this->npcHitDice = $npc->hit_dice ?? '';
        $this->npcSpeed = $npc->speed ?? '';
        $this->npcChallengeRating = $npc->challenge_rating ?? '';

        // Stats JSON
        $stats = $npc->stats ?? [];
        $this->npcAbilityScores = $stats['ability_scores'] ?? [
            'str' => 10, 'dex' => 10, 'con' => 10,
            'int' => 10, 'wis' => 10, 'cha' => 10,
        ];
        $this->npcSavingThrowProficiencies = $stats['saving_throw_proficiencies'] ?? [];
        $this->npcSkillProficiencies = implode(', ', $stats['skill_proficiencies'] ?? []);
        $this->npcDamageResistances = implode(', ', $stats['damage_resistances'] ?? []);
        $this->npcDamageImmunities = implode(', ', $stats['damage_immunities'] ?? []);
        $this->npcConditionImmunities = implode(', ', $stats['condition_immunities'] ?? []);
        $this->npcSenses = $stats['senses'] ?? '';
        $this->npcLanguages = $stats['languages'] ?? '';
        $this->npcSpecialTraits = $this->formatNameDescriptionList($stats['special_traits'] ?? []);
        $this->npcActions = $this->formatNameDescriptionList($stats['actions'] ?? []);
        $this->npcBonusActions = $this->formatNameDescriptionList($stats['bonus_actions'] ?? []);
        $this->npcReactions = $this->formatNameDescriptionList($stats['reactions'] ?? []);
        $this->npcLegendaryActions = $this->formatNameDescriptionList($stats['legendary_actions'] ?? []);

        $spellcasting = $stats['spellcasting'] ?? null;
        $this->npcSpellcastingAbility = $spellcasting['ability'] ?? '';
        $this->npcSpellSaveDc = $spellcasting['spell_save_dc'] ?? null;
        $this->npcSpellAttackBonus = $spellcasting['attack_bonus'] ?? null;
        $this->npcCantrips = isset($spellcasting['cantrips']) ? implode("\n", $spellcasting['cantrips']) : '';
    }

    public function store(Campaign $campaign): Npc
    {
        $this->validate();

        /** @var Npc $npc */
        $npc = $campaign->npcs()->create($this->buildData());

        $this->resetForm();

        return $npc;
    }

    public function update(Npc $npc): void
    {
        $this->validate();

        $npc->update($this->buildData());

        $this->resetForm();
    }

    public function destroy(Npc $npc): void
    {
        $npc->delete();
    }

    public function resetForm(): void
    {
        $this->npcName = '';
        $this->npcRole = '';
        $this->npcDescription = '';
        $this->npcPersonality = '';
        $this->npcMotivation = '';
        $this->npcBackstory = '';
        $this->npcVoiceDescription = '';
        $this->npcSpeechPatterns = '';
        $this->npcCatchphrases = '';
        $this->npcFactionId = null;
        $this->npcLocationId = null;
        $this->npcIsAlive = true;
        $this->npcRace = '';
        $this->npcSize = '';
        $this->npcAlignment = '';
        $this->npcArmorClass = null;
        $this->npcArmorType = '';
        $this->npcHpMax = null;
        $this->npcHitDice = '';
        $this->npcSpeed = '';
        $this->npcChallengeRating = '';
        $this->npcAbilityScores = [
            'str' => 10, 'dex' => 10, 'con' => 10,
            'int' => 10, 'wis' => 10, 'cha' => 10,
        ];
        $this->npcSavingThrowProficiencies = [];
        $this->npcSkillProficiencies = '';
        $this->npcDamageResistances = '';
        $this->npcDamageImmunities = '';
        $this->npcConditionImmunities = '';
        $this->npcSenses = '';
        $this->npcLanguages = '';
        $this->npcSpecialTraits = '';
        $this->npcActions = '';
        $this->npcBonusActions = '';
        $this->npcReactions = '';
        $this->npcLegendaryActions = '';
        $this->npcSpellcastingAbility = '';
        $this->npcSpellSaveDc = null;
        $this->npcSpellAttackBonus = null;
        $this->npcCantrips = '';
    }

    /** Parse "Name: Description" lines into [{name, description}] array. */
    protected function parseNameDescriptionList(string $text): array
    {
        if (trim($text) === '') {
            return [];
        }

        $result = [];
        foreach (explode("\n", $text) as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            $colonPos = strpos($line, ':');
            if ($colonPos !== false) {
                $result[] = [
                    'name' => trim(substr($line, 0, $colonPos)),
                    'description' => trim(substr($line, $colonPos + 1)),
                ];
            } else {
                $result[] = ['name' => $line, 'description' => ''];
            }
        }

        return $result;
    }

    /** Format [{name, description}] array back into "Name: Description" lines. */
    public function formatNameDescriptionList(array $items): string
    {
        return implode("\n", array_map(
            fn (array $item) => ($item['name'] ?? '').(isset($item['description']) && $item['description'] !== '' ? ': '.$item['description'] : ''),
            $items
        ));
    }

    /** Parse comma-separated string into an array of trimmed non-empty strings. */
    protected function parseCommaSeparated(string $text): array
    {
        if (trim($text) === '') {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode(',', $text))));
    }

    private function buildData(): array
    {
        $catchphrases = $this->npcCatchphrases
            ? array_values(array_filter(array_map('trim', explode("\n", $this->npcCatchphrases))))
            : null;

        $spellcasting = null;
        if ($this->npcSpellcastingAbility) {
            $spellcasting = [
                'ability' => $this->npcSpellcastingAbility,
                'spell_save_dc' => $this->npcSpellSaveDc,
                'attack_bonus' => $this->npcSpellAttackBonus,
                'cantrips' => $this->npcCantrips
                    ? array_values(array_filter(array_map('trim', explode("\n", $this->npcCantrips))))
                    : [],
                'spells_by_level' => [],
            ];
        }

        $stats = [
            'ability_scores' => array_map('intval', $this->npcAbilityScores),
            'saving_throw_proficiencies' => $this->npcSavingThrowProficiencies,
            'skill_proficiencies' => $this->parseCommaSeparated($this->npcSkillProficiencies),
            'damage_resistances' => $this->parseCommaSeparated($this->npcDamageResistances),
            'damage_immunities' => $this->parseCommaSeparated($this->npcDamageImmunities),
            'condition_immunities' => $this->parseCommaSeparated($this->npcConditionImmunities),
            'senses' => $this->npcSenses ?: null,
            'languages' => $this->npcLanguages ?: null,
            'special_traits' => $this->parseNameDescriptionList($this->npcSpecialTraits),
            'actions' => $this->parseNameDescriptionList($this->npcActions),
            'bonus_actions' => $this->parseNameDescriptionList($this->npcBonusActions),
            'reactions' => $this->parseNameDescriptionList($this->npcReactions),
            'legendary_actions' => $this->parseNameDescriptionList($this->npcLegendaryActions),
            'spellcasting' => $spellcasting,
        ];

        return [
            'name' => $this->npcName,
            'role' => $this->npcRole ?: null,
            'description' => $this->npcDescription ?: null,
            'personality' => $this->npcPersonality ?: null,
            'motivation' => $this->npcMotivation ?: null,
            'backstory' => $this->npcBackstory ?: null,
            'voice_description' => $this->npcVoiceDescription ?: null,
            'speech_patterns' => $this->npcSpeechPatterns ?: null,
            'catchphrases' => $catchphrases,
            'faction_id' => $this->npcFactionId,
            'location_id' => $this->npcLocationId,
            'is_alive' => $this->npcIsAlive,
            'race' => $this->npcRace ?: null,
            'size' => $this->npcSize ?: null,
            'alignment' => $this->npcAlignment ?: null,
            'armor_class' => $this->npcArmorClass,
            'armor_type' => $this->npcArmorType ?: null,
            'hp_max' => $this->npcHpMax,
            'hit_dice' => $this->npcHitDice ?: null,
            'speed' => $this->npcSpeed ?: null,
            'challenge_rating' => $this->npcChallengeRating ?: null,
            'stats' => $stats,
        ];
    }
}
