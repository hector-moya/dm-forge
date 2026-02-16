<?php

namespace App\Livewire\Sessions;

use App\Models\Character;
use App\Models\CustomMonster;
use App\Models\Encounter;
use App\Models\EncounterMonster;
use App\Models\EncounterNpc;
use App\Models\GameSession;
use App\Models\Npc;
use App\Models\SrdMonster;
use Livewire\Component;

class CombatTracker extends Component
{
    public GameSession $session;

    public Encounter $encounter;

    public array $combatants = [];

    public int $currentTurnIndex = 0;

    public bool $inCombat = false;

    public ?int $selectedCombatantIndex = null;

    // Add combatant form
    public bool $showAddCombatant = false;

    public string $combatantName = '';

    public int $combatantInitiative = 0;

    public int $combatantHpMax = 10;

    public int $combatantAc = 10;

    // Custom HP adjustment
    public int $customHpAmount = 0;

    /** @var string[] */
    public array $conditionOptions = [
        'blinded', 'charmed', 'deafened', 'frightened', 'grappled',
        'incapacitated', 'invisible', 'paralyzed', 'petrified',
        'poisoned', 'prone', 'restrained', 'stunned', 'unconscious',
    ];

    public function mount(GameSession $session, Encounter $encounter): void
    {
        abort_unless($session->campaign->user_id === auth()->id(), 403);
        abort_unless($encounter->game_session_id === $session->id, 404);

        $this->session = $session;
        $this->encounter = $encounter;

        // Auto-load encounter monsters
        foreach ($encounter->monsters()->get() as $monster) {
            $this->combatants[] = $this->buildMonsterCombatant($monster);
        }

        // Auto-load encounter NPCs
        foreach ($encounter->npcs()->get() as $encounterNpc) {
            $this->combatants[] = $this->buildEncounterNpcCombatant($encounterNpc);
        }

        // Auto-load campaign characters
        foreach ($session->campaign->characters()->get() as $character) {
            $this->combatants[] = $this->buildCharacterCombatant($character);
        }
    }

    // ── Initiative ────────────────────────────────────────────────────

    public function addCharacterToCombat(int $characterId): void
    {
        if (collect($this->combatants)->where('source_type', 'character')->where('source_id', $characterId)->isNotEmpty()) {
            return;
        }

        $character = $this->session->campaign->characters()->findOrFail($characterId);
        $this->combatants[] = $this->buildCharacterCombatant($character);
        $this->sortCombatants();
    }

    public function addNpcToCombat(int $npcId): void
    {
        if (collect($this->combatants)->where('source_type', 'npc')->where('source_id', $npcId)->isNotEmpty()) {
            return;
        }

        $npc = $this->session->campaign->npcs()->findOrFail($npcId);
        $this->combatants[] = [
            'name' => $npc->name,
            'initiative' => 0,
            'hp_max' => 10,
            'hp_current' => 10,
            'armor_class' => 10,
            'conditions' => [],
            'source_type' => 'npc',
            'source_id' => $npc->id,
            'monster_source_type' => null,
            'monster_source_id' => null,
            'is_pc' => false,
        ];
        $this->sortCombatants();
    }

    public function addCustomCombatant(): void
    {
        $this->validate([
            'combatantName' => ['required', 'string', 'max:255'],
            'combatantInitiative' => ['required', 'integer'],
            'combatantHpMax' => ['required', 'integer', 'min:1'],
            'combatantAc' => ['required', 'integer', 'min:1'],
        ]);

        $this->combatants[] = [
            'name' => $this->combatantName,
            'initiative' => $this->combatantInitiative,
            'hp_max' => $this->combatantHpMax,
            'hp_current' => $this->combatantHpMax,
            'armor_class' => $this->combatantAc,
            'conditions' => [],
            'source_type' => 'custom',
            'source_id' => null,
            'monster_source_type' => null,
            'monster_source_id' => null,
            'is_pc' => false,
        ];

        $this->combatantName = '';
        $this->combatantInitiative = 0;
        $this->combatantHpMax = 10;
        $this->combatantAc = 10;
        $this->showAddCombatant = false;
        $this->sortCombatants();
    }

    public function setInitiative(int $index, int $value): void
    {
        if (isset($this->combatants[$index])) {
            $this->combatants[$index]['initiative'] = $value;
            $this->sortCombatants();
        }
    }

    public function removeCombatant(int $index): void
    {
        unset($this->combatants[$index]);
        $this->combatants = array_values($this->combatants);

        if ($this->currentTurnIndex >= count($this->combatants)) {
            $this->currentTurnIndex = 0;
        }

        if ($this->selectedCombatantIndex === $index) {
            $this->selectedCombatantIndex = null;
        }
    }

    public function startCombat(): void
    {
        $this->inCombat = true;
        $this->currentTurnIndex = 0;
        $this->sortCombatants();
    }

    public function nextTurn(): void
    {
        if (empty($this->combatants)) {
            return;
        }

        $this->currentTurnIndex = ($this->currentTurnIndex + 1) % count($this->combatants);
    }

