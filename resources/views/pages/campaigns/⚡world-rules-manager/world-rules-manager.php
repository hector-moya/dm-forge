<?php

use App\Livewire\Forms\WorldRuleForm;
use App\Models\Campaign;
use App\Models\WorldRule;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public WorldRuleForm $form;

    public Campaign $campaign;

    public ?int $selectedWorldRuleId = null;

    public string $search = '';

    public function mount(Campaign $campaign): void
    {
        abort_unless($campaign->user_id === auth()->id(), 403);

        $this->campaign = $campaign;
    }

    #[Computed]
    public function campaignWorldRules(): Collection
    {
        return $this->campaign->worldRules()
            ->where('name', 'like', "%{$this->search}%")
            ->get();
    }

    public function setWorldRuleId(?int $id): void
    {
        $this->selectedWorldRuleId = $id;

        if ($this->selectedWorldRuleId) {
            $this->form->setWorldRule(WorldRule::findOrFail($this->selectedWorldRuleId));
        }
    }

    public function save(): void
    {
        if ($this->selectedWorldRuleId) {
            $this->form->update(WorldRule::findOrFail($this->selectedWorldRuleId));
        } else {
            $this->form->store($this->campaign);
        }

        $this->resetSelectedWorldRuleId();
    }

    public function resetSelectedWorldRuleId(): void
    {
        $this->modal('create-world-rule')->close();

        $this->selectedWorldRuleId = null;

        $this->form->resetForm();
    }

    public function openViewWorldRuleModal(int $id): void
    {
        $this->selectedWorldRuleId = $id;
        $this->modal("view-world-rule-{$id}")->show();
    }

    public function render(): \Illuminate\View\View
    {
        return view('pages.campaigns.⚡world-rules-manager.world-rules-manager')
            ->title(__('World Rules').' — '.$this->campaign->name);
    }
};
