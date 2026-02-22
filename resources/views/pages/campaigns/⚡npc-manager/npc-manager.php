<?php

use App\Ai\Agents\NpcGenerator;
use App\Livewire\Forms\NpcForm;
use App\Models\Campaign;
use App\Models\Npc;
use App\Services\EntityImageGenerator;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public NpcForm $form;

    public Campaign $campaign;

    public string $search = '';

    public string $factionFilter = '';

    public string $aliveFilter = 'all';

    // Detail flyout
    public ?int $viewingNpcId = null;

    // Inline form
    public bool $showForm = false;

    public ?int $editingNpcId = null;

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
            $this->form->setNpc($npc);
        }
    }

    public function save(): void
    {
        if ($this->editingNpcId) {
            $npc = $this->campaign->npcs()->findOrFail($this->editingNpcId);
            $this->form->update($npc);
            \Flux::toast(__('NPC updated.'));
        } else {
            $npc = $this->form->store($this->campaign);
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
        $this->pendingImageGeneration = false;
        $this->form->resetForm();
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
            $this->form->npcName = $response['name'] ?? '';
            $this->form->npcRole = $response['role'] ?? '';
            $this->form->npcDescription = $response['description'] ?? '';
            $this->form->npcPersonality = $response['personality'] ?? '';
            $this->form->npcMotivation = $response['motivation'] ?? '';
            $this->form->npcBackstory = $response['backstory'] ?? '';
            $this->form->npcVoiceDescription = $response['voice_description'] ?? '';
            $this->form->npcSpeechPatterns = $response['speech_patterns'] ?? '';
            $this->form->npcCatchphrases = isset($response['catchphrases']) ? implode("\n", $response['catchphrases']) : '';

            // Stat block identity
            $this->form->npcRace = $response['race'] ?? '';
            $this->form->npcSize = $response['size'] ?? '';
            $this->form->npcAlignment = $response['alignment'] ?? '';

            // Combat stats
            $this->form->npcArmorClass = isset($response['armor_class']) ? (int) $response['armor_class'] : null;
            $this->form->npcArmorType = $response['armor_type'] ?? '';
            $this->form->npcHpMax = isset($response['hp_max']) ? (int) $response['hp_max'] : null;
            $this->form->npcHitDice = $response['hit_dice'] ?? '';
            $this->form->npcSpeed = $response['speed'] ?? '';
            $this->form->npcChallengeRating = (string) ($response['challenge_rating'] ?? '');

            // Ability scores
            if (isset($response['ability_scores'])) {
                $this->form->npcAbilityScores = array_map('intval', $response['ability_scores']);
            }

            // Proficiencies
            $this->form->npcSavingThrowProficiencies = $response['saving_throw_proficiencies'] ?? [];
            $this->form->npcSkillProficiencies = implode(', ', $response['skill_proficiencies'] ?? []);

            // Defenses
            $this->form->npcDamageResistances = implode(', ', $response['damage_resistances'] ?? []);
            $this->form->npcDamageImmunities = implode(', ', $response['damage_immunities'] ?? []);
            $this->form->npcConditionImmunities = implode(', ', $response['condition_immunities'] ?? []);

            // Senses and languages
            $this->form->npcSenses = $response['senses'] ?? '';
            $this->form->npcLanguages = $response['languages'] ?? '';

            // Actions and traits
            $this->form->npcSpecialTraits = $this->form->formatNameDescriptionList($response['special_traits'] ?? []);
            $this->form->npcActions = $this->form->formatNameDescriptionList($response['actions'] ?? []);
            $this->form->npcBonusActions = $this->form->formatNameDescriptionList($response['bonus_actions'] ?? []);
            $this->form->npcReactions = $this->form->formatNameDescriptionList($response['reactions'] ?? []);
            $this->form->npcLegendaryActions = $this->form->formatNameDescriptionList($response['legendary_actions'] ?? []);

            // Spellcasting
            $spellcasting = $response['spellcasting'] ?? null;
            $this->form->npcSpellcastingAbility = $spellcasting['ability'] ?? '';
            $this->form->npcSpellSaveDc = isset($spellcasting['spell_save_dc']) ? (int) $spellcasting['spell_save_dc'] : null;
            $this->form->npcSpellAttackBonus = isset($spellcasting['attack_bonus']) ? (int) $spellcasting['attack_bonus'] : null;
            $this->form->npcCantrips = isset($spellcasting['cantrips']) ? implode("\n", $spellcasting['cantrips']) : '';

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
