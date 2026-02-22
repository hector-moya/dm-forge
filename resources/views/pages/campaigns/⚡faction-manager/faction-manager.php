<?php

use App\Ai\Agents\FactionGenerator;
use App\Livewire\Forms\FactionForm;
use App\Models\Campaign;
use App\Models\Faction;
use App\Services\EntityImageGenerator;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public FactionForm $form;

    public Campaign $campaign;

    public string $search = '';

    // Detail flyout
    public ?int $viewingFactionId = null;

    // Inline form
    public bool $showForm = false;

    public ?int $editingFactionId = null;

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

    public function viewFaction(int $id): void
    {
        $this->viewingFactionId = $id;
        $this->modal('view-faction')->show();
    }

    #[Computed]
    public function viewingFaction(): ?Faction
    {
        if (! $this->viewingFactionId) {
            return null;
        }

        return $this->campaign->factions()
            ->withCount('npcs')
            ->find($this->viewingFactionId);
    }

    // ── CRUD ──────────────────────────────────────────────────────────

    public function openForm(?int $factionId = null): void
    {
        $this->resetForm();
        $this->showForm = true;

        if ($factionId) {
            $faction = $this->campaign->factions()->findOrFail($factionId);
            $this->editingFactionId = $faction->id;
            $this->form->setFaction($faction);
        }
    }

    public function save(): void
    {
        if ($this->editingFactionId) {
            $faction = $this->campaign->factions()->findOrFail($this->editingFactionId);
            $this->form->update($faction);
            \Flux::toast(__('Faction updated.'));
        } else {
            $faction = $this->form->store($this->campaign);
            \Flux::toast(__('Faction created.'));

            if ($this->pendingImageGeneration) {
                try {
                    app(EntityImageGenerator::class)->generate(
                        $faction, 'faction', null,
                        fn (string $status) => $this->stream(to: 'imageStatus', content: $status, replace: true),
                    );
                    \Flux::toast(__('Image generated!'));
                } catch (\Throwable) {
                    \Flux::toast(__('Faction saved, but image generation failed.'));
                }
            }
        }

        $this->resetForm();
    }

    public function delete(int $factionId): void
    {
        $this->campaign->factions()->findOrFail($factionId)->delete();

        if ($this->viewingFactionId === $factionId) {
            $this->viewingFactionId = null;
        }

        \Flux::toast(__('Faction deleted.'));
    }

    private function resetForm(): void
    {
        $this->showForm = false;
        $this->editingFactionId = null;
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
            $generator = new FactionGenerator($this->campaign);
            $prompt = 'Generate a unique faction for this campaign.';
            if ($this->generateContext) {
                $prompt .= " Context: {$this->generateContext}";
            }

            $response = $generator->prompt($prompt);

            $this->showGenerateModal = false;

            $this->resetForm();
            $this->showForm = true;
            $this->form->name = $response['name'] ?? '';
            $this->form->description = $response['description'] ?? '';
            $this->form->alignment = $response['alignment'] ?? '';
            $this->form->goals = $response['goals'] ?? '';
            $this->form->resources = $response['resources'] ?? '';

            $this->pendingImageGeneration = $this->generateImageOnCreate;

            \Flux::toast(__('Faction generated! Review and save below.'));
        } catch (\Throwable $e) {
            \Flux::toast(__('Generation failed: ').$e->getMessage());
        }

        $this->generating = false;
    }

    // ── Image Generation ──────────────────────────────────────────────

    public function generateImage(int $factionId): void
    {
        $faction = $this->campaign->factions()->findOrFail($factionId);

        try {
            $path = app(EntityImageGenerator::class)->generate(
                $faction, 'faction', null,
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

    public function getFactions(): Collection
    {
        $query = $this->campaign->factions()->withCount('npcs');

        if ($this->search !== '') {
            $query->where('name', 'like', "%{$this->search}%");
        }

        return $query->orderBy('name')->get();
    }

    public function render(): \Illuminate\View\View
    {
        $viewingFaction = $this->viewingFaction;

        return view('pages.campaigns.⚡faction-manager.faction-manager', [
            'factions' => $this->getFactions(),
            'viewingFaction' => $viewingFaction,
            'history' => $viewingFaction
                ? $viewingFaction->worldEvents()->with(['location', 'gameSession'])->orderByDesc('occurred_at')->limit(20)->get()
                : collect(),
        ])->title(__('Factions').' — '.$this->campaign->name);
    }
};
