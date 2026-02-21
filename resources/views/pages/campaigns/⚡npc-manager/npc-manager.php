<?php

use App\Ai\Agents\NpcGenerator;
use App\Models\Campaign;
use App\Models\Npc;
use App\Services\EntityImageGenerator;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public Campaign $campaign;

    public string $search = '';

    public string $factionFilter = '';

    public string $aliveFilter = 'all';

    // Detail flyout
    public ?int $viewingNpcId = null;

    // Inline form
    public bool $showForm = false;

    public ?int $editingNpcId = null;

    // Narrative fields
    public string $npcName = '';

    public string $npcRole = '';

    public string $npcDescription = '';

    public string $npcPersonality = '';

    public string $npcMotivation = '';

    public string $npcBackstory = '';

    public string $npcVoiceDescription = '';

    public string $npcSpeechPatterns = '';

    public string $npcCatchphrases = '';

    public ?int $npcFactionId = null;

    public ?int $npcLocationId = null;

    public bool $npcIsAlive = true;

    // Stat block identity
    public string $npcRace = '';

    public string $npcSize = '';

    public string $npcAlignment = '';

    // Combat stats
    public ?int $npcArmorClass = null;

    public string $npcArmorType = '';

    public ?int $npcHpMax = null;

    public string $npcHitDice = '';

    public string $npcSpeed = '';

    public string $npcChallengeRating = '';

    // Ability scores
    public array $npcAbilityScores = [
        'str' => 10, 'dex' => 10, 'con' => 10,
        'int' => 10, 'wis' => 10, 'cha' => 10,
    ];

    // Proficiencies
    public array $npcSavingThrowProficiencies = [];

    public string $npcSkillProficiencies = '';

    // Defenses
    public string $npcDamageResistances = '';

    public string $npcDamageImmunities = '';

    public string $npcConditionImmunities = '';

    // Senses and languages
    public string $npcSenses = '';

    public string $npcLanguages = '';

    // Actions and traits (text area format: "Name: Description" per line)
    public string $npcSpecialTraits = '';

    public string $npcActions = '';

    public string $npcBonusActions = '';

    public string $npcReactions = '';

    public string $npcLegendaryActions = '';

    // Spellcasting
    public string $npcSpellcastingAbility = '';

    public ?int $npcSpellSaveDc = null;

    public ?int $npcSpellAttackBonus = null;

    public string $npcCantrips = '';

    // Generator
    public bool $showGenerateModal = false;

    public string $generateContext = '';

    public bool $generating = false;

    public bool $generateImageOnCreate = false;

    public bool $pendingImageGeneration = false;

    public function mount(Campaign $campaign): void
    {
        abort_unless($campaign->user_id === auth()->id(), 403);

        $this->campaign = $campaign;
    }

    public function viewNpc(int $id): void
    {
        $this->viewingNpcId = $id;
        $this->modal('view-npc')->show();
    }

    #[Computed]
    public function viewingNpc(): ?Npc
    {
        if (! $this->viewingNpcId) {
            return null;
        }

        return $this->campaign->npcs()
            ->with(['faction', 'location'])
            ->find($this->viewingNpcId);
    }

    // ── CRUD ──────────────────────────────────────────────────────────

    public function openForm(?int $npcId = null): void
    {
        $this->resetForm();
        $this->showForm = true;

        if ($npcId) {
            $npc = $this->campaign->npcs()->findOrFail($npcId);
            $this->editingNpcId = $npc->id;

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
    }

    public function save(): void
    {
        $this->validate([
            'npcName' => ['required', 'string', 'max:255'],
            'npcRole' => ['nullable', 'string', 'max:255'],
            'npcDescription' => ['nullable', 'string', 'max:5000'],
            'npcPersonality' => ['nullable', 'string', 'max:2000'],
            'npcMotivation' => ['nullable', 'string', 'max:2000'],
            'npcBackstory' => ['nullable', 'string', 'max:5000'],
            'npcVoiceDescription' => ['nullable', 'string', 'max:2000'],
            'npcSpeechPatterns' => ['nullable', 'string', 'max:2000'],
            'npcCatchphrases' => ['nullable', 'string', 'max:2000'],
            'npcFactionId' => ['nullable', 'exists:factions,id'],
            'npcLocationId' => ['nullable', 'exists:locations,id'],
            'npcIsAlive' => ['boolean'],
            'npcRace' => ['nullable', 'string', 'max:100'],
            'npcSize' => ['nullable', 'string', 'max:50'],
            'npcAlignment' => ['nullable', 'string', 'max:100'],
            'npcArmorClass' => ['nullable', 'integer', 'min:0', 'max:30'],
            'npcArmorType' => ['nullable', 'string', 'max:100'],
            'npcHpMax' => ['nullable', 'integer', 'min:1', 'max:9999'],
            'npcHitDice' => ['nullable', 'string', 'max:50'],
            'npcSpeed' => ['nullable', 'string', 'max:100'],
            'npcChallengeRating' => ['nullable', 'string', 'max:10'],
            'npcAbilityScores.str' => ['integer', 'min:1', 'max:30'],
            'npcAbilityScores.dex' => ['integer', 'min:1', 'max:30'],
            'npcAbilityScores.con' => ['integer', 'min:1', 'max:30'],
            'npcAbilityScores.int' => ['integer', 'min:1', 'max:30'],
            'npcAbilityScores.wis' => ['integer', 'min:1', 'max:30'],
            'npcAbilityScores.cha' => ['integer', 'min:1', 'max:30'],
        ]);

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
            'skill_proficiencies' => is_array($this->npcSkillProficiencies)
                ? $this->npcSkillProficiencies
                : $this->parseCommaSeparated($this->npcSkillProficiencies),
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

        $data = [
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

        if ($this->editingNpcId) {
            $this->campaign->npcs()->findOrFail($this->editingNpcId)->update($data);
            \Flux::toast(__('NPC updated.'));
        } else {
            $npc = $this->campaign->npcs()->create($data);
            \Flux::toast(__('NPC created.'));

            if ($this->pendingImageGeneration) {
                try {
                    app(EntityImageGenerator::class)->generate(
                        $npc, 'npc', null,
                        fn (string $status) => $this->stream(to: 'imageStatus', content: $status, replace: true),
                    );
                    \Flux::toast(__('Image generated!'));
                } catch (\Throwable) {
                    \Flux::toast(__('NPC saved, but image generation failed.'));
                }
            }
        }

        $this->resetForm();
    }

    public function delete(int $npcId): void
    {
        $this->campaign->npcs()->findOrFail($npcId)->delete();

        if ($this->viewingNpcId === $npcId) {
            $this->viewingNpcId = null;
        }

        \Flux::toast(__('NPC deleted.'));
    }

    private function resetForm(): void
    {
        $this->showForm = false;
        $this->editingNpcId = null;
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
        $this->pendingImageGeneration = false;
        $this->resetValidation();
    }

    // ── Generator ─────────────────────────────────────────────────────

    public function openGenerateModal(): void
    {
        $this->showGenerateModal = true;
        $this->generateContext = '';
        $this->generating = false;
    }

    public function generate(): void
    {
        $this->generating = true;

        try {
            $generator = new NpcGenerator($this->campaign);
            $prompt = 'Generate a unique NPC for this campaign.';
            if ($this->generateContext) {
                $prompt .= " Context: {$this->generateContext}";
            }

            $response = $generator->prompt($prompt);

            $this->showGenerateModal = false;
            $this->resetForm();
            $this->showForm = true;

            // Narrative
            $this->npcName = $response['name'] ?? '';
            $this->npcRole = $response['role'] ?? '';
            $this->npcDescription = $response['description'] ?? '';
            $this->npcPersonality = $response['personality'] ?? '';
            $this->npcMotivation = $response['motivation'] ?? '';
            $this->npcBackstory = $response['backstory'] ?? '';
            $this->npcVoiceDescription = $response['voice_description'] ?? '';
            $this->npcSpeechPatterns = $response['speech_patterns'] ?? '';
            $this->npcCatchphrases = isset($response['catchphrases']) ? implode("\n", $response['catchphrases']) : '';

            // Stat block identity
            $this->npcRace = $response['race'] ?? '';
            $this->npcSize = $response['size'] ?? '';
            $this->npcAlignment = $response['alignment'] ?? '';

            // Combat stats
            $this->npcArmorClass = isset($response['armor_class']) ? (int) $response['armor_class'] : null;
            $this->npcArmorType = $response['armor_type'] ?? '';
            $this->npcHpMax = isset($response['hp_max']) ? (int) $response['hp_max'] : null;
            $this->npcHitDice = $response['hit_dice'] ?? '';
            $this->npcSpeed = $response['speed'] ?? '';
            $this->npcChallengeRating = (string) ($response['challenge_rating'] ?? '');

            // Ability scores
            if (isset($response['ability_scores'])) {
                $this->npcAbilityScores = array_map('intval', $response['ability_scores']);
            }

            // Proficiencies
            $this->npcSavingThrowProficiencies = $response['saving_throw_proficiencies'] ?? [];
            $this->npcSkillProficiencies = implode(', ', $response['skill_proficiencies'] ?? []);

            // Defenses
            $this->npcDamageResistances = implode(', ', $response['damage_resistances'] ?? []);
            $this->npcDamageImmunities = implode(', ', $response['damage_immunities'] ?? []);
            $this->npcConditionImmunities = implode(', ', $response['condition_immunities'] ?? []);

            // Senses and languages
            $this->npcSenses = $response['senses'] ?? '';
            $this->npcLanguages = $response['languages'] ?? '';

            // Actions and traits
            $this->npcSpecialTraits = $this->formatNameDescriptionList($response['special_traits'] ?? []);
            $this->npcActions = $this->formatNameDescriptionList($response['actions'] ?? []);
            $this->npcBonusActions = $this->formatNameDescriptionList($response['bonus_actions'] ?? []);
            $this->npcReactions = $this->formatNameDescriptionList($response['reactions'] ?? []);
            $this->npcLegendaryActions = $this->formatNameDescriptionList($response['legendary_actions'] ?? []);

            // Spellcasting
            $spellcasting = $response['spellcasting'] ?? null;
            $this->npcSpellcastingAbility = $spellcasting['ability'] ?? '';
            $this->npcSpellSaveDc = isset($spellcasting['spell_save_dc']) ? (int) $spellcasting['spell_save_dc'] : null;
            $this->npcSpellAttackBonus = isset($spellcasting['attack_bonus']) ? (int) $spellcasting['attack_bonus'] : null;
            $this->npcCantrips = isset($spellcasting['cantrips']) ? implode("\n", $spellcasting['cantrips']) : '';

            $this->pendingImageGeneration = $this->generateImageOnCreate;

            \Flux::toast(__('NPC generated! Review and save below.'));
        } catch (\Throwable $e) {
            \Flux::toast(__('Generation failed: ').$e->getMessage());
        }

        $this->generating = false;
    }

    // ── Image Generation ──────────────────────────────────────────────

    public function generateImage(int $npcId): void
    {
        $npc = $this->campaign->npcs()->findOrFail($npcId);

        try {
            $path = app(EntityImageGenerator::class)->generate(
                $npc, 'npc', null,
                fn (string $status) => $this->stream(to: 'imageStatus', content: $status, replace: true),
            );

            if ($path) {
                \Flux::toast(__('Image generated!'));
            } else {
                \Flux::toast(__('Image generation failed.'));
            }
        } catch (\Throwable $e) {
            \Flux::toast(__('Image generation failed: ').$e->getMessage());
        }
    }

    // ── Helpers ────────────────────────────────────────────────────────

    /** Parse "Name: Description" lines into [{name, description}] array. */
    private function parseNameDescriptionList(string $text): array
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
    private function formatNameDescriptionList(array $items): string
    {
        return implode("\n", array_map(
            fn (array $item) => ($item['name'] ?? '').(isset($item['description']) && $item['description'] !== '' ? ': '.$item['description'] : ''),
            $items
        ));
    }

    /** Parse comma-separated string into an array of trimmed non-empty strings. */
    private function parseCommaSeparated(string $text): array
    {
        if (trim($text) === '') {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode(',', $text))));
    }

    // ── Render ─────────────────────────────────────────────────────────

    public function getNpcs(): Collection
    {
        $query = $this->campaign->npcs()->with(['faction', 'location']);

        if ($this->search !== '') {
            $query->where('name', 'like', "%{$this->search}%");
        }

        if ($this->factionFilter !== '') {
            $query->where('faction_id', $this->factionFilter);
        }

        if ($this->aliveFilter === 'alive') {
            $query->where('is_alive', true);
        } elseif ($this->aliveFilter === 'dead') {
            $query->where('is_alive', false);
        }

        return $query->orderBy('name')->get();
    }

    public function render(): \Illuminate\View\View
    {
        $viewingNpc = $this->viewingNpc;

        return view('pages.campaigns.⚡npc-manager.npc-manager', [
            'npcs' => $this->getNpcs(),
            'viewingNpc' => $viewingNpc,
            'factions' => $this->campaign->factions()->orderBy('name')->get(),
            'locations' => $this->campaign->locations()->orderBy('name')->get(),
            'history' => $viewingNpc
                ? $viewingNpc->worldEvents()->with(['faction', 'location', 'gameSession'])->orderByDesc('occurred_at')->limit(20)->get()
                : collect(),
        ])->title(__('NPCs').' — '.$this->campaign->name);
    }
};