    public function previousTurn(): void
    {
        if (empty($this->combatants)) {
            return;
        }

        $this->currentTurnIndex = ($this->currentTurnIndex - 1 + count($this->combatants)) % count($this->combatants);
    }

    public function endCombat(): void
    {
        $this->inCombat = false;
        $this->currentTurnIndex = 0;
        $this->syncToDb();
    }

    // ── Combat Panel ──────────────────────────────────────────────────

    public function selectCombatant(int $index): void
    {
        $this->selectedCombatantIndex = $index;
        $this->customHpAmount = 0;
    }

    public function adjustHp(int $index, int $amount): void
    {
        if (! isset($this->combatants[$index])) {
            return;
        }

        $current = $this->combatants[$index]['hp_current'] + $amount;
        $this->combatants[$index]['hp_current'] = max(0, min($current, $this->combatants[$index]['hp_max']));
    }

    public function applyCustomHp(int $index): void
    {
        if ($this->customHpAmount !== 0) {
            $this->adjustHp($index, $this->customHpAmount);
            $this->customHpAmount = 0;
        }
    }

    public function healFull(int $index): void
    {
        if (isset($this->combatants[$index])) {
            $this->combatants[$index]['hp_current'] = $this->combatants[$index]['hp_max'];
        }
    }

    public function toggleCondition(int $index, string $condition): void
    {
        if (! isset($this->combatants[$index])) {
            return;
        }

        $conditions = $this->combatants[$index]['conditions'];

        if (in_array($condition, $conditions)) {
            $conditions = array_values(array_diff($conditions, [$condition]));
        } else {
            $conditions[] = $condition;
        }

        $this->combatants[$index]['conditions'] = $conditions;
    }

    // ── Stat Block ────────────────────────────────────────────────────

    /**
     * @return array<string, mixed>|null
     */
    public function getStatBlock(int $index): ?array
    {
        if (! isset($this->combatants[$index])) {
            return null;
        }

        $combatant = $this->combatants[$index];

        if ($combatant['source_type'] === 'monster') {
            return $this->getMonsterStatBlock($combatant);
        }

        if ($combatant['source_type'] === 'character') {
            return $this->getCharacterStatBlock($combatant);
        }

        if ($combatant['source_type'] === 'npc' || $combatant['source_type'] === 'encounter_npc') {
            return $this->getNpcStatBlock($combatant);
        }

        return null;
    }

    // ── Rendering ─────────────────────────────────────────────────────

    public function render()
    {
        $characters = $this->session->campaign->characters()->get();
        $npcs = $this->session->campaign->npcs()->get();

        $statBlock = $this->selectedCombatantIndex !== null
            ? $this->getStatBlock($this->selectedCombatantIndex)
            : null;

        return view('livewire.sessions.combat-tracker', [
            'characters' => $characters,
            'npcs' => $npcs,
            'statBlock' => $statBlock,
        ])->title(__('Combat').' — '.$this->encounter->name);
    }

    // ── Private Helpers ───────────────────────────────────────────────

