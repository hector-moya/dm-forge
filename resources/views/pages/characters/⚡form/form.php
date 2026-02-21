<?php

use App\Models\Campaign;
use App\Models\Character;
use Livewire\Component;

new class extends Component
{
    public Campaign $campaign;

    public ?Character $character = null;

    // Character Info
    public string $name = '';

    public string $player_name = '';

    public string $characterClass = '';

    public string $race = '';

    public string $background = '';

    public int $level = 1;

    public string $alignment_label = '';

    public int $experience_points = 0;

    public string $notes = '';

    // Combat Stats
    public int $hp_max = 10;

    public ?int $hp_current = null;

    public int $armor_class = 10;

    public int $speed = 30;

    public ?int $proficiency_bonus = null;

    // Ability Scores (in stats JSON)
    public array $abilityScores = [
        'str' => 10, 'dex' => 10, 'con' => 10,
        'int' => 10, 'wis' => 10, 'cha' => 10,
    ];

    public array $savingThrowProficiencies = [];

    public string $skillProficiencies = '';

    // Proficiencies & Equipment (in stats JSON)
    public string $otherProficiencies = '';

    public string $languages = '';

    public string $equipment = '';

    public string $featuresTraits = '';

    // Spells (in stats JSON)
    public string $spellcastingAbility = '';

    public ?int $spellSaveDc = null;

    public ?int $spellAttackBonus = null;

    public string $cantrips = '';

    public string $spellsByLevel = '';

    public function mount(Campaign $campaign, ?Character $character = null): void
    {
        abort_unless($campaign->user_id === auth()->id(), 403);

        $this->campaign = $campaign;

        if ($character && $character->exists) {
            abort_unless($character->campaign_id === $campaign->id, 403);

            $this->character = $character;
            $this->name = $character->name;
            $this->player_name = $character->player_name ?? '';
            $this->characterClass = $character->class ?? '';
            $this->race = $character->race ?? '';
            $this->background = $character->background ?? '';
            $this->level = $character->level;
            $this->alignment_label = $character->alignment_label ?? '';
            $this->experience_points = $character->experience_points ?? 0;
            $this->notes = $character->notes ?? '';
            $this->hp_max = $character->hp_max;
            $this->hp_current = $character->hp_current;
            $this->armor_class = $character->armor_class;
            $this->speed = $character->speed ?? 30;
            $this->proficiency_bonus = $character->proficiency_bonus;

            $stats = $character->stats ?? [];
            $this->abilityScores = $stats['ability_scores'] ?? [
                'str' => 10, 'dex' => 10, 'con' => 10,
                'int' => 10, 'wis' => 10, 'cha' => 10,
            ];
            $this->savingThrowProficiencies = $stats['saving_throw_proficiencies'] ?? [];
            $this->skillProficiencies = implode(', ', $stats['skill_proficiencies'] ?? []);
            $this->otherProficiencies = $stats['other_proficiencies'] ?? '';
            $this->languages = $stats['languages'] ?? '';
            $this->equipment = implode("\n", $stats['equipment'] ?? []);
            $this->featuresTraits = $this->formatFeaturesTraits($stats['features_traits'] ?? []);

            $spells = $stats['spells'] ?? [];
            $this->spellcastingAbility = $spells['spellcasting_ability'] ?? '';
            $this->spellSaveDc = $spells['spell_save_dc'] ?? null;
            $this->spellAttackBonus = $spells['spell_attack_bonus'] ?? null;
            $this->cantrips = implode("\n", $spells['cantrips'] ?? []);
            $this->spellsByLevel = $this->formatSpellsByLevel($spells['spells_by_level'] ?? []);
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'player_name' => ['nullable', 'string', 'max:255'],
            'characterClass' => ['nullable', 'string', 'max:255'],
            'race' => ['nullable', 'string', 'max:100'],
            'background' => ['nullable', 'string', 'max:100'],
            'level' => ['required', 'integer', 'min:1', 'max:30'],
            'alignment_label' => ['nullable', 'string', 'max:50'],
            'experience_points' => ['integer', 'min:0'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'hp_max' => ['required', 'integer', 'min:1'],
            'hp_current' => ['nullable', 'integer', 'min:0'],
            'armor_class' => ['required', 'integer', 'min:1'],
            'speed' => ['integer', 'min:0'],
            'proficiency_bonus' => ['nullable', 'integer', 'min:1', 'max:9'],
            'abilityScores.str' => ['integer', 'min:1', 'max:30'],
            'abilityScores.dex' => ['integer', 'min:1', 'max:30'],
            'abilityScores.con' => ['integer', 'min:1', 'max:30'],
            'abilityScores.int' => ['integer', 'min:1', 'max:30'],
            'abilityScores.wis' => ['integer', 'min:1', 'max:30'],
            'abilityScores.cha' => ['integer', 'min:1', 'max:30'],
            'spellSaveDc' => ['nullable', 'integer', 'min:1', 'max:30'],
            'spellAttackBonus' => ['nullable', 'integer'],
        ];
    }

    public function save(): void
    {
        $this->validate();

        $stats = [
            'ability_scores' => array_map('intval', $this->abilityScores),
            'saving_throw_proficiencies' => $this->savingThrowProficiencies,
            'skill_proficiencies' => $this->parseCommaSeparated($this->skillProficiencies),
            'other_proficiencies' => $this->otherProficiencies ?: null,
            'languages' => $this->languages ?: null,
            'equipment' => array_values(array_filter(array_map('trim', explode("\n", $this->equipment)))),
            'features_traits' => $this->parseFeaturesTraits($this->featuresTraits),
            'spells' => [
                'spellcasting_ability' => $this->spellcastingAbility ?: null,
                'spell_save_dc' => $this->spellSaveDc,
                'spell_attack_bonus' => $this->spellAttackBonus,
                'cantrips' => array_values(array_filter(array_map('trim', explode("\n", $this->cantrips)))),
                'spells_by_level' => $this->parseSpellsByLevel($this->spellsByLevel),
            ],
        ];

        $data = [
            'name' => $this->name,
            'player_name' => $this->player_name ?: null,
            'class' => $this->characterClass ?: null,
            'race' => $this->race ?: null,
            'background' => $this->background ?: null,
            'level' => $this->level,
            'alignment_label' => $this->alignment_label ?: null,
            'experience_points' => $this->experience_points,
            'notes' => $this->notes ?: null,
            'hp_max' => $this->hp_max,
            'hp_current' => $this->hp_current ?? $this->hp_max,
            'armor_class' => $this->armor_class,
            'speed' => $this->speed,
            'proficiency_bonus' => $this->proficiency_bonus,
            'stats' => $stats,
        ];

        if ($this->character) {
            $this->character->update($data);
            session()->flash('message', 'Character updated.');
        } else {
            $this->character = $this->campaign->characters()->create($data);
            session()->flash('message', 'Character created.');
        }

        $this->redirect(route('campaigns.characters', $this->campaign), navigate: true);
    }

    public function deleteCharacter(): void
    {
        if ($this->character) {
            $this->character->delete();
            session()->flash('message', 'Character deleted.');
        }

        $this->redirect(route('campaigns.characters', $this->campaign), navigate: true);
    }

    private function parseCommaSeparated(string $text): array
    {
        if (trim($text) === '') {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode(',', $text))));
    }

    private function parseFeaturesTraits(string $text): array
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

    private function formatFeaturesTraits(array $items): string
    {
        return implode("\n", array_map(
            fn (array $item) => ($item['name'] ?? '').(isset($item['description']) && $item['description'] !== '' ? ': '.$item['description'] : ''),
            $items
        ));
    }

    private function parseSpellsByLevel(string $text): array
    {
        if (trim($text) === '') {
            return [];
        }

        $result = [];
        foreach (explode("\n", $text) as $line) {
            $line = trim($line);
            if ($line === '' || ! str_contains($line, ':')) {
                continue;
            }

            [$level, $spells] = explode(':', $line, 2);
            $level = trim($level);
            $spellList = array_values(array_filter(array_map('trim', explode(',', $spells))));
            if ($level !== '' && ! empty($spellList)) {
                $result[$level] = $spellList;
            }
        }

        return $result;
    }

    private function formatSpellsByLevel(array $spellsByLevel): string
    {
        $lines = [];
        foreach ($spellsByLevel as $level => $spells) {
            if (is_array($spells)) {
                $lines[] = $level.': '.implode(', ', $spells);
            }
        }

        return implode("\n", $lines);
    }

    public function render(): \Illuminate\View\View
    {
        $title = ($this->character?->exists ? __('Edit').' '.$this->character->name : __('New Character')).' — '.$this->campaign->name;

        return view('pages.characters.⚡form.form')
            ->title($title);
    }
};