    /**
     * @return array<string, mixed>
     */
    private function buildMonsterCombatant(EncounterMonster $monster): array
    {
        $monsterSourceType = null;
        $monsterSourceId = null;

        if ($monster->srd_monster_id) {
            $monsterSourceType = 'srd';
            $monsterSourceId = $monster->srd_monster_id;
        } elseif ($monster->custom_monster_id) {
            $monsterSourceType = 'custom';
            $monsterSourceId = $monster->custom_monster_id;
        }

        return [
            'name' => $monster->name,
            'initiative' => $monster->initiative ?? 0,
            'hp_max' => $monster->hp_max,
            'hp_current' => $monster->hp_current ?? $monster->hp_max,
            'armor_class' => $monster->armor_class,
            'conditions' => $monster->conditions ?? [],
            'source_type' => 'monster',
            'source_id' => $monster->id,
            'monster_source_type' => $monsterSourceType,
            'monster_source_id' => $monsterSourceId,
            'is_pc' => false,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildCharacterCombatant(Character $character): array
    {
        return [
            'name' => $character->name,
            'initiative' => 0,
            'hp_max' => $character->hp_max,
            'hp_current' => $character->hp_current ?? $character->hp_max,
            'armor_class' => $character->armor_class,
            'conditions' => [],
            'source_type' => 'character',
            'source_id' => $character->id,
            'monster_source_type' => null,
            'monster_source_id' => null,
            'is_pc' => true,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildEncounterNpcCombatant(EncounterNpc $encounterNpc): array
    {
        return [
            'name' => $encounterNpc->name,
            'initiative' => $encounterNpc->initiative ?? 0,
            'hp_max' => $encounterNpc->hp_max,
            'hp_current' => $encounterNpc->hp_current ?? $encounterNpc->hp_max,
            'armor_class' => $encounterNpc->armor_class,
            'conditions' => $encounterNpc->conditions ?? [],
            'source_type' => 'encounter_npc',
            'source_id' => $encounterNpc->id,
            'monster_source_type' => null,
            'monster_source_id' => null,
            'is_pc' => false,
        ];
    }

    private function sortCombatants(): void
    {
        usort($this->combatants, fn ($a, $b) => $b['initiative'] <=> $a['initiative']);

        // Reset selection since indices shifted
        $this->selectedCombatantIndex = null;
    }

    private function syncToDb(): void
    {
        foreach ($this->combatants as $combatant) {
            if ($combatant['source_type'] === 'monster' && $combatant['source_id']) {
                EncounterMonster::where('id', $combatant['source_id'])->update([
                    'hp_current' => $combatant['hp_current'],
                    'initiative' => $combatant['initiative'],
                    'conditions' => $combatant['conditions'],
                ]);
            }

            if ($combatant['source_type'] === 'encounter_npc' && $combatant['source_id']) {
                EncounterNpc::where('id', $combatant['source_id'])->update([
                    'hp_current' => $combatant['hp_current'],
                    'initiative' => $combatant['initiative'],
                    'conditions' => $combatant['conditions'],
                ]);
            }

            if ($combatant['source_type'] === 'character' && $combatant['source_id']) {
                Character::where('id', $combatant['source_id'])->update([
                    'hp_current' => $combatant['hp_current'],
                ]);
            }
        }
    }

    /**
     * @param  array<string, mixed>  $combatant
     * @return array<string, mixed>|null
     */
    private function getMonsterStatBlock(array $combatant): ?array
    {
        $source = null;

        if ($combatant['monster_source_type'] === 'srd' && $combatant['monster_source_id']) {
            $source = SrdMonster::query()->find($combatant['monster_source_id']);
        } elseif ($combatant['monster_source_type'] === 'custom' && $combatant['monster_source_id']) {
            $source = CustomMonster::query()->find($combatant['monster_source_id']);
        }

        if (! $source) {
            return null;
        }

        return [
            'type' => 'monster',
            'name' => $source->name,
            'size' => $source->size,
            'monster_type' => $source->type,
            'subtype' => $source->subtype ?? null,
            'alignment' => $source->alignment,
            'armor_class' => $source->armor_class,
            'armor_class_type' => $source->armor_class_type ?? null,
            'hit_points' => $source->hit_points,
            'hit_dice' => $source->hit_dice,
            'speed' => $source->speed,
            'strength' => $source->strength ?? 10,
            'dexterity' => $source->dexterity ?? 10,
            'constitution' => $source->constitution ?? 10,
            'intelligence' => $source->intelligence ?? 10,
            'wisdom' => $source->wisdom ?? 10,
            'charisma' => $source->charisma ?? 10,
            'proficiencies' => $source->proficiencies ?? [],
            'damage_vulnerabilities' => $source->damage_vulnerabilities ?? [],
            'damage_resistances' => $source->damage_resistances ?? [],
            'damage_immunities' => $source->damage_immunities ?? [],
            'condition_immunities' => $source->condition_immunities ?? [],
            'senses' => $source->senses,
            'languages' => $source->languages,
            'challenge_rating' => $source->challenge_rating,
            'xp' => $source->xp,
            'special_abilities' => $source->special_abilities ?? [],
            'actions' => $source->actions ?? [],
            'legendary_actions' => $source->legendary_actions ?? [],
            'reactions' => $source->reactions ?? [],
            'image_url' => $source->full_image_url ?? null,
        ];
    }

    /**
     * @param  array<string, mixed>  $combatant
     * @return array<string, mixed>|null
     */
    private function getCharacterStatBlock(array $combatant): ?array
    {
        $character = Character::find($combatant['source_id']);
        if (! $character) {
            return null;
        }

        return [
            'type' => 'character',
            'name' => $character->name,
            'player_name' => $character->player_name,
            'class' => $character->class,
            'level' => $character->level,
            'armor_class' => $character->armor_class,
            'hp_max' => $character->hp_max,
            'stats' => $character->stats,
            'alignment_label' => $character->alignment_label,
            'notes' => $character->notes,
        ];
    }

    /**
     * @param  array<string, mixed>  $combatant
     * @return array<string, mixed>|null
     */
    private function getNpcStatBlock(array $combatant): ?array
    {
        $npc = null;

        if ($combatant['source_type'] === 'encounter_npc') {
            $encounterNpc = EncounterNpc::find($combatant['source_id']);
            $npc = $encounterNpc?->npc;
        } else {
            $npc = Npc::find($combatant['source_id']);
        }

        if (! $npc) {
            return null;
        }

        return [
            'type' => 'npc',
            'name' => $npc->name,
            'role' => $npc->role,
            'description' => $npc->description,
            'personality' => $npc->personality,
            'motivation' => $npc->motivation,
            'stats' => $npc->stats,
            'faction' => $npc->faction?->name ?? null,
            'location' => $npc->location?->name ?? null,
        ];
    }
}
